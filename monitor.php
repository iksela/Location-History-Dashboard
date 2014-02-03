<?php
session_start();
$percent = round($_SESSION['ftell'] / $_SESSION['filesize'] * 100, 2);
echo $percent;