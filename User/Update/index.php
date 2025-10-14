<!-- <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
  session_id($_GET['PHPSESSID']);
}
session_start();
include("../../connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login");
    exit();
}
// Get user details from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, contact_number FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// If user not found (safety check)
if (!$user) {
    echo "<script>alert('User not found. Please log in again.'); window.location.href='../Login';</script>";
    exit();
}
?> -->

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Information</title>
    <link rel="icon" href="../../asset/fingerprint.icon" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="../../style.css" />
    <script>
      // Prevent going back to this page after logout
      window.history.pushState(null, "", window.location.href);
      window.onpopstate = function () {
        window.history.pushState(null, "", window.location.href);
      };
    </script>
  </head>
  <body class="min-h-screen bg-gradient-to-b from-[#304475] to-[#0c0c0c] flex flex-col">
    <!-- Centered Main Form -->
    <div class="flex-grow flex items-center justify-center px-4">
      <main class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 text-center">
        <!-- Logo and Heading -->
        <div class="mb-8">
          <img
            src="https://cdn-icons-png.flaticon.com/512/747/747376.png"
            alt="Form Icon"
            class="w-16 h-16 mx-auto"
          />
          <h1 class="text-2xl font-bold text-[#1c2a64] mt-4">Update information</h1>
        </div>

      <!-- Form -->
      <form id="updateForm" action="update_profile.php" method="post" class="space-y-4">
        <!-- Email -->
        <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
          <div class="flex justify-between text-sm text-gray-500 mb-1">
            <span>Email</span>
          </div>
          <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="w-full px-2 py-3 text-sm font-medium text-center">
        </div>

        <!-- Mobile Number -->
        <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
          <div class="flex justify-between text-sm text-gray-500 mb-1">
            <span>Mobile number</span>
          </div>
          <input type="tel" name="contact_number" id="contact_number" pattern="^09\d{9}$" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11" required value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" class="w-full px-2 py-3 text-sm font-medium text-center">
        </div>

          <!-- Submit Button -->
          <button type="submit" class="bg-blue-600 text-white py-2 px-3 rounded-xl hover:bg-blue-700 transition duration-200">Update Details</button>

        </form>
      </main>
    </div>
  </body>
</html>
