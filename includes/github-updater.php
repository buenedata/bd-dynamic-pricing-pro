<?php
/**
 * GitHub Updater Class
 * Handles automatic updates from GitHub repository
 */

defined('ABSPATH') || exit;

class BD_Dynamic_Pricing_GitHub_Updater {
    
    private $file;
    private $plugin;
    private $basename;
    private $active;
    private $username;
    private $repository;
    private $authorize_token;
    private $github_response;
    
    public function __construct($file, $username, $repository, $access_token = '') {
        $this->file = $file;
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);
        $this->username = $username;
        $this->repository = $repository;
        $this->authorize_token = $access_token;
        
        add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }
    
    /**
     * Get information regarding our plugin from GitHub
     */
    private function get_repository_info() {
        if (is_null($this->github_response)) {
            $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository);
            
            $args = array();
            if (!empty($this->authorize_token)) {
                $args['headers'] = array(
                    'Authorization' => 'token ' . $this->authorize_token
                );
            }
            
            $response = wp_remote_get($request_uri, $args);
            
            if (is_wp_error($response)) {
                return false;
            }
            
            $body = wp_remote_retrieve_body($response);
            $response_code = wp_remote_retrieve_response_code($response);
            
            if ($response_code !== 200) {
                return false;
            }
            
            $response_data = json_decode($body, true);
            
            if (!is_array($response_data) || !isset($response_data['tag_name'])) {
                return false;
            }
            
            $this->github_response = $response_data;
        }
        
        return $this->github_response;
    }
    
    /**
     * Modify the transient to tell WordPress an update is available
     */
    public function modify_transient($transient) {
        if (property_exists($transient, 'checked')) {
            if ($checked = $transient->checked) {
                $repo_info = $this->get_repository_info();
                
                if (!$repo_info) {
                    return $transient;
                }
                
                // Remove 'v' prefix from tag if present
                $remote_version = ltrim($repo_info['tag_name'], 'v');
                $local_version = $checked[$this->basename];
                
                $out_of_date = version_compare($remote_version, $local_version, '>');
                
                if ($out_of_date) {
                    $new_files = $repo_info['zipball_url'];
                    
                    $slug = current(explode('/', $this->basename));
                    
                    $plugin = array(
                        'url' => $this->plugin['PluginURI'],
                        'slug' => $slug,
                        'package' => $new_files,
                        'new_version' => $remote_version
                    );
                    
                    $transient->response[$this->basename] = (object) $plugin;
                }
            }
        }
        
        return $transient;
    }
    
    /**
     * Push in plugin version information to get the update notification
     */
    public function plugin_popup($result, $action, $args) {
        if (!empty($args->slug)) {
            if ($args->slug == get_plugin_data($this->file)['TextDomain']) {
                $this->get_repository_info();
                
                $plugin = array(
                    'name' => $this->plugin['Name'],
                    'slug' => $this->basename,
                    'requires' => '4.0',
                    'tested' => get_bloginfo('version'),
                    'rating' => '100.0',
                    'num_ratings' => '10823',
                    'downloaded' => '14249',
                    'added' => '2021-01-01',
                    'version' => $this->github_response['tag_name'],
                    'author' => $this->plugin['AuthorName'],
                    'author_profile' => $this->plugin['AuthorURI'],
                    'last_updated' => $this->github_response['published_at'],
                    'homepage' => $this->plugin['PluginURI'],
                    'short_description' => $this->plugin['Description'],
                    'sections' => array(
                        'Description' => $this->plugin['Description'],
                        'Updates' => $this->github_response['body'],
                    ),
                    'download_link' => $this->github_response['zipball_url']
                );
                
                return (object) $plugin;
            }
        }
        return $result;
    }
    
    /**
     * Perform additional actions to successfully install our plugin
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;
        
        if ($this->active) {
            activate_plugin($this->basename);
        }
        
        return $result;
    }
}
