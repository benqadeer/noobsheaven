<?php
global $wpdb;
$table = $wpdb->prefix . 'rg_ce_logs';
$logs = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 100", ARRAY_A );
?>
<div class="wrap">
	<h1>System Logs</h1>
	<p>Last 100 log entries.</p>

	<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
		<thead>
			<tr>
				<th>Date</th>
				<th>Level</th>
				<th>Message</th>
				<th>Context</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $logs ) ) : ?>
				<tr><td colspan="4">No logs found.</td></tr>
			<?php else : ?>
				<?php foreach ( $logs as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log['created_at'] ); ?></td>
						<td><span class="log-level-<?php echo esc_attr( $log['level'] ); ?>"><?php echo esc_html( strtoupper( $log['level'] ) ); ?></span></td>
						<td><?php echo esc_html( $log['message'] ); ?></td>
						<td><small><?php echo esc_html( $log['context'] ); ?></small></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<style>
.log-level-error { color: #d63638; font-weight: bold; }
.log-level-warning { color: #dba617; font-weight: bold; }
.log-level-info { color: #2271b1; }
</style>
