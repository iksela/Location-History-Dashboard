<?php
session_start();
$percent = 0;
if (array_key_exists('current', $_SESSION)) {
	$percent = round($_SESSION['current'] / $_SESSION['total'] * 100, 2);
}
echo $percent;