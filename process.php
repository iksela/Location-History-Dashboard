<?php
include 'lib.php';

$start = microtime(true);

if ($_FILES['lh']['name'] != '') {
	session_start();
	$_SESSION['filesize'] = $_FILES['lh']['size'];
	$_SESSION['ftell'] = 0;
	session_write_close();

	$handle = fopen($_FILES['lh']['tmp_name'], "r");
	$started = false;
	$buffer = '';
	$items = 0;
	$lines_processed = 0;
	$lhd = new LHD();
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			if (strpos($line, 'locations') !== false && !$started) {
				$started = true;
				$buffer = '{';
			}
			elseif ($started) {
				//var_dump(strpos($line, '}, {'));
				if (strpos($line, '}, {') != 2) {
					$buffer .= $line;
				}
				else {
					$buffer .= '}';
					$object = json_decode($buffer);
					//var_dump($object);
					//var_dump(strpos($line, '}, {'));
					//if (!is_object($object)) var_dump("above");
					if (!is_object($object)) {
						var_dump($buffer);
					}
					else {
						$items += $lhd->add($object);
					}
					$buffer = '{';
				}
			}
			$lines_processed++;

			if ($lines_processed % 10000 == 0) {
				@session_start();
				$_SESSION['ftell'] = ftell($handle);
				$_SESSION['debug'] = array(
					'commits'	=> $items,
					'lines'		=> $lines_processed
				);
				session_write_close();
				if ($items > 0) {
					$lhd->commit();
				}
				$items = 0;
			}

			if ($lines_processed > 100000) {
				break;
			}
		}
		$end = microtime(true) - $start;
		var_dump($end);
	}
}