<?php

require '../connection.php';
// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}

// get search input (if any)
$search = isset($_GET['search']) ? $_GET['search'] : '';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=1.5">
    <style>mark.search-highlight {background-color: #FDE68A; color: inherit; padding: 0 1px; border-radius: 3px;}</style>
    <script src="/script.js"></script>
    <script src="script/book_search.js"></script>
    <script src="script/reserve.js"></script>

    <title>Libraprint | Reservation</title>
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] h-screen text-gray-900 bg-fixed accent-cyan-600">
    <!-- Header -->
    <header class="bg-gray-900 text-white sticky top-0 z-10">
        <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
                <button id="menu" title="Open navigation menu" class="text-[30px] focus:outline-none cursor-pointer hover:opacity-60 active:opacity-40 transition-opacity duration-200 p-2">
                    <i class="fas fa-bars"></i>
                </button>
                <div id="brand" class="flex items-center space-x-1.5">
                    <i class="fas fa-fingerprint text-white text-[30px]"></i>
                    <a class="text-2xl font-serif" href="https://libraprintlucena.com">Libraprint</a>
                </div>
            </div>
            <div class="flex items-center mx-4 space-x-6">
                <div class="hidden sm:block">
                    <nav>
                        <ul class="md:flex md:flex-col lg:flex-row md:text-sm space-x-6">
                            <li><a href="https://libraprintlucena.com" class="hover:opacity-60 transition-opacity duration-200">Home</a></li>
                            <li><a href="../AboutUs" class="hover:opacity-60 transition-opacity duration-200">About Us</a></li>
                            <li><a href="../ContactUs" class="hover:opacity-60 transition-opacity duration-200">Contact Us</a></li>
                        </ul>
                    </nav>
                </div>
                <div>
                    <a href="/Login/logout.php" class="hidden lg:block bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded">Logout</a>
                    <a href="/Login/logout.php" class="block lg:hidden active:opacity-60 transition-opacity duration-200 px-2 py-1 rounded"><i class="fas fa-sign-out-alt text-3xl"></i></a>
                </div>
            </div>
        </section>
    </header>

    <!-- Sidenav -->
    <div id="side-menu" class="hidden fixed flex-col bg-[#F4F4F4] w-fit z-11">
        <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
            <ul>
                <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../User"><img class="w-8 m-2" src="../asset/profile.png">Profile</a></li>
                <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/book_r.png">Book Reservation</a></li>
                <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../Borrowing"><img class="w-8 m-2" src="../asset/book_b.png">Book Borrowing</a></li>
                <!-- <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/setting.png">Settings</a></li> -->
                <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../AboutUs"><img class="w-8 m-2" src="../asset/about_us.png">About Us</a></li>
                <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../ContactUs"><img class="w-8 m-2" src="../asset/contact_us.png">Contact Us</a></li>
            </ul>
        </nav>
    </div>

    <main class="flex justify-center items-center grow">
        <div class=" max-w-7xl w-full p-5">
            <!-- Header Section -->
            <div class="w-full">
                <div class="text-center mb-12">
                    <i class="fas fa-bookmark text-6xl text-blue-400 drop-shadow-md mb-6 block"></i>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-3 tracking-tight">
                        Reserve Knowledge. Experience Convenience.
                    </h1>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed">
                        Access Lucena City Library’s collection. Easily reserve your next read and access knowledge with LibraPrint
                    </p>
                </div>
            </div>

            <!-- Main Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                <!-- Table -->
                <section class="lg:col-span-2 bg-gray-900/30 p-6 md:p-8 rounded-2xl shadow-lg border border-gray-700">
                    <!-- Search Bar -->
                    <div class="relative w-full mb-2 lg:mb-5">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="search" id="search" name="search" placeholder="Search..." class="block w-full p-4 ps-10 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <!-- Table -->
                    <div id="results">Loading...</div>
                    
                </section>
                
                <!-- Sidebar -->
                <aside class="bg-gray-900/40 rounded-2xl p-8 shadow-xl border border-gray-700 max-h-[]">
                    <h2 class="text-2xl font-semibold text-white text-center mb-6 tracking-tight">
                        RESERVE NOW!
                    </h2>
                    <div class="space-y-5">
                        <div class="flex items-start gap-4 hover:bg-gray-800 hover:bg-opacity-60 rounded-xl p-3 transition">
                            <i class="fas fa-calendar-check text-3xl text-blue-400 mt-1"></i>
                            <div>
                                <h4 class="text-lg font-semibold text-white">Instant Reservations</h4>
                                <p class="text-gray-400 text-sm">Reserve books anytime, anywhere in seconds.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 hover:bg-gray-800 hover:bg-opacity-60 rounded-xl p-3 transition">
                            <i class="fas fa-layer-group text-3xl text-blue-400 mt-1"></i>
                            <div>
                                <h4 class="text-lg font-semibold text-white">Extensive Collection</h4>
                                <p class="text-gray-400 text-sm">Browse 12,000+ titles from all genres.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 hover:bg-gray-800 hover:bg-opacity-60 rounded-xl p-3 transition">
                            <i class="fas fa-clock text-3xl text-blue-400 mt-1"></i>
                            <div>
                                <h4 class="text-lg font-semibold text-white">Real-Time Availability</h4>
                                <p class="text-gray-400 text-sm">See which books are available instantly.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 hover:bg-gray-800 hover:bg-opacity-60 rounded-xl p-3 transition">
                            <i class="fas fa-bell text-3xl text-blue-400 mt-1"></i>
                            <div>
                                <h4 class="text-lg font-semibold text-white">Smart Notifications</h4>
                                <p class="text-gray-400 text-sm">Receive alerts for borrowed or reserved books.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 hover:bg-gray-800 hover:bg-opacity-60 rounded-xl p-3 transition">
                            <i class="fas fa-user-shield text-3xl text-blue-400 mt-1"></i>
                            <div>
                                <h4 class="text-lg font-semibold text-white">Secure Access</h4>
                                <p class="text-gray-400 text-sm">Enjoy safe and authenticated transactions.</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    <!-- Overlay for reservation -->
    <div id="overlay" class="fixed inset-0 bg-gray-900/80 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96 shadow-lg text-center relative">
            <h2 class="text-2xl font-bold mb-4">Read or Borrow</h2>
            <div class="grid grid-cols-6 gap-4 mb-4">
                <button id="read-btn" class="col-span-2 col-start-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg w-full">Read</button>
                <button id="borrow-btn" class="col-span-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg w-full">Borrow</button>
            </div>
            <!-- ✅ Success message -->
            <div id="successMsg" class="hidden bg-green-100 text-green-800 p-2 rounded-md">
                ✅ Book successfully recorded!
            </div>

            <button id="closeOverlayBtn" class="absolute top-2 right-3 text-gray-600 hover:text-black">✕</button>
        </div>
    </div>
</body>
</html>