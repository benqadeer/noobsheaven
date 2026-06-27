<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Generator;

/**
 * Logger Class.
 *
 * @package RG\ContentEngine\Generator
 */
class Logger {

	/**
	 * Log a message.
	 *
	 * @param string $level Log level (info, error, warning).
	 * @param string $message Log message.
	 * @param mixed  $context Additional context.
	 */
	public static function log( string $level, string $message, $context = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_logs';

		$wpdb->insert( $table, [
			'level'   => $level,
			'message' => $message,
			'context' => is_scalar( $context ) ? $context : json_encode( $context ),
		] );
	}

	public static function error( string $message, $context = null ) {
		self::log( 'error', $message, $context );
	}

	public static function info( string $message, $context = null ) {
		self::log( 'info', $message, $context );
	}
}
