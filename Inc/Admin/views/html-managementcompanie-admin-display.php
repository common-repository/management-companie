<?php

use Automattic\WooCommerce\Utilities\OrderUtil;
use FacturareOnline\Inc\Libraries\FO;

/**
 * @link              https://facturare.online
 * @since             1.0.0
 * @package           FacturareOnline
 *
 */
global $theorder;
global $post;
OrderUtil::init_theorder_object($post);
$order = $theorder;
$order_id = $order->get_id();

$exchange_rate = !empty(get_post_meta($order_id, '_wcpbc_base_exchange_rate', true)) ? (float)get_post_meta($order_id, '_wcpbc_base_exchange_rate', true) : 1;
global $wpdb;

$facturi = $wpdb->get_results(
    "SELECT
	f.*,
	concat('[',
	GROUP_CONCAT( distinct
	CONCAT( '{\"receiptid\":\"', c.receiptid, '\", \"serie_chitanta\":\"', c.serie, '\", \"numar_chitanta\":\"', c.numar, '\", \"total_chitanta\":\"', c.total,'\", \"moneda_chitanta\":\"', c.moneda,'\", \"created_on_chitanta\":\"', c.created_on, '\"}' ) order by c.id desc),
	 ']') AS chitante,
	 concat('[',
	GROUP_CONCAT( distinct
	CONCAT( '{\"suma_incasata\":\"', ib.amount,'\", \"data_incasare\":\"', ib.date, '\"}' ) order by c.id desc),
	 ']') AS incasari_banca 
FROM
	{$wpdb->prefix}managementcompanie_facturi f
	LEFT JOIN {$wpdb->prefix}managementcompanie_chitante c ON f.invoiceid = c.invoiceid 
	LEFT JOIN {$wpdb->prefix}managementcompanie_incasari_banca ib ON f.invoiceid = ib.invoiceid 
WHERE
	f.orderid = $order_id
GROUP BY
	f.id
ORDER BY
	f.id DESC"
);

$facturi_active = $wpdb->get_row("SELECT * from {$wpdb->prefix}managementcompanie_facturi where orderid = $order_id and status not in (3,6) order by 1 desc");

$data_facturi = array();
foreach ($facturi as $factura) {
    $data_facturi[] = array(
        'id' => $factura->invoiceid,
        'tip' => FO::translateType($factura->tip),
        'serie' => $factura->serie,
        'numar' => $factura->numar,
        'total' => $factura->total,
        'status' => FO::translateStatus($factura->status),
        'created_on' => $factura->created_on,
    );
}

?>

<?php if (empty($facturi_active)): ?>
    <div id="mc-container">
        <div id="new-invoice-details">
            <h4><?php echo __('You will generate a new invoice using the following information', 'management-companie') ?></h4>
            <ul>
                <li><?php echo __('Series', 'management-companie') ?>:
                    <b><?php echo get_option('managementcompanie_setari_generale')['serie'] ?></b></li>
                <li><?php echo __('Type', 'management-companie') ?>:
                    <b>
                        <?php echo FO::translateType(get_option('managementcompanie_setari_generale')['tip_serie']); ?>
                    </b>
                </li>
                <li><?php echo __('Language', 'management-companie') ?>:
                    <b><?php echo get_option('managementcompanie_setari_generale')['limba'] ?></b></li>
                <li><?php echo __('Comments', 'management-companie') ?>:
                    <b><?php echo !empty(get_option('managementcompanie_setari_generale')['observatii']) ? get_option('managementcompanie_setari_generale')['observatii'] : '-' ?></b>
                </li>
                <li><?php echo __('Due days', 'management-companie') ?>:
                    <b><?php echo !empty(get_option('managementcompanie_setari_generale')['scadenta']) ? get_option('managementcompanie_setari_generale')['scadenta'] : '-' ?></b> <?php echo __('days', 'management-companie') ?>
                </li>
            </ul>
        </div>
        <div id="new-invoice-button">
            <button class="button-primary" id="generate-invoice"
                    data-order="<?php echo $order_id ?>"><?php echo __('Generate new invoice', 'management-companie') ?></button>
        </div>
    </div>
<?php else: ?>
    <h4><?php echo sprintf(__('You already have issued invoice ID <b>%d</b>, Series <b>%s</b>, Number <b>%s</b>, on <b>%s</b> for this order', 'management-companie'), $facturi_active->invoiceid, $facturi_active->serie, $facturi_active->numar, $facturi_active->created_on) ?></h4>
<?php endif; ?>

<?php if (!empty($facturi)): ?>
    <table id="invoices" class="" style="width:100%">
        <thead>
        <tr>
            <th><?php echo __('Type', 'management-companie'); ?></th>
            <th><?php echo __('Series', 'management-companie'); ?></th>
            <th><?php echo __('Number', 'management-companie'); ?></th>
            <th><?php echo __('Total', 'management-companie'); ?></th>
            <th><?php echo __('Status', 'management-companie'); ?></th>
            <th><?php echo __('Generated at', 'management-companie'); ?></th>
            <th><?php echo __('Actions', 'management-companie'); ?></th>
            <th><?php echo __('Receipt actions', 'management-companie'); ?></th>
            <th><?php echo __('Bank actions', 'management-companie'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($facturi as $factura): ?>
            <?php
            // verific cat am incasat prin chitante ca sa stiu ce suma maxima afisez la generarea noii chitante sau incasarii prin banca
            $chitante = json_decode($factura->chitante ?: '');
            $total_chitante = 0;
            if (!empty($chitante)) {
                foreach ($chitante as $chitanta) {
                    $total_chitante += $chitanta->total_chitanta;
                }
            }

            // verific cat am incasat prin banca ca sa stiu ce suma maxima afisez la generarea noii chitante sau incasarii prin banca
            $incasari_banca = json_decode($factura->incasari_banca ?: '');
            $total_banca = 0;
            if (!empty($incasari_banca)) {
                foreach ($incasari_banca as $ib) {
                    $total_banca += $ib->suma_incasata * $exchange_rate;
                }
            }
            ?>
            <tr style="text-align: center; background: #eee">
                <td><?php echo FO::translateType($factura->tip) ?></td>
                <td><?php echo $factura->serie ?></td>
                <td><?php echo $factura->numar ?></td>
                <td><?php echo $factura->total . ' ' . $factura->moneda ?></td>
                <td><?php echo FO::translateStatus($factura->status) ?></td>
                <td><?php echo date('d-m-Y H:i:s', strtotime($factura->created_on)) ?></td>
                <td style="width:20%">
                    <button class="view-invoice button-primary"
                            data-order="<?php echo $order_id ?>"
                            data-invoiceid="<?php echo (int)$factura->invoiceid ?>">
                        <?php echo __('View', 'management-companie') ?>
                    </button>
                    <a href="<?php echo admin_url('admin-ajax.php') . '?' . http_build_query(array('action' => 'descarca_factura', 'order' => $order_id, 'invoiceid' => (int)$factura->invoiceid)) ?>"
                       class="download-invoice button-secondary">
                        <?php echo __('Download', 'management-companie') ?>
                    </a>
                    <?php if ($factura->status == 1): ?>
                        <button class="cancel-invoice button-secondary"
                                data-order="<?php echo $order_id ?>"
                                data-invoiceid="<?php echo (int)$factura->invoiceid ?>">
                            <?php echo __('Cancel', 'management-companie') ?>
                        </button>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $permite_generare_chitanta = isset(get_option('managementcompanie_setari_generale')['permite_generare_chitanta_factura']) ? get_option('managementcompanie_setari_generale')['permite_generare_chitanta_factura'] : 0;
                    if ($permite_generare_chitanta && in_array($factura->status, array(1, 5)) && $factura->tip == 'ff'): ?>
                        <div class="generate-receipt-line">
                            <input value="<?php echo round($factura->total - $total_chitante - $total_banca, 2) ?>"
                                   class="receipt-amount" type="number"
                                   step="0.0001" min="0.0001"
                                   max="<?php echo round($factura->total - $total_chitante - $total_banca, 2) ?>"/> <?php echo $factura->moneda ?>
                            <button class="generate-receipt button-secondary"
                                    data-order="<?php echo $order_id ?>"
                                    data-invoiceid="<?php echo (int)$factura->invoiceid ?>">
                                <?php echo __('Generate receipt', 'management-companie') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $permite_incasare_banca = isset(get_option('managementcompanie_setari_generale')['permite_incasare_banca_factura']) ? get_option('managementcompanie_setari_generale')['permite_incasare_banca_factura'] : 0;
                    if ($permite_incasare_banca && in_array($factura->status, array(1, 5)) && $factura->tip == 'ff'): ?>
                        <div class="charge-bank-line">
                            <div>
                                <label for="bank-amount"><?php echo __('Charge amount', 'management-companie'); ?>
                                    <input value="<?php echo round($factura->total - $total_chitante - $total_banca, 2) ?>"
                                           class="bank-amount"
                                           name="bank-amount"
                                           type="number"
                                           step="0.0001" min="0.0001"
                                           max="<?php echo round($factura->total - $total_chitante - $total_banca, 2) ?>"/> <?php echo $factura->moneda ?>
                                </label>
                            </div>
                            <div>
                                <label for="mc-datepicker"><?php echo __('Charge date', 'management-companie'); ?>
                                    <input data-mindate="<?php echo date('d-m-Y', strtotime($factura->created_on)) ?>"
                                           value="<?php echo date('d-m-Y', strtotime($factura->created_on)) ?>"
                                           type="text"
                                           class="mc-datepicker"
                                           name="mc-datepicker"/>
                                </label>
                            </div>
                            <hr/>
                            <div>
                                <button class="charge-bank button-secondary"
                                        data-order="<?php echo $order_id ?>"
                                        data-invoiceid="<?php echo (int)$factura->invoiceid ?>">
                                    <?php echo __('Charge via bank', 'management-companie') ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>

            <?php
            $chitante = json_decode($factura->chitante ?: '');
            if (!empty($chitante)) :
                foreach ($chitante as $chitanta):
                    echo '<tr style="text-align: center">'; ?>
                    <td><?php echo __('Receipt', 'management-companie') ?></td>
                    <td><?php echo $chitanta->serie_chitanta ?></td>
                    <td><?php echo $chitanta->numar_chitanta ?></td>
                    <td><?php echo $chitanta->total_chitanta . ' ' . $chitanta->moneda_chitanta ?></td>
                    <td><?php echo FO::translateStatus(1) ?></td>
                    <td><?php echo date('d-m-Y H:i:s', strtotime($chitanta->created_on_chitanta)) ?></td>
                    <td>
                        <button class="view-receipt button-primary"
                                data-order="<?php echo $order_id ?>"
                                data-receiptid="<?php echo (int)$chitanta->receiptid ?>">
                            <?php echo __('View', 'management-companie') ?>
                        </button>
                        <a href="<?php echo admin_url('admin-ajax.php') . '?' . http_build_query(array('action' => 'descarca_chitanta', 'order' => $order_id, 'receiptid' => (int)$chitanta->receiptid)) ?>"
                           class="download-receipt button-secondary">
                            <?php echo __('Download', 'management-companie') ?>
                        </a>
                    </td>
                    <td></td>
                    <?php echo '</tr>';
                endforeach;
            endif;

            $incasari_banca = json_decode($factura->incasari_banca ?: '');
            if (!empty($incasari_banca)) :
                foreach ($incasari_banca as $ib):
                    echo '<tr style="text-align: center">'; ?>
                    <td><?php echo __('Bank charge', 'management-companie') ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td><?php echo $ib->suma_incasata * $exchange_rate . ' RON' ?></td>
                    <td>-</td>
                    <td><?php echo date('d-m-Y H:i:s', strtotime($ib->data_incasare)) ?></td>
                    <td></td>
                    <td></td>
                    <?php echo '</tr>';
                endforeach;
            endif; ?>
        <?php endforeach; ?>

        </tbody>
    </table>
<?php endif; ?>

<div id="page-loading" class="hidden">
    <div class="msg">
        <div class="c">
            <div class="icon"></div>
            <span class="muted" id="pl-msg"></span>
        </div>
    </div>
</div>
