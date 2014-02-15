<?php
session_start();
session_unset();
include 'layout.php';
include 'DB.php';
include 'Distance.php';

$layout = new Layout();
$layout->header();
$layout->nav('index.php');
$db = new DB();
?>
<div class="container">
	<div class="page-header">
		<h1>LHD <small>a Google Location History Dashboard</small></h1>
	</div>

	<?php
	$distance	= round($db->getSumDistance());
	$kmleft		= Distance::MOON - $distance;
	$percent	= $distance/Distance::MOON*100; 
	?>

	<div class="jumbotron">
		<h1>Reach for the moon!</h1>
		<p><?=$distance; ?>km travelled, <?=$kmleft; ?>km to go.</p>
		<div id='progress-container'>
			<div class="progress progress-striped active">
					<div id='progress' class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?=$percent;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$percent;?>%"></div>
			</div>
		</div>
	</div>

	Currently <span class="badge"><?=$db->getNbDataPoints(); ?></span> data points and <span class="badge"><?=$db->getNbSummaries(); ?></span> events.
</div>

<?php
$layout->footer();
?>
