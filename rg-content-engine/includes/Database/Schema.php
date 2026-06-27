<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Database;

/**
 * Database Schema Class.
 *
 * @package RG\ContentEngine\Database
 */
class Schema {

	/**
	 * Create custom tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Keywords Table.
		$table_keywords = $wpdb->prefix . 'rg_ce_keywords';
		$sql_keywords = "CREATE TABLE $table_keywords (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			keyword varchar(255) NOT NULL,
			search_volume int(11) DEFAULT 0,
			difficulty int(11) DEFAULT 0,
			intent varchar(50) DEFAULT '',
			priority int(11) DEFAULT 0,
			notes text DEFAULT '',
			is_used tinyint(1) DEFAULT 0,
			product_id bigint(20) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY keyword (keyword),
			KEY is_used (is_used)
		) $charset_collate;";

		// Profiles Table.
		$table_profiles = $wpdb->prefix . 'rg_ce_profiles';
		$sql_profiles = "CREATE TABLE $table_profiles (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			provider varchar(50) NOT NULL,
			model varchar(50) NOT NULL,
			prompt text NOT NULL,
			html_blueprint longtext NOT NULL,
			seo_rules text,
			writing_style varchar(100),
			temperature float DEFAULT 0.7,
			max_tokens int(11) DEFAULT 2000,
			keyword_rules text,
			image_alt_rules text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// History Table.
		$table_history = $wpdb->prefix . 'rg_ce_history';
		$sql_history = "CREATE TABLE $table_history (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			product_id bigint(20) NOT NULL,
			keyword_id bigint(20) DEFAULT NULL,
			profile_id bigint(20) DEFAULT NULL,
			provider varchar(50),
			model varchar(50),
			prompt longtext,
			response longtext,
			cost float DEFAULT 0,
			execution_time float DEFAULT 0,
			status varchar(50),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY product_id (product_id)
		) $charset_collate;";

		// Logs Table.
		$table_logs = $wpdb->prefix . 'rg_ce_logs';
		$sql_logs = "CREATE TABLE $table_logs (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			level varchar(20) NOT NULL,
			message text NOT NULL,
			context longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql_keywords );
		dbDelta( $sql_profiles );
		dbDelta( $sql_history );
		dbDelta( $sql_logs );
	}
}
