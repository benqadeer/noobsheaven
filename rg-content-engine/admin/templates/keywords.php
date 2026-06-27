<?php
global $wpdb;
$table = $wpdb->prefix . 'rg_ce_keywords';
$keywords = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A );
?>
<div class="wrap">
	<h1>Keyword Manager</h1>

	<div class="card">
		<h3>Import Keywords (CSV)</h3>
		<p>Upload a CSV file with headers: <code>Keyword, Search Volume, Difficulty, Intent, Priority, Notes</code></p>
		<form id="rg-ce-import-form">
			<input type="file" name="file" accept=".csv" required>
			<button type="submit" class="button button-primary">Import</button>
		</form>
		<div id="import-status" style="margin-top:10px;"></div>
	</div>

	<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Keyword</th>
				<th>Volume</th>
				<th>Difficulty</th>
				<th>Intent</th>
				<th>Used?</th>
				<th>Product ID</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $keywords ) ) : ?>
				<tr><td colspan="6">No keywords found.</td></tr>
			<?php else : ?>
				<?php foreach ( $keywords as $kw ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $kw['keyword'] ); ?></strong></td>
						<td><?php echo esc_html( $kw['search_volume'] ); ?></td>
						<td><?php echo esc_html( $kw['difficulty'] ); ?></td>
						<td><?php echo esc_html( $kw['intent'] ); ?></td>
						<td><?php echo $kw['is_used'] ? 'Yes' : 'No'; ?></td>
						<td><?php echo $kw['product_id'] ?: '-'; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<script>
document.getElementById('rg-ce-import-form').addEventListener('submit', async (e) => {
	e.preventDefault();
	const status = document.getElementById('import-status');
	status.innerText = 'Importing...';

	const formData = new FormData(e.target);
	const response = await fetch('<?php echo esc_url( get_rest_url( null, 'rg-content-engine/v1/keywords/import' ) ); ?>', {
		method: 'POST',
		headers: {
			'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
		},
		body: formData
	});

	if (response.ok) {
		const result = await response.json();
		status.innerText = 'Successfully imported ' + result.count + ' keywords.';
		setTimeout(() => window.location.reload(), 1500);
	} else {
		status.innerText = 'Import failed.';
	}
});
</script>
