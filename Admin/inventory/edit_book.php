<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

// Get book ID
$id = $_GET['item_id'] ?? null;
if (!$id) {
    die("Invalid book ID.");
}

// Fetch book details
$sql = "SELECT * FROM book_inventory WHERE item_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    die("Book not found.");
}

function generateSampleIsbnFromId(int $itemId): string {
    $id = max(1, $itemId);
    $padded = str_pad((string)$id, 6, '0', STR_PAD_LEFT);
    $group = substr($padded, 0, 3);
    $publisher = substr($padded, 3);
    $check = ($id % 9) + 1;
    return "978-1-$group-$publisher-$check";
}

$prefilledIsbn = $book['isbn'] ?? '';
$isbnSampleUsed = false;
if (trim($prefilledIsbn) === '') {
    $prefilledIsbn = generateSampleIsbnFromId((int)$book['item_id']);
    $isbnSampleUsed = true;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $author = $_POST['author'];
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $property_no = $_POST['property_no'];
    $unit = $_POST['unit'];
    $unit_value = $_POST['unit_value'];
    $accession_no = $_POST['accession_no'];
    $class_no = $_POST['class_no'];
    $date_acquired = $_POST['date_acquired'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];
    $barcode = $_POST['barcode'];
    $location = $_POST['location'] ?? 'Shelved';

    // --- Additional logic for status restrictions ---

    // Check current status in DB
    $current_status = $book['status'];

    // If the new status is Reserved
    if ($status == 'Reserved') {
        // Check if it already exists in reservation table
        $check_res = $conn->prepare("SELECT * FROM reservation WHERE item_id = ?");
        $check_res->bind_param("i", $id);
        $check_res->execute();
        $res_result = $check_res->get_result();

        if ($res_result->num_rows > 0) {
            // Book is already reserved → remove reservation and reset to Available
            $del_res = $conn->prepare("DELETE FROM reservation WHERE item_id = ?");
            $del_res->bind_param("i", $id);
            $del_res->execute();

            // Automatically reset status
            $status = 'Available';
        }
    }

    // If the book is currently Checked Out and admin tries to change it
    if ($current_status == 'Checked Out' && $status != 'Checked Out') {
        echo "
        <script>
            if (!confirm('⚠️ This book is currently checked out. Changing the status may cause data mismatch. Do you still want to continue?')) {
                window.history.back();
            }
        </script>";
    }

    // Automatically reset Reserved books that expired
    $conn->query("
        UPDATE book_inventory b
        JOIN reservation r ON b.item_id = r.item_id
        SET b.status = 'Available'
        WHERE DATE(r.date_reserved) < CURDATE()
    ");
    $conn->query("DELETE FROM reservation WHERE DATE(date_reserved) < CURDATE()");


    $update_sql = "UPDATE book_inventory 
                   SET author=?, title=?, isbn=?, genre=?, property_no=?, unit=?, unit_value=?, accession_no=?, class_no=?, date_acquired=?, remarks=?, status=?, barcode=?, location=?
                   WHERE item_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssssssssssi", $author, $title, $isbn, $genre, $property_no, $unit, $unit_value, $accession_no, $class_no, $date_acquired, $remarks, $status, $barcode, $location, $id);
    $stmt->execute();

    header("Location: https://libraprintlucena.com/Admin/inventory");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/style.css?v=1.5">

    <title>Libraprint|Admin|Inventory|Edit</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed overflow-hidden min-h-screen">
    <main class="mt-4 flex flex-col p-4 md:p-8 max-w-7xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-serif text-white text-center p-4 mb-6">Edit Book</h1>

        <form method="post" class="bg-white rounded-lg shadow-lg p-6 md:p-8 border-2 border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Author *</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ISBN</label>
                    <input type="text" name="isbn" value="<?php echo htmlspecialchars($prefilledIsbn); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]" placeholder="978-...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Genre</label>
                    <input type="text" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="<?php echo htmlspecialchars($book['barcode']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Property No.</label>
                    <input type="text" name="property_no" value="<?php echo htmlspecialchars($book['property_no']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
                    <input type="text" name="unit" value="<?php echo htmlspecialchars($book['unit']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Value</label>
                    <input type="text" name="unit_value" value="<?php echo htmlspecialchars($book['unit_value']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date Acquired</label>
                    <input type="date" name="date_acquired" value="<?php echo htmlspecialchars($book['date_acquired']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Accession No.</label>
                    <input type="text" name="accession_no" value="<?php echo htmlspecialchars($book['accession_no']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Class No.</label>
                    <input type="text" name="class_no" value="<?php echo htmlspecialchars($book['class_no']); ?>"
                    class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        <option <?php if($book['status']=='Available') echo 'selected'; ?>>Available</option>
                        <option <?php if($book['status']=='Checked Out') echo 'selected'; ?>>Checked Out</option>
                        <option <?php if($book['status']=='Missing') echo 'selected'; ?>>Missing</option>
                        <option <?php if($book['status']=='Reserved') echo 'selected'; ?>>Reserved</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Location</label>
                    <select name="location" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        <option value="Shelved" <?php if(($book['location'] ?? 'Shelved')=='Shelved') echo 'selected'; ?>>Shelved</option>
                        <option value="Archived" <?php if(($book['location'] ?? 'Shelved')=='Archived') echo 'selected'; ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks</label>
                <input type="text" name="remarks" value="<?php echo htmlspecialchars($book['remarks']); ?>"
                class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
            </div>

            <div class="flex flex-wrap gap-3 justify-center mt-6">
                <button onclick="return confirmStatusChange()" type="submit" class='bg-green-600 hover:bg-green-700 px-6 py-3 rounded-xl font-semibold text-white transition-colors shadow-lg'>Save Changes</button>
                <a href="/Admin/inventory" class='bg-gray-300 hover:bg-gray-400 px-6 py-3 rounded-xl font-semibold text-gray-800 transition-colors shadow-lg'>Cancel</a>
            </div>
        </form>
        <script>
            function confirmStatusChange() {
                const status = document.querySelector('select[name="status"]').value;
                const currentStatus = "<?php echo $book['status']; ?>";

                if (currentStatus === "Checked Out" && status !== "Checked Out") {
                    return confirm("⚠️ This book is currently checked out. Are you sure you want to change its status?");
                }
                return true;
            }
        </script>
</body>
</html>
