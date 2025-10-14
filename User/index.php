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

// Get user data from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Generate initials from first_name and last_name
    $first_initial = strtoupper(substr($user['first_name'], 0, 1));
    $last_initial = strtoupper(substr($user['last_name'], 0, 1));
    $initials = $first_initial . $last_initial;
    
    // Generate dynamic colors based on initials
    $avatar_colors = generateAvatarColors($initials);
    
    // Format full name
    $full_name = $user['first_name'] . ' ' . $user['last_name'];
    
    // Format birthday
    $birthday = date('F j, Y', strtotime($user['birthday']));
} else {
    // User not found, redirect to login
    header("Location: ../Login");
    exit();
}

$stmt->close();

// Function to generate avatar colors based on initials
function generateAvatarColors($initials) {
    // Create a hash from initials for consistent colors
    $hash = crc32($initials);
    
    // Define pleasant color palettes (avoiding very dark colors)
    $colorPalettes = [
        // Blue tones
        ['bg' => '#3B82F6', 'text' => '#FFFFFF'], // Blue-500
        ['bg' => '#60A5FA', 'text' => '#FFFFFF'], // Blue-400
        ['bg' => '#93C5FD', 'text' => '#1E40AF'], // Blue-300
        
        // Green tones
        ['bg' => '#10B981', 'text' => '#FFFFFF'], // Emerald-500
        ['bg' => '#34D399', 'text' => '#064E3B'], // Emerald-400
        ['bg' => '#6EE7B7', 'text' => '#064E3B'], // Emerald-300
        
        // Purple tones
        ['bg' => '#8B5CF6', 'text' => '#FFFFFF'], // Violet-500
        ['bg' => '#A78BFA', 'text' => '#4C1D95'], // Violet-400
        ['bg' => '#C4B5FD', 'text' => '#4C1D95'], // Violet-300
        
        // Pink tones
        ['bg' => '#EC4899', 'text' => '#FFFFFF'], // Pink-500
        ['bg' => '#F472B6', 'text' => '#831843'], // Pink-400
        ['bg' => '#F9A8D4', 'text' => '#831843'], // Pink-300
        
        // Orange tones
        ['bg' => '#F59E0B', 'text' => '#FFFFFF'], // Amber-500
        ['bg' => '#FBBF24', 'text' => '#92400E'], // Amber-400
        ['bg' => '#FCD34D', 'text' => '#92400E'], // Amber-300
        
        // Teal tones
        ['bg' => '#14B8A6', 'text' => '#FFFFFF'], // Teal-500
        ['bg' => '#5EEAD4', 'text' => '#134E4A'], // Teal-400
        ['bg' => '#99F6E4', 'text' => '#134E4A'], // Teal-300
        
        // Indigo tones
        ['bg' => '#6366F1', 'text' => '#FFFFFF'], // Indigo-500
        ['bg' => '#818CF8', 'text' => '#312E81'], // Indigo-400
        ['bg' => '#A5B4FC', 'text' => '#312E81'], // Indigo-300
        
        // Rose tones
        ['bg' => '#F43F5E', 'text' => '#FFFFFF'], // Rose-500
        ['bg' => '#FB7185', 'text' => '#881337'], // Rose-400
        ['bg' => '#FDA4AF', 'text' => '#881337'], // Rose-300
    ];
    
    // Use hash to select a consistent color for these initials
    $colorIndex = abs($hash) % count($colorPalettes);
    return $colorPalettes[$colorIndex];
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
    <link rel="icon" href="asset/fingerprint.icon" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <script src="../script.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-[#304475] to-[#0c0c0c]">
    <!-- Header-->
  <header class="bg-gray-900 text-white sticky top-0 z-60">
    <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
      <div class="flex items-center mx-4 space-x-6">
        <button id="menu" class="text-[30px] p-2 hover:opacity-60 z-40 active:opacity-40">
          <i class="fas fa-bars"></i> 
        </button>
        <div class="flex items-center space-x-1.5">
          <i class="fas fa-fingerprint text-[30px]"></i>
          <a class="text-2xl font-serif" href="https://libraprintlucena.com">Libraprint</a>
        </div>
      </div>
      <div class="flex items-center mx-4 space-x-6">
        <div class="hidden sm:block">
          <nav>
            <ul class="md:flex md:flex-col lg:flex-row md:text-sm sm:text-center space-x-6">
              <li><a href="https://libraprintlucena.com" class="hover:opacity-60 transition-opacity duration-200">Home</a></li>
               <li><a href="../AboutUs" class="hover:opacity-60 transition-opacity duration-200">About Us</a></li>
               <li><a href="../ContactUs" class="hover:opacity-60 transition-opacity duration-200">Contact Us</a></li>
              </ul>
          </nav>
        </div>
        <div>
          <a href="../Login/logout.php" class="hidden lg:block bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded">Logout</a>
          <a href="../Login/logout.php" class="block lg:hidden active:opacity-60 transition-opacity duration-200 px-2 py-1 rounded"><i class="fas fa-sign-out-alt text-3xl"></i></a>
        </div>

      
      </div>
    </section>
  </header>

  <!--Side Navigation--> 
  <div id ="side-menu" class="hidden fixed top-[84px] left-0 bg-[#F4F4F4] w-fit z-50 h-[calc(100vh-76.9886px)]">
    <nav class="flex flex-col bg-[#F4F4F4] h-full justify-between">
        <ul>
            <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/profile.png">Profile</a></li>
            <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../Reservation"><img class="w-8 m-2" src="../asset/book_r.png">Book Reservation</a></li>
            <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../Borrowing"><img class="w-8 m-2" src="../asset/book_b.png">Book Borrowing</a></li>
            <!-- <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="../asset/setting.png">Settings</a></li> -->
            <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../AboutUs"><img class="w-8 m-2" src="../asset/about_us.png">About Us</a></li>
            <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="../ContactUs"><img class="w-8 m-2" src="../asset/contact_us.png">Contact Us</a></li>
        </ul>
    </nav>
  </div>
  <!--Profile content-->
  <main class="flex flex-col items-center justify-center px-4 py-12 pt-[6.5rem] min-h-[calc(100vh-84px)]">

      <!-- Profile Card Container -->
      <div class="relative w-full max-w-2xl xl:max-w-4xl bg-white text-black rounded-2xl shadow-lg pt-20 pb-8 px-8 text-center">
        <!-- Avatar Circle -->
        <div class="absolute -top-14 left-1/2 transform -translate-x-1/2 w-28 h-28 flex items-center justify-center text-4xl font-bold rounded-full border-4 border-white shadow" 
             style="background-color: <?php echo $avatar_colors['bg']; ?>; color: <?php echo $avatar_colors['text']; ?>;">
          <?php echo $initials; ?>
        </div>

        <!-- Profile Info -->
        <h2 class="text-3xl font-semibold mb-6"><?php echo htmlspecialchars($full_name); ?></h2>
        <div class="space-y-4 text-left text-lg font-medium">
          <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
            <div class="flex justify-between text-sm text-gray-500 mb-1">
              <span>Email</span>
            </div>
            <p class="text-sm font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
          </div>

          <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
            <div class="flex justify-between text-sm text-gray-500 mb-1">
              <span>Gender</span>
            </div>
            <p class="text-sm font-medium"><?php echo htmlspecialchars($user['gender']); ?></p>
          </div>

          <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
            <div class="flex justify-between text-sm text-gray-500 mb-1">
              <span>Birthday</span>
            </div>
            <p class="text-sm font-medium"><?php echo $birthday; ?></p>
          </div>
      
          <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
            <div class="flex justify-between text-sm text-gray-500 mb-1">
              <span>Address</span>
            </div>
            <p class="text-sm font-medium"><?php echo htmlspecialchars($user['address']); ?></p>
          </div>

          <?php if (!empty($user['contact_number'])): ?>
          <div class="bg-gray-200 rounded-xl px-4 py-2 text-black">
            <div class="flex justify-between text-sm text-gray-500 mb-1">
              <span>Contact Number</span>
            </div>
            <p class="text-sm font-medium"><?php echo htmlspecialchars($user['contact_number']); ?></p>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Action Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mt-6 w-full max-w-2xl xl:max-w-4xl text-center">
        <!-- Book Reservation -->
        <button
          onclick="window.location.href='../Reservation'"
          class="hover:bg-gray-300 active:bg-gray-400 bg-white rounded-2xl p-5 flex flex-col items-center hover:shadow-lg transition">
          <img
            src="../asset/calendar.png"
            class="h-14 mb-2"
            alt="Book Reservation"/>
          <span class="text-black font-semibold">Book Reservation</span>
        </button>

        <!-- Update Account -->
        <button onclick="window.location.href='/User/Update'"class="hover:bg-gray-300 active:bg-gray-400 bg-white rounded-2xl p-5 flex flex-col items-center hover:shadow-lg transition">
          <img
            src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
            class="h-14 mb-2"
            alt="Update Account"/>
          <span class="text-black font-semibold">Update account details</span>
        </button>

        <!--Book borrowing-->
        <button onclick="window.location.href='../Borrowing'"class="hover:bg-gray-300 active:bg-gray-400 bg-white rounded-2xl p-5 flex flex-col items-center hover:shadow-lg transition">
          <img src="https://cdn-icons-png.flaticon.com/512/2983/2983787.png" 
              class="h-14 mb-2" 
              alt="Book Borrowing"/>
          <span class="text-black font-semibold">Book Borrowing</span>
        </button>
      </div>
    </main>
  </body>
</html>