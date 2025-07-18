<?php
// Pricing rule application logic here

add_action('wp_ajax_bd_dp_search_products', 'bd_dp_search_products');
function bd_dp_search_products() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json([]);
    }

    $term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    error_log('[BD_DP] AJAX product search term: ' . $term);

    $limit = 20;
    $product_ids = [];

    // 1. Search by product title/content using WP_Query
    $query = new WP_Query([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        's' => $term,
        'fields' => 'ids',
    ]);
    error_log('[BD_DP] WP_Query args: ' . json_encode([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        's' => $term,
        'fields' => 'ids',
    ]));
    if (!empty($query->posts)) {
        $product_ids = array_merge($product_ids, $query->posts);
    }

    // 2. Search by brand taxonomy (e.g., product_brand)
    $brand_tax = 'product_brand';
    if (taxonomy_exists($brand_tax)) {
        $brand_terms = get_terms([
            'taxonomy' => $brand_tax,
            'name__like' => $term,
            'hide_empty' => false,
            'number' => $limit,
        ]);
        error_log('[BD_DP] Brand taxonomy terms found: ' . json_encode($brand_terms));
        if (!is_wp_error($brand_terms) && !empty($brand_terms)) {
            foreach ($brand_terms as $brand_term) {
                $brand_products = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => $limit,
                    'fields' => 'ids',
                    'tax_query' => [
                        [
                            'taxonomy' => $brand_tax,
                            'field' => 'term_id',
                            'terms' => $brand_term->term_id,
                        ]
                    ],
                    'post_status' => 'publish',
                ]);
                $product_ids = array_merge($product_ids, $brand_products);
            }
        }
    }

    // 3. Search by brand attribute (e.g., pa_brand)
    $brand_attr = 'pa_brand';
    global $wpdb;
    $attr_term_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT t.term_id FROM {$wpdb->terms} t
         INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
         WHERE tt.taxonomy = %s AND t.name LIKE %s LIMIT %d",
         $brand_attr, '%' . $wpdb->esc_like($term) . '%', $limit
    ));
    error_log('[BD_DP] Brand attribute term IDs: ' . json_encode($attr_term_ids));
    if (!empty($attr_term_ids)) {
        foreach ($attr_term_ids as $attr_term_id) {
            $attr_products = get_posts([
                'post_type' => 'product',
                'posts_per_page' => $limit,
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => $brand_attr,
                        'field' => 'term_id',
                        'terms' => $attr_term_id,
                    ]
                ],
                'post_status' => 'publish',
            ]);
            $product_ids = array_merge($product_ids, $attr_products);
        }
    }

    // Deduplicate and limit results
    $product_ids = array_unique($product_ids);
    $product_ids = array_slice($product_ids, 0, $limit);

    $results = [];
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $results[] = [
                'id' => $product_id,
                'text' => $product->get_name(),
            ];
        }
    }

    error_log('[BD_DP] Final product IDs: ' . json_encode($product_ids));
    error_log('[BD_DP] Final results: ' . json_encode($results));

    wp_send_json($results);
}

add_action('wp_ajax_bd_dp_search_categories', 'bd_dp_search_categories');
function bd_dp_search_categories() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json([]);
    }

    $term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    $args = [
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'search' => $term,
        'number' => 20,
    ];
    $categories = get_terms($args);
    $results = [];

    foreach ($categories as $category) {
        $results[] = [
            'id' => $category->term_id,
            'text' => $category->name,
        ];
    }

    wp_send_json($results);
}
