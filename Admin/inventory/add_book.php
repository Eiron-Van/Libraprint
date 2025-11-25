<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

 // Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Expect arrays for each field
    $authors = $_POST['author'] ?? [];
    $titles = $_POST['title'] ?? [];
    $genres = $_POST['genre'] ?? [];
    $isbns = $_POST['isbn'] ?? [];
    $propertyNos = $_POST['property_no'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unitValues = $_POST['unit_value'] ?? [];
    $accessionNos = $_POST['accession_no'] ?? [];
    $classNos = $_POST['class_no'] ?? [];
    $dates = $_POST['date_acquired'] ?? [];
    $remarksArr = $_POST['remarks'] ?? [];
    $statuses = $_POST['status'] ?? [];
    $barcodes = $_POST['barcode'] ?? [];
    $locations = $_POST['location'] ?? [];

    $count = max(
        count($authors), count($titles), count($isbns), count($genres), count($propertyNos), count($units), count($unitValues),
        count($accessionNos), count($classNos), count($dates), count($remarksArr), count($statuses), count($barcodes), count($locations)
    );

     $inserted = 0;
     if ($count > 0) {
        $sql = "INSERT INTO book_inventory (author, title, isbn, genre, property_no, unit, unit_value, accession_no, class_no, date_acquired, remarks, status, barcode, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        for ($i = 0; $i < $count; $i++) {
            $author = trim($authors[$i] ?? '');
            $title = trim($titles[$i] ?? '');
            $isbn = trim($isbns[$i] ?? '');
            $genre = trim($genres[$i] ?? '');
            $property_no = trim($propertyNos[$i] ?? '');
            $unit = trim($units[$i] ?? '');
            $unit_value = trim($unitValues[$i] ?? '');
            $accession_no = trim($accessionNos[$i] ?? '');
            $class_no = trim($classNos[$i] ?? '');
            $date_acquired = trim($dates[$i] ?? '');
            $remarks = trim($remarksArr[$i] ?? '');
            $status = trim($statuses[$i] ?? '');
            $barcode = trim($barcodes[$i] ?? '');
            $location = trim($locations[$i] ?? 'Shelved');

            // Minimal validation: require at least title or author
            if ($author === '' && $title === '') {
                continue;
            }

            $stmt->bind_param(
                'ssssssssssssss',
                $author,
                $title,
                $isbn,
                $genre,
                $property_no,
                $unit,
                $unit_value,
                $accession_no,
                $class_no,
                $date_acquired,
                $remarks,
                $status,
                $barcode,
                $location
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
    <main class="mt-2 flex flex-col p-4 md:p-8 max-w-7xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-serif text-white text-center p-4 mb-6">Add Book</h1>

        <form method="post" id="books-form">
            <div class="overflow-y-auto max-h-[70vh] rounded-lg shadow-lg space-y-4 p-4" id="books-container">
                <div class="bg-white rounded-lg shadow-md p-6 book-row border-2 border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Title *</label>
                            <input type="text" name="title[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Author *</label>
                            <input type="text" name="author[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ISBN</label>
                            <input type="text" name="isbn[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]" placeholder="978-...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Genre</label>
                            <input type="text" name="genre[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Barcode</label>
                            <input type="text" name="barcode[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Property No.</label>
                            <input type="text" name="property_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
                            <input type="text" name="unit[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Value</label>
                            <input type="text" name="unit_value[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Date Acquired</label>
                            <input type="date" name="date_acquired[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Accession No.</label>
                            <input type="text" name="accession_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Class No.</label>
                            <input type="text" name="class_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                                <option>Available</option>
                                <option>Checked Out</option>
                                <option>Missing</option>
                                <option>Reserved</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Location</label>
                            <select name="location[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                                <option>Shelved</option>
                                <option>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks</label>
                        <input type="text" name="remarks[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                    </div>

                    <div class="flex justify-end">
                        <button type="button" class='bg-red-400 hover:bg-red-500 px-4 py-2 rounded-lg font-semibold text-white remove-row transition-colors'>Remove Book</button>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 mt-6 justify-center">
                <button type="button" id="add-row" class='bg-[#7581a6] hover:bg-[#5a6480] px-6 py-3 rounded-xl font-semibold text-white transition-colors shadow-lg'>Add More Books</button>
                <button type="submit" class='bg-green-600 hover:bg-green-700 px-6 py-3 rounded-xl font-semibold text-white transition-colors shadow-lg'>Save All</button>
                <a href="/Admin/inventory" class='bg-gray-300 hover:bg-gray-400 px-6 py-3 rounded-xl font-semibold text-gray-800 transition-colors shadow-lg'>Cancel</a>
            </div>
        </form>
    </main>

    <template id="row-template">
        <div class="bg-white rounded-lg shadow-md p-6 book-row border-2 border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Author *</label>
                    <input type="text" name="author[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ISBN</label>
                    <input type="text" name="isbn[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]" placeholder="978-...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Genre</label>
                    <input type="text" name="genre[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Property No.</label>
                    <input type="text" name="property_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
                    <input type="text" name="unit[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Value</label>
                    <input type="text" name="unit_value[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date Acquired</label>
                    <input type="date" name="date_acquired[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Accession No.</label>
                    <input type="text" name="accession_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Class No.</label>
                    <input type="text" name="class_no[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        <option>Available</option>
                        <option>Checked Out</option>
                        <option>Missing</option>
                        <option>Reserved</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Location</label>
                    <select name="location[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
                        <option>Shelved</option>
                        <option>Archived</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks</label>
                <input type="text" name="remarks[]" class="w-full shadow px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#7581a6]">
            </div>

            <div class="flex justify-end">
                <button type="button" class='bg-red-400 hover:bg-red-500 px-4 py-2 rounded-lg font-semibold text-white remove-row transition-colors'>Remove Book</button>
            </div>
        </div>
    </template>

    <script>
        const addRowBtn = document.getElementById('add-row');
        const booksContainer = document.getElementById('books-container');
        const rowTemplate = document.getElementById('row-template');

        addRowBtn.addEventListener('click', () => {
            const clone = rowTemplate.content.cloneNode(true);
            booksContainer.appendChild(clone);
        });

        booksContainer.addEventListener('click', (e) => {
            if (e.target && e.target.classList.contains('remove-row')) {
                const bookRow = e.target.closest('.book-row');
                if (!bookRow) return;
                // Keep at least one row
                if (booksContainer.querySelectorAll('.book-row').length > 1) {
                    bookRow.remove();
                } else {
                    alert('You must keep at least one book entry.');
                }
            }
        });
    </script>
</body>
</html>


