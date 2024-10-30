<?php

namespace FacturareOnline\Inc\Core;

use function curl_version;
use function extension_loaded;
use function version_compare;

/**
 * @link              https://facturare.online
 * @since             1.0.0
 * @package           FacturareOnline
 *
 */
class Activator
{

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$php_min_version = '5.5';
		$curl_min_version = '7.29.0';
		$openssl_min_version = 0x1000100f; //1.0.1

		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if (version_compare(PHP_VERSION, $php_min_version, '<')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires a minmum PHP Version of ' . $php_min_version);
		}

		if (version_compare(WOOCOMMERCE_VERSION, '6.9.0', '<')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires Woocommerce minimum version 6.9.0 or later');
		}

		if (!extension_loaded('curl')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires PHP CURL extension to be installed and active');
		}

		if (version_compare(curl_version()['version'], $curl_min_version, '<')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires a minmum cURL Version of ' . $curl_min_version);
		}

		if (!extension_loaded('openssl')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires a minmum OpenSSL extension');
		}

		if (OPENSSL_VERSION_NUMBER < $openssl_min_version) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('This plugin requires a minmum OpenSSL Version of 1.0.1' . $openssl_min_version);
		}

		self::install_db();
	}

	public static function install_db()
	{
		global $jal_db_version;
		global $wpdb;

		$installed_ver = get_option("jal_db_version");

		if ($installed_ver != $jal_db_version) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'managementcompanie_facturi';

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`orderid` int(10) unsigned NOT NULL,
				`invoiceid` int(11) unsigned NOT NULL,
				`serie` varchar(255) NOT NULL,
				`numar` int(3) unsigned NOT NULL,
				`total` decimal(20,2) NOT NULL DEFAULT '0.00',
				`moneda` char(3) NOT NULL DEFAULT 'RON',
				`tip` enum('ff','fp') NOT NULL DEFAULT 'ff',
				`code` varchar(255) NOT NULL,
				`status` int(3) NOT NULL DEFAULT '1',
				`document` mediumblob NOT NULL COMMENT 'document = max 16MB',
				`created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`modified_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE KEY `invoiceid_UNIQUE` (`invoiceid`),
				KEY `fk_ps_managementcompanie_1_idx` (`orderid`),
				KEY `orderid_invoiceid` (`orderid`,`invoiceid`),
				KEY `orderid_status` (`orderid`,`status`)
		  	) $charset_collate;";
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'managementcompanie_chitante';
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`receiptid` int(11) unsigned NOT NULL,
				`invoiceid` int(11) unsigned NOT NULL,
				`serie` varchar(255) NOT NULL,
				`numar` int(3) unsigned NOT NULL,
				`total` decimal(20,2) NOT NULL DEFAULT '0.00',
				`moneda` char(3) NOT NULL DEFAULT 'RON',
				`code` varchar(255) NOT NULL,
				`document` mediumblob NOT NULL COMMENT 'document = max 16MB',
				`created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE KEY `receiptid_UNIQUE` (`receiptid`),
				KEY `receiptid_invoiceid` (`receiptid`,`invoiceid`)
		  	) $charset_collate;";
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'managementcompanie_incasari_banca';
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`invoiceid` int(11) unsigned NOT NULL,
				`amount` decimal(20,2) NOT NULL,
				`date` datetime NOT NULL,
				`created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `invoiceid` (`invoiceid`)
		  	) $charset_collate;";
			dbDelta($sql);

			update_option("jal_db_version", $jal_db_version);
		}
	}
}
