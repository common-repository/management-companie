<?php
/**
 * @link              https://facturare.online
 * @since             1.0.0
 * @package           FacturareOnline
 *
 */

namespace FacturareOnline\Inc\Libraries;

use FacturareOnline\Inc\Libraries\JsonSchema\Constraints\Constraint as Constraint;
use FacturareOnline\Inc\Libraries\JsonSchema\Validator as Validator;
use FacturareOnline\Inc\Libraries\phpseclib\Crypt\AES as AES;
use FacturareOnline\Inc\Libraries\phpseclib\Crypt\RSA as RSA;
use FacturareOnline\Inc\Libraries\sylouuu\Curl\Method as Curl;
use WC_Order;
use WC_Product;
use WC_TAX;
use function abs;
use function html_entity_decode;
use function round;
use function strip_tags;
use function strtolower;
use function substr;
use function wc_string_to_bool;

class FO
{
	// absolute URL to this module
	private static $url_factura_noua = 'https://facturare.online/api/adauga-factura';
	// absolute path (in OS sense) to this module
	private static $url_anuleaza_factura = 'https://facturare.online/api/anuleaza-factura';
	private static $url_genereza_chitanta_factura = 'https://facturare.online/api/genereaza-chitanta-factura';
	private static $url_incaseaza_banca_factura = 'https://facturare.online/api/incaseaza-banca-factura';

	public static function getFactura($order_id)
	{
		$order = new WC_Order($order_id);

		$request = [];
		if (empty($order->get_billing_company())) {
			// persoana fizica
			$request['client'] = [
				'tip'        => 'pf',
				'denumire'   => $order->get_formatted_billing_full_name(),
				'adresa'     => $order->get_billing_address_1() . ($order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : ''),
				'tara'       => $order->get_billing_country(),
				'judet'      => $order->get_billing_state(),
				'localitate' => $order->get_billing_city(),
				'telefon'    => $order->get_billing_phone(),
				'email'      => $order->get_billing_email(),
			];
		} else {
			// persoana juridica
            $vat_number = get_post_meta($order_id, 'company_cui', true);
			// determin atributul fiscal RO
			if (stripos($vat_number, 'RO') !== false) {
				$atribut_fiscal = 'RO';
				$cui = trim(str_ireplace('RO', '', $vat_number));
				$vat = 1;
			} elseif (stripos($vat_number, 'R') !== false) {
				// determin atributul fiscal R
				$atribut_fiscal = 'R';
				$cui = trim(str_ireplace('R', '', $vat_number));
				$vat = 1;
			} else {
				$atribut_fiscal = '-';
				$cui = $vat_number;
				$vat = 0;
			}
			$request['client'] = [
				'tip'            => 'pj',
				'denumire'       => $order->get_billing_company(),
				'cui'            => $cui,
				'nr_reg_com'     => get_post_meta($order_id, 'company_regcom', true),
				'atribut_fiscal' => $atribut_fiscal, // RO, R, -
				'vat'            => $vat, // 0 - neplatitor tva, 1 - platitor tva
				'adresa'         => $order->get_billing_address_1() . ($order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : ''),
				'tara'           => $order->get_billing_country(),
				'judet'          => $order->get_billing_state(),
				'localitate'     => $order->get_billing_city(),
				'telefon'        => $order->get_billing_phone(),
				'email'          => $order->get_billing_email(),
			];
		}

		$p = [];
		foreach ($order->get_items() as $product) {
			$prod = new WC_Product($product->get_product_id());

			$mc_product_alias = get_post_meta($product->get_product_id(), 'mc_product_alias', true);

			$product_name = (!empty($mc_product_alias) ? $mc_product_alias : $product['name']);

			$tax_rate = 0;
			if ($prod->is_taxable()) {
				$tax = WC_TAX::get_rates_for_tax_class($product->get_tax_class());
				if (!empty($tax)) {
					$tax = array_pop($tax);
					$tax_rate = (int)$tax->tax_rate;
				}
			}

			$p[] = [
				'cod'                => $prod->get_sku(),
				'denumire'           => substr(strip_tags(html_entity_decode($product_name, ENT_QUOTES)), 0, 250),
				'denumire_english'   => '',
				'denumire_french'    => '',
				'denumire_german'    => '',
				'denumire_hungarian' => '',
				'pret'               => round(($product->get_subtotal() + $product->get_subtotal_tax()) / $product->get_quantity(), 4),
				'vat'                => $tax_rate,
				'tip'                => 1,
				'cantitate'          => round((float)$product->get_quantity(), 2),
				'info'               => '',
				'um'                 => 'buc',
			];
		}

		if (!empty($order->get_total_discount())) {
			$p[] = [
				'cod'                => '',
				'denumire'           => 'Reducere cos',
				'denumire_english'   => 'Cart discount',
				'denumire_french'    => '',
				'denumire_german'    => '',
				'denumire_hungarian' => '',
				'pret'               => round((float)$order->cart_discount + (float)$order->cart_discount_tax, 4),
				'vat'                => $order->cart_discount > 0 ? round(((float)$order->cart_discount + (float)$order->cart_discount_tax) / (float)$order->cart_discount * 100 - 100) : 0,
				'tip'                => 1,
				'cantitate'          => -1,
				'info'               => '',
				'um'                 => 'buc',
			];
		}

		if (!empty($order->get_fees())) {
			foreach ($order->get_fees() as $fee) {
				$p[] = [
					'cod'                => '',
					'denumire'           => substr(strip_tags(html_entity_decode($fee->get_name(), ENT_QUOTES)), 0, 250),
					'denumire_english'   => '',
					'denumire_french'    => '',
					'denumire_german'    => '',
					'denumire_hungarian' => '',
					'pret'               => round(abs((float)$fee->get_total() + (float)$fee->get_total_tax()), 4),
					'vat'                => $fee->get_total() > 0 ? round(((float)$fee->get_total() + (float)$fee->get_total_tax()) / (float)$fee->get_total() * 100 - 100) : 0,
					'tip'                => 1,
					'cantitate'          => ($fee->get_total() > 0 ? 1 : -1),
					'info'               => '',
					'um'                 => 'buc',
				];
			}
		}

		if ($order->has_shipping_address() && !empty($order->get_shipping_method())) {
			$shipping_tax_rate = 0;
			if (wc_string_to_bool(get_option('woocommerce_calc_taxes'))) {
				$shipping_rate = WC_TAX::get_shipping_tax_rates();
				if (!empty($shipping_rate)) {
					$shipping_rate = array_pop($shipping_rate);
					$shipping_tax_rate = (int)$shipping_rate['rate'];
				}
			}

			$p[] = [
				'cod'                => '',
				'denumire'           => substr($order->get_shipping_method(), 0, 250),
				'denumire_english'   => '',
				'denumire_french'    => '',
				'denumire_german'    => '',
				'denumire_hungarian' => '',
				'pret'               => round((float)$order->get_shipping_total() + (float)$order->get_shipping_tax(), 4),
				'vat'                => $shipping_tax_rate,
				'tip'                => 1,
				'cantitate'          => 1,
				'info'               => '',
				'um'                 => 'buc',
			];
		}

		$currency = get_woocommerce_currency();

		if (strtolower($currency) == 'lei') {
			$currency = 'RON';
		}

		$urlparts = parse_url(home_url());
		$domain = preg_replace('/www\./i', '', $urlparts['host']);

		$observatii = __('Order no. ', 'management-companie') . $order_id . __(' on website ', 'management-companie') . get_bloginfo('url');
		$payment_method = !empty($order->get_payment_method_title()) ? '<br />' . __('Payment method: ', 'management-companie') . $order->get_payment_method_title() : '';
		$observatii .= $payment_method;
		$custom_observatii = (!empty(get_option('managementcompanie_setari_generale')['observatii'])) ? '<br />' . get_option('managementcompanie_setari_generale')['observatii'] : '';
		$observatii .= $custom_observatii;

		$request['produse'] = $p;
		$exchange_rate = $order->get_meta('_wcpbc_base_exchange_rate');
		$request['factura'] = [
			'tip'          => get_option('managementcompanie_setari_generale')['tip_serie'],
			'serie'        => get_option('managementcompanie_setari_generale')['serie'],
			'limba'        => get_option('managementcompanie_setari_generale')['limba'],
			'moneda'       => $currency,
			'orderid'      => $order_id,
			'website'      => $domain,
			'curs_valutar' => round(!empty($exchange_rate) ? $exchange_rate : 1, 4), // 1- RON, curs valutar valuta/ron
			'zecimale'     => 2, //int
			'observatii'   => $observatii,
			'platita'      => (int)$order->is_paid(),
		];

		if (!empty(get_option('managementcompanie_setari_generale')['scadenta']) && (int)get_option('managementcompanie_setari_generale')['scadenta'] > 0) {
			$request['factura']['zile_scadenta'] = (int)get_option('managementcompanie_setari_generale')['scadenta'];
		}

		$post = self::encryptMessageCompany(get_option('managementcompanie_setari_generale')['companyid'], $request, 'https://facturare.online/static/validation/factura.json');
		if (self::isError($post)) {
			return $post;
		} else {
			return self::MCCommunicate(self::$url_factura_noua, $post);
		}
	}

	private static function encryptMessageCompany($loginid, $message, $schema)
	{
		$message = json_encode($message);
		$validate = self::validateJSONagainstSCHEMA($message, $schema);

		if (self::isError($validate)) {
			return $validate;
		}

		if (empty($loginid)) {
			return self::response('', '02', 'Nu ati setat ID-ul companiei');
		}

		$response = [];

		$response['loginid'] = $loginid;
		// criptare aes
		$aes_key = md5(uniqid());
		$aes = new AES();

		$aes->setIV(get_option('managementcompanie_setari_generale')['iv']);
		$aes->setKey($aes_key);
		$response['message'] = bin2hex(base64_encode($aes->encrypt($message)));

		// criptare rsa
		$rsa = new RSA();
		$rsa->loadKey(get_option('managementcompanie_setari_generale')['rsa_key_public']);
		$response['crypt_message'] = base64_encode($rsa->encrypt($aes_key));

		return $response;
	}

	private static function validateJSONagainstSCHEMA($data, $schema)
	{
		$validator = new Validator;
		$data = json_decode($data);

		$request = new Curl\Get($schema);
		$request->send();
		if ($request->getStatus() !== 200) {
			return self::response('', '-1', 'Nu am putut obtine schema de validare de la Facturare.Online');
		}
		$schemaMC = $request->getResponse();
		$validator->validate($data, $schemaMC, Constraint::CHECK_MODE_APPLY_DEFAULTS);
		if (!$validator->isValid()) {
			$message = [];
			$message['reason'] = 'JSON does not validate';
			$message['errors'] = [];
			foreach ($validator->getErrors() as $error) {
				$message['errors'][] = $error['property'] . ' ' . $error['message'];
			}
			return self::response('', '10', json_encode($message));
		}
		return true;
	}

	public static function response($message = null, $code = null, $reason = null)
	{
		$message = json_encode($message);
		if (!is_array($reason)) {
			$reason = json_encode(['reason' => $reason, 'errors' => []]);
		}
		return json_encode(
			[
				'response' => [
					'error_code'   => $code,
					'error_reason' => $reason,
					'message'      => $message,
				],
			]
		);
	}

	public static function isError($message)
	{
		if (isset($message) && isset($message->response) && isset($message->response->error_code) && $message->response->error_code !== null) {
			return true;
		} else {
			return false;
		}
	}

	private static function MCCommunicate($url, $payload)
	{
		$request = new Curl\Post($url, [
			'data'       => [
				'loginid'       => $payload['loginid'],
				'message'       => $payload['message'],
				'crypt_message' => $payload['crypt_message'],
			],
			'is_payload' => true,
		]);
		/*$request->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
		$request->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
		$request->setCurlOption(CURLOPT_RETURNTRANSFER, true);*/
		$request->send();

		return $request->getResponse();
	}

	public static function anuleazaFactura($invoiceid)
	{
		$request = [];
		$request['invoiceid'] = $invoiceid;
		$post = self::encryptMessageCompany(get_option('managementcompanie_setari_generale')['companyid'], $request, 'https://facturare.online/static/validation/anuleaza_factura.json');
		if (self::isError($post)) {
			return $post;
		} else {
			return self::MCCommunicate(self::$url_anuleaza_factura, $post);
		}
	}

	public static function genereazaChitantaFactura($invoiceid, $suma)
	{
		$request = [];
		$request['invoiceid'] = $invoiceid;
		$request['suma'] = (float)$suma;
		$post = self::encryptMessageCompany(get_option('managementcompanie_setari_generale')['companyid'], $request, 'https://facturare.online/static/validation/genereaza_chitanta_factura.json');
		if (self::isError($post)) {
			return $post;
		} else {
			return self::MCCommunicate(self::$url_genereza_chitanta_factura, $post);
		}
	}

	public static function incaseazaBancaFactura($invoiceid, $suma, $data = null, $exchange_rate = 1)
	{
		$request = [];
		$request['invoiceid'] = $invoiceid;
		// suma va fi mereu in RON
		$request['suma'] = round($suma * $exchange_rate, 2);
		if (!empty($data)) {
			$request['data'] = date('Y-m-d H:i:s', strtotime($data));
		}
		$post = self::encryptMessageCompany(get_option('managementcompanie_setari_generale')['companyid'], $request, 'https://facturare.online/static/validation/incaseaza_banca_factura.json');
		if (self::isError($post)) {
			return $post;
		} else {
			return self::MCCommunicate(self::$url_incaseaza_banca_factura, $post);
		}
	}

	public static function translateStatus($status)
	{
		switch ($status) {
			case '1':
				return __('Issued', 'management-companie');
			case '2':
				return __('Draft', 'management-companie');
			case '3':
				return __('Canceled', 'management-companie');
			case '4':
				return __('Fully charged', 'management-companie');
			case '5':
				return __('Partially charged', 'management-companie');
			case '6':
				return __('Reversed', 'management-companie');
			case '7':
				return __('Reverses an invoice', 'management-companie');
			case '8':
				return __('Partially reversed', 'management-companie');
			case '9':
				return __('Partially reverses an invoice', 'management-companie');
			case '10':
				return __('Scheduled issuance', 'management-companie');
		}
		return '';
	}

	public static function translateType($status)
	{
		switch ($status) {
			case 'ff':
				return __('Fiscal invoice', 'management-companie');
			case 'fp':
				return __('Proforma invoice', 'management-companie');
		}
		return '';
	}

	public static function getError($ret)
	{
		$ret = json_decode($ret);
		$response = $ret->response;
		$error_code = $response->error_code;
		$error_reason = $response->error_reason;
		if (!is_null($error_code)) {
			return "Eroare " . $error_code . ": Motiv: " . $error_reason;
		}
		return false;
	}

	public static function isJson($string)
	{
		if (is_object(json_decode($string))) {
			return true;
		}
		return false;
	}

	public static function decryptMessageCompany($loginid, $message, $crypt_message)
	{
		if (empty($loginid)) {
			return self::response('', '02', 'Nu ati setat ID-ul companiei');
		}
		if (empty($message)) {
			return self::response('', '03', 'Decriptare raspuns - nu se primeste [criptul AES]');
		}
		if (empty($crypt_message)) {
			return self::response('', '04', 'Decriptare raspuns - nu se primeste [criptul RSA]');
		}

		$rsa = new RSA();
		$rsa->loadKey(get_option('managementcompanie_setari_generale')['rsa_key_private']);

		$aes_key = $rsa->decrypt(base64_decode($crypt_message));
		if (empty($aes_key)) {
			return self::response('', '05', 'Nu am putut decripta cheia AES din RSA');
		}

		$aes = new AES();

		$aes->setIV(get_option('managementcompanie_setari_generale')['iv']);
		$aes->setKey($aes_key);
		$response = $aes->decrypt(base64_decode(self::hex2str($message)));

		if (empty($response)) {
			return self::response('', '06', 'Nu am putut decripta mesajul din criptul AES');
		}
		return json_decode($response);
	}

	private static function hex2str($hex)
	{
		$str = '';
		for ($i = 0; $i < strlen($hex); $i += 2) {
			$str .= chr(hexdec(substr($hex, $i, 2)));
		}
		return $str;
	}
}
