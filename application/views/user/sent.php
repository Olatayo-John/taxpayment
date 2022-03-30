
<div class="ml-3 mr-3">
	<table data-toggle="table" data-cache="false" data-search="true" data-show-export="true" data-buttons-prefix="btn-md btn" data-buttons-align="left" data-pagination="true" data-page-size="50" data-sticky-header="true" data-sticky-header-offset-y="60">
		<thead class="text-light" style="background:#223b55">
			<tr>
				<th data-field="Number">Mobile</th>
				<th data-field="Message">Message</th>
				<th data-field="Response">Response</th>
				<th data-field="Status">Status</th>
				<th data-field="Date">Date</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($sent->result_array() as $info) : ?>
				<tr>
					<td>
						<?php echo ($info['mobile']) ?>
					</td>
					<td>
						<?php echo ($info['msg']) ?>
					</td>
					<td>
						<?php echo ($info['error']) ?>
					</td>
					<td>
						<?php echo ($info['status']) ?>
					</td>
					<td>
						<?php echo ($info['date']) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>


<script>
	$('table').bootstrapTable({
		exportOptions: {
			fileName: "signal-logs"
		},
		exportTypes: ['json', 'csv', 'txt', 'excel']
	})

	$(document).ready(function() {

		$(document).on("click", ".vvv", function(e) {
			e.preventDefault();
		});
	});
</script>