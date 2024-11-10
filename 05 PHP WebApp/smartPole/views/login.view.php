<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($username, $password)) {
        header('Location: index.php');
        exit();
    }
    else {
        $error_message = "Invalid username or password";
    }
}

if (isAuthenticated()) {
    header('Location: index.php');
    exit();
}
?>

<?php
require 'partials/head.php';
?>

<body class="h-[100vh] flex flex-col">
    <!-- Navigation bar -->
    <?php
    include 'partials/nav.php';
    ?>
    <!-- End of Navigation bar -->
    <!-- Main -->
    <main class="flex justify-center items-center h-full">
        <div class="w-full max-w-xs">
            <h1 class="text-3xl font-bold text-center mb-8">Είσοδος Χρήστη</h1>
            <?php
            if (isset($error_message)) {
                ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
                <?php
            }
            ?>
            <form class="space-y-4" action="#" method="POST">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Όνομα Χρήστη</label>
                    <input type="text" id="username" name="username" required class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Κωδικός Πρόσβασης</label>
                    <input type="password" id="password" name="password" required class="mt-1 p-2 w-full border rounded-md">
                </div>
                <div>
                    <button type="submit" class="bg-cyan-900 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg w-full">Είσοδος</button>
                </div>
            </form>
            <p class="text-center mt-4">Δεν έχετε λογαριασμό; <a href="register.php" class="text-cyan-800 hover:underline">Εγγραφή νέου χρήστη</a></p>
        </div>
    </main>
     <!-- End of Main -->
    <!-- Footer -->
    <?php
    include 'partials/footer.php';
    ?>
    <!-- End of footer -->
</body>
</html>
