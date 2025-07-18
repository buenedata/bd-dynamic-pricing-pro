<?php
/**
 * Plugin Name: BD Dynamic Pricing Pro
 * Plugin URI: https://buenedata.no/produkter/plugins/bd-dynamic-pricing
 * Description: Fullversjon av BD Dynamic Pricing med støtte for kampanjer, rabatter og lisensbasert tilgang. <span style="color: red;"><strong>Kjøp Pro versjon i dag!</strong></span>
 * Version: 1.1
 * Author: Buene Data
 * Author URI: https://buenedata.no
 */


defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-ui.php';
require_once plugin_dir_path(__FILE__) . 'includes/logic.php';

register_activation_hook(__FILE__, 'bd_dp_create_table');

require_once plugin_dir_path(__FILE__) . 'includes/apply-rules.php';

// GitHub Updater integration
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/plugin-update-checker/plugin-update-checker.php';

    if (class_exists('Puc_v5p6_Factory')) {
        $myUpdateChecker = Puc_v5p6_Factory::buildUpdateChecker(
            'https://github.com/buenedata/bd-dynamic-pricing-pro/',
            __FILE__,
            'bd-dynamic-pricing-pro'
        );
        $myUpdateChecker->setBranch('main');
    } else {
        error_log('Puc_v5p6_Factory not found. Plugin update checker not loaded.');
    }
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
