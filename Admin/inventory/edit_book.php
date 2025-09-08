<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    $property_no = $_POST['property_no'];
    $unit = $_POST['unit'];
    $unit_value = $_POST['unit_value'];
    $accession_no = $_POST['accession_no'];
    $class_no = $_POST['class_no'];
    $date_acquired = $_POST['date_acquired'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];

    $update_sql = "UPDATE book_inventory 
                   SET author=?, title=?, property_no=?, unit=?, unit_value=?, accession_no=?, class_no=?, date_acquired=?, remarks=?, status=?
                   WHERE item_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssssssi", $author, $title, $property_no, $unit, $unit_value, $accession_no, $class_no, $date_acquired, $remarks, $status, $id);
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
                        <th class="p-3 text-sm font-semibold tracking-wide text-left">Title</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-28">Property No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Unit</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Unit Value</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Accession No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-23">Class No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Date Acquired</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-10">Remarks</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-center w-40">Status</th>
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
                            </td">
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <button type="submit" class='bg-green-300 px-2 py-1 rounded-2xl inline-block'>Save Changes</button>
                            </td>
                        </tr>
                    </form>



                    <!-- <tr class="bg-white">
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">Carnegie, Dale</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">How to win friends and influence people</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap "></td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">1 cp</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">17.95</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">1</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">177.6</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">1984-02-24</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">RB</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">Available</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                            <a href="" class='bg-green-300 px-2 py-1 rounded-2xl inline-block'>Edit</a>
                            <a href="" class='bg-red-300 px-2 py-1 rounded-2xl inline-block'>Delete</a>
                        </td>
                    </tr> -->
                </tbody>
            </table>
        </div>

    
</body>
</html>
