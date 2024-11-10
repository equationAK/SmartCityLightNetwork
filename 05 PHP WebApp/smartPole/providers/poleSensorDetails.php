<?php

$currentPole = [];

$pole = require_once 'providers/poleDetails.php';

foreach ($pole as $item) {
    if ($item[ 'poleID' ] == $poleID) {
        $currentPole = $item;
        break;
    }
}

global $conn;


$queryAlarm = "select   p.poleID as poleID,
                        p.pole_name as poleName,
                        s.sensorName as label,
                        s.Abbr as colName,
                        s.DisplayNameGr as DisplayNameGr,
                        s.UoMSymbol as UoM,
                        p.area as area,
                        s.isAlarm as alarm,
                        s.thingSpeak as iot
                from PoleSensors ps
                inner join Pole p on p.poleID = ps.poleID
                inner join Sensor s on s.sensorID = ps.sensorID
                where s.isAlarm = 1 and p.poleID = {$poleID};";

$querySensor = "select   p.poleID as poleID,
                        p.pole_name as poleName,
                        s.sensorName as label,
                        s.Abbr as colName,
                        s.DisplayNameGr as DisplayNameGr,
                        s.UoMSymbol as UoM,
                        p.area as area,
                        s.isAlarm as alarm,
                        s.rangeTo as rangeTo
                from PoleSensors ps
                inner join Pole p on p.poleID = ps.poleID
                inner join Sensor s on s.sensorID = ps.sensorID
                where s.isAlarm = 0 and p.poleID = {$poleID}";

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
    "poleDetails" => $currentPole,
    "dataAlarm" => $dataAlarm,
    "dataSensor" => $dataSensor
];

//dd($data["dataAlarm"]);

foreach ($data["dataAlarm"] as $key => $value){
        $label = $value['colName'];
        $poleId = $value['poleID'];
        $queryLatest = "SELECT {$label}
                        FROM sensor_values
                        WHERE {$label} IS NOT NULL AND
                              poleID = {$poleId}
                        ORDER BY timestamp DESC
                        LIMIT 1";
        $resultLatest = mysqli_query($conn, $queryLatest);
        $row = mysqli_fetch_row($resultLatest);
        if ($row == null){
            $insertValue = 0;
        } else {
            $insertValue = $row[0];
        }
        $data["dataAlarm"][$key]["value"] =  $insertValue;
}

foreach ($data["dataSensor"] as $key => $value){
    $label = $value['colName'];
    $poleId = $value['poleID'];
    $queryLatest = "SELECT {$label}
                    FROM sensor_values
                    WHERE {$label} IS NOT NULL AND
                           poleID = {$poleId}
                    ORDER BY timestamp DESC
                    LIMIT 1";
    //dd($queryLatest);
    $resultLatest = mysqli_query($conn, $queryLatest);
    $row = mysqli_fetch_row($resultLatest);
    if ($row == null){
        $insertValue = 0;
    } else {
        $insertValue = $row[0];
    }
    $data["dataSensor"][$key]["value"] = $insertValue;
}



return $data;