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

$user_id = $_SESSION['user_id'];

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
    <main class="flex items-center justify-center px-4 py-12">
        <div class="rounded-2xl w-11/12 md:w-2/3 lg:w-7/10 max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="text-center p-6 text-white">
                <h1 class="text-3xl font-bold text-white mb-6 text-center">📚 List of Reserved/Available Books</h1>
            </div>

            <!-- Table -->
            <div class="overflow-y-auto text-gray-700 text-sm leading-relaxed space-y-4 flex-1">

                <table class="w-full table-auto border-gray-300 rounded-xl">
                    <thead class="text-gray-50 sticky top-0 z-[8]">
                        <tr class="bg-gray-800 text-white rounded-lg grid grid-cols-9 gap-2">
                            <th class="px-6 py-3 text-left col-span-4">Title</th>
                            <th class="px-6 py-3 text-left col-span-2">Author</th>
                            <th class="px-6 py-3 text-center col-span-2">Status</th>
                            <th class="col-span-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reservedResult->num_rows > 0): ?>
                                <tr class="bg-blue-600 text-white sticky top-0 z-[9] rounded-xl">
                                    <td colspan="9" class="px-6 py-2 text-left font-semibold">Your Reserved Books</td>
                                </tr>

                                <?php while ($row = $reservedResult->fetch_assoc()): ?>
                                <tr class="bg-blue-100 hover:bg-blue-200 grid grid-cols-9 gap-2 border-b border-gray-200 items-center">
                                    <td class="flex items-center px-6 py-3 col-span-4"><?= htmlspecialchars($row['title']) ?></td>
                                    <td class="flex items-center px-6 py-3 col-span-2"><?= htmlspecialchars($row['author']) ?></td>
                                    <td class="flex items-center px-6 py-3 text-center col-span-2"><?= htmlspecialchars($row['status']) ?> (<?= htmlspecialchars($row['purpose']) ?>)</td>
                                    <td class="flex items-center px-6 py-3 text-center col-span-1">
                                        <form method="POST" action="">
                                            <input type="hidden" name="borrow_item_id" value="<?= $row['item_id'] ?>">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1.5 rounded-lg shadow">
                                                Borrow
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>

                            <tr class="bg-gray-600 text-white sticky top-0 z-[10] rounded-xl">
                                <td colspan="9" class="px-6 py-2 text-left font-semibold">Available Books</td>
                            </tr>

                            <?php if ($availableBooks->num_rows > 0): ?>
                                <?php while ($row = $availableBooks->fetch_assoc()): ?>
                                <tr class="bg-gray-100 hover:bg-gray-200 grid grid-cols-9 gap-2 border-b border-gray-200">
                                    <td class="flex items-center px-6 py-3 col-span-4"><?= htmlspecialchars($row['title']) ?></td>
                                    <td class="flex items-center px-6 py-3 col-span-2"><?= htmlspecialchars($row['author']) ?></td>
                                    <td class="flex items-center px-6 py-3 text-center col-span-2"><?= htmlspecialchars($row['status']) ?></td>
                                    <td class="flex items-center px-6 py-3 text-center col-span-1">
                                        <form method="POST" action="">
                                            <input type="hidden" name="borrow_item_id" value="<?= $row['item_id'] ?>">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg shadow">
                                                Borrow
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-gray-300">No available books at the moment.</td></tr>
                            <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </main>
</body>
</html>