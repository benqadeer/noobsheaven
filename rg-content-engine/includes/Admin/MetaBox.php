<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Admin;

/**
 * Meta Box Class for WooCommerce Product Page.
 *
 * @package RG\ContentEngine\Admin
 */
class MetaBox {

	/**
	 * Register meta box.
	 */
	public function register() {
		add_action( 'add_meta_boxes', [ $this, 'add_product_meta_box' ] );
	}

	/**
	 * Add meta box to product page.
	 */
	public function add_product_meta_box() {
		add_meta_box(
			'rg-ce-generator',
			'RG Content Engine - AI Generator',
			[ $this, 'render_meta_box' ],
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box content.
	 */
	public function render_meta_box( $post ) {
		$profiles = \RG\ContentEngine\Profiles\Profile::get_all();
		?>
		<div id="rg-ce-generator-app">
			<div class="rg-ce-row">
				<label for="rg-ce-profile-select">Select Content Profile:</label>
				<select id="rg-ce-profile-select">
					<option value="">-- Choose Profile --</option>
					<?php foreach ( $profiles as $profile ) : ?>
						<option value="<?php echo esc_attr( $profile->id ); ?>"><?php echo esc_html( $profile->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" id="rg-ce-generate-btn" class="button button-primary">Generate with AI</button>
			</div>

			<div id="rg-ce-preview-container" style="display:none; margin-top: 20px; border: 1px solid #ccd0d4; padding: 15px; background: #fff;">
				<h3>Preview Generated Content</h3>
				<div id="rg-ce-preview-content"></div>
				<div style="margin-top: 15px;">
					<button type="button" id="rg-ce-apply-btn" class="button button-primary">Apply to Product</button>
					<button type="button" id="rg-ce-discard-btn" class="button">Discard</button>
				</div>
			</div>

			<div id="rg-ce-status" style="margin-top: 10px;"></div>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const generateBtn = document.getElementById('rg-ce-generate-btn');
			const applyBtn = document.getElementById('rg-ce-apply-btn');
			const discardBtn = document.getElementById('rg-ce-discard-btn');
			const profileSelect = document.getElementById('rg-ce-profile-select');
			const previewContainer = document.getElementById('rg-ce-preview-container');
			const previewContent = document.getElementById('rg-ce-preview-content');
			const statusDiv = document.getElementById('rg-ce-status');

			let lastGeneratedData = null;

			generateBtn.addEventListener('click', async function() {
				const profileId = profileSelect.value;
				if (!profileId) {
					alert('Please select a profile');
					return;
				}

				statusDiv.innerHTML = '<span class="spinner is-active" style="float:none;"></span> Generating...';
				generateBtn.disabled = true;

				try {
					const response = await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/generate' ) ); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
						},
						body: JSON.stringify({
							product_id: <?php echo $post->ID; ?>,
							profile_id: profileId
						})
					});

					const result = await response.json();
					if (result.success) {
						lastGeneratedData = result.data;
						statusDiv.innerHTML = 'Success! Execution Time: ' + result.execution_time.toFixed(2) + 's';
						previewContent.innerHTML = '<pre style="white-space: pre-wrap; background: #f6f7f7; padding: 10px; border: 1px solid #dcdcde;">' + JSON.stringify(result.data, null, 2) + '</pre>';
						previewContainer.style.display = 'block';
					} else {
						statusDiv.innerHTML = 'Error: ' + (result.message || 'Unknown error');
					}
				} catch (error) {
					statusDiv.innerHTML = 'Error: ' + error.message;
				} finally {
					generateBtn.disabled = false;
				}
			});

			applyBtn.addEventListener('click', async function() {
				if (!lastGeneratedData) return;

				statusDiv.innerHTML = 'Applying changes...';
				applyBtn.disabled = true;

				try {
					const response = await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/apply' ) ); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
						},
						body: JSON.stringify({
							product_id: <?php echo $post->ID; ?>,
							data: lastGeneratedData,
							fields: ['description', 'short_description', 'slug', 'focus_keyword', 'meta_title', 'meta_description', 'alt_tags']
						})
					});

					const result = await response.json();
					if (result.success) {
						statusDiv.innerHTML = 'Product updated! Reloading...';
						setTimeout(() => window.location.reload(), 1000);
					} else {
						statusDiv.innerHTML = 'Error applying changes.';
					}
				} catch (error) {
					statusDiv.innerHTML = 'Error: ' + error.message;
				} finally {
					applyBtn.disabled = false;
				}
			});

			discardBtn.addEventListener('click', function() {
				previewContainer.style.display = 'none';
				lastGeneratedData = null;
				statusDiv.innerHTML = '';
			});
		});
		</script>
		<?php
	}
}
