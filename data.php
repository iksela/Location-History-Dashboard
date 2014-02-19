<?php
session_start();
include 'DB.php';
include 'Distance.php';

$db = new DB();

if ($_POST['day']) {
	$data = array();
	$sumdistance = 0;
	$summary = $db->getSummaryByDay($_POST['day']);
	$points = $db->getSummarizedDataPointsByDay($_POST['day']);

	$html = '<div class="well well-sm">';

	foreach ($summary as $event) {
		$from = new DateTime();
		$from->setTimestamp($event->dp_from/1000);
		$to = new DateTime();
		$to->setTimestamp($event->dp_to/1000);
		$interval = $from->diff($to);

		$distance = round($event->distance/1000);
		$sumdistance += $event->distance;

		$html .= "[".$from->format("Y-m-d H:i")." to ".$to->format("Y-m-d H:i")." (".$interval->format("%H:%I").")] ";

		if ($event->moving) {
			$html .= "Moved $distance km.";
		}
		else {
			$html .= "Idle.";
		}

		$html .= '<br/>';

		$aggregated = array(
			"lat" => 0,
			"lng" => 0,
			"acc" => 0,
			"cnt" => 0,
		);
		foreach ($points as $point) {
			if ($point->dp_from >= $event->dp_from && $point->dp_to <= $event->dp_to) {
				$thisPoint = array(
					"lat" => $point->latitude/Distance::E7,
					"lng" => $point->longitude/Distance::E7,
					"acc" => $point->accuracy,
					"cnt" => 1
				);
				if ($event->moving) {
					$data[] = $thisPoint;
				}
				else {
					$aggregated["lat"] += $thisPoint["lat"];
					$aggregated["lng"] += $thisPoint["lng"];
					$aggregated["acc"] += $thisPoint["acc"];
					$aggregated["cnt"]++;
				}
			}
		}
		if ($aggregated["cnt"] > 0) {
			$data[] = array(
				"lat" => $aggregated["lat"]/$aggregated["cnt"],
				"lng" => $aggregated["lng"]/$aggregated["cnt"],
				"acc" => $aggregated["acc"]/$aggregated["cnt"],
				"cnt" => $aggregated["cnt"]/count($points)*100
			);
		}
	}
	$html .= '</div>';
	
	$obj = new stdClass();
	$obj->html = $html;
	$obj->points = $data;
	$obj->distance = $sumdistance;
	echo json_encode($obj);
}