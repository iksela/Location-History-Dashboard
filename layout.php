<?php
class Layout {
	private $menu;

	public function __construct($menu=array()) {
		if (count($menu) == 0) {
			$cfg = parse_ini_file('config.ini', true);
			$menu = $cfg['menu'];
		}
		$this->menu = $menu;
	}

	public function header() {
		echo <<<EOT
<!doctype html>
<html>
<head>
	<title>LHD</title>
	<meta charset="utf-8">

	<script src="https://code.jquery.com/jquery.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>

	<link rel="stylesheet" href="css/css.css">

	<link rel="stylesheet" href="css/datepicker3.css">

	<script src="js/bootstrap-datepicker.js"></script>
</head>
EOT;
	}

	public function nav($here) {
		echo <<<EOT
<body>
	<div class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="#">LHD</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
EOT;
		foreach($this->menu as $key => $item) {
			echo ($key == $here) ? '<li class="active">' : '<li>';
			echo "<a href='$key'>$item</a></li>";
		}

		echo <<<EOT
				</ul>
			</div>
		</div>
	</div>
EOT;
	}

	public function footer() {
		echo <<<EOT
</body>
</html>
EOT;
	}
}