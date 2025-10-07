    <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../connection.php'; // ensure $conn is defined

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purpose'])) {
    $purpose = $_POST['purpose']; // "Read" or "Borrow"
    $user_id = $_SESSION['user_id'];

    $_SESSION['borrowing_purpose'] = $purpose;

    // ✅ Try to find an active reservation
    $checkReservation = $conn->prepare("
        SELECT id, item_id 
        FROM reservation 
        WHERE user_id = ? AND is_claimed = 0 
        ORDER BY date_borrowed DESC 
        LIMIT 1
    ");
    $checkReservation->bind_param("i", $user_id);
    $checkReservation->execute();
    $reservation = $checkReservation->get_result()->fetch_assoc();
    $checkReservation->close();

    if ($reservation && !empty($reservation['item_id'])) {
        // ✅ Case 1: User has an active reservation
        $reservation_id = $reservation['id'];
        $item_id = $reservation['item_id'];

        // Update reservation purpose
$updateReservation = $conn->prepare("
    UPDATE reservation
    SET purpose = ?
    WHERE id = ? AND user_id = ?
");
        $updateReservation->bind_param("sii", $purpose, $reservation_id, $user_id);
        $updateReservation->execute();
        $updateReservation->close();

        // Add to claim_log
        $insertClaimLog = $conn->prepare("
            INSERT INTO claim_log (user_id, item_id, reservation_id, date_claimed, purpose, is_returned)
            VALUES (?, ?, ?, NOW(), ?, 0)
        ");
        $insertClaimLog->bind_param("iiis", $user_id, $item_id, $reservation_id, $purpose);
        $insertClaimLog->execute();
        $insertClaimLog->close();

    } else {
        // ✅ Case 2: No active reservation
        // Insert minimal claim_log (no item_id)
        $insertClaimLog = $conn->prepare("
            INSERT INTO claim_log (user_id, item_id, reservation_id, date_claimed, purpose, is_returned)
            VALUES (?, NULL, NULL, NOW(), ?, 0)
        ");
        $insertClaimLog->bind_param("is", $user_id, $purpose);
        $insertClaimLog->execute();
        $insertClaimLog->close();
    }

    header("Location: terms.html");
    exit;
}
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
        <script src="../tailwind.config.js"></script>
        <title>Book Borrowing | Libraprint</title>
    </head>

    <body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">
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
                                <li><a href="/AboutUs" class="font-bold hover:opacity-60 transition-opacity duration-200">About Us</a></li>
                                <li><a href="/ContactUs" class="hover:opacity-60 transition-opacity duration-200">Contact Us</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </section>
        </header>

        <!-- Main Content -->
        <main class="flex items-center justify-center min-h-screen px-4 py-12 bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">
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
                        <button type="submit" name="purpose" value="Read" class="bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20 hover:bg-opacity-20 transition-all duration-300 shadow-lg">
                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md">
                                <i class="fas fa-book-open text-2xl text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3">Read in Library</h3>
                            <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                                Access books for reading within the library premises. Perfect for research and study sessions.
                            </p>
                            <span class="w-full py-3 bg-green-500 hover:bg-green-600 text-black font-bold rounded-lg shadow-md transition duration-300 inline-block">
                                <i class="fas fa-book-reader mr-2"></i>
                                Read a Book
                            </span>
                        </button>

                        <!-- Borrow a Book -->
                        <button type="submit" name="purpose" value="Borrow" class="bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20 hover:bg-opacity-20 transition-all duration-300 shadow-lg">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md">
                                <i class="fas fa-home text-2xl text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3">Borrow for Home</h3>
                            <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                                Take books home for extended reading. Standard 7-day borrowing period with renewal options.
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
    </body>
    </html>
