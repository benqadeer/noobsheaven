<?php

namespace RG\ContentEngine\Generator;

use RG\ContentEngine\API\AI\AIProviderFactory;
use RG\ContentEngine\Profiles\Profile;
use RG\ContentEngine\Keywords\Keyword;
use RG\ContentEngine\Integrations\WooCommerce\ProductHandler;
use RG\ContentEngine\Prompt\PromptEngine;

/**
 * Generator Engine Class.
 *
 * @package RG\ContentEngine\Generator
 */
class GeneratorEngine {

	/**
	 * Generate content for a single product.
	 *
	 * @param int $product_id Product ID.
	 * @param int $profile_id Profile ID.
	 * @param int|null $keyword_id Optional Keyword ID.
	 * @return array Result of the generation.
	 * @throws \Exception
	 */
	public function generate_for_product( int $product_id, int $profile_id, int $keyword_id = null ): array {
		$start_time = microtime( true );

		$profile = Profile::get_by_id( $profile_id );
		if ( ! $profile ) {
			throw new \Exception( "Profile not found." );
		}

		$product_handler = new ProductHandler();
		$product_data = $product_handler->get_product_data( $product_id );

		$keyword_text = '';
		$keyword_obj = null;
		if ( $keyword_id ) {
			// Use specific keyword
			global $wpdb;
			$table = $wpdb->prefix . 'rg_ce_keywords';
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $keyword_id ), ARRAY_A );
			if ( $row ) {
				$keyword_obj = new Keyword( $row );
				$keyword_text = $keyword_obj->keyword;
			}
		} else {
			// Auto-select keyword
			$keyword_obj = Keyword::get_next_available();
			if ( $keyword_obj ) {
				$keyword_text = $keyword_obj->keyword;
			}
		}

		$product_data['keyword'] = $keyword_text;

		$prompt_engine = new PromptEngine();
		$final_prompt = $prompt_engine->build_final_prompt( $profile->prompt, $profile->html_blueprint, $product_data );

		$provider = AIProviderFactory::make( $profile->provider );
		$ai_response = $provider->generate( $final_prompt, [
			'model'       => $profile->model,
			'temperature' => $profile->temperature,
			'max_tokens'  => $profile->max_tokens,
		] );

		$generated_data = json_decode( $ai_response['content'], true );
		if ( ! $generated_data ) {
			throw new \Exception( "AI returned malformed JSON: " . $ai_response['content'] );
		}

		$execution_time = microtime( true ) - $start_time;

		// Log History
		$this->log_history( [
			'product_id'     => $product_id,
			'keyword_id'     => $keyword_obj ? $keyword_obj->id : null,
			'profile_id'     => $profile_id,
			'provider'       => $profile->provider,
			'model'          => $profile->model,
			'prompt'         => $final_prompt,
			'response'       => $ai_response['content'],
			'cost'           => $this->calculate_cost( $profile->provider, $profile->model, $ai_response['usage'] ),
			'execution_time' => $execution_time,
			'status'         => 'success',
		] );

		// Mark keyword as used
		if ( $keyword_obj ) {
			$keyword_obj->mark_as_used( $product_id );
		}

		return [
			'success'        => true,
			'data'           => $generated_data,
			'execution_time' => $execution_time,
		];
	}

	/**
	 * Log generation history.
	 */
	private function log_history( array $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'rg_ce_history';
		$wpdb->insert( $table, $data );
	}

	/**
	 * Calculate estimated cost (very rough estimation).
	 */
	private function calculate_cost( string $provider, string $model, array $usage ): float {
		// Example pricing for OpenAI
		if ( $provider === 'openai' ) {
			if ( str_contains( $model, 'gpt-4o' ) ) {
				$input_cost = ( $usage['prompt_tokens'] / 1000000 ) * 5.00;
				$output_cost = ( $usage['completion_tokens'] / 1000000 ) * 15.00;
				return $input_cost + $output_cost;
			}
		}
		return 0;
	}
}
