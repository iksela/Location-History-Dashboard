<?php
class LHD {
	public static function initMonitor($total) {
		session_start();
		$_SESSION['total']		= $total;
		$_SESSION['current']	= 0;
		session_write_close();
	}

	public static function updateMonitor($value) {
		@session_start();
		$_SESSION['current'] = $value;
		session_write_close();
	}
}