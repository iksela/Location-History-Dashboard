<?php
include 'DB.php';
include 'LHD.php';
include 'Distance.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

$db = new DB();

LHD::initMonitor($db->getNbDataPoints());

$q = $db->getAllDataPoints();

$i			= 0;
$lastdate	= null;
$last		= null;
$summary	= new Summary();

while ($r = $q->fetch(PDO::FETCH_OBJ)) {
	// set accuracy if missing
	if (!property_exists($r, 'accuracy')) $r->accuracy = 0;
	
	$date = explode(' ', $r->pointdate)[0];

	// save
	if ($date != $lastdate && $summary->nbPoints > 0) {
		//var_dump("date changed, got at least one point - should save");
		$summary->day = $lastdate;
		$summary->avgSpeed = (count($summary->avgSpeed) > 0) ? array_sum($summary->avgSpeed) / count($summary->avgSpeed) : 0;
		var_dump($summary);
		//TODO: save summary entry
		// Changed days, this checks if we moved between days
		$summary = new Summary();
		$summary->day		= $date;
		$summary->from		= $last->timestampMs;
		$summary->to		= $r->timestampMs;
		$summary->setDistance($last, $r);

		var_dump($summary);
		//TODO: save summary entry
		$summary = new Summary();
		$summary->day		= $date;
		$summary->from		= $r->timestampMs;
	}
	elseif ($date == $lastdate) {
		// distance
		$stateChanged = $summary->addDistance($last, $r);
		$interval = intval(($r->timestampMs - $last->timestampMs) / 1000);

		if ($stateChanged) {
			$summary->to = $r->timestampMs;
			var_dump("event changed!");
			var_dump($summary);
			$summary = new Summary();
			$summary->day		= $date;
			$summary->from		= $r->timestampMs;
			$summary->setDistance($last, $r);
		}
		
/*		// interval
		$interval = intval(($r->timestampMs - $last->timestampMs) / 1000);
		
		// speed, only relevant if > 1kmh
		$speed = ($interval) ? $distance / ($interval / 3600) : 0;
		if ($speed > 1) $summary->avgSpeed[] = $speed;
		if ($speed > $summary->maxSpeed) $summary->maxSpeed = $speed;*/
	}
	else {
		$summary->from	= $r->timestampMs;
		$summary->to	= $r->timestampMs;
	}
	$lastdate = $date;
	$last = $r;
	$summary->nbPoints++;

	$i++;

	if ($i%1000 == 0) {
		LHD::updateMonitor($i);
	}

	if ($i > 100) break;
}

$end = microtime(true) - $start;
var_dump($end);