<?php
include 'lib.php';

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
				if (strpos($line, '}, {') === false) {
					$buffer .= $line;
				}
				else {
					$buffer .= '}';
					$object = json_decode($buffer);
					//var_dump($object);
					
					$lhd->add($object);
					$buffer = '{';
				}
			}
			$lines_processed++;

			if ($lines_processed % 100 == 0) {
				session_start();
				$_SESSION['ftell'] = ftell($handle);
				session_write_close();
			}

			if ($lines_processed > 90000) exit();
		}
	}
}