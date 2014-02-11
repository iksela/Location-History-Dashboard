<?php
include 'DB.php';

ini_set('max_execution_time', 5*60);

$start = microtime(true);

$end = microtime(true) - $start;
var_dump($end);