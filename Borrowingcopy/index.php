<?php
// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

include '../connection.php';

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
    <script src="/Borrowingcopy/script/read.js"></script>
    <script src="/Borrowingcopy/script/terms.js"></script>
    
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
                    <div>
                        <a href="/Login/logout.php" class="hidden lg:block bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded">Logout</a>
                        <a href="/Login/logout.php" class="block lg:hidden active:opacity-60 transition-opacity duration-200 px-2 py-1 rounded"><i class="fas fa-sign-out-alt text-3xl"></i></a>
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
                        <button id="borrowBtn" class="bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20 hover:bg-opacity-20 transition-all duration-300 shadow-lg">
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

        <!-- Terms and Conditions Overlay -->
        <div id="termsOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-70 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-hidden flex flex-col">

                <!-- Header -->
                <div class="relative text-center p-6 border-b border-gray-200 text-white">
                    <button id="closeTerms" class="absolute top-4 right-5 text-gray-600 hover:text-gray-400 text-xl font-bold">
                        ✕
                    </button>
                    <div class="w-16 h-16 bg-blue-600 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <i class="fas fa-scroll text-2xl text-white"></i>
                    </div>
                    <h1 class="text-2xl md:text-3xl text-gray-800 m font-bold mb-2">Terms & Conditions</h1>
                    <div class="flex items-center justify-center space-x-2 text-gray-600 mb-2">
                        <i class="fas fa-building text-blue-800"></i>
                        <span class="font-medium">Lucena City Library</span>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-800 font-medium">
                            <i class="fas fa-info-circle mr-2"></i>
                            Please read and accept these terms to continue with book borrowing services
                        </p>
                    </div>
                </div>

                <!-- Scrollable Terms -->
                <div id="termsContent" class="overflow-y-auto p-8 text-gray-700 text-sm leading-relaxed space-y-4 flex-1 bg-gray-50 border-b border-gray-200">
                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-gavel mr-2"></i>1. Terms
                        </h2>
                        <p class="text-gray-700">By registering and using the LibraPrint system, you agree to abide by these Terms and Conditions. These terms govern your use of the system, including attendance tracking, book borrowing, reservations, and related services.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-user-plus mr-2"></i>2. User Registration
                        </h2>
                        <p class="text-gray-700 mb-2">2.1 Only registered users with verified accounts (via fingerprint and profile registration) are allowed to use LibraPrint.</p>
                        <p class="text-gray-700">2.2 Biometric Registration is required for attendance and identification. Data is processed in compliance with the Data Privacy Act of 2012 (RA 10173).</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-fingerprint mr-2"></i>3. Attendance and Access
                        </h2>
                        <p class="text-gray-700 mb-2">3.1 All users must scan their registered fingerprint upon entering the library for attendance logging.</p>
                        <p class="text-gray-700">3.2 Unauthorized use of another person's fingerprint or account is prohibited.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-book mr-2"></i>4. Book Borrowing and Reservation
                        </h2>
                        <p class="text-gray-700 mb-2">4.1 Users may reserve books online via LibraPrint.</p>
                        <p class="text-gray-700 mb-2">4.2 Each book is assigned a unique barcode for borrowing, returning, or reservation.</p>
                        <p class="text-gray-700 mb-2">4.3 Books must be returned on or before the due date. Late returns may incur penalties.</p>
                        <p class="text-gray-700">4.4 Reserved books must be claimed within 24 hours or the reservation will be cancelled.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>5. Loan Period
                        </h2>
                        <p class="text-gray-700 mb-2">5.1 The standard borrowing period is seven (7) calendar days.</p>
                        <p class="text-gray-700">5.2 Borrowing may be renewed if the book is returned on the due date and no other reservation exists.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-user-shield mr-2"></i>6. User Responsibilities
                        </h2>
                        <p class="text-gray-700 mb-2">6.1 Handle borrowed materials with care. Damaged or lost items must be replaced or repaired.</p>
                        <p class="text-gray-700">6.2 Maintain confidentiality of your account and avoid activities that disrupt system operations.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>7. Late Returns and Penalties
                        </h2>
                        <p class="text-gray-700 mb-2">7.1 Books returned after their due date are considered overdue.</p>
                        <p class="text-gray-700">7.2 A penalty of ₱1.00 per day per book will be applied.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-tools mr-2"></i>8. Lost or Damaged Books
                        </h2>
                        <p class="text-gray-700 mb-2">8.1 Users are responsible for proper care of borrowed books.</p>
                        <p class="text-gray-700 mb-2">8.2 Lost or damaged books must be replaced with the same title/newer edition or paid at purchase value.</p>
                        <p class="text-gray-700">8.3 Failure to comply may result in permanent ban from the system.</p>
                    </section>

                    <section class="border-b border-gray-200 pb-3">
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-lock mr-2"></i>9. Data Privacy and Security
                        </h2>
                        <p class="text-gray-700 mb-2">9.1 Personal and biometric data is collected solely for library services.</p>
                        <p class="text-gray-700">9.2 Users may request access, correction, or deletion of data under applicable laws.</p>
                    </section>

                    <section>
                        <h2 class="text-base font-bold text-blue-600 mb-2">
                        <i class="fas fa-ban mr-2"></i>10. Prohibited Activities
                        </h2>
                        <p class="text-gray-700">10.1 Users are prohibited from tampering with the system, using another account/biometric data, or removing/altering materials without proper checkout.</p>
                    </section>
                </div>

                <!-- Agreement Section -->
                <div class="p-6 bg-gray-100 flex flex-col space-y-4">
                    <div class="flex items-start space-x-2">
                        <input type="checkbox" id="agreeCheckbox" class="w-4 h-4 mt-1" disabled>
                        <label for="agreeCheckbox" class="text-sm text-gray-700">
                        I have read and agree to the Terms & Conditions.
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button id="cancelTerms" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Cancel</button>
                        <button id="continueBtn" disabled class="px-4 py-2 bg-blue-400 text-white rounded-lg cursor-not-allowed transition-all">Continue</button>
                    </div>
                </div>
            </div>
        </div>

</body>
</html>