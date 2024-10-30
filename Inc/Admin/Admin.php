<?php

namespace FacturareOnline\Inc\Admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;
use FacturareOnline\Inc\Libraries\FO;
use WC_Order;
use WP_Post;
use function get_current_user_id;
use function sprintf;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * @link              https://facturare.online
 * @since             1.0.0
 * @package           FacturareOnline
 *
 */
class Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    private $plugin_text_domain;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $plugin_text_domain The text domain of this plugin.
     *
     * @since       1.0.0
     *
     */
    public function __construct($plugin_name, $version, $plugin_text_domain)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_text_domain = $plugin_text_domain;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name . '-jquery-confirm', plugin_dir_url(__FILE__) . 'css/jquery-confirm.min.css', [], $this->version);
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/managementcompanie-admin.css', [], $this->version);
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_register_script($this->plugin_name . '-jquery-confirm', plugin_dir_url(__FILE__) . 'js/jquery-confirm.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script($this->plugin_name . '-jquery-confirm');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_register_script($this->plugin_name . '-admin-ajax-script', plugin_dir_url(__FILE__) . 'js/managementcompanie-admin.js', ['jquery', 'wp-i18n'], $this->version, true);
        wp_set_script_translations($this->plugin_name . '-admin-ajax-script', 'management-companie');
        wp_localize_script($this->plugin_name . '-admin-ajax-script', 'mc', ['ajaxurl' => admin_url('admin-ajax.php')]);
        wp_enqueue_script($this->plugin_name . '-admin-ajax-script');

        wp_register_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('jquery-ui');
    }

    public function add_meta_box_mc()
    {
        global $theorder;
        global $post;
        $order = ($post instanceof WP_Post) ? wc_get_order($post->ID) : null;

        if ($order instanceof WC_Order === false) {
            $order = $theorder;
        }

        if ($order instanceof WC_Order) {
            $screen = wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
                ? wc_get_page_screen_id('shop-order')
                : 'shop_order';

            add_meta_box(
                'form_managementcompanie',
                sprintf(__('Facturare.Online  - Order ID #%s', 'management-companie'), $order->get_id()
                ),
                [$this, 'form_managementcompanie'],
                $screen
            );
        }
    }

    public function form_managementcompanie()
    {
        load_template(plugin_dir_path(__FILE__) . 'views/html-managementcompanie-admin-display.php');
    }

    public function public_factura($order_id)
    {
        global $wpdb;
        $factura = $wpdb->get_row(
            "SELECT
                f.*,
                concat('[',
                GROUP_CONCAT(
                CONCAT( '{\"receiptid\":\"', c.receiptid, '\", \"serie_chitanta\":\"', c.serie, '\", \"numar_chitanta\":\"', c.numar, '\", \"total_chitanta\":\"', c.total,'\", \"moneda_chitanta\":\"', c.moneda,'\", \"created_on_chitanta\":\"', c.created_on, '\"}' ) order by c.id desc),
                 ']') AS chitante 
            FROM
                {$wpdb->prefix}managementcompanie_facturi f
                LEFT JOIN {$wpdb->prefix}managementcompanie_chitante c ON f.invoiceid = c.invoiceid 
            WHERE
                f.orderid = $order_id 
                AND f.status in (1,4,5)
            GROUP BY
                f.id 
            ORDER BY
                f.id DESC"
        );

        if (!empty($factura)):
            echo '<div style="background: #eeeeee;padding:10px;border:1px solid #cccccc;margin-bottom:10px">';
            echo sprintf(__('The invoice Series <b>%s</b>, Number <b>%s</b>, was issued for this order at <b>%s</b>', 'management-companie'), $factura->serie, $factura->numar, $factura->created_on) . '</div>';
            ?>
            <table style="width:100%">
                <thead>
                <tr>
                    <th style="text-align: center"><?php echo __('Type', 'management-companie'); ?></th>
                    <th style="text-align: center"><?php echo __('Series', 'management-companie'); ?></th>
                    <th style="text-align: center"><?php echo __('Number', 'management-companie'); ?></th>
                    <th style="text-align: center"><?php echo __('Total', 'management-companie'); ?></th>
                    <th style="text-align: center"><?php echo __('Generated at', 'management-companie'); ?></th>
                    <th style="text-align: center"><?php echo __('Actions', 'management-companie'); ?></th>
                </tr>
                </thead>
                <tbody>

                <tr style="background: #eee">
                    <td style="text-align: center"><?php echo FO::translateType($factura->tip) ?></td>
                    <td style="text-align: center"><?php echo $factura->serie ?></td>
                    <td style="text-align: center"><?php echo $factura->numar ?></td>
                    <td style="text-align: center"><?php echo $factura->total . ' ' . $factura->moneda ?></td>
                    <td style="text-align: center"><?php echo date('d-m-Y H:i', strtotime($factura->created_on)) ?></td>
                    <td style="text-align: center" style="width:20%">
                        <a href="<?php echo admin_url('admin-ajax.php') . '?' . http_build_query(['action' => 'descarca_factura_public', 'order' => $order_id, 'invoiceid' => (int)$factura->invoiceid]) ?>"
                           class="download-invoice button">
                            <?php echo __('Download', 'management-companie') ?>
                        </a>
                    </td>
                </tr>

                <?php
                $chitante = json_decode($factura->chitante);
                if (!empty($chitante)) :
                    foreach ($chitante as $chitanta):
                        echo '<tr>'; ?>
                        <td style="text-align: center"><?php echo __('Receipt', 'management-companie') ?></td>
                        <td style="text-align: center"><?php echo $chitanta->serie_chitanta ?></td>
                        <td style="text-align: center"><?php echo $chitanta->numar_chitanta ?></td>
                        <td style="text-align: center"><?php echo $chitanta->total_chitanta . ' ' . $chitanta->moneda_chitanta ?></td>
                        <td style="text-align: center"><?php echo date('d-m-Y H:i', strtotime($chitanta->created_on_chitanta)) ?></td>
                        <td style="text-align: center">
                            <a href="<?php echo admin_url('admin-ajax.php') . '?' . http_build_query(['action' => 'descarca_chitanta_public', 'order' => $order_id, 'receiptid' => (int)$chitanta->receiptid]) ?>"
                               class="download-receipt button">
                                <?php echo __('Download', 'management-companie') ?>
                            </a>
                        </td>
                        <?php echo '</tr>';
                    endforeach;
                endif; ?>
                </tbody>
            </table>
        <?php
        endif;
    }

    public function descarca_factura_public()
    {
        global $wpdb;
        if (empty($_GET['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_GET['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_GET['order']);

        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $order = wc_get_order($order_id);
        $order_user_id = $order->get_user_id();
        $loggedin_user_id = get_current_user_id();

        if ($order_user_id != $loggedin_user_id) {
            wp_die(__('You are not allowed to download this resource', 'management-companie'));
        }

        $table_name = $wpdb->prefix . 'managementcompanie_facturi';
        $result = $wpdb->get_row("SELECT * from $table_name where invoiceid = " . (int)sanitize_text_field($_GET['invoiceid']));

        if (empty($result)) {
            wp_die(__('This resource is unavailable', 'management-companie'));
        }

        $decoded = base64_decode($result->document);
        switch ($result->tip) {
            case 'ff':
                $prefix = 'factura-fiscala-';
                break;
            case 'fp':
                $prefix = 'factura-proforma-';
                break;
            case 'c':
                $prefix = 'chitanta-';
                break;
        }
        $file = $prefix . $result->serie . '-' . $result->numar . '.pdf';

        file_put_contents($file, $decoded);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            wp_die();
        }
        wp_die();
    }

    public function descarca_chitanta_public()
    {
        global $wpdb;
        if (empty($_GET['receiptid'])) {
            $this->response('error', __('No receiptid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No receiptid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_GET['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_GET['order']);

        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }
        $order = wc_get_order($order_id);
        $order_user_id = $order->get_user_id();
        $loggedin_user_id = get_current_user_id();

        if ($order_user_id != $loggedin_user_id) {
            wp_die(__('You are not allowed to download this resource', 'management-companie'));
        }

        $table_name = $wpdb->prefix . 'managementcompanie_chitante';
        $result = $wpdb->get_row("SELECT * from $table_name where receiptid = " . (int)sanitize_text_field($_GET['receiptid']));

        if (empty($result)) {
            wp_die(__('This resource is unavailable', 'management-companie'));
        }

        $decoded = base64_decode($result->document);
        $file = 'chitanta-' . $result->serie . '-' . $result->numar . '.pdf';

        file_put_contents($file, $decoded);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            wp_die();
        }
        wp_die();
    }

    public function descarca_factura()
    {
        global $wpdb;
        if (empty($_GET['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_GET['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_GET['order']);

        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $table_name = $wpdb->prefix . 'managementcompanie_facturi';
        $result = $wpdb->get_row("SELECT * from $table_name where invoiceid = " . (int)sanitize_text_field($_GET['invoiceid']));
        $decoded = base64_decode($result->document);
        switch ($result->tip) {
            case 'ff':
                $prefix = 'factura-fiscala-';
                break;
            case 'fp':
                $prefix = 'factura-proforma-';
                break;
            case 'c':
                $prefix = 'chitanta-';
                break;
        }
        $file = $prefix . $result->serie . '-' . $result->numar . '.pdf';

        file_put_contents($file, $decoded);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            wp_die();
        }
        wp_die();
    }

    public function descarca_chitanta()
    {
        global $wpdb;
        if (empty($_GET['receiptid'])) {
            $this->response('error', __('No receiptid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No receipt sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_GET['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_GET['order']);

        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $table_name = $wpdb->prefix . 'managementcompanie_chitante';
        $result = $wpdb->get_row("SELECT * from $table_name where receiptid = " . (int)sanitize_text_field($_GET['receiptid']));
        $decoded = base64_decode($result->document);
        $file = 'chitanta-' . $result->serie . '-' . $result->numar . '.pdf';

        file_put_contents($file, $decoded);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            wp_die();
        }
        wp_die();
    }

    private function response($status = 'error', $message = '', $error_code = null, $error_reason = '')
    {
        echo json_encode(['response' => ['status' => $status, 'message' => __($message, 'management-companie'), 'error_code' => $error_code, 'error_reason' => $error_reason]]);
    }

    public function incaseaza_banca_factura()
    {
        global $wpdb;

        if (empty($_POST['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['suma'])) {
            $this->response('error', __('No amount sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No amount sent', 'management-companie')])]));
            wp_die();
        }
        if (isset($_POST['suma']) && $_POST['suma'] < 0.0001) {
            $this->response('error', __('Amount not valid, must be greater than 0.0001', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('Amount not valid, must be greater than 0.0001', 'management-companie')])]));
            wp_die();
        }

        $date = sanitize_text_field($_POST['date']);
        $exchange_rate = !empty(get_post_meta($order_id, '_wcpbc_base_exchange_rate', true)) ? (float)get_post_meta($order_id, '_wcpbc_base_exchange_rate', true) : 1;
        $incaseaza_banca_factura = FO::incaseazaBancaFactura((int)sanitize_text_field($_POST['invoiceid']), (float)sanitize_text_field($_POST['suma']), $date, $exchange_rate);

        if (FO::isJson($incaseaza_banca_factura) === true) {
            if (FO::isError(json_decode($incaseaza_banca_factura))) {
                echo $incaseaza_banca_factura;
                wp_die();
            } else {
                $incasare = json_decode($incaseaza_banca_factura);
                $message = json_decode($incasare->response->message);

                // am generat cu succes chitanta, o salvez in baza de date
                $table_name = $wpdb->prefix . 'managementcompanie_incasari_banca';

                $wpdb->insert($table_name, [
                    'invoiceid' => (int)$message->invoiceid,
                    'amount' => (float)$message->amount,
                    'date' => $message->date,
                ]);

                // actualizez starea facturii
                $table_name = $wpdb->prefix . 'managementcompanie_facturi';

                $wpdb->update($table_name, [
                    'status' => (int)$message->invoice_status,
                ], [
                    'invoiceid' => (int)$message->invoiceid,
                ]);

                echo $incaseaza_banca_factura;
                wp_die();
            }
        } else {
            echo FO::response('', '01', __('The message from server response is not JSON', 'management-companie'));
            wp_die();
        }
    }

    public function genereaza_chitanta_factura()
    {
        global $wpdb;
        if (empty($_POST['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['suma'])) {
            $this->response('error', __('No amount sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No amount sent', 'management-companie')])]));
            wp_die();
        }
        if (isset($_POST['suma']) && $_POST['suma'] < 0.0001) {
            $this->response('error', __('Amount not valid, must be greater than 0.0001', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('Amount not valid, must be greater than 0.0001', 'management-companie')])]));
            wp_die();
        }

        $generare_chitanta_factura = FO::genereazaChitantaFactura((int)sanitize_text_field($_POST['invoiceid']), (float)sanitize_text_field($_POST['suma']));

        if (FO::isJson($generare_chitanta_factura) === true) {
            if (FO::isError(json_decode($generare_chitanta_factura))) {
                echo $generare_chitanta_factura;
                wp_die();
            } else {
                $chitanta = json_decode($generare_chitanta_factura);
                $message = json_decode($chitanta->response->message);

                // am generat cu succes chitanta, o salvez in baza de date
                $table_name = $wpdb->prefix . 'managementcompanie_chitante';

                $wpdb->insert($table_name, [
                    'receiptid' => (int)$message->receiptid,
                    'invoiceid' => (int)$message->invoiceid,
                    'serie' => $message->serie,
                    'total' => (float)$message->total,
                    'moneda' => $message->moneda,
                    'numar' => (int)$message->numar,
                    'code' => $message->code,
                    'document' => $message->document,
                ]);

                // actualizez starea facturii
                $table_name = $wpdb->prefix . 'managementcompanie_facturi';

                $wpdb->update($table_name, [
                    'status' => (int)$message->invoice_status,
                ], [
                    'invoiceid' => (int)$message->invoiceid,
                ]);

                echo $generare_chitanta_factura;
                wp_die();
            }
        } else {
            echo FO::response('', '01', __('The message from server response is not JSON', 'management-companie'));
            wp_die();
        }
    }

    public function anuleaza_factura()
    {
        global $wpdb;
        if (empty($_POST['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $anulare_factura = FO::anuleazaFactura((int)sanitize_text_field($_POST['invoiceid']));

        if (FO::isJson($anulare_factura) === true) {
            if (FO::isError(json_decode($anulare_factura))) {
                echo $anulare_factura;
                wp_die();
            } else {
                $factura = json_decode($anulare_factura);
                $message = json_decode($factura->response->message);

                // am anulat cu succes factura, o salvez in baza de date
                $table_name = $wpdb->prefix . 'managementcompanie_facturi';

                $wpdb->update($table_name, [
                    'status' => (int)$message->status,
                    'document' => $message->document,
                ], [
                    'invoiceid' => (int)$message->invoiceid,
                ]);
                echo $anulare_factura;
                wp_die();
            }
        } else {
            echo FO::response('', '01', __('The message from server response is not JSON', 'management-companie'));
            wp_die();
        }
    }

    public function vizualizeaza_chitanta()
    {
        global $wpdb;
        if (empty($_POST['receiptid'])) {
            $this->response('error', __('No receiptid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No receipt sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $table_name = $wpdb->prefix . 'managementcompanie_chitante';
        $result = $wpdb->get_row("SELECT * from $table_name where receiptid = " . (int)sanitize_text_field($_POST['receiptid']));

        echo '<iframe style="display: block;border: none;height: 100vh;width: 100%;" src="data:application/pdf;base64,' . $result->document . '"></iframe>';
        wp_die();
    }

    public function vizualizeaza_factura()
    {
        global $wpdb;
        if (empty($_POST['invoiceid'])) {
            $this->response('error', __('No invoiceid sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No invoiceid sent', 'management-companie')])]));
            wp_die();
        }
        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $table_name = $wpdb->prefix . 'managementcompanie_facturi';
        $result = $wpdb->get_row("SELECT * from $table_name where invoiceid = " . (int)sanitize_text_field($_POST['invoiceid']));

        switch ($result->tip) {
            case 'ff':
                $prefix = 'factura-fiscala-';
                break;
            case 'fp':
                $prefix = 'factura-proforma-';
                break;
            case 'c':
                $prefix = 'chitanta-';
                break;
        }

        echo '<iframe style="display: block;border: none;height: 100vh;width: 100%;" src="data:application/pdf;base64,' . $result->document . '"></iframe>';
        wp_die();
    }

    public function adauga_factura()
    {
        global $wpdb;

        if (empty($_POST['order'])) {
            $this->response('error', __('No order sent', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('No order sent', 'management-companie')])]));
            wp_die();
        }
        $order_id = (int)sanitize_text_field($_POST['order']);
        if ('shop_order' !== OrderUtil::get_order_type($order_id)) {
            $this->response('error', __('The post is not an Woocommerce order', 'management-companie'), -1, json_encode(['reason' => '', 'errors' => json_encode([__('The post is not an Woocommerce order', 'management-companie')])]));
            wp_die();
        }

        $emite_factura = FO::getFactura($order_id);
        if (FO::isJson($emite_factura) === true) {
            if (FO::isError(json_decode($emite_factura))) {
                echo $emite_factura;
                wp_die();
            } else {
                $factura = json_decode($emite_factura);

                if (FO::isJson($factura->response->message) === true) {
                    $message = json_decode($factura->response->message);
                } else {
                    echo FO::response('', '01', __('The message from server response is not JSON', 'management-companie'));
                    wp_die();
                }

                // am emis cu succes factura, o salvez in baza de date
                $table_name = $wpdb->prefix . 'managementcompanie_facturi';

                $wpdb->insert($table_name, [
                    'orderid' => (int)$message->orderid,
                    'invoiceid' => (int)$message->invoiceid,
                    'total' => (float)$message->total,
                    'moneda' => $message->moneda,
                    'serie' => $message->serie,
                    'numar' => (int)$message->numar,
                    'tip' => $message->tip,
                    'code' => $message->code,
                    'status' => (int)$message->status,
                    'document' => $message->document,
                ]);
                echo $emite_factura;
                wp_die();
            }
        }

        wp_die();
    }

    public function add_company_fields($fields)
    {
        if (isset(get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica']) && get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica'] == 1) {
            $fields['billing']['company_cui'] = [
                'label' => __('Company CUI', 'management-companie'),
                'required' => false,
                'class' => ['form-row-wide'],
                'clear' => true,
                'priority' => 31,
            ];

            $fields['billing']['company_regcom'] = [
                'label' => __('Company RegCom', 'management-companie'),
                'required' => false,
                'class' => ['form-row-wide'],
                'clear' => true,
                'priority' => 32,
            ];
        } else {
            unset($fields['billing']['billing_company']);
        }
        return $fields;
    }

    public function add_admin_company_fields($fields)
    {
        global $theorder;
        global $post;

        $order = ($post instanceof WP_Post) ? wc_get_order($post->ID) : null;

        if ($order instanceof WC_Order === false) {
            $order = $theorder;
        }

        $order_id = $order->get_id();

        if ($order instanceof WC_Order) {
            $company_cui = get_post_meta($order_id, 'company_cui', true);
            $company_regcom = get_post_meta($order_id, 'company_regcom', true);

            $fields['company_cui'] = [
                'label' => __('Company CUI', 'management-companie'),
                'show' => true,
                'value' => $company_cui,
                'wrapper_class' => 'form-field-wide',
                'style' => '',
            ];

            $fields['company_regcom'] = [
                'label' => __('Company RegCom', 'management-companie'),
                'show' => true,
                'value' => $company_regcom,
                'wrapper_class' => 'form-field-wide',
                'style' => '',
            ];
        }
        return $fields;
    }

    function mc_customer_meta_fields($fields)
    {
        $fields['billing']['fields']['company_cui'] = [
            'label' => __('Company CUI', 'management-companie'),
            'description' => '',
            'required' => false,
        ];

        $fields['billing']['fields']['company_regcom'] = [
            'label' => __('Company RegCom', 'management-companie'),
            'description' => '',
            'required' => false,
        ];

        return $fields;
    }

    public function mc_woocommerce_billing_fields($fields)
    {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        if (!$user) {
            return $fields;
        }

        if (isset(get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica']) && get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica'] == 1) {
            $fields['company_cui'] = [
                'label' => __('Company CUI', 'management-companie'),
                'required' => false,
                'class' => ['form-row-wide'],
                'clear' => true,
                'priority' => 31,
            ];

            $fields['company_regcom'] = [
                'label' => __('Company RegCom', 'management-companie'),
                'required' => false,
                'class' => ['form-row-wide'],
                'clear' => true,
                'priority' => 32,
            ];
        } else {
            unset($fields['billing_company']);
        }

        return $fields;
    }

    function mc_display_order_data($order_id)
    { ?>
        <?php if (isset(get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica']) && get_option('managementcompanie_setari_generale')['permite_factura_persoana_juridica'] == 1) : ?>
        <h2><?php _e(__('Company information', 'management-companie')); ?></h2>
        <table class="shop_table shop_table_responsive additional_info">
            <tbody>
            <tr>
                <th><?php _e(__('Company CUI', 'management-companie')); ?></th>
                <td><?php echo get_post_meta($order_id, 'company_cui', true); ?></td>
            </tr>
            <tr>
                <th><?php _e(__('Company RegCom', 'management-companie')); ?></th>
                <td><?php echo get_post_meta($order_id, 'company_regcom', true); ?></td>
            </tr>
            </tbody>
        </table>
    <?php endif;
    }

    public function mc_email_order_meta_fields($fields, $sent_to_admin, $order)
    {
        $fields['company_cui'] = [
            'label' => __('Company CUI', 'management-companie'),
            'value' => get_post_meta($order->get_id(), 'company_cui', true),
        ];
        $fields['company_regcom'] = [
            'label' => __('Company RegCom', 'management-companie'),
            'value' => get_post_meta($order->get_id(), 'company_regcom', true),
        ];
        return $fields;
    }

    public function save_admin_company_fields($order_id)
    {
        if (is_admin()) {
            if (isset($_POST['_billing_company_cui'])) {
                update_post_meta($order_id, 'company_cui', sanitize_text_field($_POST['_billing_company_cui']));
            }
            if (isset($_POST['_billing_company_regcom'])) {
                update_post_meta($order_id, 'company_regcom', sanitize_text_field($_POST['_billing_company_regcom']));
            }
        }
    }

    public function save_company_fields($order_id)
    {
        if (!empty($_POST['company_cui'])) {
            update_post_meta($order_id, 'company_cui', sanitize_text_field($_POST['company_cui']));
        }
        if (!empty($_POST['company_regcom'])) {
            update_post_meta($order_id, 'company_regcom', sanitize_text_field($_POST['company_regcom']));
        }
    }

    public function check_company_fields($data, $errors)
    {
        if (!empty($data['billing_company'])) {
            if (empty($data['company_cui'])) {
                $errors->add('validation', __('You entered a company name, please fill in the Company CUI field', 'management-companie'));
            }
            if (empty($data['company_regcom'])) {
                $errors->add('validation', __('You entered a company name, please fill in the Company RegCom field', 'management-companie'));
            }
        }
    }

    public function create_alias_name_for_product()
    {
        $args = [
            'id' => 'mc_product_alias',
            'label' => __('Facturare.Online alias name for product', 'management-companie'),
            'class' => 'form-row-wide',
            'desc_tip' => true,
            'description' => __('If set, this value will be used for invoice product name instead of Woocommerce product name', 'management-companie'),
        ];
        woocommerce_wp_text_input($args);
    }

    function save_alias_name_for_product($post_id)
    {
        $product = wc_get_product($post_id);
        $title = $_POST['mc_product_alias'] ?? '';
        $product->update_meta_data('mc_product_alias', sanitize_text_field($title));
        $product->save();
    }

    public function snif()
    {
        global $wpdb;
        $input = file_get_contents('php://input');
        if (FO::isJson($input) === true) {
            $input = json_decode($input);

            $snif = FO::decryptMessageCompany($input->loginid, $input->message, $input->crypt_message);
            if (FO::isJson($snif) === true) {
                $snif = json_decode($snif);
                $table_name = $wpdb->prefix . 'managementcompanie_facturi';

                $result = $wpdb->update($table_name, [
                    'status' => (int)$snif->status,
                    'document' => $snif->document,
                ], [
                    'invoiceid' => (int)$snif->invoiceid,
                ]);
                if ($result) {
                    die(FO::response(['invoiceid' => (int)$snif->invoiceid, 'status' => (int)$snif->status]));
                } else {
                    die(FO::response('', '03', 'Could not update invoice in your system'));
                }
            } else {
                die(FO::response('', '02', 'The message from server response is not JSON'));
            }
        } else {
            die(FO::response('', '01', 'The message from server response is not JSON'));
        }
    }
}
