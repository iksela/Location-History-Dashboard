<?php
session_start();
include 'DB.php';

$db = new DB();

if ($_POST['day']) {
	$data = array();
	$summary = $db->getSummaryByDay($_POST['day']);

	$html = '';

	foreach ($summary as $event) {
		$html .= '<div class="well well-sm">';

		$from = new DateTime();
		$from->setTimestamp($event->dp_from/1000);
		$to = new DateTime();
		$to->setTimestamp($event->dp_to/1000);
		$interval = $from->diff($to);

		$distance = round($event->distance/1000);

		$html .= "[".$from->format("Y-m-d H:i")." to ".$to->format("Y-m-d H:i")." (".$interval->format("%H:%I").")] ";

		if ($event->moving) {
			$html .= "Moved $distance km.";
		}
		else {
			$html .= "Idle.";
		}

		$html .= '</div>';
/*		$data[] = array(
			'from'	=> $from->format("Y-m-d H:i"),
			'to'	=> $to->format("Y-m-d H:i"),
			'interval'	=> $interval->format("%H:%I"),
			'moving'	=> $event->moving,
			'distance'	=> round($event->distance/1000)
		);*/
	}
	
	echo $html;
}