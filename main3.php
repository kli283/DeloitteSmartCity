<?php
	$searched = false;
if (!empty($_GET['id'])) {
	$searched = true;
    if (is_numeric($_GET['id'])) {

        $mysqli = new mysqli('localhost', 'root', 'TekChange2018', 'tech_city');

        if($mysqli->connect_errno) {
        	printf("Connection Failed: %s\n", $mysqli->connect_error);
        	exit;
        }

        $chekpoint_id = $_GET['id'];
        $id = $chekpoint_id;

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
      } else {
          $chekpoint_name = $_GET['id'];

          $mysqli = new mysqli('localhost', 'root', 'TekChange2018', 'tech_city');

          if($mysqli->connect_errno) {
            printf("Connection Failed: %s\n", $mysqli->connect_error);
            exit;
          }

          $stmt = $mysqli->prepare("SELECT name, id, latitude, longitude FROM chekpoint WHERE name REGEXP ?");
              if(!$stmt){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit();
              }
              $stmt->bind_param("s", $chekpoint_name);
              $stmt->execute();
              $stmt->bind_result($name, $id, $latitude, $longitude);

              $stmt->fetch();
              $stmt->close();
    }

    if (!empty($_GET['category'])){
            $category = $_GET['category'];
	    $stmt = $mysqli->prepare("SELECT name, latitude, longitude FROM locations WHERE category=?");
	    if (!$stmt) {
		printf("Query Prep failed: %s\n", $mysqli->error);
		exit();
	    }
	    $stmt->bind_param("s", $checkpoint_name);
	    $stmt->execute();
	    $stmt->bind_result($store_name, $store_lat, $store_long);
	    $stores = array();
	    $i = 0;
	    while ($stmt->fetch()) {
        $theta = $store_long - $longitude;
        $dist = sin(deg2rad($store_lat)) * sin(deg2rad($latitude)) +  cos(deg2rad($store_long)) * cos(deg2rad($longitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $km = $dist * 60 * 1.1515 * 1.609344;
        if ($km < 100) {

		$stores[$i]['name'] = $store_name;
		$stores[$i]['latitude'] = $store_lat;
		$stores[$i]['longitude'] = $store_long;
		$i++;
  }
	    }

        }
  }
    ?>


<!DOCTYPE html>
<html>
    <head>
        <title> <?php echo $name ?> </title>
        <style>
            /* Set the size of the div element that contains the map */
            #map
            {
                height: 400px;  /* The height is 400 pixels */
                width: 100%;  /* The width is the width of the web page */
            }
        </style>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    </head>

    <body>
        <div class="topnav">
          <img src="img/logo.png" class="logo">

          <form action="main3.php" method="get">
              Search: <input name="id" type="text" placeholder="Type in ID"> &nbsp
              <input class="button" type="submit">
          </form>

        </div>

        <?php
        if (!empty($name)) {
        ?>

        <h1>Name: <?php echo $name ?> </h1>
        <h1>ID: <?php echo $id?> </h1>
        <div id="map"></div>

        <script>
          var map;
          function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
              zoom: 18,
              //center: new google.maps.LatLng(22.2780691, 114.16490905),
              center: new google.maps.LatLng(<?php echo $latitude ?>, <?php echo $longitude ?>),
              mapTypeId: 'roadmap'
            });

/*            var features = [
              {
                position: new google.maps.LatLng(22.2776447, 114.1653936),
                title: 'Uluru (Ayers Rock)',
                contentInfo: "THIs IS SHIT"
              }, {
                position: new google.maps.LatLng(22.2783696, 114.16440903),
                contentInfo: "THIs IS SHIT"
              }, {
                position: new google.maps.LatLng(22.2781890, 114.16430901),
                contentInfo: "THIs IS SHIT"

              }, {
                position: new google.maps.LatLng(22.2784692, 114.16410900),
                contentInfo: "THIs IS SHIT"

              }, {
                position: new google.maps.LatLng(22.2782697, 114.16450917),
                contentInfo: "THIs IS SHIT"

              }
            ];
*/
            var features = [
              <?php
                  foreach($allStores as $store)
                  {
                    if($store['category'] == $category)
                    {
                      echo '{position: new google.maps.LatLng('.$store['latitude'].','.$store['longitude'].')}';
                    }

                  }
               ?>
            ];
            // Create markers.
            features.forEach(function(feature) {

            var infowindow = new google.maps.InfoWindow({
              content: feature.contentInfo
            });

              var marker = new google.maps.Marker({
                position: feature.position,
                //icon: icons[feature.type].icon,
                map: map
              });
              marker.addListener('click', function() {
              infowindow.open(map, marker);
              });
            });
          }
        </script>
        <div class="bottom">

            <form action="main3.php" method="get" id="categories">
                    Category: <select id='lists' onchange="updateValue(this.value)">
                      <option value="defaultSelect">- SELECT -</option>
                       <option value='1' id='one'>food</option>
                       <option value='2' id='two'>mtr</option>
                       <option value='3' id='three'>shopping</option>
                       <option value='4'id='four'>banks</option>
                    </select>

            <br></br>
            <b id="mode2">Mode of Travel: </b>

            <select id="mode">
              <option value="WALKING">Walking</option>
              <option value="DRIVING">Driving</option>
            </select>

                    <!--<button type="submit" name="id" value= <#?php echo $id?>>Submit</button><br> <br> -->

            <div class="listing">
                <ul>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                </ul>
            </div>
        </div>



        <?php
      } else {
        if ($searched == true) {
         ?>
         <p>Sorry! Your search did not come up with any results. Please try again! </p>

         <?php
       }
       }
       ?>

       <script type="text/javascript">

       function updateValue(value)
           {
            if(value == 1){
              document.getElementById("mode").style.visibility = "hidden";
              document.getElementById("mode2").style.visibility = "hidden";
                map = new google.maps.Map(document.getElementById('map'), {
                  zoom: 18,
                  center: new google.maps.LatLng(22.2780691, 114.16490905),
                  mapTypeId: 'roadmap'
                });

                var features = [
                  {
                    position: new google.maps.LatLng(22.2776447, 114.1653936),
                    title: 'Uluru (Ayers Rock)',
                    contentInfo: "THIs IS SHIT"
                  }, {
                    position: new google.maps.LatLng(22.2783696, 114.16440903),
                    contentInfo: "THIs IS SHIT"
                  }, {
                    position: new google.maps.LatLng(22.2781890, 114.16430901),
                    contentInfo: "THIs IS SHIT"

                  }, {
                    position: new google.maps.LatLng(22.2784692, 114.16410900),
                    contentInfo: "THIs IS SHIT"

                  }, {
                    position: new google.maps.LatLng(22.2782697, 114.16450917),
                    contentInfo: "THIs IS SHIT"

                  }
                ];

                // Create markers.
                features.forEach(function(feature) {

                var infowindow = new google.maps.InfoWindow({
                  content: feature.contentInfo
                });

                  var marker = new google.maps.Marker({
                    position: feature.position,
                    //icon: icons[feature.type].icon,
                    map: map
                  });
                  marker.addListener('click', function() {
                  infowindow.open(map, marker);
                  });
                });
            }else if(value == 2){
               document.getElementById("mode").style.visibility = "visible";
               document.getElementById("mode2").style.visibility = "visible";

                var directionsDisplay = new google.maps.DirectionsRenderer;
                var directionsService = new google.maps.DirectionsService;
                var map = new google.maps.Map(document.getElementById('map'), {
                  zoom: 14,
                  center: {lat: 37.77, lng: -122.447},
                  disableDefaultUI: true
                });
                directionsDisplay.setMap(map);

                calculateAndDisplayRoute(directionsService, directionsDisplay);
                document.getElementById('mode').addEventListener('change', function() {
                  calculateAndDisplayRoute(directionsService, directionsDisplay);
                });


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
            }else if(value == 3){
              document.getElementById("mode").style.visibility = "hidden";
              document.getElementById("mode2").style.visibility = "hidden";
              map = new google.maps.Map(document.getElementById('map'), {
                zoom: 18,
                center: new google.maps.LatLng(22.2780691, 114.16490905),
                mapTypeId: 'roadmap'
              });

              var features = [
                {
                  position: new google.maps.LatLng(22.2776447, 114.1653936),
                  title: 'Uluru (Ayers Rock)',
                  contentInfo: "THIs IS SHIT"
                }, {
                  position: new google.maps.LatLng(22.2783696, 114.16440903),
                  contentInfo: "THIs IS SHIT"
                }, {
                  position: new google.maps.LatLng(22.2781890, 114.16430901),
                  contentInfo: "THIs IS SHIT"

                }, {
                  position: new google.maps.LatLng(22.2784692, 114.16410900),
                  contentInfo: "THIs IS SHIT"

                }, {
                  position: new google.maps.LatLng(22.2782697, 114.16450917),
                  contentInfo: "THIs IS SHIT"

                }
              ];

              // Create markers.
              features.forEach(function(feature) {

              var infowindow = new google.maps.InfoWindow({
                content: feature.contentInfo
              });

                var marker = new google.maps.Marker({
                  position: feature.position,
                  //icon: icons[feature.type].icon,
                  map: map
                });
                marker.addListener('click', function() {
                infowindow.open(map, marker);
                });
              });

            }else if(value == 4){
              document.getElementById("mode").style.visibility = "hidden";
              document.getElementById("mode2").style.visibility = "hidden";
              map = new google.maps.Map(document.getElementById('map'), {
                zoom: 18,
                center: new google.maps.LatLng(22.2780691, 114.16490905),
                mapTypeId: 'roadmap'
              });

              var features = [
                {
                  position: new google.maps.LatLng(22.2776447, 114.1653936),
                  title: 'Uluru (Ayers Rock)',
                  contentInfo: "THIs IS SHIT"
                }, {
                  position: new google.maps.LatLng(22.2783696, 114.16440903),
                  contentInfo: "THIs IS SHIT"
                }, {
                  position: new google.maps.LatLng(22.2781890, 114.16430901),
                  contentInfo: "THIs IS SHIT"

                }, {
                  position: new google.maps.LatLng(22.2784692, 114.16410900),
                  contentInfo: "THIs IS SHIT"

                }, {
                  position: new google.maps.LatLng(22.2782697, 114.16450917),
                  contentInfo: "THIs IS SHIT"

                }
              ];

              // Create markers.
              features.forEach(function(feature) {

              var infowindow = new google.maps.InfoWindow({
                content: feature.contentInfo
              });

                var marker = new google.maps.Marker({
                  position: feature.position,
                  //icon: icons[feature.type].icon,
                  map: map
                });
                marker.addListener('click', function() {
                infowindow.open(map, marker);
                });
              });

            }else {
              document.getElementById("mode").style.visibility = "hidden";
              document.getElementById("mode2").style.visibility = "hidden";

          }
        }
          //updateValue(document.getElementById('lists').value)
           // set a defalt value
       </script>

       <script async defer
       src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCMmxbZxJlswe6IVpF5TsMMGp4nTo8_7W4&callback=initMap">
       </script>

  </body>
</html>
