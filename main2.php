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
            <button>Show in Map</button>
            <form action="#" method="post">
                <select id="categories">
                    <option value="defaultSelect">- SELECT -</option>
                    <option value="food">Food</option>
                    <option value="shopping">Shopping</option>
                    <option value="bank">Banks</option>
                    <option value="mtr">MTR</option>
                </select>
            </form>
                        
            <div class="listing">
                <ul>
                    <?php
                        foreach($_POST['categories'] as $select)
                        {
                            echo '<li>' .$select->name. '</li>';
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
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC67ZO8RUoSPyKlHm3gF7iAbPKdE00C5sM&callback=initMap">
        </script>
  </body>
</html>
