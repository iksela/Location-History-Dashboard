<?php
session_start();
$percent = 0;
if (array_key_exists('ftell', $_SESSION)) {
	$percent = round($_SESSION['ftell'] / $_SESSION['filesize'] * 100, 2);
}
echo $percent;