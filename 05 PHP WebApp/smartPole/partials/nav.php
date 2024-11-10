<header>
    <nav class="bg-cyan-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-center">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="./"><img class="h-12" src="partials/svg/EAP-logo.svg" alt="SmartCity"></a>
                    </div>
                        <div class="ml-10 flex items-baseline space-x-4">
                            <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-cyan-800 hover:text-white" -->
                            <a href="./" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Αρχική Σελίδα</a>
                            <?php
                            if (isset($_SESSION['username'])) {
                                switch ($_SESSION['role_id']) {
                                    // user (χρήστης πλατφόρμας από το Δήμο
                                    case 1:
                                        echo '<a href="map.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Χάρτης</a>';
                                        // echo '<a href="statistics.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Στατιστικά</a>';
                                        echo '<div class="relative">
                                                <a href="#" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium" id="user-menu-button">Στατιστικά</a>

                                                <!-- Dropdown menu -->
                                                <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                                    <!-- Active: "bg-gray-100", Not Active: "" -->
                                                    <a href="statistics.php" class="block px-4 py-2 text-sm text-cyan-800" role="menuitem" tabindex="-1">Στατιστικά Κολώνας</a>
                                                    <a href="comparativeStatistics.php" class="block px-4 py-2 text-sm text-cyan-800" role="menuitem" tabindex="-1">Συγκρητικά Στατιστικά</a>
                                                </div>
                                            </div>';
                                        echo '<a href="camera.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Camera</a>';
                                        echo '<a href="IoT.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">IoT</a>';
                                        echo '<a href="about.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">About</a>';
                                        break;

                                        // TODO extra capabilities for super Admin
                                    // platform admin
                                    case 2:
                                        echo'<a href="#" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Καταχώριση Κολώνας</a>';
                                        echo'<a href="#" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Καταχώριση Χρήστη</a>';
                                        break;

                                        // για τους guest users
                                    default:
                                        echo '<a href="map.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Χάρτης</a>';
                                }
                                ?>
                                <a href="logout.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Αποσύνδεση</a>
                                <?php
                            }
                            else {
                                ?>
                                <a href="login.php" class="text-gray-300 hover:bg-cyan-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Είσοδος</a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
    document.getElementById('user-menu-button').addEventListener('click', function () {
        var menu = document.querySelector('.relative .absolute');
        menu.classList.toggle('hidden');
    });
</script>
