<?php

namespace RG\ContentEngine\API\AI;

/**
 * AI Provider Interface.
 *
 * @package RG\ContentEngine\API\AI
 */
interface AIProviderInterface {

	/**
	 * Send a prompt to the AI provider.
	 *
	 * @param string $prompt The prompt to send.
	 * @param array  $options Provider-specific options (model, temperature, etc.).
	 * @return array Response including content and usage data.
	 * @throws \Exception On API error.
	 */
	public function generate( string $prompt, array $options = [] ): array;

	/**
	 * Get the identifier for the provider.
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Get the list of supported models.
	 *
	 * @return array
	 */
	public function get_supported_models(): array;
}
