<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
$mapData = require_once 'providers/map.php';
include 'partials/head.php';

// Navigation bar
// include 'partials/nav.php';
?>
<body class="h-full min-h-[100vh] flex flex-col">
    <?php
    include 'partials/nav.php';
    ?>
    <!-- Header -->
<div class="bg-white shadow h-full overflow-auto">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 h-full flex flex-col justify-center">
        <div>
            <a href="index.php">
                <img src="partials/png/eap2.png" alt="logo" class="max-w-xs mx-auto mb-5">
            </a>
        </div>
        <div class="text-center mt-4">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 px-4 py-2">Σχολή Θετικών Επιστημών και Τεχνολογίας</h1>
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 mt-2 px-4 py-2">Τμήμα Πληροφορικής</h2>
            <h3 class="text-2xl font-bold tracking-tight text-gray-900 mt-2 px-4 py-2">Πτυχιακή Εργασία ΠΕ419</h3>
            <p class="text-2xl mt-4 text-gray-900">Σχεδιασμός και ανάπτυξη «Έξυπνης Κολώνας»<br>δημόσιου φωτισμού σε περιβάλλον μίας έξυπνης πόλης</p>
            <h4 class="text-2xl font-bold tracking-tight text-gray-900 mt-6">Επιβλέπων Καθηγητής</h4>
            <p class="text-lg text-gray-900">Δρ. Τοπάλης Ευάγγελος</p>
            <h5 class="text-2xl font-bold tracking-tight text-gray-900 mt-6">Φοιτητής</h5>
            <p class="text-lg text-gray-900"><strong>Κυριακίδης Αριστοκλής</strong></p>
            <p class="text-lg text-gray-900">137659</p>
        </div>
    </div>
</div>
    <!-- End of Header -->
    <!-- Main -->
    <main>
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
</html>
