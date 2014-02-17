<?php
session_start();
session_unset();
include 'layout.php';
include 'DB.php';

$layout = new Layout();
$layout->header();
$layout->nav('explore.php');

$db = new DB();
$lastdate = $db->getLastDate();
?>
<div class="container">
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	<script type="text/javascript">
		var map;
		var markers = [];

		var circle ={
			path: google.maps.SymbolPath.CIRCLE,
			fillColor: 'red',
			fillOpacity: .4,
			scale: 4.5,
			strokeColor: 'white',
			strokeWeight: 1
		};

		function initialize() {
			var myLatlng = new google.maps.LatLng(-25.363882,131.044922);
			var mapOptions = {
				zoom: 4,
				center: myLatlng
			}
			map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
		}

		google.maps.event.addDomListener(window, 'load', initialize);

		$(document).ready(function(){
			$('#dp').datepicker({
				format: "yyyy-mm-dd",
				endDate: "<?=$lastdate;?>"
			}).on('changeDate', function(e) {
				$.ajax({
					url :	'data.php',
					type:	'POST',
					dataType: "json",
					data:	{day: e.format()},
					success:	function(data) {
						$('#h3-title').html('Showing data for '+e.format());
						$('#data').html(data.html);
						console.log(data.points);
						new google.maps.Marker({position: new google.maps.LatLng(-25.363882,131.044922), map: map, icon: circle});
					}
				});
			});
		});
	</script>
	<div class="row">
		<div class="col-md-3">
			<h3>Pick a day</h3>
			<div id="dp"></div>
		</div>
		<div class="col-md-7">
			<h3 id='h3-title'>No data loaded.</h3>
			<div id="data"></div>
			<div id="map-canvas" style="height:500px;"></div>
		</div>
	</div>
</div>
<?php
$layout->footer();
?>
