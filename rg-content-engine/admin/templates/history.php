<?php
global $wpdb;
$table = $wpdb->prefix . 'rg_ce_history';
$history = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 50", ARRAY_A );
?>
<div class="wrap">
	<h1>Generation History</h1>

	<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Date</th>
				<th>Product ID</th>
				<th>Provider/Model</th>
				<th>Execution Time</th>
				<th>Cost</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $history ) ) : ?>
				<tr><td colspan="6">No history found.</td></tr>
			<?php else : ?>
				<?php foreach ( $history as $item ) : ?>
					<tr>
						<td><?php echo esc_html( $item['created_at'] ); ?></td>
						<td><?php echo esc_html( $item['product_id'] ); ?></td>
						<td><?php echo esc_html( $item['provider'] . ' / ' . $item['model'] ); ?></td>
						<td><?php echo esc_html( number_format( $item['execution_time'], 2 ) ); ?>s</td>
						<td>$<?php echo esc_html( number_format( $item['cost'], 4 ) ); ?></td>
						<td><?php echo esc_html( $item['status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
