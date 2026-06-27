<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */
/**
 * Plugin Name: RG Content Engine
 * Plugin URI: https://ranksgiving.com/
 * Description: AI-powered WooCommerce and SEO automation plugin.
 * Version: 1.0.0
 * Author: Abdur-Rehman Qadeer (RanksGiving)
 * Author URI: https://ranksgiving.com/
 * Text Domain: rg-content-engine
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package RG_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'RG_CONTENT_ENGINE_VERSION', '1.0.0' );
define( 'RG_CONTENT_ENGINE_FILE', __FILE__ );
define( 'RG_CONTENT_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'RG_CONTENT_ENGINE_URL', plugin_dir_url( __FILE__ ) );
define( 'RG_CONTENT_ENGINE_BASENAME', plugin_basename( __FILE__ ) );

// Load Autoloader.
if ( file_exists( RG_CONTENT_ENGINE_PATH . 'vendor/autoload.php' ) ) {
	require_once RG_CONTENT_ENGINE_PATH . 'vendor/autoload.php';
} else {
	// Fallback autoloader for PSR-4 if vendor/autoload.php doesn't exist (e.g. during development).
	spl_autoload_register( function ( $class ) {
		$prefix = 'RG\\ContentEngine\\';
		$base_dir = RG_CONTENT_ENGINE_PATH . 'includes/';
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
		$relative_class = substr( $class, $len );
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require $file;
		}
	} );
}

/**
 * Initialize the plugin.
 */
function rg_content_engine_init() {
	return \RG\ContentEngine\Core\Plugin::get_instance();
}

add_action( 'plugins_loaded', 'rg_content_engine_init' );

// Activation and Deactivation Hooks.
register_activation_hook( __FILE__, [ \RG\ContentEngine\Core\Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \RG\ContentEngine\Core\Deactivator::class, 'deactivate' ] );
