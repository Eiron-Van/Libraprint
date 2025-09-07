<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/style.css?v=1.5">
    <script src="/Admin/script.js"></script>

    <title>Libraprint|Admin|Inventory</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed overflow-hidden min-h-screen">
    <header class="bg-gray-900 text-white sticky top-0 z-10">
        <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
                <div>
                    <button id="menu" class="text-[30px] focus:outline-none cursor-pointer hover:opacity-60 active:opacity-40 transition-opacity duration-200 p-2">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div id="brand" class="flex flex-row items-center space-x-1.5">
                    <img src="/asset/fingerprint.png" class="w-15">
                    <a class="text-2xl font-serif pt-2" href="https://libraprintlucena.com">Libraprint</a>
                </div>
            </div>
        </section>
    </header>

    <div class="flex">
        <div id ="side-menu" class="hidden flex-col bg-[#F4F4F4] w-fit top-19 fixed z-[9] inset-y-0">
            <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
                <ul>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin"><img class="w-8 m-2" src="/asset/dashboard.png">Dashboard</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin/users"><img class="w-8 m-2" src="/asset/users.png">Users</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 bg-slate-200 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="/asset/inventory.png">Inventory</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/Admin/reports"><img class="w-8 m-2" src="/asset/reports.png">Reports</a></li>
                    <!-- <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/setting.png">Settings</a></li> -->
                </ul>
            </nav>
        </div>
    </div>

    <main class="mt-6 flex flex-col p-5">
        <h1 class="text-6xl font-serif text-white text-center p-4">Book Inventory Management</h1>

        <div class="overflow-auto rounded-2xl shadow">
            <table class="w-full">
                <thead class="bg-[#7581a6] border-b-2 border-[#5a6480] text-gray-50">
                    <tr>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Author</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left">Title</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-28">Property No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-5">Unit</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-25">Unit Value</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Accession No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-23">Class No.</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-30">Date Acquired</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-10">Remarks</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-15">Status</th>
                        <th class="p-3 text-sm font-semibold tracking-wide text-left w-35"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#5a6480]">
                    <?php
                    include 'connection.php';

                    // read all row from database
                    $sql = "SELECT * FROM book_inventory";
                    $result = $conn->query($sql);

                    if (!$result){
                        die("Invalid query: " . $conn->error);
                    }

                    if (!$result) {
                        die("Query failed: " . $conn->error);
                    } else {
                        echo "Rows found: " . $result->num_rows . "<br>";
                    }

                    if ($result->num_rows === 0) {
                        echo "<tr><td colspan='11' class='text-center text-white'>No books found in inventory.</td></tr>";
                    }


                    // read data of each row
                    while($row = $result->fetch_assoc()){
                        echo"
                        <tr class='bg-white'>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['author']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['title']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['property_no']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['unit']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['unit_value']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['accession_no']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['class_no']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['date_acquired']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['remarks']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>".$row['status']."</td>
                            <td class='p-3 text-sm text-gray-700 whitespace-nowrap'>
                                <button class='bg-amber-500 px-2 py-1 rounded-2xl inline-block'>Edit</button>
                                <button class='bg-amber-500 px-2 py-1 rounded-2xl inline-block'>Delete</button>
                            </td>
                        </tr>";
                    }
                    ?>






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
                            <button class="bg-amber-500 px-2 py-1 rounded-2xl inline-block">Edit</button>
                            <button class="bg-amber-500 px-2 py-1 rounded-2xl inline-block">Delete</button>
                        </td>
                    </tr>
                    <tr class="bg-white">
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">Eliot, George</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">Silas Marner</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap "></td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">1 cp</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">6.95</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">4</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">El42si</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">1984-02-24</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">RB</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap ">Available</td>
                        <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                            <button class="bg-yellow-300 px-2 py-1 rounded-2xl inline-block">Edit</button>
                            <button class="bg-red-300 px-2 py-1 rounded-2xl inline-block">Delete</button>
                        </td>
                    </tr> -->
                </tbody>
    
            </table>
        </div>
    </main>
</body>
</html>
