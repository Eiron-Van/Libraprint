<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

// get search input (if any)
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/style.css?v=1.5">
    <style>mark.search-highlight {background-color: #FDE68A; color: inherit; padding: 0 1px; border-radius: 3px;}</style>
    <script src="/Admin/js/script.js"></script>
    <script src="inventory_script.js"></script>

    <title>Libraprint|Admin|Inventory</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed min-h-screen">
    <header class="bg-gray-900 text-white sticky top-0 z-10">
        <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
                <div>
                    <button id="menu" class="text-[30px] focus:outline-none cursor-pointer hover:opacity-60 active:opacity-40 transition-opacity duration-200 p-2">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div id="brand" class="flex flex-row items-center space-x-1.5">
                    <img src="/asset/fingerprint.png" class="w-15">
                    <a class="text-2xl font-serif pt-2" href="https://libraprintlucena.com/Admin">Libraprint</a>
                </div>
            </div>
        </section>
    </header>

    <div class="flex">
        <div id ="side-menu" class="hidden flex-col bg-[#F4F4F4] w-fit top-19 fixed z-[9] inset-y-0">
            <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
                <ul>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin"><img class="w-8 m-2" src="/asset/dashboard.png">Dashboard</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 bg-slate-200 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="/asset/inventory.png">Inventory</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin/users"><img class="w-8 m-2" src="/asset/users.png">Users</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin/activity-logs"><img class="w-8 m-2" src="/asset/reports.png">Activity Logs</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="https://libraprintlucena.com/Registration/"><img class="w-8 m-2" src="/asset/registration.png">Registration</a></li>                    

                    <!-- <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/setting.png">Settings</a></li> -->
                </ul>
            </nav>
        </div>
    </div>

    <main class=" flex flex-col px-15">
        <?php if (isset($_GET['added']) && ctype_digit($_GET['added']) && (int)$_GET['added'] > 0): ?>
            <div id="success-banner" class="mx-auto mb-4 w-full max-w-[100rem] bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded relative">
                <span class="font-semibold"><?php echo (int)$_GET['added']; ?></span> book(s) added successfully.
                <button type="button" id="dismiss-banner" class="absolute top-1/2 -translate-y-1/2 right-3 text-green-900/60 hover:text-green-900">&times;</button>
            </div>
            <script>
                (function(){
                    var btn = document.getElementById('dismiss-banner');
                    var banner = document.getElementById('success-banner');
                    if (btn && banner) {
                        btn.addEventListener('click', function(){ banner.remove(); });
                        // Auto-dismiss after 5s
                        setTimeout(function(){ if (banner) banner.remove(); }, 5000);
                    }
                    // Clean URL (remove query string) without reload
                    if (window.history && window.history.replaceState) {
                        var url = new URL(window.location.href);
                        url.searchParams.delete('added');
                        window.history.replaceState({}, document.title, url.pathname + (url.search ? '?' + url.search : '') + url.hash);
                    }
                })();
            </script>
        <?php endif; ?>
        <h1 class="text-6xl font-serif text-white text-center p-4">Book Inventory Management</h1>
        
        <div class="flex flex-row items-center gap-4">
            <form onsubmit="return false;" class="w-1/3">
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="search" id="search" name="search" placeholder="Search..." class="block w-full p-4 ps-10 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"/>
                    <!-- <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-[#23304e] hover:bg-[#5c6072] focus:ring-3 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Search</button> -->
                </div>
            </form>
            <a href="add_book.php" class="inline-flex items-center justify-center text-white bg-[#3c5ba3] hover:bg-[#293e6f] focus:ring-3 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-4">Add Book</a>
        </div>

        <div id="results-count" class="m-2 text-white"></div>
        <div id="results" class="overflow-auto max-h-[65vh] rounded-lg shadow text-white">
            Loading...
        </div>
    </main>
</body>
</html>
