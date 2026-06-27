<?php
$profiles = \RG\ContentEngine\Profiles\Profile::get_all();
?>
<div class="wrap" id="rg-ce-profiles-app">
	<h1>Content Profiles</h1>
	<button type="button" class="page-title-action" id="rg-ce-add-profile-btn">Add New Profile</button>

	<div id="rg-ce-profile-form-container" style="display:none; background:#fff; padding:20px; border:1px solid #ccd0d4; margin-top:20px;">
		<h3 id="form-title">Add New Profile</h3>
		<form id="rg-ce-profile-form">
			<input type="hidden" name="id" id="profile-id">
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
						</select>
					</td>
				</tr>
				<tr>
					<th><label>Prompt Template</label></th>
					<td>
						<textarea name="prompt" id="profile-prompt" rows="5" class="large-text" placeholder="Use {title}, {keyword}, etc."></textarea>
					</td>
				</tr>
				<tr>
					<th><label>HTML Blueprint</label></th>
					<td>
						<textarea name="html_blueprint" id="profile-html-blueprint" rows="10" class="large-text" placeholder="<h2>{title}</h2>..."></textarea>
					</td>
				</tr>
			</table>
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
	});

	cancelBtn.addEventListener('click', () => {
		formContainer.style.display = 'none';
	});

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		const formData = new FormData(form);
		const payload = {};
		formData.forEach((value, key) => payload[key] = value);

		const response = await fetch(restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce
			},
			body: JSON.stringify(payload)
		});

		if (response.ok) {
			window.location.reload();
		} else {
			alert('Failed to save profile');
		}
	});

	list.addEventListener('click', async (e) => {
		const row = e.target.closest('tr');
		if (!row) return;
		const id = row.dataset.id;

		if (e.target.classList.contains('delete-profile')) {
			if (!confirm('Are you sure?')) return;
			const response = await fetch(`${restUrl}/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': nonce }
			});
			if (response.ok) row.remove();
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
			document.getElementById('profile-prompt').value = profile.prompt;
			document.getElementById('profile-html-blueprint').value = profile.html_blueprint;

			document.getElementById('form-title').innerText = 'Edit Profile';
			formContainer.style.display = 'block';
			window.scrollTo(0, 0);
		}
	});
});
</script>
