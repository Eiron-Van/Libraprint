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
                   SET author=?, title=?, isbn=?, genre=?, property_no=?, unit=?, unit_value=?, accession_no=?, class_no=?, date_acquired=?, remarks=?, status=?, barcode=?
                   WHERE item_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssssssssi", $author, $title, $isbn, $genre, $property_no, $unit, $unit_value, $accession_no, $class_no, $date_acquired, $remarks, $status, $barcode, $id);
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
    <main class="mt-4 flex flex-col p-15">
        <h1 class="text-6xl font-serif text-white text-center p-4">Edit Book</h1>

        <div class="overflow-auto overflow-y-auto max-h-[600px] rounded-lg shadow">
            <table class="w-full">
                <thead class="bg-[#7581a6] border-b-2 border-[#5a6480] text-gray-50 sticky top-0 z-[8]">
                    <tr>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-50">Author</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-100">Title</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-40">ISBN</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-50">Genre</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-28">Property No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Unit</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Unit Value</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Accession No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-23">Class No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Date Acquired</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-10">Remarks</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-center w-40">Status</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-center w-35">Barcode</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-35"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#5a6480]">

                    <form method="post">
                        <tr class="bg-white">
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="author" value="<?php echo $book['author']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="title" value="<?php echo $book['title']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="isbn" value="<?php echo $book['isbn'] ?? ''; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg" placeholder="978-...">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="genre" value="<?php echo $book['genre']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="property_no" value="<?php echo $book['property_no']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="unit" value="<?php echo $book['unit']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="unit_value" value="<?php echo $book['unit_value']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="accession_no" value="<?php echo $book['accession_no']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="class_no" value="<?php echo $book['class_no']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="date" name="date_acquired" value="<?php echo $book['date_acquired']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="remarks" value="<?php echo $book['remarks']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <select name="status" class="w-full shadow px-3 py-1 rounded-lg">
                                    <option <?php if($book['status']=='Available') echo 'selected'; ?>>Available</option>
                                    <option <?php if($book['status']=='Checked Out') echo 'selected'; ?>>Checked Out</option>
                                    <option <?php if($book['status']=='Missing') echo 'selected'; ?>>Missing</option>
                                    <option <?php if($book['status']=='Reserved') echo 'selected'; ?>>Reserved</option>
                                </select>
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="barcode" value="<?php echo $book['barcode']; ?>"
                                class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <button onclick="return confirmStatusChange()" type="submit" class='bg-green-300 px-2 py-1 rounded-2xl inline-block'>Save Changes</button>
                            </td>
                        </tr>
                    </form>
                </tbody>
            </table>
        </div>
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
