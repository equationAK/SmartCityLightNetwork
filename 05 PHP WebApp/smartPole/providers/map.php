<?php

$mapsApiKey = require_once 'providers/api.env';
global $conn;
$query = "SELECT lat, lng, CONCAT(streetName, ' ', COALESCE(streetNumber, '')) AS address, poleID  FROM Pole where isVisible = 1";
$result = mysqli_query($conn, $query);

// Store the fetched data in an array
$data = array();
$data[] = ['Lat', 'Long', 'Name', 'poleID'];
while ($row = mysqli_fetch_assoc($result)) {
    // Convert lat and lng values to floats
    $lat = floatval($row['lat']);
    $lng = floatval($row['lng']);
    // Add converted values to $data array
    $data[] = array($lat, $lng, $row['address'], $row['poleID']);
}
// Encode the data as JSON
$json_data = json_encode($data);

$maps = [
    "key" => $mapsApiKey,
    "mapData" => $json_data
];

//dd($json_data);
return $maps;
