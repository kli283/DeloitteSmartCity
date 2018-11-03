<?php
	$searched = false;
  $category = "";
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

    if (!empty($_GET['category']))
    {
        $category = $_GET['category'];
    }
    $stmt = $mysqli->prepare("SELECT name, latitude, longitude, category FROM locations WHERE id=?");
    if (!$stmt)
    {
      printf("Query Prep failed: %s\n", $mysqli->error);
      exit();
    }

    $stmt->bind_param("s", $chekpoint_id);
    $stmt->execute();
    $stmt->store_result();
    $stores = array();
    $allStores = array();
    $i = 0;
        if ($stmt->num_rows > 0)
        {
            $stmt->bind_result($store_name, $store_lat, $store_long, $store_category);
            while ($stmt->fetch())
            {
              $earthRadius = 6371000;
              $latFrom = deg2rad($store_lat);
              $lonFrom = deg2rad($store_long);
              $latTo = deg2rad($latitude);
              $lonTo = deg2rad($longitude);

              $latDelta = $latTo - $latFrom;
              $lonDelta = $lonTo - $lonFrom;

              $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

              $km = ($angle * $earthRadius)/1000;

                if ($km < 100000)
                {
                    $allStores[$i]['name'] = $store_name;
                    $allStores[$i]['latitude'] = $store_lat;
                    $allStores[$i]['longitude'] = $store_long;
                    $allStores[$i]['category'] = $store_category;
		    $allStores[$i]['km'] = $km;
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
          <img src="img/logo.png" class="logo"> <br></br>

          <form action="main.php" method="get">
             <input name="id" type="text" placeholder="Enter an ID or Name" class="text_input"> &nbsp
              <input class="modern_button " type="submit" value="Search">
          </form>

        </div>

        <?php
        if (!empty($name)) {
        ?>
        <h1 class="text_label">Name: <?php echo $name ?> </h1>
        <h1 class="text_label">ID: <?php echo $id?> </h1>
        <div id="map"></div>

        <br></br>
        <div class="bottom">
          <button class="modern_button" id="show_map_button"><?php echo '<a href="http://maps.google.com/maps?q=' .$latitude. ',' .$longitude. '"> </a>' ?> Show in Map</button>

<br></br>
	<h2 class="text_label">Nearby</h2>
            <form action="main.php" method="get" id="categories">
                    <input type="hidden" />
                    <select class="selector" id="categories" name='category' >
                        <option selected disabled>Choose Category</option >
                        <option value="food">Food</option>
                        <option value="shopping">Shopping</option>
                        <option value="bank">Banks</option>
                        <option value="mtr">MTR</option>
                    </select>
                    <button class="modern_button" type="submit" name="id" id="submit_category_button" value=<?php echo $id?>>Search by Category</button><br> <br>
              </form>

            <div class="listing">
                <ul>
                    <?php
                        if (!empty($_GET['category']))
                        {
                           foreach($allStores as $store)
                           {
                                if($store['category'] == $category)
                                    echo '<li>' .$store['name']. ': ' . round($store['km'], 2) . ' km'. '</li>';
                           }
                        }
                    ?>
                </ul>
            </div>
        </div>

        <script>
            var stores_arr = <?php echo json_encode($allStores);?>;
            var category = <?php echo json_encode($category); ?>;

            function initMap()
            {
                var map = new google.maps.Map(document.getElementById('map'),
                {
                  zoom: 16,
                  center: new google.maps.LatLng(<?php echo $latitude;  ?>, <?php echo $longitude; ?>),
                  disableDefaultUI: true,
                    mapTypeId: 'roadmap'
                });

                var features = [];

                var own_data = {
                  "position": new google.maps.LatLng(<?php echo $latitude;  ?>, <?php echo $longitude; ?>),
                  "contentInfo": "Your Location",
                };

                features.push(own_data);

                var i = 0;
                for (i = 0; i < stores_arr.length; i++) {
                    var store = stores_arr[i];
                    var store_data = {
                      "position": new google.maps.LatLng(store.latitude, store.longitude),
                      "contentInfo":store.name
                    };

                    if (category != "" && category == store.category) {
                      features.push(store_data);
                    }
                }

                // Create markers.
                features.forEach(function(feature) {

                var infowindow = new google.maps.InfoWindow({
                  content: feature.contentInfo
                });
                  var marker = null;

                  if (feature.contentInfo == "Your Location") {
                    var marker = new google.maps.Marker({
                      position: feature.position,
                      //icon: icons[feature.type].icon,
                      map: map,
                      label: "O"
                    });
                  } else {
                    var marker = new google.maps.Marker({
                      position: feature.position,
                      //icon: icons[feature.type].icon,
                      map: map

                    });
                  }


                  marker.addListener('click', function() {
                  infowindow.open(map, marker);
                  });
                });

            }

        </script>
        <script async defer
				src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCMmxbZxJlswe6IVpF5TsMMGp4nTo8_7W4&callback=initMap">
        </script>

        <?php
      } else {
        if ($searched == true) {
         ?>
         <p>Sorry! Your search did not come up with any results. Please try again! </p>

         <?php
       }
       }
       ?>

  </body>
</html>
