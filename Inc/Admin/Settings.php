<?php

namespace FacturareOnline\Inc\Admin;

use function printf;
use function sprintf;

class Settings
{
    private $plugin_name;
    private $version;
    private $plugin_text_domain;
    private $options;

    public function __construct($plugin_name, $version, $plugin_text_domain)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_text_domain = $plugin_text_domain;

        // Settings menu item
        add_action('admin_menu', [$this, 'menu']); // Add menu.
        add_action('admin_init', [$this, 'page_init']);
        // Links on plugin page
    }

    public function menu()
    {
        add_menu_page(
            __('Facturare.Online', 'management-companie'),
            __('Facturare.Online', 'management-companie'),
            'manage_options',
            'managementcompanie-general',
            [$this, 'managementcompanie_sublevel_setari_generale'],
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIyNTZweCIgaGVpZ2h0PSIyNTZweCIgdmlld0JveD0iMCAwIDI1NiAyNTYiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDI1NiAyNTYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxwYXRoIGZpbGw9IiMyOUFCRTIiIGQ9Ik0yMTEuMjk3LDE4NS4wNzJjLTEuNTEtMTEuNTEyLTkuNzU4LTIxLjAyMy0yMi40ODQtMjcuNzQ2YzUuMjYsNC43MDEsOC41NDMsMTAuMTgsOS4zNDIsMTYuMzUyYzMuNTU5LDI3LjI0NC00Mi4wOSw1Ny4yODktMTAxLjk0OSw2Ny4xMjNjLTIwLjQ2MiwzLjM1LTM5Ljk0NCwzLjkwNi01Ni44NTYsMi4xYzE5Ljg0Niw3LjM3MSw1NS43NjIsMTAuNTQ1LDg4LjM2Miw1Ljc4N0MxODEuMzI4LDI0MC44NDQsMjE1LjA1NywyMTMuOTA4LDIxMS4yOTcsMTg1LjA3MnoiLz48cG9seWdvbiBmaWxsPSIjMjlBQkUyIiBwb2ludHM9IjEwNi42ODUsNDIuNzk0IDEwNi42ODUsMjE1LjMwNyAxNDIuMTIzLDIxNS4zMDcgMTQyLjEyMywzLjA1OCAiLz48cG9seWdvbiBmaWxsPSIjMjlBQkUyIiBwb2ludHM9IjYzLjQ1LDEyMC40NTEgNjMuNDUsMjE1LjMwNyA5OC44ODgsMjE1LjMwNyA5OC44ODgsODAuNzE0ICIvPjxwb2x5Z29uIGZpbGw9IiMyOUFCRTIiIHBvaW50cz0iMTg1LjM0LDkwLjg2NCAxODUuMzQsMjE1LjMwNyAxNDkuOTEsMjE1LjMwNyAxNDkuOTEsNTEuMTI5ICIvPjwvc3ZnPg=='
        );
    }

    public function managementcompanie_sublevel_setari_generale()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        // Set class property
        $this->options = get_option('managementcompanie_setari_generale'); ?>
        <div class="wrap">
            <h1>Setari generale</h1>
            <?php settings_errors('managementcompanie_setari_generale_error'); ?>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('managementcompanie_setari_generale_group');
                do_settings_sections('managementcompanie-setari-generale-admin');
                submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'managementcompanie_setari_generale_group', // Option group
            'managementcompanie_setari_generale', // Option name
            [$this, 'sanitize_general_settings'] // Sanitize
        );

        // SETARI GENERALE
        add_settings_section('managementcompanie_setari_generale_section_id', __('Please setup Facturare.Online account details', 'management-companie'), [$this, 'print_general_section_info'], 'managementcompanie-setari-generale-admin');
        add_settings_field('is_active', __('Active', 'management-companie'), [$this, 'is_active_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('companyid', __('Company ID', 'management-companie'), [$this, 'companyid_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('rsa_key_public', __('Public key', 'management-companie'), [$this, 'rsa_key_public_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('rsa_key_private', __('Private key', 'management-companie'), [$this, 'rsa_key_private_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('iv', __('IV', 'management-companie'), [$this, 'iv_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('tip_serie', __('Invoice type', 'management-companie'), [$this, 'tip_serie_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('serie', __('Invoice series', 'management-companie'), [$this, 'serie_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('limba', __('Language', 'management-companie'), [$this, 'limba_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('observatii', __('Comments - will be displayed on the invoice', 'management-companie'), [$this, 'observatii_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('scadenta', __('Invoice due days', 'management-companie'), [$this, 'scadenta_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('permite_factura_persoana_juridica', __('Display company fields in checkout', 'management-companie'), [$this, 'permite_factura_persoana_juridica_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('afiseaza_factura_public', __('Allow the client to download the invoice', 'management-companie'), [$this, 'afiseaza_factura_public_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('permite_generare_chitanta_factura', __('Allow receipt generation based on invoice', 'management-companie'), [$this, 'permite_generare_chitanta_factura_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
        add_settings_field('permite_incasare_banca_factura', __('Allow invoice charge via bank', 'management-companie'), [$this, 'permite_incasare_banca_factura_callback'], 'managementcompanie-setari-generale-admin', 'managementcompanie_setari_generale_section_id');
    }

    public function companyid_callback()
    {
        printf(
            '<input type="text" id="companyid" name="managementcompanie_setari_generale[companyid]" value="%s" size="47"/>',
            isset($this->options['companyid']) ? esc_attr($this->options['companyid']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function rsa_key_public_callback()
    {
        printf(
            '<textarea type="textarea" id="rsa_key_public" name="managementcompanie_setari_generale[rsa_key_public]" rows="8" cols="50"/>%s</textarea>',
            isset($this->options['rsa_key_public']) ? esc_attr($this->options['rsa_key_public']) : ''
        );
    }

    public function rsa_key_private_callback()
    {
        printf(
            '<textarea type="textarea" id="rsa_key_private" name="managementcompanie_setari_generale[rsa_key_private]" rows="8" cols="50"/>%s</textarea>',
            isset($this->options['rsa_key_private']) ? esc_attr($this->options['rsa_key_private']) : ''
        );
    }

    public function is_active_callback()
    {
        printf(
            '<input name="managementcompanie_setari_generale[is_active]" id="is_active" type="checkbox" value="1" %s />',
            isset($this->options['is_active']) ? checked(1, esc_attr($this->options['is_active']), false) : ''
        );
    }

    public function afiseaza_factura_public_callback()
    {
        printf(
            '<input name="managementcompanie_setari_generale[afiseaza_factura_public]" id="afiseaza_factura_public" type="checkbox" value="1" %s />',
            isset($this->options['afiseaza_factura_public']) ? checked(1, esc_attr($this->options['afiseaza_factura_public']), false) : ''
        );
    }

    public function permite_factura_persoana_juridica_callback()
    {
        printf(
            '<input name="managementcompanie_setari_generale[permite_factura_persoana_juridica]" id="permite_factura_persoana_juridica" type="checkbox" value="1" %s />',
            isset($this->options['permite_factura_persoana_juridica']) ? checked(1, esc_attr($this->options['permite_factura_persoana_juridica']), false) : ''
        );
    }

    public function permite_generare_chitanta_factura_callback()
    {
        printf(
            '<input name="managementcompanie_setari_generale[permite_generare_chitanta_factura]" id="permite_generare_chitanta_factura" type="checkbox" value="1" %s />',
            isset($this->options['permite_generare_chitanta_factura']) ? checked(1, esc_attr($this->options['permite_generare_chitanta_factura']), false) : ''
        );
    }

    public function permite_incasare_banca_factura_callback()
    {
        printf(
            '<input name="managementcompanie_setari_generale[permite_incasare_banca_factura]" id="permite_incasare_banca_factura" type="checkbox" value="1" %s />',
            isset($this->options['permite_incasare_banca_factura']) ? checked(1, esc_attr($this->options['permite_incasare_banca_factura']), false) : ''
        );
    }

    public function iv_callback()
    {
        printf(
            '<input type="text" id="iv" name="managementcompanie_setari_generale[iv]" value="%s" size="47"/>',
            isset($this->options['iv']) ? esc_attr($this->options['iv']) : ''
        );
    }

    public function serie_callback()
    {
        printf(
            '<input type="text" id="serie" name="managementcompanie_setari_generale[serie]" value="%s" size="47"/>',
            isset($this->options['serie']) ? esc_attr($this->options['serie']) : ''
        );
    }

    public function scadenta_callback()
    {
        printf(
            '<input type="number" min="0" max="365" step="1" id="scadenta" name="managementcompanie_setari_generale[scadenta]" value="%s"/>',
            isset($this->options['scadenta']) ? esc_attr($this->options['scadenta']) : ''
        );
    }

    public function observatii_callback()
    {
        printf(
            '<textarea type="textarea" id="observatii" name="managementcompanie_setari_generale[observatii]" rows="4" cols="50"/>%s</textarea>',
            isset($this->options['observatii']) ? esc_attr($this->options['observatii']) : ''
        );
    }

    public function tip_serie_callback()
    {
        $items = [__('Fiscal invoice', 'management-companie') => 'ff', __('Proforma invoice', 'management-companie') => 'fp'];
        echo "<select id='tip_serie' name='managementcompanie_setari_generale[tip_serie]'>";
        foreach ($items as $key => $value) {
            $selected = (!empty($this->options['tip_serie']) && $this->options['tip_serie'] == $value) ? 'selected="selected"' : '';
            echo "<option value='$value' $selected>$key</option>";
        }
        echo "</select>";
    }

    public function limba_callback()
    {
        $items = [__('Romanian', 'management-companie') => 'RO', __('English', 'management-companie') => 'EN', __('German', 'management-companie') => 'DE', __('French', 'management-companie') => 'FR'];
        echo "<select id='limba' name='managementcompanie_setari_generale[limba]'>";
        foreach ($items as $key => $value) {
            $selected = (!empty($this->options['limba']) && $this->options['limba'] == $value) ? 'selected="selected"' : '';
            echo "<option value='$value' $selected>$key</option>";
        }
        echo "</select>";
    }


    public function sanitize_general_settings($input)
    {
        $this->type = 'updated';
        $this->message = __('The settings where successfully saved', 'management-companie');
        $new_input = [];
        $not_required_keys = ['observatii', 'scadenta'];
        $has_errors = false;
        foreach ($input as $key => $value) {
            if (!empty($value)) {
                $new_input[$key] = sanitize_text_field($value);
            } else {
                if (!in_array($key, $not_required_keys)) {
                    $has_errors = true;
                    add_settings_error(
                        'managementcompanie_setari_generale_error',
                        esc_attr('settings_updated'),
                        sprintf(__('The field %s is mandatory', 'management-companie'), $key)
                    );
                }
            }
        }

        if ($has_errors === false) {
            add_settings_error(
                'managementcompanie_setari_generale_error',
                esc_attr('settings_updated'),
                $this->message,
                $this->type
            );
        }
        return $new_input;
    }

    public function print_general_section_info()
    {
        echo sprintf(__('Please obtain the settings from %s', 'management-companie'), '<a href="https://facturare.online/acces-api" target="_blank"><b>' . __('your Facturare.Online account', 'management-companie') . '</b></a>');
        echo "<div style='background: #ffffff;padding:10px;border:1px solid #cccccc;margin-top:10px'><b>" . __('SNIF SETTINGS', 'management-companie') . "</b>";
        $urlparts = parse_url(home_url());
        $domain = preg_replace('/www\./i', '', $urlparts['host']);
        echo '<ul><li>' . __('Domain name', 'management-companie') . ': <b>' . $domain . '</b></li><li>' . __('SNIF URL', 'management-companie') . ': <b>' . home_url('/wc-api/facturareonline') . '</b></li></ul></div>';
    }

    public function settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        // Set class property
        $this->options = get_option('managementcompanie_setari_generale'); ?>
        <div class="wrap">
            <h1><?php echo __('General settings', 'management-companie') ?></h1>
            <?php settings_errors('managementcompanie_setari_generale_error'); ?>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('managementcompanie_setari_generale_group');
                do_settings_sections('managementcompanie-setari-generale-admin');
                submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
