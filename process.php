<?php
include 'DB.php';
include 'LHD.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

if ($_FILES['lh']['name'] != '') {
	LHD::initMonitor($_FILES['lh']['size']);

	$handle = fopen($_FILES['lh']['tmp_name'], "r");
	$started = false;
	$buffer = '';
	$items = 0;
	$lines_processed = 0;
	$db = new DB();
	$db->resetDataPoints();
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			if (strpos($line, 'locations') !== false && !$started) {
				$started = true;
				$buffer = '{';
			}
			elseif ($started) {
				if (strpos($line, '}, {') != 2) {
					$buffer .= $line;
				}
				else {
					$buffer .= '}';
					$object = json_decode($buffer);

					if (!is_object($object)) {
						var_dump($buffer);
					}
					else {
						$db->addDataPoint($object);
						$items++;
					}
					$buffer = '{';
				}
			}
			$lines_processed++;

			if ($lines_processed % 10000 == 0) {
				LHD::updateMonitor(ftell($handle));
				if ($items > 0) {
					$db->commitDataPoints();
				}
				$items = 0;
			}
		}
		// last commit
		LHD::updateMonitor(ftell($handle));
		if ($items > 0) {
			$db->commitDataPoints();
		}

		$end = microtime(true) - $start;
		var_dump($end);
	}
}