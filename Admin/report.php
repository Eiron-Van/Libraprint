<?php
require_once __DIR__ . '/../inc/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../asset/fingerprint.ico" type="image/x-icon">
  <link rel="stylesheet" href="../style.css?v=1.5">
  <title>LibraPrint | Generate Report</title>
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] text-white h-screen flex flex-col items-center justify-center">

  <section class="bg-white/10 p-8 rounded-xl shadow-xl max-w-xl w-full">
    <h1 class="text-3xl font-bold mb-6 text-center">📄 Monthly Report Generator</h1>

    <form id="reportForm" method="POST" action="/Admin/api/generate_report.php" class="flex flex-col items-center space-y-6 text-white">
      <div class="flex flex-col items-start w-full">
        <label for="month" class="mb-2 text-lg">Select Month to Generate:</label>
        <input type="month" id="month" name="month" required class="text-white p-2 rounded w-full">
      </div>

      <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-5 py-3 rounded-lg font-semibold text-lg transition-all">
        Generate PDF Report
      </button>
    </form>

    <div class="mt-6 text-sm text-gray-300 text-center">
      The report will automatically compile visitor, gender, and reading data for the selected month.
    </div>
  </section>

</body>
</html>
