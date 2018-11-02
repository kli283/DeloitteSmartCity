<?php

if (empty($_GET['id'])) {
  //don't do anything
  //echo("No ID specified");
} else {
    $mysqli = new mysqli('localhost', 'root', 'TekChange2018', 'tech_city');

    if($mysqli->connect_errno) {
    	printf("Connection Failed: %s\n", $mysqli->connect_error);
    	exit;
    }

    $chekpoint_id = $_GET['id'];

    $stmt = $mysqli->prepare("SELECT name, latitude, longitude FROM chekpoint WHERE id=?");
        if(!$stmt){
          printf("Query Prep Failed: %s\n", $mysqli->error);
          exit();
        }
        $stmt->bind_param("s", $chekpoint_id);
        $stmt->execute();
        $stmt->bind_result($name, $latitude, $longitude);

        $stmt->fetch();
        $stmt->close();
}
?>


<!doctype html>
  <html>
    <head>
      <meta charset="UTF-8">
      <title> <?php echo $name ?>  </title>
      <style>
      /* Set the size of the div element that contains the map */
      #map {
        height: 400px;  /* The height is 400 pixels */
        width: 100%;  /* The width is the width of the web page */
       }
       #map2 {
         height: 400px;  /* The height is 400 pixels */
         width: 100%;  /* The width is the width of the web page */
        }
    </style>
    </head>

    <body>
          <h3>TekChange</h3>
          <h3>Name: &nbsp <?php echo $name ?> </h3>
          <h3>ID: &nbsp <?php echo $chekpoint_id?> </h3>

        <!--The div element for the map -->
        <div id="map"></div>
        <b>Mode of Travel: </b>
      <select id="mode">
        <option value="WALKING">Walking</option>
        <option value="DRIVING">Driving</option>
      </select>

      <div id="map2"></div>
        <script>
    // Initialize and add the map
    function initMap() {
      // The location of Uluru
      var tempMap = {lat: 22.2780691, lng: 114.16490905};
      // The map, centered at Uluru
      var map = new google.maps.Map(
          document.getElementById('map'), {zoom: 20, center: tempMap,disableDefaultUI: true});
      // The marker, positioned at Uluru
      var marker = new google.maps.Marker({position: tempMap, map: map});

      var directionsDisplay = new google.maps.DirectionsRenderer;
      var directionsService = new google.maps.DirectionsService;
      var map2 = new google.maps.Map(document.getElementById('map2'), {
        zoom: 14,
        center: {lat: 37.77, lng: -122.447},
        disableDefaultUI: true
      });
      directionsDisplay.setMap(map2);

      calculateAndDisplayRoute(directionsService, directionsDisplay);
      document.getElementById('mode').addEventListener('change', function() {
        calculateAndDisplayRoute(directionsService, directionsDisplay);
      });
    }

    function calculateAndDisplayRoute(directionsService, directionsDisplay) {
      var selectedMode = document.getElementById('mode').value;
      directionsService.route({
        origin: {lat: 22.2780691, lng: 114.16490905},  // Haight.
        destination: {lat: 22.27851332, lng: 114.16481472},  // Ocean Beach.
        // Note that Javascript allows us to access the constant
        // using square brackets and a string value as its
        // "property."
        travelMode: google.maps.TravelMode[selectedMode]
      }, function(response, status) {
        if (status == 'OK') {
          directionsDisplay.setDirections(response);
        } else {
          window.alert('Directions request failed due to ' + status);
        }
      });
    }
        </script>

    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBR8YK6VN7gr_t3DH6ywMiC0sejSzb3Lyc&callback=initMap">
    </script>


    </body>
  </html>
