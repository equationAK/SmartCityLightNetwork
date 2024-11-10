<?php

require '../functions/functions.php';
require '../db_config.php';

$poleID = isset($_GET['id']) ? $_GET['id'] : null;
$label = isset( $_GET['colName']) ? $_GET['colName'] : null;



global $conn;

$queryLatest = "SELECT {$label}
                FROM sensor_values
                WHERE {$label} IS NOT NULL AND poleID = {$poleID}
                ORDER BY timestamp DESC
                LIMIT 1";

$resultLatest = mysqli_query($conn, $queryLatest);

$value = 0;

$row = mysqli_fetch_row($resultLatest);
if ($row != null)
    $value = $row[0];

echo json_encode($value);