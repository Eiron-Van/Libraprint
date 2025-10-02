<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';

 // Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Expect arrays for each field
    $authors = $_POST['author'] ?? [];
    $titles = $_POST['title'] ?? [];
    $propertyNos = $_POST['property_no'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unitValues = $_POST['unit_value'] ?? [];
    $accessionNos = $_POST['accession_no'] ?? [];
    $classNos = $_POST['class_no'] ?? [];
    $dates = $_POST['date_acquired'] ?? [];
    $remarksArr = $_POST['remarks'] ?? [];
    $statuses = $_POST['status'] ?? [];

    $count = max(
        count($authors), count($titles), count($propertyNos), count($units), count($unitValues),
        count($accessionNos), count($classNos), count($dates), count($remarksArr), count($statuses)
    );

     $inserted = 0;
     if ($count > 0) {
        $sql = "INSERT INTO book_inventory (author, title, property_no, unit, unit_value, accession_no, class_no, date_acquired, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        for ($i = 0; $i < $count; $i++) {
            $author = trim($authors[$i] ?? '');
            $title = trim($titles[$i] ?? '');
            $property_no = trim($propertyNos[$i] ?? '');
            $unit = trim($units[$i] ?? '');
            $unit_value = trim($unitValues[$i] ?? '');
            $accession_no = trim($accessionNos[$i] ?? '');
            $class_no = trim($classNos[$i] ?? '');
            $date_acquired = trim($dates[$i] ?? '');
            $remarks = trim($remarksArr[$i] ?? '');
            $status = trim($statuses[$i] ?? '');

            // Minimal validation: require at least title or author
            if ($author === '' && $title === '') {
                continue;
            }

            $stmt->bind_param(
                'ssssssssss',
                $author,
                $title,
                $property_no,
                $unit,
                $unit_value,
                $accession_no,
                $class_no,
                $date_acquired,
                $remarks,
                $status
            );
             if ($stmt->execute()) {
                 $inserted++;
             }
        }
        $stmt->close();
    }

     $qs = $inserted > 0 ? ('?added=' . $inserted) : '';
     header('Location: https://libraprintlucena.com/Admin/inventory' . $qs);
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

    <title>Libraprint|Admin|Inventory|Add</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed overflow-hidden min-h-screen">
    <main class="mt-2 flex flex-col p-15">
        <h1 class="text-6xl font-serif text-white text-center p-4">Add Book</h1>

        <form method="post">
            <div class="overflow-auto overflow-y-auto max-h-[600px] rounded-lg shadow">
                <table class="w-full" id="books-table">
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
                    <tbody class="divide-y divide-[#5a6480]" id="rows">
                        <tr class="bg-white book-row">
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="author[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="title[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="property_no[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="unit[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="unit_value[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="accession_no[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="class_no[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="date" name="date_acquired[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                                <input type="text" name="remarks[]" class="w-full shadow px-3 py-1 rounded-lg">
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <select name="status[]" class="w-full shadow px-3 py-1 rounded-lg">
                                    <option>Available</option>
                                    <option>Checked Out</option>
                                    <option>Missing</option>
                                    <option>Reserved</option>
                                </select>
                            </td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <button type="button" class='bg-red-300 px-2 py-1 rounded-2xl inline-block remove-row'>Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="button" id="add-row" class='bg-[#7581a6] px-3 py-2 rounded-xl font-semibold text-white'>Add More Books</button>
                <button type="submit" class='bg-green-600 hover:bg-green-700 px-4 py-2 rounded-xl font-semibold text-white'>Save All</button>
                <a href="/Admin/inventory" class='bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-xl font-semibold text-gray-800'>Cancel</a>
            </div>
        </form>
    </main>

    <template id="row-template">
        <tr class="bg-white book-row">
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="author[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="title[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="property_no[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="unit[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="unit_value[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="accession_no[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="class_no[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="date" name="date_acquired[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">
                <input type="text" name="remarks[]" class="w-full shadow px-3 py-1 rounded-lg">
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                <select name="status[]" class="w-full shadow px-3 py-1 rounded-lg">
                    <option>Available</option>
                    <option>Checked Out</option>
                    <option>Missing</option>
                    <option>Reserved</option>
                </select>
            </td>
            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                <button type="button" class='bg-red-300 px-2 py-1 rounded-2xl inline-block remove-row'>Remove</button>
            </td>
        </tr>
    </template>

    <script>
        const addRowBtn = document.getElementById('add-row');
        const rowsTbody = document.getElementById('rows');
        const rowTemplate = document.getElementById('row-template');

        addRowBtn.addEventListener('click', () => {
            const clone = rowTemplate.content.cloneNode(true);
            rowsTbody.appendChild(clone);
        });

        rowsTbody.addEventListener('click', (e) => {
            if (e.target && e.target.classList.contains('remove-row')) {
                const tr = e.target.closest('tr');
                if (!tr) return;
                // Keep at least one row
                if (rowsTbody.querySelectorAll('tr.book-row').length > 1) {
                    tr.remove();
                }
            }
        });
    </script>
</body>
</html>


