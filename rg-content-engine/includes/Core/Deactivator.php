<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

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
