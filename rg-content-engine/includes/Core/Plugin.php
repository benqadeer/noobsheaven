<?php

namespace RG\ContentEngine\Core;

/**
 * Main Plugin Class.
 *
 * @package RG\ContentEngine\Core
 */
class Plugin {

	/**
	 * Instance of this class.
	 *
	 * @var Plugin
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Initialize modules here.
		add_action( 'init', [ $this, 'load_textdomain' ] );

		if ( is_admin() ) {
			$this->init_admin();
		}

		$this->init_rest_api();
	}

	/**
	 * Load translation files.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'rg-content-engine', false, dirname( RG_CONTENT_ENGINE_BASENAME ) . '/languages' );
	}

	/**
	 * Initialize admin functionality.
	 */
	private function init_admin() {
		$menu = new \RG\ContentEngine\Admin\Menu();
		add_action( 'admin_menu', [ $menu, 'register' ] );

		$meta_box = new \RG\ContentEngine\Admin\MetaBox();
		$meta_box->register();

		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting( 'rg_content_engine_settings_group', 'rg_content_engine_settings' );
	}

	/**
	 * Initialize REST API.
	 */
	private function init_rest_api() {
		add_action( 'rest_api_init', function() {
			$controller = new \RG\ContentEngine\REST\Controller();
			$controller->register_routes();
		} );
	}
}
