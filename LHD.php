<?php
class LHD {

	const E7 = 10000000;

	public static function getDistanceE7($latitude1, $longitude1, $latitude2, $longitude2) {
		$latitude1	= $latitude1 / self::E7;
		$longitude1	= $longitude1 / self::E7;
		$latitude2	= $latitude2 / self::E7;
		$longitude2	= $longitude2 / self::E7;

		$earth_radius = 6371;

		$dLat = deg2rad($latitude2 - $latitude1);
		$dLon = deg2rad($longitude2 - $longitude1);

		$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
		$c = 2 * asin(sqrt($a));
		$d = $earth_radius * $c;

		return $d;
	}

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

class Summary {
	public $day;
	public $distance;
	public $avgSpeed;
	public $maxSpeed;
	public $nbPoints;

	public function __construct() {
		$this->day			= null;
		$this->distance		= 0;
		$this->avgSpeed		= array();
		$this->maxSpeed		= 0;
		$this->nbPoints		= 0;
	}
}