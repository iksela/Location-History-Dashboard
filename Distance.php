<?php
class Distance {
	const E7 = 10000000;

	const MOON = 384400;

	public static function getDistance($last, $current) {
		return self::getDistanceE7($last->latitude, $last->longitude, $current->latitude, $current->longitude);
	}

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

	public static function getTimeIntervalFromTS($last, $current) {
		return abs(intval(($current - $last) / 1000));
	}

	public static function getTimeInterval($last, $current) {
		return self::getTimeIntervalFromTS($current->timestampMs, $last->timestampMs);
	}
}

class Summary {
	const MovingThreshold	= 0.1;	// km
	const TimeThreshold		= 120;	// seconds
	const SpeedThreshold	= 2;	// kmh

	public $day			= null;
	public $moving		= false;
	public $from		= null;
	public $to			= null;
	public $distance	= 0;
	public $nbPoints	= 0;

	public $ref;

	public function __construct() {
		$this->ref = array(
			'latitude'	=> 0,
			'longitude'	=> 0,
			'accuracy'	=> 0,
			'lastMotion' => 0
		);
	}

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
		// corrected distance for moving event detection
		$corrected = $distance - ($last->accuracy + $current->accuracy)/1000;
		$interval = (Distance::getTimeInterval($last, $current)/3600);
		$speed = ($interval) ? $corrected / $interval : 0;
		if ($this->nbPoints == 0) $this->nbPoints = 1;

		$distFromRef = ($this->ref) ? Distance::getDistanceE7($this->ref['latitude']/$this->nbPoints, $this->ref['longitude']/$this->nbPoints, $current->latitude, $current->longitude) - ($this->ref['accuracy']/$this->nbPoints + $current->accuracy)/1000 : 0;

		if ($distFromRef > 100) var_dump($this->ref);
		//var_dump("corrected distance: $corrected - speed: $speed - from ref: $distFromRef - moving:$this->moving");

		// if interval < threshold, do not trigger event when moving
		/*
		if (Distance::getTimeInterval($last, $current) < self::TimeThreshold && $this->moving && $corrected >) {
			var_dump("still moving because < TimeThreshold");
			return true;
		}
		*/

		$this->ref['latitude'] += $current->latitude;
		$this->ref['longitude'] += $current->longitude;
		$this->ref['accuracy'] += $current->accuracy;
		if ($speed > self::SpeedThreshold) $this->ref['lastMotion'] = $current->timestampMs;

		//if (($distFromRef > self::MovingThreshold && $this->nbPoints > 1) || ($speed > 2)) {
		/*
		Motion detection:
			Speed > Threshold & Distance > Threshold
		OR	DistanceFromReferencePoint > Threshold & Not moving (reference point is an aggregate of all the points while idling)	
		OR	TimeInterval(lastMotion, now) < Threshold & Moving - We'll wait at least TimeThreshold from last motion until deciding if motion has stopped
		*/
		if (   ($speed > self::SpeedThreshold && $corrected > self::MovingThreshold)
			|| ($distFromRef > self::MovingThreshold && !$this->moving)
			|| (Distance::getTimeIntervalFromTS($this->ref['lastMotion'], $current->timestampMs) < self::TimeThreshold && $this->moving)
			) {
			//var_dump("motion detected");
			return true;
		}
		//var_dump("assuming still");
		return false;
	}
}
