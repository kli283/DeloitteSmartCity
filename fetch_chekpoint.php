<?php

if (empty($_POST['id'])) {
  die("No ID specified");
}

$mysqli = new mysqli('localhost', 'root', 'TekChange2018', 'chekpoint');

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}

$chekpoint_id = $_POST['id'];

$stmt = $mysqli->prepare("SELECT name, latitude, longitude FROM checkpoint WHERE id=?");
    if(!$stmt){
      printf("Query Prep Failed: %s\n", $mysqli->error);
      exit();
    }

    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->bind_result($name, $latitude, $longitude);
    $stmt->fetch();
    $stmt->close();

    $result = [
      "name" => $name,
      "latitude" => $latitude
      "longitude" => $longitude
    ];

    echo($result);
    exit(); 
?>
