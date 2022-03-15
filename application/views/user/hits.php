
<div class="ml-3 mr-3">
	<table data-toggle="table" data-cache="false" data-search="true" data-show-export="true" data-buttons-prefix="btn-md btn" data-buttons-align="left" data-pagination="true" data-page-size="50" data-sticky-header="true" data-sticky-header-offset-y="60">
		<thead class="text-light" style="background:#223b55">
			<tr>
				<th data-field="IP">IP</th>
				<th data-field="Number">Number</th>
				<th data-field="Date">Date</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($hits->result_array() as $hit) : ?>
				<tr>
					<td>
						<?php echo ($hit['ip']) ?>
					</td>
					<td>
						<?php echo ($hit['number']) ?>
					</td>
					<td>
						<?php echo ($hit['date']) ?>
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