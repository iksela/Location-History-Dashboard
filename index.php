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

$data = array();
$months = $db->getMonthlyDistance()->fetchAll(PDO::FETCH_NUM);
foreach ($months as $month) {
	$date = explode('-', $month[0]);
	$data[] = array(
		'new Date('.$date[0].','.$date[1].')',
		round($month[1])
	);
}
$monthsJson = json_encode($data);
$monthsJson = str_replace('"', '', $monthsJson);
?>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(function(){
	var continuousData = new google.visualization.DataTable();
	continuousData.addColumn('date', 'Month');
	continuousData.addColumn('number', 'Distance');

	continuousData.addRows(<?=$monthsJson;?>);

	var continuousChart = new google.visualization.LineChart(document.getElementById('chart_div'));
	continuousChart.draw(continuousData, {
		legend: {position: 'none'},
		curveType: 'function',
		vAxis: {
			viewWindow: {min:0}
		}
	});
});
</script>

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
			<div class="progress progress-striped">
					<div id='progress' class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?=$percent;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$percent;?>%"></div>
			</div>
		</div>
	</div>

	Currently <span class="badge"><?=$db->getNbDataPoints(); ?></span> data points and <span class="badge"><?=$db->getNbSummaries(); ?></span> events.

	<h2>Monthly distance</h2>
	<div id="chart_div"></div>
</div>

<?php
$layout->footer();
?>
