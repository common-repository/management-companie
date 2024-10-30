<?php

namespace FacturareOnline\Inc\Core;

/**
 * @link              https://facturare.online
 * @since             1.0.0
 * @package           FacturareOnline
 *
 */

use FacturareOnline as NS;
use FacturareOnline\Inc\Admin as Admin;
use FacturareOnline\Inc\Core\Internationalization_I18n as Internationalization_I18n;

class Init
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;
    protected $plugin_name;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_base_name The string used to uniquely identify this plugin.
     */
    protected $plugin_basename;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * The text domain of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $plugin_text_domain;

    /**
     * Initialize and define the core functionality of the plugin.
     */
    public function __construct()
    {
        $this->plugin_name = NS\PLUGIN_NAME;
        $this->version = NS\PLUGIN_VERSION;
        $this->plugin_basename = NS\PLUGIN_BASENAME;
        $this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Loads the following required dependencies for this plugin.
     *
     * - Loader - Orchestrates the hooks of the plugin.
     * - Internationalization_I18n - Defines internationalization functionality.
     * - Admin - Defines all hooks for the admin area.
     * - Frontend - Defines all hooks for the public side of the site.
     *
     * @access    private
     */
    private function load_dependencies()
    {
        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Internationalization_I18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @access    private
     */
    private function set_locale()
    {
        $plugin_i18n = new Internationalization_I18n($this->plugin_text_domain);

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @access    private
     */
    private function define_admin_hooks()
    {
        $plugin_settings = new Admin\Settings($this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain());
        $plugin_admin = new Admin\Admin($this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain());
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_box_mc');

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('wp_ajax_adauga_factura', $plugin_admin, 'adauga_factura');
        $this->loader->add_action('wp_ajax_vizualizeaza_factura', $plugin_admin, 'vizualizeaza_factura');
        $this->loader->add_action('wp_ajax_vizualizeaza_chitanta', $plugin_admin, 'vizualizeaza_chitanta');
        $this->loader->add_action('wp_ajax_anuleaza_factura', $plugin_admin, 'anuleaza_factura');
        $this->loader->add_action('wp_ajax_descarca_factura', $plugin_admin, 'descarca_factura');
        $this->loader->add_action('wp_ajax_descarca_factura_public', $plugin_admin, 'descarca_factura_public');
        $this->loader->add_action('wp_ajax_descarca_chitanta', $plugin_admin, 'descarca_chitanta');
        $this->loader->add_action('wp_ajax_descarca_chitanta_public', $plugin_admin, 'descarca_chitanta_public');
        $this->loader->add_action('wp_ajax_genereaza_chitanta_factura', $plugin_admin, 'genereaza_chitanta_factura');
        $this->loader->add_action('wp_ajax_incaseaza_banca_factura', $plugin_admin, 'incaseaza_banca_factura');
        $this->loader->add_action('woocommerce_product_options_general_product_data', $plugin_admin, 'create_alias_name_for_product');
        $this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'save_alias_name_for_product');

        $afiseaza = !empty(get_option('managementcompanie_setari_generale')['afiseaza_factura_public']) ? 1 : 0;
        if ($afiseaza) {
            $this->loader->add_action('woocommerce_view_order', $plugin_admin, 'public_factura');
        }

        $this->loader->add_filter('woocommerce_email_order_meta_fields', $plugin_admin, 'mc_email_order_meta_fields', 10, 3);
        $this->loader->add_action('woocommerce_thankyou', $plugin_admin, 'mc_display_order_data', 20);
        $this->loader->add_action('woocommerce_view_order', $plugin_admin, 'mc_display_order_data', 20);
        $this->loader->add_filter('woocommerce_checkout_fields', $plugin_admin, 'add_company_fields');
        $this->loader->add_filter('woocommerce_admin_billing_fields', $plugin_admin, 'add_admin_company_fields');
        $this->loader->add_action('save_post_shop_order', $plugin_admin, 'save_admin_company_fields');
        $this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_admin, 'save_company_fields');
        $this->loader->add_filter('woocommerce_billing_fields', $plugin_admin, 'mc_woocommerce_billing_fields');
        $this->loader->add_filter('woocommerce_customer_meta_fields', $plugin_admin, 'mc_customer_meta_fields');
        $this->loader->add_action('woocommerce_after_checkout_validation', $plugin_admin, 'check_company_fields', 10, 2);

        // SNIF URL
        $this->loader->add_action('woocommerce_api_facturareonline', $plugin_admin, 'snif');

        add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', $this->plugin_basename, true);
            }
        });

        $this->loader->add_filter('woocommerce_product_quick_edit_start', $plugin_admin, 'create_alias_name_for_product');
        $this->loader->add_filter('woocommerce_product_quick_edit_save', $plugin_admin, 'save_alias_name_for_product');
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Retrieve the text domain of the plugin.
     *
     * @return    string    The text domain of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_text_domain()
    {
        return $this->plugin_text_domain;
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @access    private
     */

    private function define_public_hooks()
    {
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
}
