<!doctype html>
<html>
<head>
	<title>LHD</title>
	<meta charset="utf-8">

	<script src="https://code.jquery.com/jquery.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>

	<script src="//gregpike.net/demos/bootstrap-file-input/bootstrap.file-input.js"></script>
</head>
<body>
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
						}
					});
				});
			});
		</script>
		<div class="page-header">
			<h1>LHD <small>a Google Location History Dashboard</small></h1>
		</div>
		<form action="process.php" method="POST" enctype="multipart/form-data" class="form-inline" role="form" id='myform'>
			<fieldset>
				<legend>File Upload</legend>
				<div class="form-group">
					<input type="file" name="lh" class="btn btn-default" title="Choose file">
					<input type="button" value="Let's go!" class="btn btn-primary" id='submit'>
				</div>
			</fieldset>
		</form>
	</div>
</body>
</html>
