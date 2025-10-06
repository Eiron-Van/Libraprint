<?php
// claim.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../connection.php';

// ✅ Session to identify user
session_start();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['class_no'])) {
    $class_no = trim($_POST['class_no']);
    $purpose = isset($_POST['purpose']) && in_array($_POST['purpose'], ['Borrow','Read']) ? $_POST['purpose'] : 'Borrow';

    // ✅ Find the book
    $book_sql = "SELECT item_id, title, status FROM book_inventory WHERE class_no = ? LIMIT 1";
    $book_stmt = $conn->prepare($book_sql);
    $book_stmt->bind_param('s', $class_no);
    $book_stmt->execute();
    $book_res = $book_stmt->get_result();

    if ($book = $book_res->fetch_assoc()) {
        $itemId = (int)$book['item_id'];
        $title  = $book['title'];
        $status = $book['status'];

        // ✅ Check reservation
        $res_sql = "SELECT id, user_id, is_claimed FROM reservation WHERE item_id = ? AND is_returned = 0 ORDER BY id DESC LIMIT 1";
        $res_stmt = $conn->prepare($res_sql);
        $res_stmt->bind_param('i', $itemId);
        $res_stmt->execute();
        $res_res = $res_stmt->get_result();

        $is_reserved = false;
        $reservation_user = null;
        $reservation_id = null;
        $is_claimed = 0;

        if ($res_row = $res_res->fetch_assoc()) {
            $is_reserved = true;
            $reservation_user = (int)$res_row['user_id'];
            $reservation_id = (int)$res_row['id'];
            $is_claimed = (int)$res_row['is_claimed'];
        }

        $res_stmt->close();
        $conn->begin_transaction();

        // ✅ CASE 1: Reserved by current user and not yet claimed
        if ($is_reserved && $reservation_user === $user_id && $is_claimed === 0) {

            $updateRes = $conn->prepare("UPDATE reservation SET is_claimed = 1, purpose = ?, date_borrowed = NOW() WHERE id = ?");
            $updateRes->bind_param("si", $purpose, $reservation_id);
            $ok1 = $updateRes->execute();

            $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Checked Out' WHERE item_id = ?");
            $updateBook->bind_param("i", $itemId);
            $ok2 = $updateBook->execute();

            $insertClaim = $conn->prepare("INSERT INTO claim_log (user_id, item_id, date_borrowed, purpose, is_returned) VALUES (?, ?, NOW(), ?, 0)");
            $insertClaim->bind_param("iis", $user_id, $itemId, $purpose);
            $ok3 = $insertClaim->execute();

            if ($ok1 && $ok2 && $ok3) {
                $conn->commit();
                echo "<script>alert('✅ Book " . addslashes($title) . " claimed successfully for " . addslashes($purpose) . ".');</script>";
            } else {
                $conn->rollback();
                echo "<script>alert('❌ Failed to update reservation claim.');</script>";
            }

            $updateRes->close();
            $updateBook->close();
            $insertClaim->close();

        }

        // ✅ CASE 2: Book is reserved but by another user
        elseif ($is_reserved && $reservation_user !== $user_id) {
            $conn->rollback();
            echo "<script>alert('⚠️ This book is reserved by another user. You cannot claim it.');</script>";
        }

        // ✅ CASE 3: Book is available (not reserved OR already returned)
        elseif ($status === 'Available' || ($is_reserved && $is_claimed === 1)) {

            // Create a new reservation record for this user
            $insertRes = $conn->prepare("INSERT INTO reservation (user_id, item_id, date_borrowed, is_claimed, purpose, is_returned) VALUES (?, ?, NOW(), 1, ?, 0)");
            $insertRes->bind_param("iis", $user_id, $itemId, $purpose);
            $ok4 = $insertRes->execute();
            $reservation_id = $conn->insert_id;

            // Update book status
            $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Checked Out' WHERE item_id = ?");
            $updateBook->bind_param("i", $itemId);
            $ok5 = $updateBook->execute();

            // Insert into claim_log
            $insertClaim = $conn->prepare("INSERT INTO claim_log (user_id, item_id, date_claimed, purpose, is_returned) VALUES (?, ?, NOW(), ?, 0)");
            $insertClaim->bind_param("iis", $user_id, $itemId, $purpose);
            $ok6 = $insertClaim->execute();

            if ($ok4 && $ok5 && $ok6) {
                $conn->commit();
                echo "<script>alert('✅ Book " . addslashes($title) . " claimed successfully for " . addslashes($purpose) . ".');</script>";
            } else {
                $conn->rollback();
                echo "<script>alert('❌ Failed to claim available book.');</script>";
            }

            $insertRes->close();
            $updateBook->close();
            $insertClaim->close();

        }

        // ✅ CASE 4: Book already checked out or unavailable
        else {
            $conn->rollback();
            echo "<script>alert('⚠️ This book is currently unavailable for claiming.');</script>";
        }

    } else {
        echo "<script>alert('❌ Book not found. Please check the barcode.');</script>";
    }

    $book_stmt->close();
}
?>

<!-- (rest of your original HTML remains unchanged below) -->

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <title>Claim Book | Libraprint</title>
  <link rel="stylesheet" href="../style.css?v=1.5">
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] min-h-screen flex flex-col items-center justify-center text-white">
  <header class="bg-gray-900 text-white sticky top-0 z-10">
    <!-- Logo -->
    
   <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
              <div id="brand" class="flex items-center space-x-1.5">
                    <i class="fas fa-fingerprint text-white text-[30px]"></i>
                    <a class="text-2xl font-serif" href="https://libraprintlucena.com">Libraprint</a>
                </div>
            </div>
      </section> 
</header>
<div class="pt-10 px-4">
        <main class="flex flex-col justify-center px-4 py-10 w-full">
 <div class="flex flex-col items-center space-y-4">
      <img src="https://img.icons8.com/color/96/000000/barcode-reader.png"  class="w-20 h-20"/>

      <h1 class="text-2xl font-semibold">Please scan the book barcode<br>to complete transaction.</h1>
                  

  <form id="claim-form" method="POST" action="" class="flex flex-col items-center space-y-4">
    <input type="hidden" name="purpose" value="<?= isset($_GET['purpose']) ? htmlspecialchars($_GET['purpose']) : 'Borrow' ?>">
    <input type="text" name="class_no" id="class_no-input"
           placeholder="Scan class_no"
           class="px-4 py-2 rounded-full w-64 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 bg-cyan-600 text-white"
           autocomplete="off" autofocus>
  </form>
    </div>
  <script>
    (function() {
      const input = document.getElementById('class_no');
      if (!input) return;

      input.focus();

      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          input.form.submit();
        }
      });

      let timer = null;
      input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          const val = input.value.trim();
          if (val.length >= 3) {
            input.form.submit();
          }
        }, 300);
      });
    })();
  </script>
        </main>
    </div>
</body>
</html>
