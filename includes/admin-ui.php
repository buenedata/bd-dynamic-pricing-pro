<?php
add_action('admin_menu', 'bd_dp_add_admin_menu');
add_action('admin_enqueue_scripts', 'bd_dp_enqueue_admin_scripts');

function bd_dp_add_admin_menu() {
    // Use BD menu helper to integrate with unified Buene Data menu
    if (function_exists('bd_add_buene_data_menu')) {
        bd_add_buene_data_menu(
            'Dynamic Pricing Pro',
            'bd-dynamic-pricing',
            'bd_dp_render_admin_page',
            '💰'
        );
    } else {
        // Fallback to WooCommerce submenu if BD menu helper is not available
        add_submenu_page(
            'woocommerce',
            'BD Dynamic Pricing Pro',
            'BD Dynamic Pricing Pro',
            'manage_woocommerce',
            'bd-dynamic-pricing',
            'bd_dp_render_admin_page'
        );
    }
}

function bd_dp_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'bd-dynamic-pricing') !== false) {
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
        wp_enqueue_script('bd_dp_admin_js', plugins_url('../assets/js/admin.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_style('bd_dp_admin_css', plugins_url('../assets/css/admin.css', __FILE__), [], '1.2');
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
    <div class="wrap bd-dynamic-pricing-admin">
        <!-- Modern BD Header Section -->
        <div class="bd-admin-header">
            <div class="bd-branding">
                <h2>💰 BD Dynamic Pricing Pro</h2>
                <p>Avansert prisingsmotor for WooCommerce med kampanjer og rabatter</p>
            </div>
            <div class="bd-actions">
                <button type="button" class="button button-primary" onclick="document.getElementById('campaign-form').scrollIntoView({behavior: 'smooth'})">
                    <?php echo $editing ? '✏️ Rediger kampanje' : '➕ Ny kampanje'; ?>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <nav class="nav-tab-wrapper">
            <a href="#campaigns" class="nav-tab nav-tab-active" data-tab="campaigns">📋 Kampanjer</a>
            <a href="#settings" class="nav-tab" data-tab="settings">⚙️ Innstillinger</a>
            <a href="#help" class="nav-tab" data-tab="help">❓ Hjelp</a>
        </nav>

        <!-- Tab Content: Campaigns -->
        <div id="campaigns" class="tab-content active">
            <div class="bd-settings-section" id="campaign-form">
                <h3><?php echo $editing ? '✏️ Rediger kampanje' : '➕ Opprett ny kampanje'; ?></h3>
                <form method="post" action="">
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
                    </table>
                    <div class="bd-form-actions">
                        <?php submit_button($editing ? '💾 Oppdater kampanje' : '💾 Lagre kampanje', 'primary', 'submit', false, ['class' => 'button-primary bd-save-button']); ?>
                        <?php if ($editing): ?>
                            <a href="?page=bd-dynamic-pricing" class="button button-secondary">❌ Avbryt redigering</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bd-settings-section">
                <h3>📊 Eksisterende kampanjer</h3>
                <div class="bd-campaigns-grid">
                    <?php if (empty($campaigns)): ?>
                        <div class="bd-empty-state">
                            <div class="bd-empty-icon">💰</div>
                            <h3>Ingen kampanjer ennå</h3>
                            <p>Opprett din første priskampanje for å komme i gang med dynamisk prising.</p>
                            <button type="button" class="button button-primary" onclick="document.getElementById('campaign-form').scrollIntoView({behavior: 'smooth'})">
                                ➕ Opprett kampanje
                            </button>
                        </div>
                    <?php else: ?>
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
                            $rule_emoji = '';
                            switch ($c->rule_type) {
                                case 'percent':
                                    $rule_label = 'Prosentvis rabatt';
                                    $rule_emoji = '📊';
                                    break;
                                case 'fixed':
                                    $rule_label = 'Fast rabatt';
                                    $rule_emoji = '💵';
                                    break;
                                case '3for2':
                                    $rule_label = '3 for 2';
                                    $rule_emoji = '🎁';
                                    break;
                                case 'bulk':
                                    $rule_label = 'Mengderabatt';
                                    $rule_emoji = '📦';
                                    break;
                                default:
                                    $rule_label = $c->rule_type;
                                    $rule_emoji = '⚙️';
                            }
                            $applies_to = (!empty($prod_names) ? 'Produkter: ' . implode(', ', array_slice($prod_names, 0, 3)) . (count($prod_names) > 3 ? '...' : '') : '') .
                                          (!empty($cat_names) ? (empty($prod_names) ? '' : ' | ') . 'Kategorier: ' . implode(', ', array_slice($cat_names, 0, 2)) . (count($cat_names) > 2 ? '...' : '') : '');
                            if (empty($applies_to)) $applies_to = 'Gjelder alle produkter';
                            
                            // Check if campaign is active
                            $now = current_time('Y-m-d H:i:s');
                            $is_active = true;
                            if (!empty($c->start) && $now < $c->start) $is_active = false;
                            if (!empty($c->end) && $now > $c->end) $is_active = false;
                            ?>
                            <div class="bd-campaign-card bd-hover-lift">
                                <div class="bd-campaign-header">
                                    <div class="bd-campaign-title">
                                        <h4><?php echo $rule_emoji; ?> <?php echo esc_html($c->name); ?></h4>
                                        <span class="bd-label <?php echo $is_active ? 'success' : 'warning'; ?>">
                                            <?php echo $is_active ? '✅ Aktiv' : '⏸️ Inaktiv'; ?>
                                        </span>
                                    </div>
                                    <div class="bd-campaign-actions">
                                        <a href="?page=bd-dynamic-pricing&edit=<?php echo esc_attr($c->id); ?>" class="button button-secondary bd-edit-btn">
                                            ✏️ Rediger
                                        </a>
                                        <a href="?page=bd-dynamic-pricing&delete=<?php echo esc_attr($c->id); ?>"
                                           class="button bd-delete-btn"
                                           onclick="return confirm('Er du sikker på at du vil slette kampanjen \"<?php echo esc_js($c->name); ?>\"?');">
                                            🗑️ Slett
                                        </a>
                                    </div>
                                </div>
                            
                                <script>
                                jQuery(document).ready(function($) {
                                    // Tab switching functionality
                                    $('.nav-tab').click(function(e) {
                                        e.preventDefault();
                                        var target = $(this).data('tab');
                                        
                                        // Update active tab
                                        $('.nav-tab').removeClass('nav-tab-active');
                                        $(this).addClass('nav-tab-active');
                                        
                                        // Show target content
                                        $('.tab-content').removeClass('active');
                                        $('#' + target).addClass('active');
                                    });
                                });
                                </script>
                                
                                <div class="bd-campaign-details">
                                    <div class="bd-campaign-info">
                                        <div class="bd-info-item">
                                            <strong>Regeltype:</strong> <?php echo esc_html($rule_label); ?>
                                        </div>
                                        <div class="bd-info-item">
                                            <strong>Verdi:</strong> <?php echo esc_html($c->value); ?><?php echo $c->rule_type === 'percent' ? '%' : ' kr'; ?>
                                        </div>
                                        <?php if (!empty($c->start) || !empty($c->end)): ?>
                                            <div class="bd-info-item">
                                                <strong>Periode:</strong>
                                                <?php echo !empty($c->start) ? esc_html($c->start) : 'Ingen start'; ?> -
                                                <?php echo !empty($c->end) ? esc_html($c->end) : 'Ingen slutt'; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="bd-info-item">
                                            <strong>Gjelder for:</strong> <?php echo $applies_to; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab Content: Settings -->
        <div id="settings" class="tab-content">
            <div class="bd-settings-section">
                <h3>⚙️ Generelle innstillinger</h3>
                <div class="bd-info-box">
                    <strong>💡 Tips:</strong> Disse innstillingene kommer i en fremtidig versjon av BD Dynamic Pricing Pro.
                </div>
            </div>
        </div>

        <!-- Tab Content: Help -->
        <div id="help" class="tab-content">
            <div class="bd-settings-section">
                <h3>❓ Hjelp og dokumentasjon</h3>
                <div class="bd-help-grid">
                    <div class="bd-help-card">
                        <h4>📋 Hvordan opprette kampanjer</h4>
                        <p>Lær hvordan du setter opp forskjellige typer priskampanjer for dine WooCommerce-produkter.</p>
                        <a href="https://buenedata.no/dokumentasjon/bd-dynamic-pricing-pro" target="_blank" class="button button-secondary">
                            📖 Les dokumentasjon
                        </a>
                    </div>
                    <div class="bd-help-card">
                        <h4>🎯 Målretting av produkter</h4>
                        <p>Forstå hvordan du kan målrette kampanjer til spesifikke produkter eller kategorier.</p>
                        <a href="https://buenedata.no/support" target="_blank" class="button button-secondary">
                            💬 Kontakt support
                        </a>
                    </div>
                    <div class="bd-help-card">
                        <h4>⚡ Avanserte funksjoner</h4>
                        <p>Utforsk avanserte prisingsregler som mengderabatter og tidsbaserte kampanjer.</p>
                        <a href="https://buenedata.no/produkter/plugins/bd-dynamic-pricing-pro" target="_blank" class="button button-primary">
                            🚀 Oppgrader til Pro
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } // End of bd_dp_render_admin_page ?>
