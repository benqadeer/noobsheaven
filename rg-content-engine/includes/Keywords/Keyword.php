<?php

namespace RG\ContentEngine\Keywords;

/**
 * Keyword Model Class.
 *
 * @package RG\ContentEngine\Keywords
 */
class Keyword {

	/** @var int|null */
	public $id;

	/** @var string */
	public $keyword;

	/** @var int */
	public $search_volume;

	/** @var int */
	public $difficulty;

	/** @var string */
	public $intent;

	/** @var int */
	public $priority;

	/** @var string */
	public $notes;

	/** @var bool */
	public $is_used;

	/** @var int|null */
	public $product_id;

	/**
	 * Constructor.
	 *
	 * @param array $data Keyword data.
	 */
	public function __construct( array $data = [] ) {
		$this->id            = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->keyword       = $data['keyword'] ?? '';
		$this->search_volume = (int) ( $data['search_volume'] ?? 0 );
		$this->difficulty    = (int) ( $data['difficulty'] ?? 0 );
		$this->intent        = $data['intent'] ?? '';
		$this->priority      = (int) ( $data['priority'] ?? 0 );
		$this->notes         = $data['notes'] ?? '';
		$this->is_used       = (bool) ( $data['is_used'] ?? false );
		$this->product_id    = isset( $data['product_id'] ) ? (int) $data['product_id'] : null;
	}

	/**
	 * Get keywords with filters.
	 */
	public static function get_all( array $args = [] ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_keywords';

		$where = [];
		if ( isset( $args['is_used'] ) ) {
			$where[] = $wpdb->prepare( "is_used = %d", $args['is_used'] );
		}

		$where_sql = ! empty( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$order_sql = " ORDER BY priority DESC, search_volume DESC";

		$results = $wpdb->get_results( "SELECT * FROM $table $where_sql $order_sql", ARRAY_A );

		return array_map( function( $row ) {
			return new self( $row );
		}, $results );
	}

	/**
	 * Save keyword.
	 */
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_keywords';

		$data = [
			'keyword'       => $this->keyword,
			'search_volume' => $this->search_volume,
			'difficulty'    => $this->difficulty,
			'intent'        => $this->intent,
			'priority'      => $this->priority,
			'notes'         => $this->notes,
			'is_used'       => $this->is_used ? 1 : 0,
			'product_id'    => $this->product_id,
		];

		if ( $this->id ) {
			$wpdb->update( $table, $data, [ 'id' => $this->id ] );
			return true;
		} else {
			$wpdb->insert( $table, $data );
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
	}

	/**
	 * Get next available keyword.
	 *
	 * @return Keyword|null
	 */
	public static function get_next_available(): ?Keyword {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_keywords';
		$row = $wpdb->get_row( "SELECT * FROM $table WHERE is_used = 0 ORDER BY priority DESC, search_volume DESC LIMIT 1", ARRAY_A );

		return $row ? new self( $row ) : null;
	}

	/**
	 * Mark keyword as used.
	 *
	 * @param int $product_id Product ID.
	 */
	public function mark_as_used( int $product_id ) {
		$this->is_used = true;
		$this->product_id = $product_id;
		$this->save();
	}
}
