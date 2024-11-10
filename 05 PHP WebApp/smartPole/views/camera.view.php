<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
include 'partials/head.php';
$data = require_once 'providers/poleDetails.php';
?>

<body class="h-[100vh] flex flex-col">
<!-- Navigation bar -->
<?php
include 'partials/nav.php';
?>
<!-- End of Navigation bar -->
<!-- Header -->

<header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Προβολή και Διαχείριση Live Video από Κάμερες</h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main class="h-full">
    <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8 flex gap-5">
        <!-- Left side div for checkbox selection -->
        <div>
            <div class="flex flex-col p-5 border border-gray-400 rounded-2xl shadow-xl bg-white gap-4">
                <h2 class="text-lg font-semibold mb-4">Επιλογή Έξυπνης Κολώνας</h2>
                <?php foreach ($data as $camera) : ?>
                    <div>
                        <input type="checkbox" id="camera_<?php echo $camera['poleID']; ?>" value="<?php echo $camera['poleID']; ?>" class="camera-checkbox">
                        <label for="camera_<?php echo $camera['poleID']; ?>"><?php echo $camera['area'] . " - " . $camera['address']; ?></label>
                    </div>
                <?php endforeach; ?>
                <br><button id="submitBtn" class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg w-full"type="submit" value="Submit"> Υποβολή </button>
            </div>
        </div>

        <!-- Right side div for displaying selected elements in a grid layout -->
        <div class="flex flex-col items-center w-full gap-4" id="selectedCamerasGrid">
                <!-- Selected cameras will be displayed here -->
            </div>
        </div>
    </main>

    <script>
        document.getElementById('submitBtn').addEventListener('click', function() {
            // Clear previous entries in the grid
            document.getElementById('selectedCamerasGrid').innerHTML = '<h2 class="bg-white shadow-lg border border-gray-400 rounded-2xl w-full text-2xl py-5 text-center font-semibold">Προβολή Επιλογών</h2><div class="flex gap-4 flex-wrap w-full justify-center" id="camera-grid"></div>';

            // Get all checked checkboxes
            var checkboxes = document.getElementsByClassName('camera-checkbox');
            var count = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    // Get the corresponding camera data
                    var cameraId = checkboxes[i].value;
                    var camera = <?php echo json_encode($data); ?>.find(function(camera) {
                        return camera.poleID == cameraId;
                    });

                    // Create a div for each selected camera
                    var newDiv = document.createElement('div');
                    newDiv.className = 'flex flex-col p-5 border border-gray-400 rounded-2xl shadow-xl bg-white gap-4';
                    newDiv.innerHTML = `
                    <label class="text-lg font-semibold text-center">${camera.area} - ${camera.address}</label>
                    <img src="${camera.cameraIP}" id="$camera.cameraId">
                    <!-- TODO the button functionality is for superAdmin -->
                    <button type="button" formtarget="_blank" class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg w-full" onclick="location.href='${camera.cameraOptions}'">Camera Options</button>
                `;
                    document.getElementById('camera-grid').appendChild(newDiv);
                    count++;
                }
            }
        });
    </script>
    </div>
    <?php
    if (!isAuthenticated()) {
        ?>
        <div class="mx-auto max-w-7xl py-2 sm:px-6 lg:px-8 flex justify-center space-x-8">
            <a href="login.php" >
                <button class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded">Είσοδος</button>
            </a>
            <a href="register.php" >
                <button class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded">Εγγραφή</button>
            </a>
        </div>
        <?php
    }
    ?>
<!-- End of Main -->
<!-- Footer -->
<?php include 'partials/footer.php';?>
<!-- End of footer -->
</body>
