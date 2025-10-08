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

require '../connection.php';

// get search input (if any)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$_SESSION['user_id'] = $user_id;


// --- Borrow action ---
if (isset($_POST['borrow_item_id'])) {
    $item_id = $_POST['borrow_item_id'];

    // 1. Update book status to Checked Out
    $update = $conn->prepare("UPDATE book_inventory SET status = 'Checked Out' WHERE item_id = ?");
    $update->bind_param("i", $item_id);
    $update->execute();

    // 2. Remove from reservation (if exists)
    $delete = $conn->prepare("DELETE FROM reservation WHERE item_id = ? AND user_id = ?");
    $delete->bind_param("ii", $item_id, $user_id);
    $delete->execute();

    // 3. Optional: Add to borrow_log (if you want)
    // $log = $conn->prepare("INSERT INTO borrow_log (user_id, item_id, borrow_date) VALUES (?, ?, NOW())");
    // $log->bind_param("ii", $user_id, $item_id);
    // $log->execute();

    header("Location: borrow_book.php?success=1");
    exit();
}

// --- Get Reserved Books ---
$reservedBooks = $conn->prepare("
    SELECT bi.item_id, bi.title, bi.author, bi.status, r.purpose
    FROM reservation r
    INNER JOIN book_inventory bi ON r.item_id = bi.item_id
    WHERE r.user_id = ?
");
$reservedBooks->bind_param("s", $user_id);
$reservedBooks->execute();
$reservedResult = $reservedBooks->get_result();

// --- Get Available Books ---
$availableBooks = $conn->query("
    SELECT item_id, title, author, status
    FROM book_inventory
    WHERE status = 'Available'
");

?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=1.5">
    <script src="../script.js"></script>
    <script src="script/book_search.js"></script>
    
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
                    <a href="../Login/logout.php" class="hidden lg:block bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded">Logout</a>
                    <a href="../Login/logout.php" class="block lg:hidden active:opacity-60 transition-opacity duration-200 px-2 py-1 rounded"><i class="fas fa-sign-out-alt text-3xl"></i></a>
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

    <!-- Reserved Books Table -->
    <main class="flex items-center justify-center p-4">
        <div class="rounded-2xl w-11/12 md:w-2/3 lg:w-7/10 max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="text-center p-6 text-white">
                <h1 class="text-3xl font-bold text-white mb-2 text-center">📚 List of Reserved/Available Books</h1>
            </div>


            <div class="flex flex-row items-center justify-between mb-2 px-0.5">
                <div class="relative w-1/3">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="search" id="search" name="search" placeholder="Search..." class="block w-full p-4 ps-10 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"/>
                    <!-- <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-[#23304e] hover:bg-[#5c6072] focus:ring-3 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Search</button> -->
                </div>
                <button class="text-white bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 focus:ring-1 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-4">Borrow</button>
            </div>
            
            <!-- Table -->
            <div id="results">Loading...</div>

        </div>
    </main>
</body>
</html>