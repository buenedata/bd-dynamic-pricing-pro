<?php
function bd_dp_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'bd_dynamic_pricing_rules';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (id INT NOT NULL AUTO_INCREMENT, name VARCHAR(255), rule_type VARCHAR(50), value FLOAT, products TEXT, categories TEXT, start DATETIME, end DATETIME, PRIMARY KEY(id)) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
