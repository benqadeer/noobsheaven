<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Profiles;

/**
 * Profile Model Class.
 *
 * @package RG\ContentEngine\Profiles
 */
class Profile {

	/**
	 * Profile ID.
	 * @var int|null
	 */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $provider;

	/** @var string */
	public $model;

	/** @var string */
	public $prompt;

	/** @var string */
	public $html_blueprint;

	/** @var array */
	public $seo_rules;

	/** @var string */
	public $writing_style;

	/** @var float */
	public $temperature;

	/** @var int */
	public $max_tokens;

	/** @var array */
	public $keyword_rules;

	/** @var array */
	public $image_alt_rules;

	/**
	 * Constructor.
	 *
	 * @param array $data Profile data.
	 */
	public function __construct( array $data = [] ) {
		$this->id              = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->name            = $data['name'] ?? '';
		$this->provider        = $data['provider'] ?? 'openai';
		$this->model           = $data['model'] ?? 'gpt-4o';
		$this->prompt          = $data['prompt'] ?? '';
		$this->html_blueprint  = $data['html_blueprint'] ?? '';
		$this->seo_rules       = is_string( $data['seo_rules'] ?? '' ) ? json_decode( $data['seo_rules'], true ) : ( $data['seo_rules'] ?? [] );
		$this->writing_style   = $data['writing_style'] ?? 'Professional';
		$this->temperature     = (float) ( $data['temperature'] ?? 0.7 );
		$this->max_tokens      = (int) ( $data['max_tokens'] ?? 2000 );
		$this->keyword_rules   = is_string( $data['keyword_rules'] ?? '' ) ? json_decode( $data['keyword_rules'], true ) : ( $data['keyword_rules'] ?? [] );
		$this->image_alt_rules = is_string( $data['image_alt_rules'] ?? '' ) ? json_decode( $data['image_alt_rules'], true ) : ( $data['image_alt_rules'] ?? [] );
	}

	/**
	 * Get all profiles.
	 *
	 * @return array
	 */
	public static function get_all(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_profiles';
		$results = $wpdb->get_results( "SELECT * FROM $table ORDER BY name ASC", ARRAY_A );

		return array_map( function( $row ) {
			return new self( $row );
		}, $results );
	}

	/**
	 * Get profile by ID.
	 *
	 * @param int $id Profile ID.
	 * @return Profile|null
	 */
	public static function get_by_id( int $id ): ?Profile {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_profiles';
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );

		return $row ? new self( $row ) : null;
	}

	/**
	 * Save profile to database.
	 *
	 * @return int|bool
	 */
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_profiles';

		$data = [
			'name'            => $this->name,
			'provider'        => $this->provider,
			'model'           => $this->model,
			'prompt'          => $this->prompt,
			'html_blueprint'  => $this->html_blueprint,
			'seo_rules'       => json_encode( $this->seo_rules ),
			'writing_style'   => $this->writing_style,
			'temperature'     => $this->temperature,
			'max_tokens'      => $this->max_tokens,
			'keyword_rules'   => json_encode( $this->keyword_rules ),
			'image_alt_rules' => json_encode( $this->image_alt_rules ),
		];

		if ( $this->id ) {
			$updated = $wpdb->update( $table, $data, [ 'id' => $this->id ] );
			return $updated !== false;
		} else {
			$inserted = $wpdb->insert( $table, $data );
			if ( $inserted ) {
				$this->id = $wpdb->insert_id;
				return $this->id;
			}
		}

		return false;
	}

	/**
	 * Delete profile.
	 *
	 * @return bool
	 */
	public function delete(): bool {
		if ( ! $this->id ) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_profiles';
		return (bool) $wpdb->delete( $table, [ 'id' => $this->id ] );
	}
}
