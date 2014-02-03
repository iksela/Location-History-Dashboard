<?php
class LHD {
	private $connection;

	public function __construct() {
		$cfg = parse_ini_file('config.ini', true);
		$this->connection = new PDO($cfg['connection']['dns'], $cfg['connection']['user'], $cfg['connection']['pass']);
	}

	public function add($object) {
		if (!$this->exists($object)) {
			$date = new DateTime();
			$date->setTimestamp($object->timestampMs/1000);
			$query = $this->connection->prepare("INSERT INTO lhd VALUES (:ts, :lat, :lng, :acc, :date)");
			$r = $query->execute(array(
				'ts'	=> $object->timestampMs,
				'lat'	=> $object->latitudeE7,
				'lng'	=> $object->longitudeE7,
				'acc'	=> $object->accuracy,
				'date'	=> $date->format('Y-m-d H:i:s')
			));
		}
	}

	public function exists($object) {
		$query = $this->connection->prepare("SELECT timestampMs FROM lhd WHERE timestampMs = :ts");
		$query->execute(array('ts' => $object->timestampMs));

		return $query->fetch(PDO::FETCH_ASSOC);
	}
}