<?php
include 'DB.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

$db = new DB();
$q = $db->getAllDataPoints();

$i = 0;
while ($r = $q->fetch(PDO::FETCH_OBJ)) {
	var_dump($r);

	$i++;

	if ($i > 10) break;
}

$end = microtime(true) - $start;
var_dump($end);