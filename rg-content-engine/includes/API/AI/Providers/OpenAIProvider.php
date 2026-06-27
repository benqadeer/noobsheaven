<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\API\AI\Providers;

use RG\ContentEngine\API\AI\AIProviderInterface;

/**
 * OpenAI Provider Class.
 *
 * @package RG\ContentEngine\API\AI\Providers
 */
class OpenAIProvider implements AIProviderInterface {

	/**
	 * API Key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key OpenAI API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * @inheritDoc
	 */
	public function generate( string $prompt, array $options = [] ): array {
		$model       = $options['model'] ?? 'gpt-4o';
		$temperature = $options['temperature'] ?? 0.7;
		$max_tokens  = $options['max_tokens'] ?? 2000;

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode( [
				'model'           => $model,
				'messages'        => [
					[
						'role'    => 'system',
						'content' => 'You are a professional SEO content writer and WooCommerce expert. Output only valid JSON.',
					],
					[
						'role'    => 'user',
						'content' => $prompt,
					],
				],
				'temperature'     => (float) $temperature,
				'max_tokens'      => (int) $max_tokens,
				'response_format' => [ 'type' => 'json_object' ],
			] ),
			'timeout' => 60,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'OpenAI API Request Failed: ' . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			throw new \Exception( 'OpenAI API Error: ' . $body['error']['message'] );
		}

		$content = $body['choices'][0]['message']['content'] ?? '';
		$usage   = $body['usage'] ?? [];

		return [
			'content'     => $content,
			'raw_response' => $body,
			'usage'       => $usage,
			'model'       => $model,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_id(): string {
		return 'openai';
	}

	/**
	 * @inheritDoc
	 */
	public function get_supported_models(): array {
		return [
			'gpt-4o' => 'GPT-4o',
			'gpt-4-turbo' => 'GPT-4 Turbo',
			'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
		];
	}
}
