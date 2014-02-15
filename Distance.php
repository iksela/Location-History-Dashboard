<?php
class Distance {
	const E7 = 10000000;

	public static function getDistance($last, $current) {
		return self::getDistanceE7($last->latitude, $last->longitude, $current->latitude, $current->longitude);
	}

	private static function getDistanceE7($latitude1, $longitude1, $latitude2, $longitude2) {
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

	public static function getTimeInterval($last, $current) {
		return intval(($current->timestampMs - $last->timestampMs) / 1000);
	}
}

class Summary {
	const MovingThreshold	= 0.5; // km
	const TimeThreshold		= 300; // seconds

	public $day			= null;
	public $moving		= false;
	public $from		= null;
	public $to			= null;
	public $distance	= 0;
	public $nbPoints	= 0;

	public function setDistance($last, $current) {
		$this->distance = Distance::getDistance($last, $current);

		$this->moving = $this->isMoving($this->distance, $last, $current);
	}

	// Adds distance, returns true if state changed
	public function addDistance($last, $current) {
		$distance = Distance::getDistance($last, $current);

		$isMoving = $this->isMoving($distance, $last, $current);

		if ($this->moving == true && $isMoving == true) {
			$this->distance += $distance;
		}
		
		if ($isMoving != $this->moving) {
			return true;
		}
		return false;
	}

	public function isMoving($distance, $last, $current) {
		// if interval < threshold, do not trigger event when moving
		if (Distance::getTimeInterval($last, $current) < self::TimeThreshold && $this->moving) {
			return true;
		}

		// corrected distance for moving event detection
		$corrected = $distance - ($last->accuracy + $current->accuracy)*2/1000;

		if ($corrected > self::MovingThreshold) {
			return true;
		}
		return false;
	}
}
