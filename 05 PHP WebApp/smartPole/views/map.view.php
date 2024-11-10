<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
$mapData = require_once 'providers/map.php';
include 'partials/head.php'
?>

<script>
    google.charts.load('current', {
        'packages': ['map'],
        'mapsApiKey': '<?= $mapData['key'] ?>'
    });
    google.charts.setOnLoadCallback(drawMap);
    function drawMap() {

        var mapData = <?= $mapData['mapData'] ?>;
        var data = google.visualization.arrayToDataTable(mapData);
        var options = {
            mapType: 'styledMap',
            zoomLevel: 14,
            showTooltip: true,
            showInfoWindow: true,
            useMapTypeControl: true,
            maps: {
                styledMap: {
                    name: 'Styled Map',
                    styles: [
                        {featureType: 'poi.attraction',
                            stylers: [{color: '#fce8b2'}]
                        },
                        {featureType: 'road.highway',
                            stylers: [{hue: '#0277bd'}, {saturation: -50}]
                        },
                        {featureType: 'road.highway',
                            elementType: 'labels.icon',
                            stylers: [{hue: '#000'}, {saturation: 100}, {lightness: 50}]
                        },
                        {featureType: 'landscape',
                            stylers: [{hue: '#259b24'}, {saturation: 10}, {lightness: -22}]
                        }
                    ]}}
        };

        var map = new google.visualization.Map(document.getElementById('map_div'));

        google.visualization.events.addListener(map, 'select', function() {
            var selection = map.getSelection();
            if (selection.length > 0) {
                var item = selection[0];
                if (item.row != null) {
                    // Get the ID of the selected row from the data table
                    var poleID = data.getValue(item.row, 3); // Assuming the first column contains the ID
                    // Redirect to poledetails.php with the selected ID
                    window.location.href = 'poledetails.php?id=' + poleID;
                }
            }
        });

        map.draw(data, options);
    };
</script>

<body class="min-h-[100vh] h-full flex flex-col">
<!-- Navigation bar -->
<?php
include 'partials/nav.php';
?>
<!-- End of Navigation bar -->
<!-- Header -->

<header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Χάρτης δικτύου «Έξυπνων Κολώνων» Φωτισμού</h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main class="h-full flex justify-center items-center">
    <div id="map_div" class="w-3/4" style="height: 75%;"></div>
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
</html>
