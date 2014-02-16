<?php
session_start();
session_unset();
include 'layout.php';
include 'DB.php';

$layout = new Layout();
$layout->header();
$layout->nav('explore.php');

$db = new DB();
$lastdate = $db->getLastDate();
?>
<div class="container">
	<script type="text/javascript">
		$(document).ready(function(){
			$('#dp').datepicker({
				format: "yyyy-mm-dd",
				endDate: "<?=$lastdate;?>"
			}).on('changeDate', function(e) {
				$.ajax({
					url :	'data.php',
					type:	'POST',
					data:	{day: e.format()},
					success:	function(data) {
						$('#h3-title').html('Showing data for '+e.format());
						$('#data').html(data);
					}
				});
			});
		});
	</script>
	<div class="row">
		<div class="col-md-3">
			<h3>Pick a day</h3>
			<div id="dp"></div>
		</div>
		<div class="col-md-7">
			<h3 id='h3-title'>No data loaded.</h3>
			<div id="data"></div>
		</div>
	</div>
</div>
<?php
$layout->footer();
?>
