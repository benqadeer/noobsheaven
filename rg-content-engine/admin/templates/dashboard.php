<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

global $wpdb;

// Stats
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = 'product' AND post_status = 'publish'");
$total_keywords = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rg_ce_keywords");
$used_keywords = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rg_ce_keywords WHERE is_used = 1");
$total_generations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rg_ce_history");
$total_cost = $wpdb->get_var("SELECT SUM(cost) FROM {$wpdb->prefix}rg_ce_history");
$recent_history = $wpdb->get_results("SELECT h.*, p.post_title FROM {$wpdb->prefix}rg_ce_history h LEFT JOIN {$wpdb->prefix}posts p ON h.product_id = p.ID ORDER BY h.created_at DESC LIMIT 5");

?>
<div class="wrap rg-ce-dashboard">
	<h1>RG Content Engine Dashboard</h1>

	<div class="rg-ce-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
		<div class="rg-ce-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Total Products</h3>
			<p style="font-size: 24px; font-weight: bold;"><?php echo number_format($total_products); ?></p>
		</div>
		<div class="rg-ce-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Keywords</h3>
			<p style="font-size: 24px; font-weight: bold;"><?php echo number_format($used_keywords); ?> / <?php echo number_format($total_keywords); ?> <small style="font-size:12px; font-weight:normal;">Used</small></p>
		</div>
		<div class="rg-ce-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Generations</h3>
			<p style="font-size: 24px; font-weight: bold;"><?php echo number_format($total_generations); ?></p>
		</div>
		<div class="rg-ce-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Estimated Cost</h3>
			<p style="font-size: 24px; font-weight: bold;">$<?php echo number_format((float)$total_cost, 2); ?></p>
		</div>
	</div>

	<div style="margin-top: 30px;">
		<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Recent Activity</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Product</th>
						<th>Status</th>
						<th>Time</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($recent_history as $item): ?>
						<tr>
							<td><?php echo esc_html($item->created_at); ?></td>
							<td><?php echo esc_html($item->post_title ?: "Product #".$item->product_id); ?></td>
							<td><?php echo esc_html($item->status); ?></td>
							<td><?php echo number_format($item->execution_time, 2); ?>s</td>
						</tr>
					<?php endforeach; ?>
					<?php if (empty($recent_history)): ?>
						<tr><td colspan="4">No activity yet.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
			<p><a href="admin.php?page=rg-ce-history" class="button">View All History</a></p>
		</div>
	</div>

	<div style="margin-top: 30px; display: flex; gap: 20px;">
		<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Quick Actions</h3>
			<ul style="list-style: disc; padding-left: 20px;">
				<li><a href="admin.php?page=rg-ce-profiles">Manage Content Profiles</a></li>
				<li><a href="admin.php?page=rg-ce-keywords">Import Keywords</a></li>
				<li><a href="admin.php?page=rg-ce-bulk">Start Bulk Generation</a></li>
				<li><a href="admin.php?page=rg-ce-settings">Configure API Keys</a></li>
			</ul>
		</div>
		<div style="flex: 1; background: #f0f0f1; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h3>Developer Info</h3>
			<p><strong>Plugin:</strong> RG Content Engine</p>
			<p><strong>Developer:</strong> Abdur-Rehman Qadeer</p>
			<p><strong>Website:</strong> <a href="https://ranksgiving.com/" target="_blank">ranksgiving.com</a></p>
		</div>
	</div>
</div>
