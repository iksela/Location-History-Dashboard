<?php
include 'lib.php';
var_dump($_FILES);
if ($_FILES['lh']['name'] != '') {
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
					var_dump($object);
					$lhd->add($object);
					$buffer = '{';
				}
			}
			$lines_processed++;

			if ($lines_processed > 90) exit();
		}
	}
}