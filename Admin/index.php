<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=1.5">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/Admin/js/chart.js"></script>
    <script src="/Admin/js/script.js"></script>

    <title>Libraprint|Admin|Dashboard</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] h-screen bg-fixed accent-emerald-500">
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
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./users"><img class="w-8 m-2" src="../asset/users.png">Users</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./inventory"><img class="w-8 m-2" src="../asset/inventory.png">Inventory</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="./reports"><img class="w-8 m-2" src="../asset/reports.png">Reports</a></li>
                </ul>
            </nav>
        </div>
    </div>

    
    <main class="p-4 text-white">
        <h1 class="text-4xl font-bold mb-8 text-center">📊 LibraPrint Analytics Dashboard</h1>

        <!-- KPI SUMMARY CARDS -->
        <section class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">👥 Total Visitors (This Month)</h2>
                <p id="totalVisitors" class="text-2xl font-bold mt-2">0</p>
            </div>
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">📚 Books Borrowed (This Month)</h2>
                <p class="text-2xl font-bold mt-2">382</p>
            </div>
            <div class="bg-white/10 p-6 rounded-xl text-center shadow-lg">
                <h2 class="text-lg font-semibold">🧾 Active Reservations</h2>
                <p class="text-2xl font-bold mt-2">96</p>
            </div>
        </section>

        <!-- SECTION A: Visitor Analytics -->
        <section class="mb-16">
            <h2 class="text-2xl font-semibold mb-4">🧍 Visitor Analytics</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[20vh]">
                    <h3 class="text-lg font-semibold mb-3">Daily Attendance</h3>
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="bg-white/10 p-6 rounded-xl grid place-content-center h-[20vh]">
                    <h3 class="text-lg font-semibold mb-3">Visitor Purpose Distribution</h3>
                    <canvas id="purposeChart"></canvas>
                </div>
            </div>
        </section>

    </main>
    
</body>
</html>
