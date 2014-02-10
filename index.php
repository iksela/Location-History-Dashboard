<?php
session_start();
session_unset();
include 'layout.php';
include 'DB.php';

$layout = new Layout(array(
	'index.php'		=> 'Home',
	'upload.php'	=> 'File upload'
));
$layout->header();
$layout->nav('index.php');
$db = new DB();
?>
<div class="container">
	<div class="page-header">
		<h1>LHD <small>a Google Location History Dashboard</small></h1>
	</div>
	Currently <span class="badge"><?=$db->getNbDataPoints(); ?></span> data points.
</div>
<?php
$layout->footer();
?>
