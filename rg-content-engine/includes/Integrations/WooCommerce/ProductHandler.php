<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Integrations\WooCommerce;

/**
 * WooCommerce Product Data Handler.
 *
 * @package RG\ContentEngine\Integrations\WooCommerce
 */
class ProductHandler {

	/**
	 * Get product data by ID.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public function get_product_data( int $product_id ): array {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return [];
		}

		$attributes = [];
		foreach ( $product->get_attributes() as $attr ) {
			$name = $attr->get_name();
			if ( $attr->is_taxonomy() ) {
				$terms = wp_get_post_terms( $product_id, $name, [ 'fields' => 'names' ] );
				$value = implode( ', ', $terms );
			} else {
				$value = implode( ', ', $attr->get_options() );
			}
			$attributes[] = $name . ': ' . $value;
		}

		$categories = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'names' ] );
		$tags       = wp_get_post_terms( $product_id, 'product_tag', [ 'fields' => 'names' ] );

		$images = $product->get_gallery_image_ids();
		if ( $product->get_image_id() ) {
			array_unshift( $images, $product->get_image_id() );
		}

		return [
			'title'       => $product->get_name(),
			'sku'         => $product->get_sku(),
			'description' => $product->get_description(),
			'short_description' => $product->get_short_description(),
			'category'    => implode( ', ', $categories ),
			'tags'        => implode( ', ', $tags ),
			'attributes'  => implode( ' | ', $attributes ),
			'brand'       => $this->get_product_brand( $product_id ),
			'image_count' => count( $images ),
			'image_ids'   => $images,
		];
	}

	/**
	 * Get product brand (supports common brand plugins).
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	private function get_product_brand( int $product_id ): string {
		// Try 'product_brand' taxonomy (used by WooCommerce Brands)
		$brands = wp_get_post_terms( $product_id, 'product_brand', [ 'fields' => 'names' ] );
		if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
			return $brands[0];
		}

		// Try 'brand' taxonomy (used by Perfect WooCommerce Brands etc.)
		$brands = wp_get_post_terms( $product_id, 'brand', [ 'fields' => 'names' ] );
		if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
			return $brands[0];
		}

		return '';
	}

	/**
	 * Update product with AI generated data.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data Generated data.
	 * @param array $fields_to_update Which fields should be updated.
	 * @return bool
	 */
	public function update_product( int $product_id, array $data, array $fields_to_update = [] ): bool {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}

		if ( in_array( 'description', $fields_to_update ) && ! empty( $data['description'] ) ) {
			$product->set_description( $data['description'] );
		}

		if ( in_array( 'short_description', $fields_to_update ) && ! empty( $data['short_description'] ) ) {
			$product->set_short_description( $data['short_description'] );
		}

		if ( in_array( 'slug', $fields_to_update ) && ! empty( $data['slug'] ) ) {
			$product->set_slug( $data['slug'] );
		}

		if ( in_array( 'tags', $fields_to_update ) && ! empty( $data['tags'] ) ) {
			$product->set_tag_ids( $data['tags'] );
		}

		$product->save();

		// Update Image ALTs
		if ( in_array( 'alt_tags', $fields_to_update ) && ! empty( $data['alt_tags'] ) ) {
			$images = $this->get_product_data( $product_id )['image_ids'];
			foreach ( $images as $index => $image_id ) {
				if ( isset( $data['alt_tags'][$index] ) ) {
					update_post_meta( $image_id, '_wp_attachment_image_alt', $data['alt_tags'][$index] );
				}
			}
		}

		// Update Yoast SEO Fields
		$this->update_yoast_fields( $product_id, $data, $fields_to_update );

		return true;
	}

	/**
	 * Update Yoast SEO fields.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data Generated data.
	 * @param array $fields_to_update Which fields should be updated.
	 */
	private function update_yoast_fields( int $product_id, array $data, array $fields_to_update ) {
		if ( in_array( 'focus_keyword', $fields_to_update ) && ! empty( $data['focus_keyword'] ) ) {
			update_post_meta( $product_id, '_yoast_wpseo_focuskw', $data['focus_keyword'] );
		}

		if ( in_array( 'meta_title', $fields_to_update ) && ! empty( $data['meta_title'] ) ) {
			update_post_meta( $product_id, '_yoast_wpseo_title', $data['meta_title'] );
		}

		if ( in_array( 'meta_description', $fields_to_update ) && ! empty( $data['meta_description'] ) ) {
			update_post_meta( $product_id, '_yoast_wpseo_metadesc', $data['meta_description'] );
		}

		if ( in_array( 'social_title', $fields_to_update ) && ! empty( $data['social_title'] ) ) {
			update_post_meta( $product_id, '_yoast_wpseo_opengraph-title', $data['social_title'] );
			update_post_meta( $product_id, '_yoast_wpseo_twitter-title', $data['social_title'] );
		}

		if ( in_array( 'social_description', $fields_to_update ) && ! empty( $data['social_description'] ) ) {
			update_post_meta( $product_id, '_yoast_wpseo_opengraph-description', $data['social_description'] );
			update_post_meta( $product_id, '_yoast_wpseo_twitter-description', $data['social_description'] );
		}
	}
}
