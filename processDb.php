<?php
include 'DB.php';
include 'LHD.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

$db = new DB();
$q = $db->getAllDataPoints();

$i			= 0;
$lastdate	= null;
$last		= null;
$summary	= new Summary();

while ($r = $q->fetch(PDO::FETCH_OBJ)) {
	if (!property_exists($r, 'accuracy')) $r->accuracy = 0;
	
	$date = explode(' ', $r->pointdate)[0];

	// save
	if ($date != $lastdate && $summary->nbPoints > 0) {
		var_dump("date changed, got at least one point - should save");
		$summary->day = $lastdate;
		$summary->avgSpeed = (count($summary->avgSpeed) > 0) ? array_sum($summary->avgSpeed) / count($summary->avgSpeed) : 0;
		var_dump($summary);
		//TODO: save summary entry
		$summary = new Summary();
	}
	elseif ($date == $lastdate) {
		// distance
		$distance = LHD::getDistanceE7($last->latitude, $last->longitude, $r->latitude, $r->longitude);
		$summary->distance += $distance;
		
		// interval
		$interval = intval(($r->timestampMs - $last->timestampMs) / 1000);

		// discard low accuracy (>100), low interval (<15m)
		if ($r->accuracy > 100 && $interval < 15*60) {
			continue;
		}
		
		// speed, only relevant if > 1kmh
		$speed = ($interval) ? $distance / ($interval / 3600) : 0;
		if ($speed > 1) $summary->avgSpeed[] = $speed;
		if ($speed > $summary->maxSpeed) $summary->maxSpeed = $speed;
	}
	$lastdate = $date;
	$last = $r;
	$summary->nbPoints++;

	$i++;

	if ($i > 500) break;
}

$end = microtime(true) - $start;
var_dump($end);