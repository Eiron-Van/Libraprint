<?php
// backend-reserved-list.php
// Replace the top PHP logic in your file with this. HTML/CSS/JS below remains unchanged.

include '../connection.php';
session_start();
date_default_timezone_set('Asia/Manila');

// ----- Configuration -----
$TESTING_INTERVAL = '2 MINUTE'; // change to '24 HOUR' for production
$now = (new DateTime())->format('Y-m-d H:i:s');

// Get currently logged in user id (common session key). Change if your app uses a different key.
$currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// If you store user id under another session name, add fallback checks here:
// $currentUserId = $currentUserId ?? (isset($_SESSION['id']) ? intval($_SESSION['id']) : null);

// If no logged-in user found, we'll show no reservations (safer). You can change behavior if you need admin view.
if (!$currentUserId) {
    // optionally: try cookie fallback (uncomment if you use cookie auth)
    // $currentUserId = isset($_COOKIE['user_id']) ? intval($_COOKIE['user_id']) : null;
}

// Helper: safe id list to SQL
function ids_to_list(array $ids) {
    if (empty($ids)) return 'NULL';
    return implode(',', array_map('intval', $ids));
}

mysqli_report(MYSQLI_REPORT_OFF);

// 1) Ensure required columns exist in reservation (non-destructive; only adds if missing)
$columns = [];
$colCheck = $conn->query("SHOW COLUMNS FROM reservation");
while ($row = $colCheck->fetch_assoc()) {
    $columns[] = $row['Field'];
}
if (!in_array('is_claimed', $columns)) {
    $conn->query("ALTER TABLE reservation ADD COLUMN is_claimed TINYINT(1) DEFAULT 0");
}
if (!in_array('purpose', $columns)) {
    $conn->query("ALTER TABLE reservation ADD COLUMN purpose VARCHAR(50) DEFAULT NULL");
}
if (!in_array('is_returned', $columns)) {
    $conn->query("ALTER TABLE reservation ADD COLUMN is_returned TINYINT(1) DEFAULT 0");
}

// Start transaction to keep state consistent
$conn->begin_transaction();

try {
    // ----------------------------
    // A) EXPIRE unclaimed reservations older than $TESTING_INTERVAL
    // (Only affect reservations whose book_inventory.status = 'Reserved' and NOT 'Checked Out')
    // ----------------------------
    $expiredQuery = "
        SELECT r.id AS reservation_id, r.user_id AS reservation_user, r.item_id, b.status AS inventory_status
        FROM reservation r
        JOIN book_inventory b ON r.item_id = b.item_id
        WHERE r.date_borrowed <= NOW() - INTERVAL $TESTING_INTERVAL
          AND (r.is_claimed = 0 OR r.is_claimed IS NULL)
          AND b.status = 'Reserved'
    ";
    $expiredRes = $conn->query($expiredQuery);
    $expiredReservations = [];
    if ($expiredRes) {
        while ($row = $expiredRes->fetch_assoc()) {
            // double-check not checked out (defensive)
            if (strtolower($row['inventory_status']) !== 'checked out') {
                $expiredReservations[] = $row;
            }
        }
    }

    if (!empty($expiredReservations)) {
        $expiredIds = array_column($expiredReservations, 'reservation_id');
        $itemIds = array_column($expiredReservations, 'item_id');

        // 1.1 Update book_inventory.status -> Available for those item_ids (only where currently 'Reserved')
        $itemList = ids_to_list($itemIds);
        $conn->query("UPDATE book_inventory SET status = 'Available' WHERE item_id IN ($itemList) AND status = 'Reserved'");

        // 1.2 Delete corresponding claim_log entries (for the same user + item combination)
        // Build list of (user_id,item_id) conditions to delete claim_log entries reliably
        foreach ($expiredReservations as $er) {
            $u = intval($er['reservation_user']);
            $it = intval($er['item_id']);
            $conn->query("DELETE FROM claim_log WHERE user_id = $u AND item_id = $it");
        }

        // 1.3 Delete expired reservations
        $resList = ids_to_list($expiredIds);
        $conn->query("DELETE FROM reservation WHERE id IN ($resList)");
    }

    // ----------------------------
    // B) HANDLE returned reservations: remove reservation & claim_log rows and set inventory to Available if needed
    // Reservation is considered returned if reservation.is_returned = 1 OR claim_log.is_returned = 1
    // ----------------------------
    // Find returned reservations (either table)
    $returnedQuery = "
        SELECT DISTINCT r.id AS reservation_id, r.item_id, r.user_id, b.status AS inventory_status
        FROM reservation r
        LEFT JOIN claim_log c ON c.user_id = r.user_id AND c.item_id = r.item_id
        JOIN book_inventory b ON r.item_id = b.item_id
        WHERE r.is_returned = 1 OR (c.is_returned = 1)
    ";
    $returnedRes = $conn->query($returnedQuery);
    $returnedReservations = [];
    if ($returnedRes) {
        while ($row = $returnedRes->fetch_assoc()) {
            $returnedReservations[] = $row;
        }
    }

    if (!empty($returnedReservations)) {
        $returnedIds = array_column($returnedReservations, 'reservation_id');
        $returnedItemIds = array_column($returnedReservations, 'item_id');

        // Update inventory status to Available for those items if they're not checked out
        $retItemList = ids_to_list($returnedItemIds);
        $conn->query("UPDATE book_inventory SET status = 'Available' WHERE item_id IN ($retItemList) AND status <> 'Checked Out'");

        // Delete related claim_log entries
        foreach ($returnedReservations as $rr) {
            $u = intval($rr['user_id']);
            $it = intval($rr['item_id']);
            $conn->query("DELETE FROM claim_log WHERE user_id = $u AND item_id = $it");
        }

        // Delete reservation rows
        $retResList = ids_to_list($returnedIds);
        $conn->query("DELETE FROM reservation WHERE id IN ($retResList)");
    }

    // Commit cleanup changes
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    // Logging error would be good in production; for now die to show problem
    die("Database cleanup error: " . $e->getMessage());
}

// --------------------------------------------------
// Before fetching: verify there is a login_record for the current user
// (the system requirement asked to verify login via login_record table)
// If no login record found, we will show an empty list (to avoid showing other users' data).
// --------------------------------------------------
$hasLoginRecord = false;
if ($currentUserId) {
    $stmt = $conn->prepare("SELECT 1 FROM login_record WHERE user_id = ? LIMIT 1");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $stmt->store_result();
    $hasLoginRecord = $stmt->num_rows > 0;
    $stmt->free_result();
    $stmt->close();
}

// If there is no logged-in user or no login record, return an empty result set
$reservedBooks = [];

if ($currentUserId && $hasLoginRecord) {
    // Fetch reservations for the CURRENT user only, and only for items that currently have status 'Reserved'
    // Purpose is taken from claim_log (if exists) else from reservation.purpose
    $sql = "
        SELECT 
            b.title,
            b.author,
            b.status,
            r.date_borrowed,
            r.is_claimed,
            COALESCE(c.purpose, r.purpose) AS purpose,
            r.id AS reservation_id,
            r.item_id
        FROM reservation r
        JOIN book_inventory b ON r.item_id = b.item_id
        LEFT JOIN claim_log c ON c.user_id = r.user_id AND c.item_id = r.item_id
        WHERE r.user_id = ?
          AND b.status = 'Reserved'
        ORDER BY r.date_borrowed DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $currentUserId);
    if (!$stmt->execute()) {
        die('Fetch failed: ' . $stmt->error);
    }
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $reservedBooks[] = $row;
    }
    $stmt->close();
} else {
    // No valid login / user — empty list. (You can change this behavior if you want admins to see all reservations.)
    $reservedBooks = [];
}

// Close connection if you like (optional)
// $conn->close();

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
        <main class="max-w-6xl mx-auto mt-10 p-6 rounded-xl shadow-md flex flex-col items-center">
            <div class="w-full md:w-5/6 lg:w-4/5 ">
                <h1 class="text-3xl font-bold text-white mb-6 text-center">📚 List of Reserved Books</h1>
                
              
               
                <div class="overflow-x-auto ">
                    <table class="min-w-ll table-auto border-gray-300 rounded-xl">
                        <thead >
                            <tr class="bg-gray-800 text-white rounded-lg ">
                                <th class="px-6 py-3 text-left">Book Title</th>
                                <th class="px-6 py-3 text-left">Author</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Date Reserved</th>
                                <th class="px-6 py-3 text-left">Purpose</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($reservedBooks)): ?>
                                <?php foreach ($reservedBooks as $book): ?>
                                    <tr class="bg-white">
                                        <td class="px-6 py-4"><?= htmlspecialchars($book['title']) ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($book['author']) ?></td>
                                        <td class="px-6 py-4 <?= $book['status'] === 'Available' ? 'text-green-600 font-semibold' : ($book['status'] === 'Reserved' ? 'text-blue-600 font-semibold' : 'text-red-600 font-semibold') ?>">
                                            <?= htmlspecialchars($book['status']) ?>
                                        </td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($book['date_borrowed']) ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($book['is_claimed'] == 1): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                                                    <?= htmlspecialchars($book['purpose'] ?: 'Claimed') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm">
                                                    Pending Claim
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No reserved or borrowed books found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- ✅ Buttons directly under the table -->
            <div class="flex justify-center gap-6 mt-20">
                <button onclick="window.location.href='claim.php'"
                    class="bg-blue-600 text-white font-bold px-10 py-3 rounded-full shadow-md hover:bg-blue-700 transition text-lg">
                    CLAIM BOOK →
                </button>
                <button onclick="window.location.href='return.php'"
                    class="bg-green-600 text-white font-bold px-10 py-3 rounded-full shadow-md hover:bg-green-700 transition text-lg">
                    RETURN BOOK →
                </button>
            </div>
        
            </div>
        
        </main>
    </body>
    </html>
