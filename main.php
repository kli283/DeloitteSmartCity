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
                $theta = $store_long - $longitude;
                $dist = sin(deg2rad($store_lat)) * sin(deg2rad($latitude)) +  cos(deg2rad($store_long)) * cos(deg2rad($longitude)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $km = $dist * 60 * 1.1515 * 1.609344;
                if ($km < 10000)
                {
                    $allStores[$i]['name'] = $store_name;
                    $allStores[$i]['latitude'] = $store_lat;
                    $allStores[$i]['longitude'] = $store_long;
                    $allStores[$i]['category'] = $store_category;
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
              <input class="modern_button " type="submit">
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
            <button class="modern_button"><?php echo '<a href="http://maps.google.com/maps?q=' .$latitude. ',' .$longitude. '"> </a>' ?> Show in Map</button>

            <form action="main.php" method="get" id="categories">
                    <input type="hidden" />
                    Category: <select id="categories" name='category' >
                        <option selected disabled>Choose here</option>
                        <option value="food">Food</option>
                        <option value="shopping">Shopping</option>
                        <option value="bank">Banks</option>
                        <option value="mtr">MTR</option>
                    </select>
                    <button type="submit" name="id" value=<?php echo $id?>>Submit</button><br> <br>
              </form>

            <div class="listing">
                <ul>
                    <?php
                        if (!empty($_GET['category']))
                        {
                           foreach($allStores as $store)
                           {
                                if($store['category'] == $category)
                                    echo '<li>' .$store['name']. '</li>';
                           }
                        }
                    ?>
                </ul>
            </div>
        </div>

        <script>
            function initMap()
            {
                var map = new google.maps.Map(document.getElementById('map'),
                {
                  zoom: 16,
//                  center: {lat: -33, lng: 151},
                    center: new google.maps.LatLng(<?php echo $latitude ?>, <?php echo $longitude ?>),
                  disableDefaultUI: true,
                    mapTypeId: 'roadmap'
                });

                var features = [];
                <?php
                    if (!empty($_GET['category']))
                    {
                       foreach($allStores as $store)
                       {
                            if($store['category'] == $category)
                                echo 'features.append({position: new google.maps.LatLng(' .$store['latitude'].',' .$store['longitude'].')})';
                       }
                    }
                ?>

                // Create markers.
                features.forEach(function(feature)
                {
                    var marker = new google.maps.Marker({
                        position: feature.position,
                        //icon: icons[feature.type].icon,
                        map: map
                      });
                });
/*
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
*/
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
