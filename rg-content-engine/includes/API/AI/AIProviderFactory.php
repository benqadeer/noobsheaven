<?php

namespace RG\ContentEngine\API\AI;

/**
 * AI Provider Factory Class.
 *
 * @package RG\ContentEngine\API\AI
 */
class AIProviderFactory {

	/**
	 * Create an AI provider instance.
	 *
	 * @param string $provider_id Provider ID.
	 * @return AIProviderInterface
	 * @throws \Exception If provider not found.
	 */
	public static function make( string $provider_id ): AIProviderInterface {
		$settings = get_option( 'rg_content_engine_settings', [] );

		switch ( $provider_id ) {
			case 'openai':
				$api_key = $settings['openai_api_key'] ?? '';
				if ( empty( $api_key ) ) {
					throw new \Exception( 'OpenAI API Key is not configured.' );
				}
				return new \RG\ContentEngine\API\AI\Providers\OpenAIProvider( $api_key );

			default:
				throw new \Exception( "AI Provider '{$provider_id}' is not supported." );
		}
	}
}
