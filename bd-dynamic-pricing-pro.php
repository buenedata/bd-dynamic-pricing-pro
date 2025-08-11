<?php
/**
 * Plugin Name: BD Dynamic Pricing Pro
 * Plugin URI: https://github.com/buenedata/bd-dynamic-pricing-pro
 * Description: Fullversjon av BD Dynamic Pricing med støtte for kampanjer, rabatter og lisensbasert tilgang. <span style="color: red;"><strong>Kjøp Pro versjon i dag!</strong></span>
 * Version: 1.5.2
 * Author: Buene Data
 * Author URI: https://buenedata.no
 * Update URI: https://github.com/buenedata/bd-dynamic-pricing-pro
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * Text Domain: bd-dynamic-pricing-pro
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('BD_DYNAMIC_PRICING_PRO_VERSION', '1.5.2');
define('BD_DYNAMIC_PRICING_PRO_FILE', __FILE__);
define('BD_DYNAMIC_PRICING_PRO_PATH', plugin_dir_path(__FILE__));
define('BD_DYNAMIC_PRICING_PRO_URL', plugin_dir_url(__FILE__));
define('BD_DYNAMIC_PRICING_PRO_BASENAME', plugin_basename(__FILE__));

// Initialize updater
if (is_admin()) {
    require_once BD_DYNAMIC_PRICING_PRO_PATH . 'includes/class-bd-updater.php';
    new BD_Dynamic_Pricing_Pro_Updater(BD_DYNAMIC_PRICING_PRO_FILE, 'buenedata', 'bd-dynamic-pricing-pro');
}

// Load core files
require_once BD_DYNAMIC_PRICING_PRO_PATH . 'includes/database.php';
require_once BD_DYNAMIC_PRICING_PRO_PATH . 'bd-menu-helper.php';
require_once BD_DYNAMIC_PRICING_PRO_PATH . 'includes/admin-ui.php';
require_once BD_DYNAMIC_PRICING_PRO_PATH . 'includes/logic.php';
require_once BD_DYNAMIC_PRICING_PRO_PATH . 'includes/apply-rules.php';

register_activation_hook(__FILE__, 'bd_dp_create_table');

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
