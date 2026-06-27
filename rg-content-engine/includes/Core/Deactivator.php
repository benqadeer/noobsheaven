<?php

namespace RG\ContentEngine\Core;

/**
 * Plugin Deactivator.
 *
 * @package RG\ContentEngine\Core
 */
class Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
