<?php
class DB {
	private $connection;

	private $data;

	private $placeholders;

	public function __construct() {
		$this->data = array();
		$this->placeholders = array();
		$cfg = parse_ini_file('config.ini', true);
		$this->connection = new PDO($cfg['connection']['dns'], $cfg['connection']['user'], $cfg['connection']['pass']);
	}

	public function addDataPoint($object) {
		$date = new DateTime();
		$date->setTimestamp($object->timestampMs/1000);
		$this->placeholders[] = "(?,?,?,?,?)";
		$this->data[] = $object->timestampMs;
		$this->data[] = $object->latitudeE7;
		$this->data[] = $object->longitudeE7;
		$this->data[] = (property_exists($object, 'accuracy')) ? $object->accuracy : 0;
		$this->data[] = $date->format('Y-m-d H:i:s');
	}

	public function commitDataPoints() {
		$sql  = "INSERT INTO lhd_datapoints (timestampMs, latitude, longitude, accuracy, pointdate) VALUES "; 
		$sql .= implode(', ', $this->placeholders);
		$sql .= " ON DUPLICATE KEY UPDATE timestampMs=VALUES(timestampMs), latitude=VALUES(latitude), longitude=VALUES(longitude), accuracy=VALUES(accuracy), pointdate=VALUES(pointdate)";
		$query = $this->connection->prepare($sql);
		$b = $query->execute($this->data);
		if (!$b) {
			var_dump($query->errorInfo());
			var_dump($sql);
			var_dump($this->data);
		}

		$this->data = array();
		$this->placeholders = array();
	}

	public function addSummary($object) {
		$this->placeholders[] = "(?,?,?,?,?)";
		$this->data[] = $object->day;
		$this->data[] = $object->distance*1000;
		$this->data[] = $object->moving;
		$this->data[] = $object->from;
		$this->data[] = $object->to;
	}

	public function commitSummaries() {
		$sql  = "INSERT INTO lhd_summary (day, distance, moving, dp_from, dp_to) VALUES ";
		$sql .= implode(', ', $this->placeholders);
		$sql .= " ON DUPLICATE KEY UPDATE day=VALUES(day), distance=VALUES(distance), moving=VALUES(moving), dp_from=VALUES(dp_from), dp_to=VALUES(dp_to)";
		$query = $this->connection->prepare($sql);
		$b = $query->execute($this->data);
		if (!$b) {
			var_dump($query->errorInfo());
			var_dump($sql);
			var_dump($this->data);
		}

		$this->data = array();
		$this->placeholders = array();
	}

	public function resetDataPoints() {
		$sql = "TRUNCATE lhd_datapoints";
		$query = $this->connection->prepare($sql);
		$query->execute();
	}

	public function resetSummaries() {
		$sql = "TRUNCATE lhd_summary";
		$query = $this->connection->prepare($sql);
		$query->execute();
	}

	public function getNbDataPoints() {
		$q = $this->connection->prepare("SELECT COUNT(*) FROM lhd_datapoints");
		$r = $q->execute();
		return $q->fetch()[0];
	}

	public function getAllDataPoints() {
		$this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		$q = $this->connection->prepare("SELECT * FROM lhd_datapoints ORDER BY pointdate ASC");
		$q->execute();
		return $q;
	}

	public function getNbSummaries() {
		$q = $this->connection->prepare("SELECT COUNT(*) FROM lhd_summary");
		$r = $q->execute();
		return $q->fetch()[0];
	}

	public function getSumDistance() {
		$q = $this->connection->prepare("SELECT SUM(distance)/1000 FROM lhd_summary");
		$r = $q->execute();
		return $q->fetch()[0];
	}

	public function getMonthlyDistance() {
		$q = $this->connection->prepare("SELECT DATE_FORMAT(day, '%Y-%m') as month, SUM(distance)/1000 as distance FROM lhd_summary GROUP BY 1");
		$r = $q->execute();
		return $q;
	}

	public function getDailyDistance() {
		$q = $this->connection->prepare("SELECT day, SUM(distance)/1000 as distance FROM lhd_summary GROUP BY 1");
		$r = $q->execute();
		return $q;
	}

	public function getLastDate() {
		$q = $this->connection->prepare("SELECT day FROM lhd_summary ORDER BY day DESC LIMIT 0,1");
		$r = $q->execute();
		return $q->fetch()[0];
	}

	public function getSummaryByDay($day) {
		$q = $this->connection->prepare("SELECT * FROM lhd_summary WHERE day=? ORDER BY dp_from ASC");
		$r = $q->execute(array($day));
		return $q->fetchAll(PDO::FETCH_OBJ);
	}

	public function getDataPointsByDay($day) {
		$q = $this->connection->prepare("SELECT * FROM lhd_datapoints WHERE DATE_FORMAT(pointdate, '%Y-%m-%d')=? ORDER BY timestampMs ASC");
		$r = $q->execute(array($day));
		return $q->fetchAll(PDO::FETCH_OBJ);
	}

	public function getSummarizedDataPointsByDay($day) {
		$sql  = "SELECT distance, moving, dp_from, dp_to, latitude, longitude, accuracy, pointdate, timestampMs FROM lhd_summary s, lhd_datapoints d WHERE day = ? AND timestampMs >= dp_from AND timestampMs <= dp_to";
		$q = $this->connection->prepare($sql);
		$r = $q->execute(array($day));
		return $q->fetchAll(PDO::FETCH_OBJ);
	}
}
