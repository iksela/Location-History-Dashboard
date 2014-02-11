<?php
class DB {
	private $connection;

	private $data;

	private $placeholders;

	const E7 = 10000000;

	public function __construct() {
		$this->data = array();
		$this->placeholders = array();
		$cfg = parse_ini_file('config.ini', true);
		$this->connection = new PDO($cfg['connection']['dns'], $cfg['connection']['user'], $cfg['connection']['pass']);
	}

	public function add($object) {
		$date = new DateTime();
		$date->setTimestamp($object->timestampMs/1000);
		$this->placeholders[] = "(?,?,?,?,?)";
		$this->data[] = $object->timestampMs;
		$this->data[] = $object->latitudeE7;
		$this->data[] = $object->longitudeE7;
		$this->data[] = (property_exists($object, 'accuracy')) ? $object->accuracy : 0;
		$this->data[] = $date->format('Y-m-d H:i:s');
	}

	public function commit() {
		$sql  = "INSERT INTO lhd_datapoints (timestampMs, latitude, longitude, accuracy, pointdate) VALUES "; 
		$sql .= implode(', ', $this->placeholders);
		$sql .= " ON DUPLICATE KEY UPDATE timestampMs=VALUES(timestampMs), latitude=VALUES(latitude), longitude=VALUES(longitude), accuracy=VALUES(accuracy), pointdate=VALUES(pointdate)";
		$query = $this->connection->prepare($sql);
		$b = $query->execute($this->data);
		if (!$b) {
			var_dump($_SESSION['debug']);
			var_dump($query->errorInfo());
			var_dump($sql);
			var_dump($this->data);
		}

		$this->data = array();
		$this->placeholders = array();
	}

	public function getNbDataPoints() {
		$q = $this->connection->prepare("SELECT COUNT(*) FROM lhd_datapoints");
		$r = $q->execute();
		return $q->fetch()[0];
	}

	public function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
		$earth_radius = 6371;

		$dLat = deg2rad($latitude2 - $latitude1);
		$dLon = deg2rad($longitude2 - $longitude1);

		$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
		$c = 2 * asin(sqrt($a));
		$d = $earth_radius * $c;

		return $d;
	}
}