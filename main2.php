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


        $category = $_GET['category'];
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
    </head>

    <body>
        <div class="topnav">
            <form action="main2.php" method="get">
                <input type="text" placeholder="Type in ID">
                <input type="submit"><i class="fa fa-search"></i>
            </form>

        </div>
        <h1>Name: <?php echo $name ?> </h1>
        <h1>ID: <?php echo $chekpoint_id?> </h1>
        <div id="map"></div>
        <div class="bottom">
            <button><?php echo '<a href="http://maps.google.com/maps?q=' .$latitude. ',' .$longitude. '">' ?> Show in Map</button>
              <form action="#" method="post">
                  <select id="categories">
                      <option value="defaultSelect">- SELECT -</option>
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
                        } else {
                          foreach($allStores as $store)
                          {
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
                  zoom: 4,
                  center: {lat: -33, lng: 151},
                  disableDefaultUI: true
                });
            }
        </script>
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCMmxbZxJlswe6IVpF5TsMMGp4nTo8_7W4&callback=initMap">
        </script>
  </body>
</html>
