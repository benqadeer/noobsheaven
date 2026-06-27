<?php

namespace RG\ContentEngine\Prompt;

/**
 * Prompt Engine Class.
 *
 * @package RG\ContentEngine\Prompt
 */
class PromptEngine {

	/**
	 * Replace variables in a prompt string.
	 *
	 * @param string $template The prompt template.
	 * @param array  $data Data to replace variables with.
	 * @return string
	 */
	public function replace_variables( string $template, array $data ): string {
		$variables = [
			'{title}'       => $data['title'] ?? '',
			'{category}'    => $data['category'] ?? '',
			'{attributes}'  => $data['attributes'] ?? '',
			'{brand}'       => $data['brand'] ?? '',
			'{keyword}'     => $data['keyword'] ?? '',
			'{sku}'         => $data['sku'] ?? '',
			'{tags}'        => $data['tags'] ?? '',
			'{description}' => $data['description'] ?? '',
			'{image_count}' => $data['image_count'] ?? 0,
		];

		return str_replace( array_keys( $variables ), array_values( $variables ), $template );
	}

	/**
	 * Build the final system and user prompts.
	 *
	 * @param string $profile_prompt The prompt from the profile.
	 * @param string $html_blueprint The HTML blueprint.
	 * @param array  $product_data Product data.
	 * @return string
	 */
	public function build_final_prompt( string $profile_prompt, string $html_blueprint, array $product_data ): string {
		$instructions = "You must respond with a JSON object.
		The JSON object MUST contain the following keys:
		- title: Optimized product title
		- slug: URL friendly slug
		- focus_keyword: The main focus keyword
		- meta_title: SEO Meta Title
		- meta_description: SEO Meta Description
		- social_title: Social Media Title
		- social_description: Social Media Description
		- short_description: Product short description
		- description: Full product description following the HTML blueprint provided below.
		- faq: An array of objects with 'question' and 'answer' keys.
		- alt_tags: An array of strings for image ALT tags.

		HTML BLUEPRINT:
		{$html_blueprint}

		Fill the placeholders in the HTML blueprint with relevant content.
		DO NOT change the structure of the blueprint.
		";

		$user_prompt = $this->replace_variables( $profile_prompt, $product_data );

		return $instructions . "\n\nUSER PROMPT:\n" . $user_prompt;
	}
}
