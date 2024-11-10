<?php

global $conn;
require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
include 'partials/head.php';

// Navigation bar
// include 'partials/nav.php';
$data = require_once 'providers/poleDetails.php';

?>

<script>var customAlert = new CustomAlert();</script>

<?php
// TODO create a file for this
if (isset($_POST['selection']) && isset($_POST['poleID'])) {// Αν γίνει αίτημα
    $selected_measurements = $_POST['selection']; // Αποθήκευση πίνακα επιλογής μετρήσεων που θα προβληθούν
    $poleIDs = [$_POST['poleID']];
    $dateFrom = mysqli_real_escape_string($conn, $_POST['dateFrom']); // Αποθήκευση ημερομηνίας αρχής
    $dateEnd = mysqli_real_escape_string($conn, $_POST['dateEnd']); // Αποθήκευση ημερομηνίας τέλους
}
else {// Αν δεν υπάρξει αίτημα τότε αρχικοποίηση στις παρακάτω τιμές
    $selected_measurements = ["temperature", "humidity"];
    $dateFrom = mysqli_real_escape_string($conn, date("Y-01-01"));
    $dateEnd = mysqli_real_escape_string($conn, date("Y-m-d"));
    $poleIDs = [4];
} // Ανάκτηση δεδομένων από την βάση, του διαστήματος και των πεδίων που έχουν επιλεγεί
$sql_query = "SELECT DATE(`timestamp`) as Date, poleID, ";
foreach ($selected_measurements as $measurement) {
    $sql_query .= "AVG($measurement) as $measurement, ";
}
$poleID = "(";
foreach ($poleIDs as $item) {
    $poleID .= $item . ",";
}
$poleID = rtrim($poleID, ", ");
$poleID .= ")";
$where = " WHERE ";
foreach ($selected_measurements as $measurement) {
    $where .= "$measurement IS NOT NULL AND ";
}
$sql_query = rtrim($sql_query, ", ");
$sql_query .= " FROM sensor_values ";
$sql_query .= $where;
$sql_query .= "(timestamp >= '$dateFrom' AND timestamp <= '$dateEnd') and poleID in ";
$sql_query .= $poleID . " GROUP BY DATE(`timestamp`), poleID ORDER BY Date ASC;";
// dd($sql_query);
$result = mysqli_query($conn, $sql_query);
// dd($poleIDs);
// Check if $result is null

if ($result->num_rows == 0) {
    // Output a JavaScript alert message
    echo '<script>
                customAlert.alert("Δεν βρέθηκαν καταχωρημένα στοιχεία για αυτές τις επιλογές.", "Δεν υπάρχουν καταχωρημένα");
            </script>';
}
?>

<script>
    document.getElementById('line_chart_form').addEventListener('submit', function(event) {
        const poleSelected = document.querySelector('input[name="poleID"]:checked');
        if (!poleSelected) {
            alert('Παρακαλώ επιλέξτε μια κολώνα.');
            event.preventDefault();
        }
    });
</script>

<body class="min-h-[100vh] h-full flex flex-col">
<?php include 'partials/nav.php'; ?>

<header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?= $heading?> </h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main class="h-full">
    <div class="mx-auto max-w-7xl py-4 sm:px-6 lg:px-8" style="display: flex;">
        <?php if (!empty($data)) : ?>
            <form name="line_chart" method="post" id="line_chart_form">
                <h2>Ημερομηνία Αρχής:</h2>
                <input style="font-size: 16px;" type="date" id="dateFrom" name="dateFrom" value="<?= !empty($_POST['dateFrom']) ? $_POST['dateFrom'] : date("Y") . "-01-01";?>">
                <h2>Ημερομηνία Τέλους:</h2>
                <input style="font-size: 16px;" type="date" id="dateEnd" name="dateEnd" value="<?= date("Y-m-d"); ?>">
                <br><br>
                <h2>Επιλογή Κολώνας</h2>
            <?php
            $firstPoleID = $data[0]['poleID'];
            foreach ($data as $index => $pole) :
                ?>
                    <input type="radio" id="poleID_<?= $index; ?>" name="poleID" value="<?= $pole['poleID']; ?>" <?= $index === 0 ? 'checked' : ''; ?>>
                    <!-- Area and Street Name -->
                    <label for="poleID_<?= $index; ?>"><?= $pole['area'] . " - " . $pole['address']; ?></label><br>
            <?php endforeach; ?>

                <!-- TODO dynamic sensors list -->
                <br>
                <h2>Επιλογή Αισθητήρα</h2>
                <input type="checkbox" id="temperature" name="selection[]" value="temperature" checked>
                <label for="temperature">Θερμοκρασία</label><br>
                <input type="checkbox" id="noiseDB" name="selection[]" value="noiseDB">
                <label for="noiseDB">Επίπεδα Θορύβου</label><br>
                <input type="checkbox" id="humidity" name="selection[]" value="humidity">
                <label for="humidity">Υγρασία</label><br>
                <input type="checkbox" id="rainHeight" name="selection[]" value="rainHeight">
                <label for="rainHeight">Επίπεδο βροχόπτωσης</label><br>
                <input type="checkbox" id="ldr" name="selection[]" value="ldr">
                <label for="ldr">Επίπεδο Ηλιακής φωτεινότητας</label><br>
                <input type="checkbox" id="carbonMono" name="selection[]" value="carbonMono">
                <label for="carbonMono">Μονοξείδιο του Άνθρακα</label><br>
                <input type="checkbox" id="AirQuality" name="selection[]" value="AirQuality">
                <label for="AirQuality">Ποιότητα Αέρα</label><br>
                <input type="checkbox" id="uv" name="selection[]" value="uv">
                <label for="uv">Δείκτης UV ακτινοβολίας</label><br>
                <input type="checkbox" id="pressure" name="selection[]" value="pressure">
                <label for="pressure">Ατμοσφαιρική Πίεση</label><br>
                <br><button class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg w-full"type="submit"  value="Submit"> Υποβολή </button>
            </form>
        <?php else : ?>
            <p>No poles available.</p>
        <?php endif; ?>
            <div style="flex">
                <script>
                    google.charts.load('current', {
                        'packages': ['corechart']
                    });
                    google.charts.setOnLoadCallback(comparativeChart);

                    function comparativeChart() { //Η συνάρτηση κατασκευής φραφήματος
                        var data = new google.visualization.DataTable();
                        data.addColumn('string', 'Date');
                        <?php   // Στήλες του πίνακα δεδομένων του γραφήματος βάσει επιλογών
                        foreach ($selected_measurements as $measurement) {
                            $input_label = "";
                            switch ($measurement) {
                                case "temperature":
                                    $input_label = "Θερμοκρασία";
                                    break;
                                case "noiseDB":
                                    $input_label = "Επίπεδα Θορύβου";
                                    break;
                                case "humidity":
                                    $input_label = "Υγρασία";
                                    break;
                                case "rainHeight":
                                    $input_label = "Επίπεδο βροχόπτωσης";
                                    break;
                                case "ldr":
                                    $input_label = "Επίπεδο Ηλιακής φωτεινότητας";
                                    break;
                                case "carbonMono":
                                    $input_label = "Μονοξείδιο του Άνθρακα";
                                    break;
                                case "AirQuality":
                                    $input_label = "Ποιότητα Αέρα";
                                    break;
                                case "uv":
                                    $input_label = "Δείκτης UV ακτινοβολίας";
                                    break;
                                case "pressure":
                                    $input_label = "Ατμοσφαιρική Πίεση";
                                    break;
                            }
                            echo "data.addColumn('number', '$input_label');\n";
                        }
                        ?>
                        data.addRows([
                            <?php
                            while ($row = mysqli_fetch_array($result)) {
                                echo "['{$row['Date']}', ";
                                foreach ($selected_measurements as $measurement) {
                                    echo "{$row[$measurement]}, ";
                                }
                                echo "],\n";
                            }
                            ?>
                        ]);

                        var options = { //Μορφοποίηση εμφάνισης γραφήματος
                            title: '',
                            hAxis: {title: ''},
                            vAxis: {title: ''},
                        };
                        //Εμφάνιση του γραφήματος μέσα στο στοιχείο <div>
                        var chart = new google.visualization.LineChart(document.getElementById('div_chart'));
                        chart.draw(data, options);
                    }

                </script>

                    <!-- Chart area -->

                <div id="div_chart" style="width: 900px; height: 500px"></div>
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
