<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

require '../connection.php';
require '../mailer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}


$user_id = $_SESSION['user_id'];

// ✅ Check if this is the "final email" trigger (not a book scan)
if (isset($_POST['finalBorrow']) && $_POST['finalBorrow'] === 'true') {
    if (!empty($_SESSION['borrowed_books']) && !empty($_SESSION['user_info'])) {
        $user = $_SESSION['user_info'];
        $borrowedBooks = $_SESSION['borrowed_books'];

        $borrowDate = date('F j, Y');
        $returnDate = date('F j, Y', strtotime('+7 days'));

        // Build book list
        $bookList = "<ul>";
        foreach ($borrowedBooks as $title) {
            $bookList .= "<li><strong>{$title}</strong></li>";
        }
        $bookList .= "</ul>";

        // Email content
        $subject = "📚 Borrowing Confirmation - LibraPrint Lucena Library";
        $body = "
            <h2>Borrowing Confirmation</h2>
            <p>Dear {$user['first_name']} {$user['last_name']},</p>
            <p>You have successfully borrowed the following book(s):</p>
            {$bookList}
            <p><strong>Borrowed on:</strong> {$borrowDate}<br>
               <strong>Return Due Date:</strong> {$returnDate}</p>
            <p>Please return your borrowed books on or before the due date to avoid penalties.</p>
            <br>
            <p>Thank you,<br><strong>LibraPrint Lucena Library</strong></p>
        ";

        // Send email once
        $emailStatus = sendEmail($user['email'], "{$user['first_name']} {$user['last_name']}", $subject, $body);

        // --- SEND SMS (once) ---
        require_once __DIR__ . '/../philsms.php'; // path to philsms helper; adjust if needed

        $smsResult = ['status'=>'skipped','message'=>'no phone'];
        $phone = $_SESSION['user_info']['contact_number'] ?? '';

        if (!empty($phone)) {
            // build a concise SMS (keep it short — SMS costs per 160 chars)
            $titles = implode(', ', array_map(function($t){ return preg_replace('/\s+/', ' ', trim($t)); }, $borrowedBooks));
            $smsMessage = "LibraPrint: Hi {$user['first_name']}, you borrowed ".count($borrowedBooks)." book(s) on {$borrowDate}. Due: {$returnDate}. Titles: {$titles}.";
            $smsResult = sendPhilSMS($phone, $smsMessage);
        }

        // Clear session borrow list
        unset($_SESSION['borrowed_books']);
        unset($_SESSION['user_info']);

        echo json_encode([
            "success" => true, 
            "emailStatus" => $emailStatus,
            "smsStatus" => $smsResult
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No borrowed books to email."]);
    }
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

// Find user info once
if (empty($_SESSION['user_info'])) {
    $findUser = $conn->prepare("SELECT id, first_name, last_name, email, contact_number FROM users WHERE user_id = ?");
    $findUser->bind_param("s", $user_id);
    $findUser->execute();
    $findUser->bind_result($id, $first_name, $last_name, $email, $contact_number);
    $findUser->fetch();
    $findUser->close();

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $_SESSION['user_info'] = [
        'id' => $id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'contact_number' => $contact_number
    ];
} else {
    $id = $_SESSION['user_info']['id'];
}

// Find the book in the inventory
$findBook = $conn->prepare("SELECT item_id, title FROM book_inventory WHERE class_no = ?");
$findBook->bind_param("s", $barcode);
$findBook->execute();
$findBook->bind_result($book_id, $book_title);
$findBook->fetch();
$findBook->close();

if (empty($book_id)) {
    echo json_encode(['success' => false, 'message' => 'Book not found in inventory']);
    exit;
}

// Insert into book_record
$stmt = $conn->prepare("INSERT INTO book_record (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $id, $book_id);

if ($stmt->execute()) {
    // Add to session borrow list
    if (!isset($_SESSION['borrowed_books'])) {
        $_SESSION['borrowed_books'] = [];
    }
    $_SESSION['borrowed_books'][] = $book_title;

    echo json_encode([
        'success' => true,
        'title' => $book_title,
        'barcode' => $barcode
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $stmt->error
    ]);
}


// --- Borrow action ---

// Update book status to Checked Out
$update = $conn->prepare("UPDATE book_inventory SET status = 'Checked Out' WHERE item_id = ?");
$update->bind_param("i", $book_id);
$update->execute();

// Remove from reservation (if exists)
$delete = $conn->prepare("DELETE FROM reservation WHERE item_id = ? AND user_id = ?");
$delete->bind_param("ii", $book_id, $id);
$delete->execute();

$stmt->close();
$conn->close();