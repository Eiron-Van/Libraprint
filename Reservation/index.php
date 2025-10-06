<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../connection.php';
date_default_timezone_set('Asia/Manila'); // ensure PH time

// ------------------------------------------
// AUTO-EXPIRE UNCLAIMED RESERVATIONS
// ------------------------------------------
$conn->query("
    UPDATE book_inventory b
    JOIN reservation r ON b.item_id = r.item_id
    SET b.status = 'Available'
    WHERE r.date_borrowed <= NOW() - INTERVAL 2 MINUTE
      AND r.is_claimed = 0
");

$conn->query("
    DELETE FROM reservation
    WHERE date_borrowed <= NOW() - INTERVAL 2 MINUTE
      AND is_claimed = 0
");

// ------------------------------------------
// INITIALIZE VARIABLES
// ------------------------------------------
$books = [];
$search = "";
$message = "";
$resultCount = null;

// ------------------------------------------
// REQUIRE USER SESSION
// ------------------------------------------
session_start();

if (!isset($_SESSION['user_id'])) {
    die("<script>alert('❌ You must be logged in to reserve a book.');window.location='/Login';</script>");
}

$user_id = intval($_SESSION['user_id']); // current logged-in user

// ------------------------------------------
// HANDLE RESERVATION (POST)
// ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $itemId = intval($_POST['item_id']);
    $now = date('Y-m-d H:i:s'); // Philippine current date/time

    // 1️⃣ Check if book is already reserved by anyone
    $check = $conn->prepare("SELECT id FROM reservation WHERE item_id = ? AND is_returned = 0");
    $check->bind_param("i", $itemId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // 2️⃣ Insert reservation linked to this user with exact timestamp
        $stmt = $conn->prepare("
            INSERT INTO reservation (item_id, user_id, date_borrowed, is_claimed, is_returned)
            VALUES (?, ?, ?, 0, 0)
        ");
        $stmt->bind_param("iis", $itemId, $user_id, $now);

        if ($stmt->execute()) {
            // 3️⃣ Update the book’s status to Reserved in inventory
            $update = $conn->prepare("UPDATE book_inventory SET status = 'Reserved' WHERE item_id = ?");
            $update->bind_param("i", $itemId);
            $update->execute();
            $update->close();

            $message = "✅ Book reserved successfully at $now (Philippine Time)";
        } else {
            $message = "❌ Reservation failed: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        $message = "⚠️ This book is already reserved by another user.";
    }

    $check->close();

    // Prevent form resubmission reload problem
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// ------------------------------------------
// HANDLE SEARCH (GET)
// ------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    if ($search !== "") {
        // 4️⃣ Fetch books + their reservation status
        $sql = "
            SELECT 
                b.item_id,
                b.title,
                b.author,
                b.status,
                r.date_borrowed,
                r.user_id AS reserved_by,
                u.username AS reserved_user
            FROM book_inventory b
            LEFT JOIN reservation r ON b.item_id = r.item_id AND r.is_returned = 0
            LEFT JOIN users u ON r.user_id = u.user_id
            WHERE b.title LIKE ? OR b.author LIKE ?
        ";

        $stmt = $conn->prepare($sql);
        $param = "%" . $search . "%";
        $stmt->bind_param("ss", $param, $param);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $resultCount = count($books);
        if ($resultCount === 0) {
            $message = "No results found for '<strong>" . htmlspecialchars($search) . "</strong>'.";
        }
    } else {
        $message = "Please enter a keyword to search.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" href="/asset/fingerprint.ico" type="image/x-icon">

    <!-- ✅ Tailwind CLI compiled CSS (kept exactly as you requested) -->
    <link rel="stylesheet" href="/style.css?v=1.5">
    <script src="../script.js"></script>
    <script src="/tailwind.config.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Reservation | Libraprint</title>
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] h-screen text-gray-900 bg-fixed">

    <!-- HEADER -->
    <header class="bg-gray-900 text-white sticky top-0 z-10">
        <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
                <button id="menu" class="text-[30px] focus:outline-none cursor-pointer hover:opacity-60 active:opacity-40 transition-opacity duration-200 p-2">
                    <i class="fas fa-bars"></i>
                </button>
                <div id="brand" class="flex items-center space-x-1.5">
                    <i class="fas fa-fingerprint text-white text-[30px]"></i>
                    <a class="text-2xl font-serif" href="https://libraprintlucena.com">Libraprint</a>
                </div>
            </div>

            <div class="flex items-center mx-4 space-x-6">
                <nav class="hidden sm:block">
                    <ul class="md:flex md:flex-col lg:flex-row md:text-sm space-x-6">
                        <li><a href="https://libraprintlucena.com" class="hover:opacity-60 transition-opacity duration-200">Home</a></li>
                        <li><a href="/AboutUs" class="font-bold hover:opacity-60 transition-opacity duration-200">About Us</a></li>
                        <li><a href="/ContactUs" class="hover:opacity-60 transition-opacity duration-200">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </section>
    </header>

    <!-- SIDENAV -->
    <div id="side-menu" class="hidden flex-col bg-[#F4F4F4] fixed inset-y-0 z-[9] w-fit top-21 lg:top-19">
        <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
            <ul>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Profile"><img class="w-8 m-2" src="/asset/profile.png" alt="">Profile</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Reservation"><img class="w-8 m-2" src="/asset/book_r.png" alt="">Book Reservation</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Borrowing"><img class="w-8 m-2" src="/asset/book_b.png" alt="">Book Borrowing</a></li>
                <li class="sm:hidden"><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 bg-slate-300 w-full px-5 py-4 flex items-center" href="/AboutUs"><img class="w-8 m-2" src="/asset/about_us.png" alt="">About Us</a></li>
                <li class="sm:hidden"><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/ContactUs"><img class="w-8 m-2" src="/asset/contact_us.png" alt="">Contact Us</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="/asset/setting.png" alt="">Settings</a></li>
            </ul>
        </nav>  
    </div>

   <!-- SEARCH + RESULTS -->
    <div class="pt-10 px-4">
        <main class="flex flex-col items-center justify-center px-4 py-10 w-full">
            <!-- Search Form -->
            <form action="" method="get" class="w-1/3 mb-8">
                <div class="relative">
                    <input type="search" id="search" name="search" value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search by Title or Author..."
                           class="block w-full p-4 ps-10 text-sm border border-gray-300 rounded-lg bg-gray-50 
                                  focus:ring-blue-500 focus:border-blue-500"/>
                    <button type="submit"
                            class="text-white absolute end-2.5 bottom-2.5 bg-[#23304e] hover:bg-[#5c6072]
                                   font-medium rounded-lg text-sm px-4 py-2">
                        Search
                    </button>
                </div>
            </form>
             <div class="mt-4">
            <!-- Message -->
            <?php if($message): ?>
                <div class="mb-4 px-4 py-3 max-w-4xl w-full rounded-lg bg-white/10 text-white text-center">
                    <?= $message ?>
                </div>
            <?php endif; ?>
             </div>
           

            <!-- Results Table -->
            <div class="w-full overflow-x-auto mt-20">
                <table class="w-full table-auto text-left text-black rounded-lg overflow-hidden shadow">
                    <thead class="bg-[#7581a6] text-gray-50">
                        <tr>
                            <th class="px-4 py-2">Author</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Date Borrowed</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $book): ?>
                                <tr class="bg-white border-t hover:bg-gray-100">
                                    <td class="px-4 py-2"><?= htmlspecialchars($book['author']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($book['title']) ?></td>
                                    <td class="px-4 py-2">
                                        <span class="<?= $book['status'] === 'Available' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' ?>">
                                            <?= htmlspecialchars($book['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($book['date_borrowed'] ?: '—') ?></td>
                                    <td class="px-4 py-2 flex flex-col items-center">
                                        <?php if ($book['status'] === 'Available'): ?>
                                            <form method="POST" action="" class="bg-cyan-100 text-white font-semibold rounded-full px-4  py-2 hover:bg-blue-700 transition">
                                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($book['item_id']) ?>">
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
                                                <button type="submit" 
                                                       >
                                                    Reserve
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="bg-gray-300 text-gray-600 font-medium rounded-full px-4 py-2 cursor-not-allowed" disabled>
                                                Reserve
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    <?= $message ?: "Start by searching for a book." ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
