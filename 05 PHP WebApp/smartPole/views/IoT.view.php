<?php

require_once 'providers/auth_provider.php';
require_once 'providers/DB.php';
include 'partials/head.php'
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
        <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?= $heading?> - Πλατφόρμα ThingSpeak</h1>
    </div>
</header>
<!-- End of Header -->
<!-- Main -->
<main class="h-full py-10">
    <div class="flex flex-wrap gap-10 mx-auto max-w-[1510px] px-10 justify-center">
        <iframe width="450" height="260" style="border: 1px solid #cccccc;" src="https://thingspeak.com/channels/2277540/charts/2?bgcolor=%23ffffff&color=%23d62020&dynamic=true&results=60&title=%CE%98%CE%B5%CF%81%CE%BC%CE%BF%CE%BA%CF%81%CE%B1%CF%83%CE%AF%CE%B1&type=line"></iframe>
        <iframe width="450" height="260" style="border: 1px solid #cccccc;" src="https://thingspeak.com/channels/2277540/charts/3?bgcolor=%23ffffff&color=%23d62020&dynamic=true&results=60&title=%CE%A5%CE%B3%CF%81%CE%B1%CF%83%CE%AF%CE%B1&type=line"></iframe>
        <iframe width="450" height="260" style="border: 1px solid #cccccc;" src="https://thingspeak.com/channels/2277540/charts/1?bgcolor=%23ffffff&color=%23d62020&dynamic=true&results=60&title=%CE%A6%CF%89%CF%84%CE%B5%CE%B9%CE%BD%CF%8C%CF%84%CE%B7%CF%84%CE%B1&type=line"></iframe>
        <iframe width="450" height="260" style="border: 1px solid #cccccc;" src="https://thingspeak.com/channels/2277540/charts/4?bgcolor=%23ffffff&color=%23d62020&dynamic=true&results=60&title=%CE%A0%CE%BF%CE%B9%CF%8C%CF%84%CE%B7%CF%84%CE%B1+%CE%91%CE%AD%CF%81%CE%B1&type=line"></iframe>
        <iframe width="450" height="260" style="border: 1px solid #cccccc;" src="https://thingspeak.com/channels/2277540/charts/6?bgcolor=%23ffffff&color=%23d62020&dynamic=true&results=60&title=%CE%95%CF%80%CE%B9%CE%BA%CE%AF%CE%BD%CE%B4%CF%85%CE%BD%CE%B1+%CE%91%CE%AD%CF%81%CE%B9%CE%B1&type=line"></iframe>
    </div>
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
