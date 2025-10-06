<?php
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

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=1.5">
    <script src="../script.js"></script>
    <script src="read.js"></script>

    <title>Libraprint | Borrowing</title>
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed max-h-dvh h-screen">
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
                </div>
            </section>
        </header>

        <div id="side-menu" class="hidden fixed flex-col bg-[#F4F4F4] w-fit z-11">
            <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
                <ul>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../User"><img class="w-8 m-2" src="../asset/profile.png">Profile</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../Reservation"><img class="w-8 m-2" src="../asset/book_r.png">Book Reservation</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/book_b.png">Book Borrowing</a></li>
                    <!-- <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/setting.png">Settings</a></li> -->
                    <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../AboutUs"><img class="w-8 m-2" src="../asset/about_us.png">About Us</a></li>
                    <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../ContactUs"><img class="w-8 m-2" src="../asset/contact_us.png">Contact Us</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex items-center justify-center px-4 py-12">
            <div class="max-w-5xl w-full text-center">
                <div class="mb-14">
                    <i class="fas fa-book text-6xl text-blue-400 drop-shadow-md mb-6 block"></i>
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">Book Borrowing</h1>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed">
                        Access our extensive library collection. Choose to read in the library or borrow books for home study.
                    </p>
                </div>

                <!-- Action Buttons -->
                <form method="POST" action="">
                    <div class="grid md:grid-cols-2 gap-8 max-w-3xl mx-auto mb-14">
                        <!-- Read in Library -->
                        <button class="bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20 hover:bg-opacity-20 transition-all duration-300 shadow-lg">
                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md">
                                <i class="fas fa-book-open text-2xl text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3">Read in Library</h3>
                            <p class="text-gray-700 mb-6 text-sm leading-relaxed">
                                Access books for reading within the library premises. Perfect for research and study sessions.
                            </p>
                            <span class="w-full py-3 bg-green-500 hover:bg-green-600 text-black font-bold rounded-lg shadow-md transition duration-300 inline-block">
                                <i class="fas fa-book-reader mr-2"></i>
                                Read a Book
                            </span>
                        </button>

                        <!-- Borrow a Book -->
                        <button class="bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20 hover:bg-opacity-20 transition-all duration-300 shadow-lg">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md">
                                <i class="fas fa-home text-2xl text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3">Borrow for Home</h3>
                            <p class="text-gray-700 mb-6 text-sm leading-relaxed">
                                Take books home for extended reading. Standard 7-day borrowing period.
                            </p>
                            <span class="w-full py-3 bg-blue-500 hover:bg-blue-600 text-black font-bold rounded-lg shadow-md transition duration-300 inline-block">
                                <i class="fas fa-book mr-2"></i>
                                Borrow a Book
                            </span>
                        </button>
                    </div>
                </form>
                <div class="grid md:grid-cols-3 gap-6 text-center max-w-4xl mx-auto">
                    <div class="bg-gray-900 bg-opacity-40 rounded-xl p-6 shadow-md hover:bg-opacity-60 transition-all duration-300">
                        <i class="fas fa-clock text-3xl text-blue-400 mb-4"></i>
                        <h4 class="text-lg font-semibold text-white mb-2">24/7 Reservations</h4>
                        <p class="text-gray-400 text-sm">Reserve books online anytime</p>
                    </div>
                    <div class="bg-gray-900 bg-opacity-40 rounded-xl p-6 shadow-md hover:bg-opacity-60 transition-all duration-300">
                        <i class="fas fa-qrcode text-3xl text-green-400 mb-4"></i>
                        <h4 class="text-lg font-semibold text-white mb-2">Easy Scanning</h4>
                        <p class="text-gray-400 text-sm">Quick barcode scanning system</p>
                    </div>
                    <div class="bg-gray-900 bg-opacity-40 rounded-xl p-6 shadow-md hover:bg-opacity-60 transition-all duration-300">
                        <i class="fas fa-shield-alt text-3xl text-purple-400 mb-4"></i>
                        <h4 class="text-lg font-semibold text-white mb-2">Secure System</h4>
                        <p class="text-gray-400 text-sm">Fingerprint authentication</p>
                    </div>
                </div>
            </div>
        </main>
        <!-- Overlay for Reading a Book -->
        <div id="overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-96 shadow-lg text-center relative">
                <h2 class="text-2xl font-bold mb-4">Scan Book Barcode</h2>
                <input type="text" id="barcodeInput" placeholder="Scan or type barcode..." class="border rounded-md p-2 w-full mb-4 text-center focus:outline-none">
                <button id="saveBookBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg w-full">Save Book</button>

                <!-- ✅ Success message -->
                <div id="successMsg" class="hidden bg-green-100 text-green-800 p-2 rounded-md mb-4">
                    ✅ Book successfully recorded!
                </div>

                <button id="closeOverlayBtn" class="absolute top-2 right-3 text-gray-600 hover:text-black">✕</button>

                <!-- ✅ Live scanned book list -->
                <div id="bookList" class="mt-4 text-left">
                    <h3 class="font-semibold mb-2">Books scanned this session:</h3>
                    <ul id="bookListItems" class="text-sm text-gray-700 list-disc pl-5 space-y-1"></ul>
                </div>
            </div>
        </div>
</body>
</html>