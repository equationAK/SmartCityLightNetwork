<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
include 'partials/head.php';
$poleID = isset($_GET['id']) ? $_GET['id'] : null;
// TODO the details for the page
//$poleDetails = "";
$data = require_once 'providers/poleSensorDetailsTest.php';
// Initialize an empty array for gauges
$gaugesAlarm = array();
$gaugesSensor = array();

// Add header for gauges
$gaugesAlarm[] = ['Label', 'Value'];
$gaugesSensor[] = ['Label', 'Value'];

// Loop through the data to build the gauges array
foreach ($data['dataAlarm'] as $gauge) {
    if ($poleID == $gauge['poleID']) {
        // TODO dynamic values for the sensors
        $gaugesAlarm[] = [$gauge['label'], 0];
    }
}

// Loop through the data to build the gauges array
foreach ($data['dataSensor'] as $gauge) {
    if ($poleID == $gauge['poleID']) {
        // TODO dynamic values for the sensors
        //dd($gauge['value']);
        $gaugesSensor[] = [$gauge['label'], 40];
    }
}

// Convert $gauges array to JSON format
$gaugesAlarmJson = json_encode($gaugesAlarm);
$gaugesSensorJson = json_encode($gaugesSensor);

?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

    google.charts.load('current', {'packages':['gauge']});
    google.charts.setOnLoadCallback(drawChart);


    function drawChartTest() {

        var data = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['test', 80],
        ]);

        var options = {
            width: 400, height: 120,
            redFrom: 90, redTo: 100,
            yellowFrom:75, yellowTo: 90,
            minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('test'));

        chart.draw(data, options);

        setInterval(function() {
            data.setValue(0, 1, 40 + Math.round(60 * Math.random()));
            chart.draw(data, options);
        }, 13000);
    }

    function drawChart() {

        var gaugesAlarm = <?= $gaugesAlarmJson ?>;
        var gaugesSensor = <?= $gaugesSensorJson ?>;

        var dataAlarm = google.visualization.arrayToDataTable(gaugesAlarm);
        var dataSensor = google.visualization.arrayToDataTable(gaugesSensor);

        var optionsAlarm = {
            width: 400, height: 120,
            redFrom: 75, redTo: 100,
            yellowFrom: 50, yellowTo: 74,
            minorTicks: 5
        };

        var optionsSensor = {
            width: 800, height: 120,
            redFrom: 90, redTo: 100,
            yellowFrom:75, yellowTo: 90,
            minorTicks: 5
        };

        var chartAlarm = new google.visualization.Gauge(document.getElementById('chart_Alarms'));
        var chartSensor = new google.visualization.Gauge(document.getElementById('chart_Sensors'));

        chartAlarm.draw(dataAlarm, optionsAlarm);
        chartSensor.draw(dataSensor, optionsSensor);

        setInterval(function() {
            // Generate a random number between 0 and 1
            var random = Math.random();
            // Set the value based on the random number
            var value = random < 0.5 ? 0 : 100;
            // Set the value in the data table
            data.setValue(0, 1, value);
            // Draw the chart with the updated data
            chart.draw(data, options);
        }, 5000);
        setInterval(function() {
            // Generate a random number between 0 and 1
            var random = Math.random();
            // Set the value based on the random number
            var value = random < 0.5 ? 0 : 100;
            // Set the value in the data table
            chartAlarm.setValue(0, 1, value);
            // Draw the chart with the updated data
            chartAlarm.draw(data, options);
        }, 5000);
        setInterval(function() {
            // Generate a random number between 0 and 1
            var random = Math.random();
            // Set the value based on the random number
            var value = random < 0.5 ? 0 : 100;
            // Set the value in the data table
            chartAlarm.setValue(0, 1, value);
            // Draw the chart with the updated data
            chartAlarm.draw(data, options);
        }, 5000);

        setInterval(function() {
            chartSensor.setValue(0, 1, 40 + Math.round(60 * Math.random()));
            chartSensor.draw(data, options);
        }, 5000);
        setInterval(function() {
            chartSensor.setValue(1, 1, 40 + Math.round(60 * Math.random()));
            chartSensor.draw(data, options);
        }, 5000);
        setInterval(function() {
            chartSensor.setValue(2, 1, Math.random() < 0.5 ? 0 : 100);
            chartSensor.draw(data, options);
        }, 5000);
    }
</script>




<body class="h-full min-h-full">
<!-- Navigation bar -->
<?php
include 'partials/nav.php';
?>
<!-- End of Navigation bar -->
<!-- Header -->

<header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Τίτλος - <?= $poleID . "#" . $heading?> </h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main>
    <main>
        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <h2 class="text-lg font-semibold mb-4">Gauges Alarm</h2>
            <div id="chart_Alarms" style="width: 400px; height: 120px;"></div>

            <h2 class="text-lg font-semibold mb-4 mt-8">Gauges Sensor</h2>
            <div id="chart_Sensors" style="width: 800px; height: 120px;"></div>

            <h2 class="text-lg font-semibold mb-4 mt-8">Test Sensor</h2>
            <div id="test" style="width: 800px; height: 120px;"></div>
        </div>
    </main>
    <?php
    if (!isAuthenticated()) {
        ?>
        <div class="mx-auto max-w-7xl py-2 sm:px-6 lg:px-8 flex justify-center space-x-8">
            <a href="login.php" >
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Είσοδος</button>
            </a>
            <a href="register.php" >
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Εγγραφή</button>
            </a>
        </div>
        <?php
    }
    ?>
</main>
<!-- End of Main -->
<!-- Footer -->
<?php include 'partials/footer.php';?>
<!-- End of footer -->

</body>
</div>
