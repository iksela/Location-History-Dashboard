<?php
session_start();
session_unset();
include 'layout.php';

$layout = new Layout();
$layout->header();
$layout->nav('analysis.php');
?>
<div class="container">
	<script type="text/javascript">
		$(document).ready(function(){
			$('#submit').on('click', function(e){
				e.stopPropagation();
				e.preventDefault();

				$('#myform').hide();
				$('#progress-container').show('slow');

				var monitor = function() {
					$.ajax({
						url :		'monitor.php',
						success :	function (data) {
							console.log(data);
							$('#progress').attr('aria-valuenow', data).css('width', data+'%');
						}
					});
				};

				var timer = setInterval(monitor, 1000);

				$.ajax({
					url			: 'processDb.php',
					type		: 'GET',
					success		: function(data, textStatus, jqXHR){
						console.log(data);
						$('#debug').append(data);
						clearInterval(timer);
						monitor();
					},
					error		: function(data) {
						console.log(data);
						$('#debug').append(data);
						clearInterval(timer);
					}
				});
			});
		});
	</script>
	<form action="processDb.php" method="GET" class="form-inline" role="form" id='myform'>
		<fieldset>
			<legend>Data points analysis</legend>
			<div class="form-group">
				<input type="button" value="Let's go!" class="btn btn-primary" id='submit'>
			</div>
		</fieldset>
	</form>
	<div id='progress-container' style='display:none'>
		<div class="progress progress-striped active">
				<div id='progress' class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
		</div>
	</div>
	<div id='debug'></div>
</div>
<?php
$layout->footer();
?>
