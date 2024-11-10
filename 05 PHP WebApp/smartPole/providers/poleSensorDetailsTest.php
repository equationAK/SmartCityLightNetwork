<?php

global $conn;


$queryAlarm = "select   p.poleID as poleID,
                        p.pole_name as poleName,
                        s.sensorName as label,
                        p.area as area,
                        s.isAlarm as alarm
                from PoleSensors ps
                inner join Pole p on p.poleID = ps.poleID
                inner join Sensor s on s.sensorID = ps.sensorID
                where s.isAlarm = 1";

$querySensor = "select   p.poleID as poleID,
                        p.pole_name as poleName,
                        s.sensorName as label,
                        p.area as area,
                        s.isAlarm as alarm
                from PoleSensors ps
                inner join Pole p on p.poleID = ps.poleID
                inner join Sensor s on s.sensorID = ps.sensorID
                where s.isAlarm = 0";

$resultAlarm = mysqli_query($conn, $queryAlarm);
$resultSensor = mysqli_query($conn, $querySensor);

// Store the fetched data in an array
$dataAlarm = array();
$dataSensor = array();

while ($row = mysqli_fetch_assoc($resultAlarm)) {
    $dataAlarm[] = $row;
}

while ($row = mysqli_fetch_assoc($resultSensor)) {
    $dataSensor[] = $row;
}

$data = [
    "dataAlarm" => $dataAlarm,
    "dataSensor" => $dataSensor
];


return $data;