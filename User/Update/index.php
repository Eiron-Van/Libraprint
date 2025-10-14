<?php
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
  session_id($_GET['PHPSESSID']);
}
session_start();
include("../connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login");
    exit();
}
// Get user details from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, gender, address, birthday, contact_number FROM users WHERE user_id = ?");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
    <link rel="icon" href="../../asset/fingerprint.icon" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../../style.css">
      <script>
      // Prevent going back to this page after logout
      window.history.pushState(null, "", window.location.href);
      window.onpopstate = function () {
        window.history.pushState(null, "", window.location.href);
      };
  </script>
</head>
<body class="flex flex-col justify-center items-center min-h-screen bg-gradient-to-b from-[#304475] to-[#0c0c0c]">
    <h1 class="text-2xl sm:text-3xl md:text-4xl font-serif text-center text-white font-bold">Update Profile</h1>
    <form id="updateForm" action="update_profile.php" method="post" class="flex flex-col gap-4 w-full p-5 md:w-1/2 lg:w-1/3">
      <input type="text" name="firstname" id="firstname" placeholder="Firstname" required value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full bg-white rounded-xl text-black px-4 py-2">
      <input type="text" name="lastname" id="lastname" placeholder="Lastname" required value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full bg-white rounded-xl text-black px-4 py-2">
      <select name="gender" id="gender" placeholder="Gender" class="w-full bg-white rounded-xl text-black px-4 py-2">
        <?php
          $genders = ["Male", "Female", "Lesbian", "Gay", "Bisexual", "Transgender", "Queer/Questioning", "Other"];
          foreach ($genders as $g) {
              $selected = ($user['gender'] === $g) ? 'selected' : '';
              echo "<option value='$g' $selected>$g</option>";
          }
        ?>
      </select>
      <input type="date" name="birthday" id="birthday" placeholder="Birthday" required value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" class="w-full bg-white rounded-xl text-black px-4 py-2">
      <input type="text" name="address" id="address" placeholder="Address" required value="<?= htmlspecialchars($user['address'] ?? '') ?>" class="w-full bg-white rounded-xl text-black px-4 py-2">
      <input type="tel" name="contact_number" id="contact_number" pattern="^09\d{9}$" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11" placeholder="Contact Number" required value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" class="w-full bg-white rounded-xl text-black px-4 py-2">
      <button type="submit" class="bg-blue-600 text-white py-2 rounded-xl hover:bg-blue-700 transition duration-200">Update Details</button>
    </form>

</body>
</html>