<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
include 'partials/head.php';

$poleID = isset($_GET['id']) ? $_GET['id'] : null;

$data = require_once 'providers/poleSensorDetails.php';
?>

<body class="min-h-[100vh] h-full flex flex-col">
<!-- Navigation bar -->
<?php
include 'partials/nav.php';
?>
<!-- End of Navigation bar -->
<!-- Header -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to update table data
    function updateTableData(colName) {
        // Make AJAX call to retrieve updated data

        $.ajax({
            url: 'providers/updateSensorValues.php?id=<?= $poleID ?>&colName=' + colName,
            type: 'POST', // You can use GET or POST depending on your preference
            dataType: 'json', // Specify that the response will be JSON
            success: function(response) {
                $('#table_' + colName).text(response);

                var currentClass = $('#led_' + colName).className;

                // Update LED indicator color based on the response value
                if (response == 0) {
                    $('#led_' + colName).removeClass().addClass("green led");
                } else if (response == 1) {
                    $('#led_' + colName).removeClass().addClass("red led");
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }


    // Call the updateTableData function for each alarm and sensor data every second
    $(document).ready(function() {
        <?php

        // Update alarm data every second
        foreach ($data['dataAlarm'] as $alarm) {
            ?>
        setInterval(function() {
            updateTableData("<?= $alarm['colName'] ?>");
        }, 1000);
            <?php
        }
        ?>

        <?php

        // Update sensor data every 1200000 milliseconds (20 minutes)
        foreach ($data['dataSensor'] as $sensor) {
            ?>
        setInterval(function() {
            updateTableData("<?= $sensor['colName'] ?>");
        }, 1200000);
            <?php
        }
        ?>
    });
</script>

<header class="bg-white shadow-lg mx-10 border border-gray-400 rounded-3xl mt-10">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Κολώνα στην οδό <?= $data['poleDetails']['address']?> </h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main class="h-full p-10">
    <div class="grid grid-rows-2 grid-cols-3 grid-flow-col gap-4">
        <div class="col-span-1 row-span-2 self-center">
            <div class="flex flex-col w-full p-5 border border-gray-400 rounded-2xl shadow-xl bg-white gap-4">
                <h2 class="text-2xl font-semibold">Στοιχεία Έξυπνης Κολώνας</h2>
                <p class="text-sm">Στην συγκεκριμένη σελίδα εμφανίζονται λεπτομέρειες για την κολώνα καθώς και συνεχής Live ενημέρωση από τους
                    αισθητήρες και τα alarm που έχει συνδεμένα.</p>
                <div class="w-full h-full flex flex-col gap-2 divide-y">
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">ID Κολώνας</p>
                        <p class="w-1/2"><?= $data['poleDetails']['poleID']; ?></p>
                    </div>
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">Όνομα Κολώνας</p>
                        <p class="w-1/2"><?= $data['poleDetails']['poleName']; ?></p>
                    </div>
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">Δήμος</p>
                        <p class="w-1/2"><?= $data['poleDetails']['municipality']; ?></p>
                    </div>
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">Περιοχή</p>
                        <p class="w-1/2"><?= $data['poleDetails']['area']; ?></p>
                    </div>
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">Διεύθυνση</p>
                        <p class="w-1/2"><?= $data['poleDetails']['address']; ?></p>
                    </div>
                    <div class="w-full py-2 flex items-center">
                        <p class="w-1/2 font-semibold">Συντεταγμένες Τοποθεσίας</p>
                        <p class="w-1/2"><?= $data['poleDetails']['lat'] . " - " . $data['poleDetails']['lng']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-2 row-span-2 flex flex-col gap-4">
            <div class="row-span-1">
                <div class="flex flex-col w-full p-5 border border-gray-400 rounded-2xl shadow-xl bg-white gap-4">
                    <p class="text-center text-2xl font-semibold">Live Feed</p>
                    <div>
                        <img class="mx-auto w-1/2" src="<?= $data['poleDetails']['cameraIP'] ?>">
                    </div>
                </div>
            </div>
            <div class="row-span-2">
                <div class="flex flex-col w-full p-5 border border-gray-400 rounded-2xl shadow-xl bg-white gap-4">
                    <h2 class="text-2xl font-semibold">Δεδομένα</h2>
                    <div class="w-full h-full flex flex-col gap-2 divide-y">
                        <?php foreach ($data['dataAlarm'] as $alarm) : ?>
                            <?php if ($alarm['poleID'] == $poleID) : ?>
                                <div class="w-full flex items-center">
                                    <p class="w-1/2 font-semibold"><?= $alarm['DisplayNameGr']; ?></p>
                                    <div class="green led" id="led_<?= $alarm['colName'] ?>"?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php foreach ($data['dataSensor'] as $sensor) : ?>
                            <?php if ($sensor['poleID'] == $poleID) : ?>
                                <div class="w-full py-2 flex items-center">
                                    <p class="w-1/2 font-semibold"><?= $sensor['DisplayNameGr']; ?></p>
                                    <p class="w-1/2" id="table_<?= $sensor['colName'] ?>"><?= $sensor['value']; ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- <div>
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <tbody>
                        </tr>
                        <?php foreach ($data['dataAlarm'] as $alarm) : ?>
                            <?php if ($alarm['poleID'] == $poleID) : ?>
                                <tr class="bg-white border-b dark:border-gray-700">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <?= $alarm['DisplayNameGr']; ?>
                                    </th>
                                    <td class="px-6 py-4">
                                        <div class="green led" id="led_<?= $alarm['colName'] ?>"?></div>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <tbody>
                        </tr>
                        <?php foreach ($data['dataSensor'] as $sensor) : ?>
                            <?php if ($sensor['poleID'] == $poleID) : ?>
                                <tr class="bg-white border-b dark:border-gray-700">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <?= $sensor['DisplayNameGr']; ?>
                                    </th>
                                    <td class="px-6 py-3"><?= $sensor['DisplayNameGr']; ?></td>
                                    <td class="px-6 py-3" id="table_<?= $sensor['colName'] ?>"><?= $sensor['value']; ?></td>
                                </>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div> -->
            </div>
        </div>
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
</main>
<!-- End of Main -->
<!-- Footer -->
<?php include 'partials/footer.php';?>
<!-- End of footer -->

</body>
</div>
