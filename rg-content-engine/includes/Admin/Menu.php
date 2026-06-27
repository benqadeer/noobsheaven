<?php

namespace RG\ContentEngine\Admin;

/**
 * Admin Menu Class.
 *
 * @package RG\ContentEngine\Admin
 */
class Menu {

	/**
	 * Register menu items.
	 */
	public function register() {
		add_menu_page(
			'RG Content Engine',
			'RG Content Engine',
			'manage_options',
			'rg-content-engine',
			[ $this, 'render_dashboard' ],
			'dashicons-superhero',
			30
		);

		add_submenu_page(
			'rg-content-engine',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'rg-content-engine',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'Content Profiles',
			'Content Profiles',
			'manage_options',
			'rg-ce-profiles',
			[ $this, 'render_profiles' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'Keyword Manager',
			'Keyword Manager',
			'manage_options',
			'rg-ce-keywords',
			[ $this, 'render_keywords' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'Bulk Generator',
			'Bulk Generator',
			'manage_options',
			'rg-ce-bulk',
			[ $this, 'render_bulk' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'History',
			'History',
			'manage_options',
			'rg-ce-history',
			[ $this, 'render_history' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'Settings',
			'Settings',
			'manage_options',
			'rg-ce-settings',
			[ $this, 'render_settings' ]
		);

		add_submenu_page(
			'rg-content-engine',
			'Logs',
			'Logs',
			'manage_options',
			'rg-ce-logs',
			[ $this, 'render_logs' ]
		);
	}

	public function render_dashboard() {
		echo '<div class="wrap"><h1>RG Content Engine Dashboard</h1><p>Welcome to RanksGiving Content Engine.</p></div>';
	}

	public function render_profiles() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/profiles.php';
	}

	public function render_keywords() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/keywords.php';
	}

	public function render_bulk() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/bulk.php';
	}

	public function render_history() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/history.php';
	}

	public function render_logs() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/logs.php';
	}

	public function render_settings() {
		include RG_CONTENT_ENGINE_PATH . 'admin/templates/settings.php';
	}
}
