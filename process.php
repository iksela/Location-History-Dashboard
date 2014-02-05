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
					$lhd->add($object);
					$items++;
					$buffer = '{';
				}
			}
			$lines_processed++;

			if ($lines_processed % 100 == 0) {
				var_dump("commit: ".$items);
				$lhd->commit();
				@session_start();
				$_SESSION['ftell'] = ftell($handle);
				session_write_close();
				$items = 0;
			}

			if ($lines_processed > 9000) {
				exit();
			}
		}
	}
}