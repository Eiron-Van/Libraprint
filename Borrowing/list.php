<?php
include '../connection.php'; // adjust this path if needed


// Fetch reserved books
$sql = "SELECT b.title, b.author, b.status, r.date_borrowed
        FROM reservation r
        JOIN book_inventory b ON r.item_id = b.item_id
        ORDER BY r.date_borrowed DESC";
$result = $conn->query($sql);
$reservedBooks = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/style.css?v=1.5">
    <script src="/script.js"></script>
    <title>Borrowing | Libraprint</title>
</head>

<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">
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

    <!-- SIDE MENU -->
    <div id="side-menu" class="hidden flex-col bg-[#F4F4F4] fixed inset-y-0 z-[9] w-fit top-21 lg:top-19">
        <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
            <ul>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Profile"><img class="w-8 m-2" src="/asset/profile.png">Profile</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Reservation"><img class="w-8 m-2" src="/asset/book_r.png">Book Reservation</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Borrowing"><img class="w-8 m-2" src="/asset/book_b.png">Book Borrowing</a></li>
                <li class="sm:hidden"><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 bg-slate-300 w-full px-5 py-4 flex items-center" href="/AboutUs"><img class="w-8 m-2" src="/asset/about_us.png">About Us</a></li>
                <li class="sm:hidden"><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/ContactUs"><img class="w-8 m-2" src="/asset/contact_us.png">Contact Us</a></li>
                <li><a class="text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="/asset/setting.png">Settings</a></li>
            </ul>
        </nav>
    </div>

    <!-- Reserved Books Table -->
    <main class="max-w-6xl mx-auto mt-10 p-6 rounded-xl shadow-md flex justify-center">
        <div class="w-full md:w-5/6 lg:w-4/5">
            <h1 class="text-3xl font-bold text-white mb-6 text-center">📚 List of Reserved Books</h1>
            <div class="overflow-x-auto flex justify-center">
                <table class="min-w-ll table-auto border-gray-300 rounded-xl">
                    <thead >
                        <tr class="bg-gray-800 text-white rounded-lg ">
                            <th class="px-6 py-3 text-left">Book Title</th>
                            <th class="px-6 py-3 text-left">Author</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Date Reserved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($reservedBooks)): ?>
                            <?php foreach ($reservedBooks as $book): ?>
                                <tr class="bg-white">
                                    <td class="px-6 py-4"><?= htmlspecialchars($book['title']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($book['author']) ?></td>
                                    <td class="px-6 py-4 <?= $book['status'] === 'Reserved' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' ?>">
                                        <?= htmlspecialchars($book['status']) ?>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($book['date_borrowed']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No reserved books found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Next Button -->
        <div class="flex justify-center mt-4">
            <button onclick="window.location.href='instructions.html'"
                class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                Next →
            </button>
        </div>
    </main>
</body>
</html>
