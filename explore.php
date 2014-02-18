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
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
	<script type="text/javascript">
		var map;
		var heatmap;
		var flightPath;
		var markers = [];

		var circle ={
			path: google.maps.SymbolPath.CIRCLE,
			fillColor: 'red',
			fillOpacity: .4,
			scale: 4.5,
			strokeColor: 'white',
			strokeWeight: 1
		};

		var bounds = new google.maps.LatLngBounds();

		function initialize() {
			var myLatlng = new google.maps.LatLng(48.858859,2.34706);
			var mapOptions = {
				zoom: 10,
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
				for (var i = 0; i < markers.length; i++) {
					markers[i].setMap(null);
				}
				if (flightPath) flightPath.setMap(null);
				$.ajax({
					url :	'data.php',
					type:	'POST',
					dataType: "json",
					data:	{day: e.format()},
					success:	function(data) {
						$('#h3-title').html('Showing data for '+e.format());
						$('#data').html(data.html);
						var points = [];
						$.each(data.points, function(i){
							var point = data.points[i];
							var currentLatLng = new google.maps.LatLng(point.lat,point.lng);
							points.push(currentLatLng);
							var marker = new google.maps.Marker({position: currentLatLng, map: map, icon: circle});
							markers.push(marker);
							bounds.extend(marker.position);
						});

						flightPath = new google.maps.Polyline({
							path: points,
							geodesic: true,
							strokeColor: '#FF0000',
							strokeOpacity: 1.0,
							strokeWeight: 2
						});

						flightPath.setMap(map);

						heatmap = new google.maps.visualization.HeatmapLayer({
							data: new google.maps.MVCArray(points)
						});

						heatmap.setMap(map);

						map.fitBounds(bounds);
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
