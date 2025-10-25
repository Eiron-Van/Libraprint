<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=1.5">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/Admin/js/return.js"></script>
    <script src="/Admin/js/apriori.js"></script>
    <script src="/Admin/js/chart.js"></script>
    <script src="/Admin/js/script.js"></script>

    <title>Libraprint|Admin|Dashboard</title>

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
                    <a class="text-2xl font-serif" href="https://libraprintlucena.com/Admin">Libraprint</a>
                </div>
            </div>
        </section>
    </header>

    <!-- Side Menu -->
    <div class="flex">
        <div id ="side-menu" class="hidden fixed flex-col bg-[#F4F4F4] w-fit z-10">
            <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-76.9886px)] justify-between">
                <ul>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 bg-slate-200 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/dashboard.png">Dashboard</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./inventory"><img class="w-8 m-2" src="../asset/inventory.png">Inventory</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./users"><img class="w-8 m-2" src="../asset/users.png">Users</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./activity-logs"><img class="w-8 m-2" src="../asset/reports.png">Activity Logs</a></li>
                    <button id="returnBookBtn" class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center"><img class="w-8 m-2" src="../asset/return.png">Return</button>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="https://libraprintlucena.com/Registration/"><img class="w-8 m-2" src="../asset/registration.png">Registration</a></li>                    
                </ul>
            </nav>
        </div>
    </div>

    
    <main class="p-8 text-white">
        <h1 class="text-4xl font-bold mb-8 text-center">📊 LibraPrint Analytics Dashboard</h1>

        <!-- KPI SUMMARY CARDS -->
        <section class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">👥 Total Visitors (This Month)</h2>
                <p id="totalVisitors" class="text-2xl font-bold mt-2">0</p>
            </div>
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">📚 Books Borrowed (This Month)</h2>
                <p class="text-2xl font-bold mt-2">0</p>
            </div>
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">🧾 Active Reservations</h2>
                <p class="text-2xl font-bold mt-2">0</p>
            </div>
        </section>

        <!-- SECTION A: Visitor Analytics -->
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">🧍 Visitor Analytics</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Daily Attendance</h3>
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Visitor Purpose Distribution</h3>
                    <canvas id="purposeChart"></canvas>
                </div>
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Visitor Age Group Distribution</h3>
                    <canvas id="ageGroupChart"></canvas>
                </div>
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Gender Distribution</h3>
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Monthly Attendance Summary</h3>
                    <canvas id="monthlyAttendanceChart"></canvas>
                </div>
            </div>
        </section>

        <!-- SECTION B: Book Usage Analytics -->
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">📚 Book Usage Analytics</h2>
            
            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">Total Books in Inventory</h2>
                    <p id="totalBooks" class="text-2xl font-bold mt-2">0</p>
                </div>
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">Read Books (This Month)</h2>
                    <p id="readBooks" class="text-2xl font-bold mt-2">0</p>
                </div>
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">Book Usage Rate</h2>
                    <p id="usageRate" class="text-2xl font-bold mt-2">0%</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white/10 p-6 rounded-xl h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Most Read Books (Top 10)</h3>
                    <canvas id="topBooksChart"></canvas>
                </div>

                <div class="bg-white/10 p-6 rounded-xl h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Most Read Genres</h3>
                    <canvas id="genreChart"></canvas>
                </div>
            </div>
        </section>

        <!-- SECTION C: Reading Trends -->
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">📈 Reading Trends & Engagement</h2>

            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">📅 Average Monthly Reads</h2>
                    <p id="avgMonthlyReads" class="text-2xl font-bold mt-2">0</p>
                </div>
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">📚 Total Reads</h2>
                    <p id="readCount" class="text-2xl font-bold mt-2">0</p>
                </div>
                <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                    <h2 class="text-lg font-semibold">🗂 Borrowed</h2>
                    <p id="borrowCount" class="text-2xl font-bold mt-2">0</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white/10 p-6 rounded-xl h-[70vh] place-content-center">
                    <h3 class="text-lg font-semibold mb-3">Monthly Reading Trend</h3>
                    <canvas id="monthlyTrendChart"></canvas>
                </div>

                <div class="bg-white/10 p-6 rounded-xl h-[70vh] place-content-center">
                    <h3 class="text-lg font-semibold mb-3">Quarterly Comparison</h3>
                    <canvas id="quarterlyChart"></canvas>
                </div>

                <div class=" bg-white/10 p-6 rounded-xl h-[70vh]">
                    <h3 class="text-lg font-semibold mb-3">Read vs Borrow Ratio</h3>
                    <canvas id="readBorrowChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Section D: Apriori Association -->
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">🧠 Apriori Association Insights</h2>
            <div class="bg-white/10 p-6 rounded-xl overflow-x-auto">
                <table class="w-full text-white border border-gray-500">
                <thead>
                    <tr class="bg-white/20">
                    <th class="px-3 py-2 text-left">Reading Relationship</th>
                    <th class="px-3 py-2 text-left">Frequency</th>
                    <th class="px-3 py-2 text-left">Confidence</th>
                    </tr>
                </thead>
                <tbody id="aprioriTableBody"></tbody>
                </table>
            </div>
            <div class="bg-white/10 p-6 rounded-xl mt-8 h-[70vh]">
                <h3 class="text-lg font-semibold mb-3">Genre Association Graph</h3>
                <canvas id="aprioriGraph"></canvas>
            </div>
        </section>
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">🧠 Apriori Association Insights (Per Age Group)</h2>
            <div id="aprioriAgeGroups" class="h-[70vh] overflow-auto border border-white/50"></div>
        </section>

    </main>

    <!-- 📘 Return Book Overlay -->
    <div id="returnOverlay" class="fixed hidden inset-0 bg-gray-900/80 items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96 shadow-lg text-center relative">
            <h2 class="text-3xl font-bold mb-4">Return Book</h2>
            <input type="text" id="returnBarcode" placeholder="Scan or type barcode..." class="border rounded-md p-2 w-full mb-4 text-center focus:outline-none">

            <div id="returnSuccess" class="hidden bg-green-100 text-green-800 p-2 rounded-md my-2">
                ✅ Book successfully returned!
            </div>

            <div id="returnError" class="hidden bg-red-100 text-red-800 p-2 rounded-md my-2">
                ❌ Book not found or already available.
            </div>

            <button id="closeReturnBtn" class="absolute top-2 right-3 text-gray-600 hover:text-black">✕</button>
        </div>
    </div>
    
    <!-- Receipt Overlay -->
    <div id="receiptOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-51">
        <div class="bg-white p-6 rounded-lg w-96 text-center shadow-lg">
            <h2 class="text-xl font-bold mb-3">Library Billing Receipt</h2>
            <p><strong>Borrower:</strong> <span id="receiptBorrower"></span></p>
            <p><strong>Book Title:</strong> <span id="receiptBook"></span></p>
            <p><strong>Days Overdue:</strong> <span id="receiptDays"></span></p>
            <p><strong>Total Penalty:</strong> ₱<span id="receiptPenalty"></span></p>
            <p class="text-sm mt-2 text-gray-500">Date: <span id="receiptDate"></span></p>

            <div class="mt-4 space-x-2">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded">Print</button>
            <button id="closeReceiptBtn" class="bg-gray-400 px-4 py-2 rounded">Close</button>
            </div>
        </div>
    </div>


    
</body>
</html>
