<?php

namespace RG\ContentEngine\Core;

/**
 * Plugin Activator.
 *
 * @package RG\ContentEngine\Core
 */
class Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		// Run database migration.
		\RG\ContentEngine\Database\Schema::create_tables();

		// Set default settings if not exists.
		if ( ! get_option( 'rg_content_engine_settings' ) ) {
			update_option( 'rg_content_engine_settings', self::get_default_settings() );
		}

		flush_rewrite_rules();
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	private static function get_default_settings() {
		return [
			'openai_api_key' => '',
			'default_provider' => 'openai',
			'logging_enabled' => true,
		];
	}
}
