<?php

global $conn;

$query = "select    poleID as poleID,
                    pole_name as poleName,
                    area as area,
                    Municipality as municipality,
                    controller as controller,
                    CONCAT(streetName, ' ', COALESCE(streetNumber, '')) AS address,
                    lat,
                    lng,
                    CONCAT('http://',cameraIP, ':', port, '/stream') as cameraIP,
                    CONCAT('http://',cameraIP) AS cameraOptions
                from Pole where isVisible = 1 order by area;";


$result = mysqli_query($conn, $query);

$data = array();

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "poleID"=> $row[ 'poleID' ],
        "poleName" => $row[ 'poleName' ],
        "area" => $row[ 'area' ],
        "municipality" => $row[ 'municipality' ],
        "controller" => $row[ 'controller' ],
        "address" => $row[ 'address' ],
        "lat" => $row[ 'lat' ],
        "lng" => $row[ 'lng' ],
        "cameraIP" => $row['cameraIP'],
        "cameraOptions" => $row['cameraOptions']
    ];
}

return $data;