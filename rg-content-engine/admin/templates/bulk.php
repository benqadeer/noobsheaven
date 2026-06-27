<div class="wrap">
	<h1>Bulk Generator</h1>
	<p>Select products and a profile to generate content in bulk.</p>

	<div class="card" style="max-width: 600px;">
		<h3>Generate Bulk</h3>
		<form id="rg-ce-bulk-form">
			<p>
				<label>Select Category:</label><br>
				<?php wp_dropdown_categories(['taxonomy' => 'product_cat', 'name' => 'bulk_cat', 'id' => 'bulk_cat', 'show_option_all' => 'All Categories']); ?>
			</p>
			<p>
				<label>Select Profile:</label><br>
				<select name="bulk_profile" id="bulk_profile">
					<?php
					$profiles = \RG\ContentEngine\Profiles\Profile::get_all();
					foreach($profiles as $profile) {
						echo '<option value="'.$profile->id.'">'.$profile->name.'</option>';
					}
					?>
				</select>
			</p>
			<p>
				<button type="submit" class="button button-primary" id="start-bulk-btn">Start Bulk Generation</button>
			</p>
		</form>
	</div>

	<div id="bulk-progress-container" style="display:none; margin-top:20px;">
		<h3>Progress: <span id="progress-percent">0</span>% (<span id="progress-current">0</span>/<span id="progress-total">0</span>)</h3>
		<div style="width:100%; background:#ccc; height:20px;">
			<div id="progress-bar" style="width:0%; background:#2271b1; height:20px;"></div>
		</div>
		<ul id="bulk-log" style="background:#fff; border:1px solid #ccd0d4; padding:10px; height:200px; overflow-y:scroll; margin-top:10px;"></ul>
	</div>
</div>

<script>
document.getElementById('rg-ce-bulk-form').addEventListener('submit', async (e) => {
	e.preventDefault();
	const catId = document.getElementById('bulk_cat').value;
	const profileId = document.getElementById('bulk_profile').value;
	const btn = document.getElementById('start-bulk-btn');
	const log = document.getElementById('bulk-log');

	btn.disabled = true;
	document.getElementById('bulk-progress-container').style.display = 'block';
	log.innerHTML = '<li>Fetching products...</li>';

	const response = await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/bulk/start' ) ); ?>', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
		},
		body: JSON.stringify({ category_id: catId, profile_id: profileId })
	});

	const result = await response.json();
	if (!result.success || !result.product_ids.length) {
		log.innerHTML += '<li>No products found or error occurred.</li>';
		btn.disabled = false;
		return;
	}

	const productIds = result.product_ids;
	const total = productIds.length;
	document.getElementById('progress-total').innerText = total;

	for (let i = 0; i < total; i++) {
		const pid = productIds[i];
		log.innerHTML += '<li>Processing Product ID ' + pid + '...</li>';
		log.scrollTop = log.scrollHeight;

		try {
			// Step 1: Generate
			const genResp = await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/generate' ) ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				},
				body: JSON.stringify({ product_id: pid, profile_id: profileId })
			});
			const genResult = await genResp.json();

			if (genResult.success) {
				// Step 2: Apply
				await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/apply' ) ); ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
					},
					body: JSON.stringify({
						product_id: pid,
						data: genResult.data,
						fields: ['description', 'short_description', 'slug', 'focus_keyword', 'meta_title', 'meta_description', 'alt_tags']
					})
				});
				log.innerHTML += '<li style="color:green;">Success: Product ' + pid + ' updated.</li>';
			} else {
				log.innerHTML += '<li style="color:red;">Error: Product ' + pid + ' failed.</li>';
			}
		} catch (err) {
			log.innerHTML += '<li style="color:red;">Exception: ' + err.message + '</li>';
		}

		const current = i + 1;
		const percent = Math.round((current / total) * 100);
		document.getElementById('progress-current').innerText = current;
		document.getElementById('progress-percent').innerText = percent;
		document.getElementById('progress-bar').style.width = percent + '%';
	}

	log.innerHTML += '<li><strong>Bulk Generation Complete!</strong></li>';
	btn.disabled = false;
});
</script>
