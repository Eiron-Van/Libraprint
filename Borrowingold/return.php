<?php
// return.php - Handle book returns
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../connection.php';

// --------- POST handling ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['class_no'])) {
    $class_no = trim($_POST['class_no']);

    // Find claimed and checked-out book that needs to be returned
    $sql = "SELECT r.id AS reservation_id, b.item_id, b.title, b.status, r.purpose
            FROM reservation r
            JOIN book_inventory b ON r.item_id = b.item_id
            WHERE b.class_no = ? 
              AND r.is_claimed = 1 
              AND r.is_returned = 0
              AND b.status = 'Checked Out'
            LIMIT 1";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $class_no);

        if (!$stmt->execute()) {
            echo "<script>alert('DB error (select exec): " . addslashes($stmt->error) . "');</script>";
        } else {
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $reservationId = (int)$row['reservation_id'];
                $itemId = (int)$row['item_id'];
                $title = $row['title'];
                $currentStatus = $row['status'];

                // Start transaction
                $conn->begin_transaction();

                // 1) Mark reservation as returned
                $u1 = $conn->prepare("UPDATE reservation SET is_returned = 1, is_claimed = 0 WHERE id = ?");
                if (!$u1) {
                    $conn->rollback();
                    echo "<script>alert('DB error (u1 prepare): " . addslashes($conn->error) . "');</script>";
                } else {
                    $u1->bind_param("i", $reservationId);
                    $ok1 = $u1->execute();

                    // 2) Update book status back to Available
                    $u2 = $conn->prepare("UPDATE book_inventory SET status = 'Available' WHERE item_id = ?");
                    if (!$u2) {
                        $conn->rollback();
                        echo "<script>alert('DB error (u2 prepare): " . addslashes($conn->error) . "');</script>";
                    } else {
                        $u2->bind_param("i", $itemId);
                        $ok2 = $u2->execute();

                        if ($ok1 && $ok2) {
                            $conn->commit();
                            echo "<script>alert('✅ Book \"" . addslashes($title) . "\" returned successfully! Status changed from " . addslashes($currentStatus) . " to Available.');</script>";
                        } else {
                            $conn->rollback();
                            echo "<script>alert('❌ Failed to return book. " . addslashes($conn->error) . "');</script>";
                        }
                    }
                    $u1->close();
                    if (isset($u2)) $u2->close();
                }
            } else {
                echo "<script>alert('❌ No matching checked out book found for this barcode.');</script>";
            }
            $res->free();
        }
        $stmt->close();
    } else {
        echo "<script>alert('DB error (prepare): " . addslashes($conn->error) . "');</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <title>Return Book | Libraprint</title>
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
      <img src="https://img.icons8.com/color/96/000000/return.png"  class="w-20 h-20"/>

      <h1 class="text-2xl font-semibold">Please scan the book barcode<br>to return the book.</h1>
                  

  <form id="return-form" method="POST" action="" class="flex flex-col items-center space-y-4">
    <input type="text" name="class_no" id="class_no-input"
           placeholder="Scan class_no to return"
           class="px-4 py-2 rounded-full w-64 text-center focus:outline-none focus:ring-2 focus:ring-green-500 bg-green-600 text-white"
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


