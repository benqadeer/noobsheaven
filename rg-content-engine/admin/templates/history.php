<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

global $wpdb;
$table = $wpdb->prefix . 'rg_ce_history';
$history = $wpdb->get_results( "SELECT h.*, p.post_title FROM $table h LEFT JOIN {$wpdb->prefix}posts p ON h.product_id = p.ID ORDER BY h.created_at DESC LIMIT 50", ARRAY_A );
?>
<div class="wrap">
	<h1>Generation History</h1>

	<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Date</th>
				<th>Product</th>
				<th>Provider/Model</th>
				<th>Execution Time</th>
				<th>Cost</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $history ) ) : ?>
				<tr><td colspan="7">No history found.</td></tr>
			<?php else : ?>
				<?php foreach ( $history as $item ) : ?>
					<tr>
						<td><?php echo esc_html( $item['created_at'] ); ?></td>
						<td><strong><?php echo esc_html( $item['post_title'] ?: "Product #".$item['product_id'] ); ?></strong></td>
						<td><?php echo esc_html( $item['provider'] . ' / ' . $item['model'] ); ?></td>
						<td><?php echo esc_html( number_format( $item['execution_time'], 2 ) ); ?>s</td>
						<td>$<?php echo esc_html( number_format( $item['cost'], 4 ) ); ?></td>
						<td><span class="status-<?php echo esc_attr( $item['status'] ); ?>"><?php echo esc_html( strtoupper( $item['status'] ) ); ?></span></td>
						<td>
							<button class="button view-details" data-id="<?php echo $item['id']; ?>">View Content</button>
						</td>
					</tr>
					<tr id="details-<?php echo $item['id']; ?>" style="display:none;">
						<td colspan="7">
							<div style="background:#f6f7f7; padding:15px; border:1px solid #dcdcde;">
								<h4>Response Content:</h4>
								<pre style="white-space:pre-wrap;"><?php echo esc_html($item['response']); ?></pre>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<script>
document.querySelectorAll('.view-details').forEach(btn => {
	btn.addEventListener('click', () => {
		const id = btn.dataset.id;
		const row = document.getElementById('details-' + id);
		row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
	});
});
</script>
<style>
.status-success { color: green; font-weight: bold; }
.status-failed { color: red; font-weight: bold; }
</style>
