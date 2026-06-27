<?php

namespace RG\ContentEngine\REST;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use RG\ContentEngine\Generator\GeneratorEngine;
use RG\ContentEngine\Integrations\WooCommerce\ProductHandler;

/**
 * REST API Controller Class.
 *
 * @package RG\ContentEngine\REST
 */
class Controller extends WP_REST_Controller {

	/**
	 * Namespace.
	 * @var string
	 */
	protected $namespace = 'rg-content-engine/v1';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/generate', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_generate' ],
			'permission_callback' => [ $this, 'permissions_check' ],
		] );

		register_rest_route( $this->namespace, '/apply', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_apply' ],
			'permission_callback' => [ $this, 'permissions_check' ],
		] );

		register_rest_route( $this->namespace, '/profiles', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_profiles' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_profile' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
		] );

		register_rest_route( $this->namespace, '/profiles/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_profile' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete_profile' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
		] );

		register_rest_route( $this->namespace, '/keywords/import', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'import_keywords' ],
			'permission_callback' => [ $this, 'permissions_check' ],
		] );

		register_rest_route( $this->namespace, '/bulk/start', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'start_bulk' ],
			'permission_callback' => [ $this, 'permissions_check' ],
		] );
	}

	/**
	 * Permissions check.
	 */
	public function permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle generation request.
	 */
	public function handle_generate( WP_REST_Request $request ) {
		$product_id = $request->get_param( 'product_id' );
		$profile_id = $request->get_param( 'profile_id' );
		$keyword_id = $request->get_param( 'keyword_id' );

		if ( ! $product_id || ! $profile_id ) {
			return new WP_Error( 'missing_params', 'Missing product_id or profile_id', [ 'status' => 400 ] );
		}

		$generator = new GeneratorEngine();
		try {
			$result = $generator->generate_for_product( (int) $product_id, (int) $profile_id, $keyword_id ? (int) $keyword_id : null );
			return new WP_REST_Response( $result, 200 );
		} catch ( \Exception $e ) {
			return new WP_Error( 'generation_failed', $e->getMessage(), [ 'status' => 500 ] );
		}
	}

	/**
	 * Get all profiles.
	 */
	public function get_profiles() {
		return new WP_REST_Response( \RG\ContentEngine\Profiles\Profile::get_all(), 200 );
	}

	/**
	 * Get single profile.
	 */
	public function get_profile( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$profile = \RG\ContentEngine\Profiles\Profile::get_by_id( (int) $id );
		if ( ! $profile ) {
			return new WP_Error( 'not_found', 'Profile not found', [ 'status' => 404 ] );
		}
		return new WP_REST_Response( $profile, 200 );
	}

	/**
	 * Save/Update profile.
	 */
	public function save_profile( WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$profile = new \RG\ContentEngine\Profiles\Profile( $params );
		$result = $profile->save();

		if ( $result ) {
			return new WP_REST_Response( [ 'success' => true, 'id' => $profile->id ], 200 );
		}
		return new WP_Error( 'save_failed', 'Failed to save profile', [ 'status' => 500 ] );
	}

	/**
	 * Import keywords from uploaded CSV.
	 */
	public function import_keywords( WP_REST_Request $request ) {
		$files = $request->get_file_params();
		if ( empty( $files['file'] ) ) {
			return new WP_Error( 'missing_file', 'No file uploaded', [ 'status' => 400 ] );
		}

		$importer = new \RG\ContentEngine\Keywords\Importer();
		$count = $importer->import_from_csv( $files['file']['tmp_name'] );

		return new WP_REST_Response( [ 'success' => true, 'count' => $count ], 200 );
	}

	/**
	 * Start bulk generation.
	 */
	public function start_bulk( WP_REST_Request $request ) {
		$category_id = (int) $request->get_param( 'category_id' );
		$profile_id  = (int) $request->get_param( 'profile_id' );

		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1,
			'fields' => 'ids',
		];

		if ( $category_id > 0 ) {
			$args['tax_query'] = [[
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $category_id,
			]];
		}

		$product_ids = get_posts( $args );

		// In a real production environment, we would use a proper background processor.
		// For this implementation, we will return the IDs and let the frontend iterate to show progress.
		return new WP_REST_Response( [
			'success' => true,
			'product_ids' => $product_ids,
			'total' => count( $product_ids )
		], 200 );
	}

	/**
	 * Delete profile.
	 */
	public function delete_profile( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$profile = \RG\ContentEngine\Profiles\Profile::get_by_id( (int) $id );
		if ( ! $profile ) {
			return new WP_Error( 'not_found', 'Profile not found', [ 'status' => 404 ] );
		}

		if ( $profile->delete() ) {
			return new WP_REST_Response( [ 'success' => true ], 200 );
		}
		return new WP_Error( 'delete_failed', 'Failed to delete profile', [ 'status' => 500 ] );
	}

	/**
	 * Handle apply generated data request.
	 */
	public function handle_apply( WP_REST_Request $request ) {
		$product_id = $request->get_param( 'product_id' );
		$data       = $request->get_param( 'data' );
		$fields     = $request->get_param( 'fields' );

		if ( ! $product_id || ! $data || ! $fields ) {
			return new WP_Error( 'missing_params', 'Missing required parameters', [ 'status' => 400 ] );
		}

		$handler = new ProductHandler();
		$success = $handler->update_product( (int) $product_id, $data, $fields );

		return new WP_REST_Response( [ 'success' => $success ], 200 );
	}
}
