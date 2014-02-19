<?php
include 'DB.php';
include 'LHD.php';
include 'Distance.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

$db = new DB();
$dbWrite = new DB();

LHD::initMonitor($db->getNbDataPoints());

$q = $db->getAllDataPoints();

$dbWrite->resetSummaries();

$i			= 0;
$lastdate	= null;
$last		= null;
$items		= 0;
$summary	= new Summary();

while ($r = $q->fetch(PDO::FETCH_OBJ)) {
	// set accuracy if missing
	if (!property_exists($r, 'accuracy')) $r->accuracy = 0;
	
	$date = explode(' ', $r->pointdate)[0];

	// if doesn't have a from, use current ts
	if (!$summary->from) {
		$summary->from = $r->timestampMs;
	}

	//var_dump($r->pointdate);
	if ($summary->ref['latitude'] == 0) $summary->ref = array('latitude' => $r->latitude, 'longitude' => $r->longitude, 'accuracy' => $r->accuracy, 'lastMotion' => 0);

	// we need a reference point
	if ($last) {
		// try adding the distance and retrieve a changed state
		$stateChanged = $summary->addDistance($last, $r);

		if ($stateChanged) {
			$summary->day = $lastdate;
			$summary->to = $last->timestampMs;
			$nowMoving = !$summary->moving;

			// Save summary
			//var_dump($summary);
			$dbWrite->addSummary($summary);
			$items++;

			// Begin new event
			$summary = new Summary();
			$summary->from = $last->timestampMs;
			$summary->moving = $nowMoving;
			
			$summary->ref = array('latitude' => $r->latitude, 'longitude' => $r->longitude, 'accuracy' => $r->accuracy, 'lastMotion' => 0);
			//$summary->nbPoints = 1;
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

	// Buffered writes
	if ($i%1000 == 0) {
		LHD::updateMonitor($i);
		if ($items > 0) {
			$dbWrite->commitSummaries();
		}
		$items = 0;
	}
}
// last commit
LHD::updateMonitor($i);
if ($items > 0) {
	$dbWrite->commitSummaries();
}

$end = microtime(true) - $start;
var_dump($end);