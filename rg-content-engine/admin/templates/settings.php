<?php
$settings = get_option( 'rg_content_engine_settings', [] );
?>
<div class="wrap">
	<h1>RG Content Engine Settings</h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'rg_content_engine_settings_group' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row">OpenAI API Key</th>
				<td>
					<input type="password" name="rg_content_engine_settings[openai_api_key]" value="<?php echo esc_attr( $settings['openai_api_key'] ?? '' ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">Default Provider</th>
				<td>
					<select name="rg_content_engine_settings[default_provider]">
						<option value="openai" <?php selected( $settings['default_provider'] ?? '', 'openai' ); ?>>OpenAI</option>
					</select>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
