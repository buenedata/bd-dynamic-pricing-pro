<?php
add_action('admin_menu', 'bd_dp_add_admin_menu');
add_action('admin_enqueue_scripts', 'bd_dp_enqueue_admin_scripts');

function bd_dp_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'BD Dynamic Pricing Pro',
        'BD Dynamic Pricing Pro',
        'manage_woocommerce',
        'bd-dynamic-pricing',
        'bd_dp_render_admin_page'
    );
}

function bd_dp_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'bd-dynamic-pricing') !== false) {
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
        wp_enqueue_script('bd_dp_admin_js', plugins_url('../assets/js/admin.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    }
}

function bd_dp_render_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'bd_dynamic_pricing_rules';

    $editing = false;
    $edit_campaign = null;

    if (isset($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
        $wpdb->delete($table, ['id' => $delete_id]);
        echo '<div class="updated"><p>Kampanje slettet!</p></div>';
    }

    if (isset($_GET['edit'])) {
        $editing = true;
        $edit_id = intval($_GET['edit']);
        $edit_campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $edit_id));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['campaign_name'])) {
        $data = [
            'name' => sanitize_text_field($_POST['campaign_name']),
            'rule_type' => sanitize_text_field($_POST['rule_type']),
            'value' => floatval($_POST['value']),
            'products' => json_encode(array_filter(array_map('intval', explode(',', $_POST['products'])))),
            'categories' => json_encode(array_filter(array_map('intval', explode(',', $_POST['categories'])))),
            'start' => !empty($_POST['start']) ? sanitize_text_field($_POST['start']) : null,
            'end' => !empty($_POST['end']) ? sanitize_text_field($_POST['end']) : null,
        ];

        if (!empty($_POST['campaign_id'])) {
            $wpdb->update($table, $data, ['id' => intval($_POST['campaign_id'])]);
            echo '<div class="updated"><p>Kampanje oppdatert!</p></div>';
        } else {
            $wpdb->insert($table, $data);
            echo '<div class="updated"><p>Kampanje lagret!</p></div>';
        }
    }

    $campaigns = $wpdb->get_results("SELECT * FROM $table ORDER BY start DESC");

    ?>
    <div class="wrap">
        <h1>BD Dynamic Pricing Pro</h1>
        <form method="post" action="">
            <h2><?php echo $editing ? 'Rediger kampanje' : 'Opprett ny kampanje'; ?></h2>
            <input type="hidden" name="campaign_id" value="<?php echo $editing ? esc_attr($edit_campaign->id) : ''; ?>" />
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="campaign_name">Navn</label></th>
                    <td><input name="campaign_name" type="text" id="campaign_name" class="regular-text" value="<?php echo $editing ? esc_attr($edit_campaign->name) : ''; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="rule_type">Regeltype</label></th>
                    <td>
                        <select name="rule_type" id="rule_type">
                            <option value="percent" <?php selected($editing && $edit_campaign->rule_type === 'percent'); ?>>Prosentvis rabatt</option>
                            <option value="fixed" <?php selected($editing && $edit_campaign->rule_type === 'fixed'); ?>>Fast rabatt</option>
                            <option value="3for2" <?php selected($editing && $edit_campaign->rule_type === '3for2'); ?>>3 for 2</option>
                            <option value="bulk" <?php selected($editing && $edit_campaign->rule_type === 'bulk'); ?>>Mengderabatt</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="value">Verdi</label></th>
                    <td><input name="value" type="number" step="any" id="value" class="small-text" value="<?php echo $editing ? esc_attr($edit_campaign->value) : ''; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="products">Produkter</label></th>
                    <td>
                        <select name="products" id="products" multiple="multiple" style="width: 100%;" data-placeholder="Velg produkter">
                            <?php
                            if ($editing && $edit_campaign->products) {
                                $ids = explode(',', $edit_campaign->products);
                                foreach ($ids as $id) {
                                    $prod = wc_get_product($id);
                                    if ($prod) {
                                        echo '<option value="' . esc_attr($id) . '" selected>' . esc_html($prod->get_name()) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="categories">Kategorier</label></th>
                    <td>
                        <select name="categories" id="categories" multiple="multiple" style="width: 100%;" data-placeholder="Velg kategorier">
                            <?php
                            if ($editing && $edit_campaign->categories) {
                                $ids = explode(',', $edit_campaign->categories);
                                foreach ($ids as $id) {
                                    $term = get_term($id, 'product_cat');
                                    if ($term && !is_wp_error($term)) {
                                        echo '<option value="' . esc_attr($id) . '" selected>' . esc_html($term->name) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="start">Start</label></th>
                    <td><input name="start" type="text" id="start" value="<?php echo $editing ? esc_attr($edit_campaign->start) : ''; ?>" />
                    <p class="description">Valgfritt. Hvis tomt: gjelder alltid.</p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="end">Slutt</label></th>
                    <td><input name="end" type="text" id="end" value="<?php echo $editing ? esc_attr($edit_campaign->end) : ''; ?>" />
                    <p class="description">Valgfritt. Hvis tomt: gjelder alltid.</p></td>
                </tr>
            </table>
            <?php submit_button($editing ? 'Oppdater kampanje' : 'Lagre kampanje'); ?>
        </form>

        <h2>Eksisterende kampanjer</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Navn</th>
                    <th>Regeltype</th>
                    <th>Verdi</th>
                    <th>Start</th>
                    <th>Slutt</th>
                    <th>Handling</th>
                    <th>Gjelder for</th>
                </tr>
            </thead>
            <tbody>
<?php
    $prod_names = [];
    if ($c->products) {
        $ids = json_decode($c->products, true);
        foreach ($ids as $pid) {
            $prod = wc_get_product($pid);
            if ($prod) {
                $prod_names[] = $prod->get_name();
            }
        }
    }
    $cat_names = [];
    if ($c->categories) {
        $ids = json_decode($c->categories, true);
        foreach ($ids as $cid) {
            $term = get_term($cid, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $cat_names[] = $term->name;
            }
        }
    }

    $rule_label = '';
    switch ($c->rule_type) {
        case 'percent': $rule_label = 'Prosentvis rabatt'; break;
        case 'fixed': $rule_label = 'Fast rabatt'; break;
        case '3for2': $rule_label = '3 for 2'; break;
        case 'bulk': $rule_label = 'Mengderabatt'; break;
        default: $rule_label = $c->rule_type;
    }

    $applies_to = (!empty($prod_names) ? 'Produkter: ' . implode(', ', $prod_names) : '') .
                  (!empty($cat_names) ? (empty($prod_names) ? '' : '<br>') . 'Kategorier: ' . implode(', ', $cat_names) : '');
    if (empty($applies_to)) $applies_to = 'Gjelder alle produkter';
?>
<?php foreach ($campaigns as $c): ?>
<?php
    $prod_names = [];
    if ($c->products) {
        $ids = json_decode($c->products, true);
        foreach ($ids as $pid) {
            $prod = wc_get_product($pid);
            if ($prod) {
                $prod_names[] = $prod->get_name();
            }
        }
    }
    $cat_names = [];
    if ($c->categories) {
        $ids = json_decode($c->categories, true);
        foreach ($ids as $cid) {
            $term = get_term($cid, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $cat_names[] = $term->name;
            }
        }
    }
    $rule_label = '';
    switch ($c->rule_type) {
        case 'percent': $rule_label = 'Prosentvis rabatt'; break;
        case 'fixed': $rule_label = 'Fast rabatt'; break;
        case '3for2': $rule_label = '3 for 2'; break;
        case 'bulk': $rule_label = 'Mengderabatt'; break;
        default: $rule_label = $c->rule_type;
    }
    $applies_to = (!empty($prod_names) ? 'Produkter: ' . implode(', ', $prod_names) : '') .
                  (!empty($cat_names) ? (empty($prod_names) ? '' : '<br>') . 'Kategorier: ' . implode(', ', $cat_names) : '');
    if (empty($applies_to)) $applies_to = 'Gjelder alle produkter';
?>
<tr>
    <td><?php echo esc_html($c->name); ?></td>
    <td><?php echo esc_html($rule_label); ?></td>
    <td><?php echo esc_html($c->value); ?></td>
    <td><?php echo esc_html($c->start); ?></td>
    <td><?php echo esc_html($c->end); ?></td>
    <td>
        <a href="?page=bd-dynamic-pricing&edit=<?php echo esc_attr($c->id); ?>" class="button">Rediger</a>
        <a href="?page=bd-dynamic-pricing&delete=<?php echo esc_attr($c->id); ?>" class="button" onclick="return confirm('Er du sikker på at du vil slette denne kampanjen?');">Slett</a>
    </td>
    <td><?php echo $applies_to; ?></td>
</tr>
<?php endforeach; ?>


</tbody>
</table>

<?php } // End of bd_dp_render_admin_page ?>
