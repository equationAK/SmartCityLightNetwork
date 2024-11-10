<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';

if (isAuthenticated()) {
    header("Location: index.php");
    exit();
}

require 'partials/head.php';
?>

<body class="h-[100vh] flex flex-col">
    <!-- Navigation bar -->
    <?php include 'partials/nav.php'; ?>
    <!-- End of Navigation bar -->
    <!-- Main -->
    <main class="flex justify-center items-center h-full">
        <div class="w-full max-w-xs">
            <h1 class="text-3xl font-bold text-center mb-8">Εγγραφή Νέου Χρήστη</h1>
            <form class="space-y-4" action="insertUser.php" method="POST" name="regForm" onsubmit="return validateForm()">
                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-700">Πλήρες Όνομα Χρήστη</label>
                    <input type="text" id="fullname" name="fullname" class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="username" name="username" class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Κωδικός Πρόσβασης</label>
                    <input id="password" name="password" class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Επιβεβαίωση Κωδικού</label>
                    <input id="confirm_password" name="confirm_password" class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <button type="submit" class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg w-full">Εγγραφή</button>
                </div>
            </form>
            <p class="text-center mt-4">Έχετε ήδη λογαριασμό; <a href="login.php" class="text-cyan-800 hover:underline">Είσοδος Χρήστη</a></p>
        </div>
    </main>
    <!-- Footer -->
    <?php
    include 'partials/footer.php';
    ?>
    <!-- End of footer -->
</body>
</html>


