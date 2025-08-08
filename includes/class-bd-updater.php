<?php
/**
 * BD Plugin Updater
 * Håndterer automatisk oppdatering via GitHub
 */

if (!defined('ABSPATH')) {
    exit;
}

class BD_Plugin_Updater {
    private $plugin_file;
    private $github_username;
    private $github_repo;
    private $version;
    private $plugin_slug;
    private $plugin_basename;

    public function __construct($plugin_file, $github_username, $github_repo) {
        $this->plugin_file = $plugin_file;
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;
        $this->plugin_basename = plugin_basename($plugin_file);
        $this->plugin_slug = dirname($this->plugin_basename);
        
        // Get version from plugin header
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data($plugin_file);
        $this->version = $plugin_data['Version'];

        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_pre_download', [$this, 'download_package'], 10, 3);
    }

    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get remote version
        $remote_version = $this->get_remote_version();
        
        if (version_compare($this->version, $remote_version, '<')) {
            $transient->response[$this->plugin_basename] = (object) [
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $remote_version,
                'url' => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'package' => $this->get_download_url($remote_version),
                'tested' => '6.4',
                'requires_php' => '7.4',
                'compatibility' => new stdClass(),
            ];
        }

        return $transient;
    }

    /**
     * Get remote version from GitHub
     */
    private function get_remote_version() {
        $request = wp_remote_get("https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest", [
            'timeout' => 30,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        ]);
        
        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body, true);
            
            if (isset($data['tag_name'])) {
                $version = ltrim($data['tag_name'], 'v');
                error_log("BD Dynamic Pricing Pro: Found remote version {$version}");
                return $version;
            }
        } else {
            if (is_wp_error($request)) {
                error_log("BD Dynamic Pricing Pro Update Error: " . $request->get_error_message());
            } else {
                error_log("BD Dynamic Pricing Pro Update Error: HTTP " . wp_remote_retrieve_response_code($request));
            }
        }

        return $this->version;
    }

    /**
     * Get download URL for specific version
     */
    private function get_download_url($version) {
        return "https://github.com/{$this->github_username}/{$this->github_repo}/releases/download/v{$version}/{$this->github_repo}.zip";
    }

    /**
     * Provide plugin information for update screen
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $request = wp_remote_get("https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest", [
            'timeout' => 30,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        ]);
        
        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body, true);
            
            $result = (object) [
                'name' => $data['name'] ?? $this->plugin_slug,
                'slug' => $this->plugin_slug,
                'version' => ltrim($data['tag_name'] ?? $this->version, 'v'),
                'author' => 'Buene Data',
                'homepage' => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'short_description' => 'BD Plugin fra Buene Data',
                'sections' => [
                    'description' => $data['body'] ?? 'Ingen beskrivelse tilgjengelig.',
                    'changelog' => $data['body'] ?? 'Se GitHub for endringer.',
                ],
                'download_link' => $this->get_download_url(ltrim($data['tag_name'] ?? $this->version, 'v')),
                'requires' => '5.0',
                'tested' => '6.4',
                'requires_php' => '7.4',
                'last_updated' => $data['published_at'] ?? date('Y-m-d'),
            ];
        }

        return $result;
    }

    /**
     * Download package from GitHub
     */
    public function download_package($result, $package, $upgrader) {
        if (strpos($package, 'github.com') === false) {
            return $result;
        }

        // WordPress will handle the download
        return $result;
    }
}