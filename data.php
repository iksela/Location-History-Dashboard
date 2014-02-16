<?php
session_start();
include 'DB.php';

$db = new DB();

if ($_POST['day']) {
	$data = array();
	$summary = $db->getSummaryByDay($_POST['day']);

	foreach ($summary as $event) {
		$from = new DateTime();
		$from->setTimestamp($event->dp_from/1000);
		$to = new DateTime();
		$to->setTimestamp($event->dp_to/1000);
		$interval = $from->diff($to);
		$data[] = array(
			'from'	=> $from->format("Y-m-d H:i"),
			'to'	=> $to->format("Y-m-d H:i"),
			'interval'	=> $interval->format("%H:%I"),
			'moving'	=> $event->moving,
			'distance'	=> round($event->distance/1000)
		);
	}
	
	echo json_encode($data);
}