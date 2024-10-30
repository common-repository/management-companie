<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                    https://facturare.online
 * @since                   1.0.0
 * @package                 FacturareOnline
 *
 * @wordpress-plugin
 * Plugin Name:             Facturare.Online
 * Plugin URI:              https://facturare.online/acces-api
 * Description:             Online invoicing software using https://facturare.online API
 * Version:                 1.2.5
 * WC requires at least:    6.9.0
 * Author:                  ITCreative SRL
 * Author URI:              https://itcreative.ro
 * License:                 GPL-3.0
 * License URI:             https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:             management-companie
 * Domain Path:             /languages
 */

namespace FacturareOnline;

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

global $jal_db_version;
$jal_db_version = 1.2;

/**
 * Define Constants
 */

const NS = __NAMESPACE__ . '\\';
define(NS . 'PLUGIN_NAME', 'management-companie');
define(NS . 'PLUGIN_VERSION', '1.2.5');
define(NS . 'PLUGIN_NAME_DIR', plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_NAME_URL', plugin_dir_url(__FILE__));
define(NS . 'PLUGIN_BASENAME', plugin_basename(__FILE__));
define(NS . 'PLUGIN_TEXT_DOMAIN', 'management-companie');

/**
 * Autoload Classes
 */
require_once(PLUGIN_NAME_DIR . 'Inc/Libraries/autoloader.php');

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook(__FILE__, [NS . 'Inc\Core\Activator', 'activate']);

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */
register_deactivation_hook(__FILE__, [NS . 'Inc\Core\Deactivator', 'deactivate']);


/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */
class FacturareOnline
{

	/**
	 * The instance of the plugin.
	 *
	 * @since    1.0.0
	 * @var      Init $init Instance of the plugin.
	 */
	private static $init;

	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init()
	{
		if (null === self::$init) {
			self::$init = new Inc\Core\Init();
			self::$init->run();
		}

		return self::$init;
	}
}

return FacturareOnline::init();
