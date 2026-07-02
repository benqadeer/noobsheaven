<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */
$profiles = \RG\ContentEngine\Profiles\Profile::get_all();
?>
<div class="wrap" id="rg-ce-profiles-app">
	<h1>Content Profiles</h1>
	<button type="button" class="page-title-action" id="rg-ce-add-profile-btn">Add New Profile</button>

	<div id="rg-ce-profile-form-container" style="display:none; background:#fff; padding:20px; border:1px solid #ccd0d4; margin-top:20px;">
		<h3 id="form-title">Add New Profile</h3>
		<form id="rg-ce-profile-form">
			<input type="hidden" name="id" id="profile-id">
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
				<div>
					<table class="form-table">
						<tr>
							<th><label>Profile Name</label></th>
							<td><input type="text" name="name" id="profile-name" class="regular-text" required></td>
						</tr>
						<tr>
							<th><label>AI Provider</label></th>
							<td>
								<select name="provider" id="profile-provider">
									<option value="openai">OpenAI</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label>Model</label></th>
							<td>
								<select name="model" id="profile-model">
									<option value="gpt-4o">GPT-4o</option>
									<option value="gpt-4-turbo">GPT-4 Turbo</option>
									<option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label>Writing Style</label></th>
							<td><input type="text" name="writing_style" id="profile-writing-style" class="regular-text" placeholder="Professional, Humorous, etc."></td>
						</tr>
						<tr>
							<th><label>Temperature</label></th>
							<td><input type="number" name="temperature" id="profile-temperature" step="0.1" min="0" max="1" value="0.7"></td>
						</tr>
						<tr>
							<th><label>Max Tokens</label></th>
							<td><input type="number" name="max_tokens" id="profile-max-tokens" step="100" value="2000"></td>
						</tr>
					</table>
				</div>
				<div>
					<table class="form-table">
						<tr>
							<th><label>Prompt Template</label></th>
							<td>
								<textarea name="prompt" id="profile-prompt" rows="6" class="large-text" placeholder="Use {title}, {keyword}, {category}, {attributes}, {brand}, {sku}, {tags}, {description}, {image_count}"></textarea>
							</td>
						</tr>
						<tr>
							<th><label>HTML Blueprint</label></th>
							<td>
								<textarea name="html_blueprint" id="profile-html-blueprint" rows="10" class="large-text" placeholder="<h2>Product Introduction</h2>..."></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<p>
				<button type="submit" class="button button-primary">Save Profile</button>
				<button type="button" class="button" id="rg-ce-cancel-btn">Cancel</button>
			</p>
		</form>
	</div>

	<table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
		<thead>
			<tr>
				<th>Name</th>
				<th>Provider</th>
				<th>Model</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody id="profiles-list">
			<?php foreach ( $profiles as $profile ) : ?>
				<tr data-id="<?php echo esc_attr( $profile->id ); ?>">
					<td><strong><?php echo esc_html( $profile->name ); ?></strong></td>
					<td><?php echo esc_html( $profile->provider ); ?></td>
					<td><?php echo esc_html( $profile->model ); ?></td>
					<td>
						<button class="button edit-profile">Edit</button>
						<button class="button delete-profile">Delete</button>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const addBtn = document.getElementById('rg-ce-add-profile-btn');
	const cancelBtn = document.getElementById('rg-ce-cancel-btn');
	const formContainer = document.getElementById('rg-ce-profile-form-container');
	const form = document.getElementById('rg-ce-profile-form');
	const list = document.getElementById('profiles-list');
	const restUrl = '<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/profiles' ) ); ?>';
	const nonce = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';

	addBtn.addEventListener('click', () => {
		form.reset();
		document.getElementById('profile-id').value = '';
		document.getElementById('form-title').innerText = 'Add New Profile';
		formContainer.style.display = 'block';
		window.scrollTo(0, 0);
	});

	cancelBtn.addEventListener('click', () => {
		formContainer.style.display = 'none';
	});

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		const formData = new FormData(form);
		const payload = {};
		formData.forEach((value, key) => {
			payload[key] = value;
		});

		try {
			const response = await fetch(restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				},
				body: JSON.stringify(payload)
			});

			const result = await response.json();
			if (response.ok) {
				window.location.reload();
			} else {
				alert('Failed to save profile: ' + (result.message || 'Unknown error'));
			}
		} catch (error) {
			alert('Error: ' + error.message);
		}
	});

	list.addEventListener('click', async (e) => {
		const row = e.target.closest('tr');
		if (!row) return;
		const id = row.dataset.id;

		if (e.target.classList.contains('delete-profile')) {
			if (!confirm('Are you sure you want to delete this profile?')) return;
			const response = await fetch(`${restUrl}/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': nonce }
			});
			if (response.ok) {
				row.remove();
			} else {
				alert('Failed to delete profile.');
			}
		}

		if (e.target.classList.contains('edit-profile')) {
			const response = await fetch(`${restUrl}/${id}`, {
				headers: { 'X-WP-Nonce': nonce }
			});
			const profile = await response.json();

			document.getElementById('profile-id').value = profile.id;
			document.getElementById('profile-name').value = profile.name;
			document.getElementById('profile-provider').value = profile.provider;
			document.getElementById('profile-model').value = profile.model;
			document.getElementById('profile-writing-style').value = profile.writing_style || '';
			document.getElementById('profile-temperature').value = profile.temperature || 0.7;
			document.getElementById('profile-max-tokens').value = profile.max_tokens || 2000;
			document.getElementById('profile-prompt').value = profile.prompt;
			document.getElementById('profile-html-blueprint').value = profile.html_blueprint;

			document.getElementById('form-title').innerText = 'Edit Profile';
			formContainer.style.display = 'block';
			window.scrollTo(0, 0);
		}
	});
});
</script>
