<?php
/**
 * Plugin Name: BD Dynamic Pricing Pro
 * Plugin URI: https://github.com/buenedata/bd-dynamic-pricing-pro
 * Description: Fullversjon av BD Dynamic Pricing med støtte for kampanjer, rabatter og lisensbasert tilgang. <span style="color: red;"><strong>Kjøp Pro versjon i dag!</strong></span>
 * Version: 1.3
 * Author: Buene Data
 * Author URI: https://buenedata.no
 * Text Domain: bd-dynamic-pricing-pro
 * Domain Path: /languages
 * GitHub Plugin URI: buenedata/bd-dynamic-pricing-pro
 * Primary Branch: main
 */


defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'bd-menu-helper.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-ui.php';
require_once plugin_dir_path(__FILE__) . 'includes/logic.php';

register_activation_hook(__FILE__, 'bd_dp_create_table');

require_once plugin_dir_path(__FILE__) . 'includes/apply-rules.php';
require_once plugin_dir_path(__FILE__) . 'includes/github-updater.php';

// Initialize GitHub updater
if (is_admin()) {
    new BD_Dynamic_Pricing_GitHub_Updater(__FILE__, 'buenedata', 'bd-dynamic-pricing-pro');
}

add_filter('plugin_row_meta', 'bd_dp_pro_plugin_meta_links', 10, 2);

function bd_dp_pro_plugin_meta_links($links, $file) {
    if (strpos($file, 'bd-dynamic-pricing-pro.php') !== false) {
        $new_links = array(
            '<a href="https://buenedata.no/produkter/plugins/bd-dynamic-pricing-pro" target="_blank"><strong style="color:red;">Kjøp Pro-versjon</strong></a>',
            '<a href="https://buenedata.no" target="_blank">Utviklet av Buene Data</a>',
        );
        $links = array_merge($links, $new_links);
    }
    return $links;
}
