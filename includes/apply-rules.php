<?php
add_filter('woocommerce_product_get_sale_price', 'bd_dp_dynamic_sale_price', 10, 2);
add_filter('woocommerce_product_get_price', 'bd_dp_dynamic_main_price', 10, 2);
add_filter('woocommerce_get_price_html', 'bd_dp_price_html_output', 100, 2);
add_action('woocommerce_before_calculate_totals', 'bd_dp_apply_discount_to_cart', 10);

function bd_dp_get_active_rules() {
    global $wpdb;
    $now = current_time('mysql');
    $table = $wpdb->prefix . 'bd_dynamic_pricing_rules';

    return $wpdb->get_results("
        SELECT * FROM $table
        WHERE (start IS NULL OR start <= '$now') AND (end IS NULL OR end >= '$now')
    ");
}

function bd_dp_dynamic_sale_price($sale_price, $product) {
    $product_id = $product->get_id();
    $regular_price = $product->get_regular_price();
    if (!$regular_price) return $sale_price;

    $rules = bd_dp_get_active_rules();

    foreach ($rules as $rule) {
        $product_ids = json_decode($rule->products, true) ?: [];
        $category_ids = json_decode($rule->categories, true) ?: [];

        if (in_array((string)$product_id, $product_ids) || in_array((int)$product_id, $product_ids)) {
            return bd_dp_calculate_discount($regular_price, $rule);
        }

        $product_cats = wc_get_product_term_ids($product_id, 'product_cat');
        if (array_intersect($category_ids, $product_cats)) {
            return bd_dp_calculate_discount($regular_price, $rule);
        }
    }

    return '';
}

function bd_dp_dynamic_main_price($price, $product) {
    $sale_price = $product->get_sale_price();
    return $sale_price !== '' ? $sale_price : $product->get_regular_price();
}

function bd_dp_apply_discount_to_cart($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $price = $product->get_price();
        $product->set_price($price);
    }
}

function bd_dp_calculate_discount($price, $rule) {
    if ($rule->rule_type === 'percent') {
        return round($price * (1 - ($rule->value / 100)), wc_get_price_decimals());
    } elseif ($rule->rule_type === 'fixed') {
        return max(0, $price - $rule->value);
    }
    return $price;
}

function bd_dp_price_html_output($price_html, $product) {
    $regular = floatval($product->get_regular_price());
    $sale = $product->get_sale_price();

    if ($sale !== '' && $sale < $regular) {
        return '<del>' . wc_price($regular) . '</del> <ins>' . wc_price($sale) . '</ins>';
    }

    return '<span class="price">' . wc_price($regular) . '</span>';
}
