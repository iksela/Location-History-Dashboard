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
			});
		</script>
		<div class="page-header">
			<h1>LHD <small>a Google Location History Dashboard</small></h1>
		</div>
		<form action="process.php" method="POST" enctype="multipart/form-data" class="form-inline" role="form">
			<fieldset>
				<legend>File Upload</legend>
				<div class="form-group">
					<input type="file" name="lh" class="btn btn-default" title="Choose file">
					<input type="submit" value="Let's go!" class="btn btn-primary">
				</div>
			</fieldset>
		</form>
	</div>
</body>
</html>
