</div>


<script type="text/javascript">
	$('[data-toggle="tooltip"]').tooltip();


	// setTimeout(() => document.querySelector('.alertsuccess,.alerterror').remove(), 6000);


	$(document).ready(function() {

		$(document).on("click", ".ajax_succ_div_close", function() {
			$(".ajax_succ_div").hide();
		});

		$(document).on("click", ".ajax_err_div_close", function() {
			$(".ajax_err_div").hide();
		});
	})
</script>
</body>

</html>