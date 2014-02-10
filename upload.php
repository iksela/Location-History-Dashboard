<?php
session_start();
session_unset();
include 'layout.php';

$layout = new Layout(array(
	'index.php'		=> 'Home',
	'upload.php'	=> 'File upload'
));
$layout->header();
$layout->nav('upload.php');
?>
<div class="container">
	<script type="text/javascript">
		$(document).ready(function(){
			$('input[type=file]').bootstrapFileInput();

			var files;
			$('input[type=file]').on('change', function prepareUpload(event) {
				files = event.target.files;
			});

			$('#submit').on('click', function(e){
				e.stopPropagation();
				e.preventDefault();
				var form = $(this);
				
				var data = new FormData();
				data.append('lh', files[0]);

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

				var formAction = form.attr('action');
				$.ajax({
					url         : 'process.php',
					data        : data,
					cache       : false,
					contentType : false,
					processData : false,
					type        : 'POST',
					success     : function(data, textStatus, jqXHR){
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
	<form action="process.php" method="POST" enctype="multipart/form-data" class="form-inline" role="form" id='myform'>
		<fieldset>
			<legend>File Upload</legend>
			<div class="form-group">
				<input type="file" name="lh" class="btn btn-default" title="Choose file">
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
