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

	// if doesn't have a from, use current ts
	if (!$summary->from) {
		$summary->from = $r->timestampMs;
	}

	// we need a reference point
	if ($last) {
		// try adding the distance and retrieve a changed state
		$stateChanged = $summary->addDistance($last, $r);

		if ($stateChanged) {
			var_dump('state changed');
			$summary->day = $lastdate;
			$summary->to = $last->timestampMs;
			$nowMoving = !$summary->moving;
			var_dump($summary);
			// TODO: save summary here

			// Begin new event
			$summary = new Summary();
			$summary->from = $last->timestampMs;
			$summary->moving = $nowMoving;
			// if switched from static to moving, set initial distance
			if ($nowMoving == true) {
				$summary->setDistance($last, $r);
			}
		}
	}

	$lastdate = $date;
	$last = $r;
	$summary->nbPoints++;

	$i++;

	if ($i%1000 == 0) {
		LHD::updateMonitor($i);
	}

	if ($i > 50) break;
}

$end = microtime(true) - $start;
var_dump($end);