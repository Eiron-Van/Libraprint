<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("../connection.php");
include("../function.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Raw inputs
    $username = htmlspecialchars(trim($_POST["username"]));
    $firstname = htmlspecialchars(trim($_POST["firstname"]));
    $lastname = htmlspecialchars(trim($_POST["lastname"]));
    $gender = htmlspecialchars(trim($_POST["gender"]));
    $city = htmlspecialchars(trim($_POST["city"]));
    $barangay = htmlspecialchars(trim($_POST["barangay"]));
    $birthdate = htmlspecialchars(trim($_POST["birthdate"]));
    $contactnumber = htmlspecialchars(trim($_POST["contact_number"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = trim($_POST["password"]);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please enter some valid information!'); window.history.back();</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit();
    }

    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
        exit();
    }

    // Combined check for existing username or email
    $checkStmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->bind_result($existingUsername, $existingEmail);
        while ($checkStmt->fetch()) {
            if ($existingUsername === $username) {
                echo "<script>alert('Username already exists. Please choose a different one.'); window.history.back();</script>";
                exit();
            }
            if ($existingEmail === $email) {
                echo "<script>alert('Email already exists. Please use a different email.'); window.history.back();</script>";
                exit();
            }
        }
    }
    $checkStmt->close();

    // Store into session (password already hashed here)
    $_SESSION['pending_registration'] = [
        "username" => $username,
        "firstname" => $firstname,
        "lastname" => $lastname,
        "gender" => $gender,
        "city" => $city,
        "barangay" => $barangay,
        "birthdate" => $birthdate,
        "contactnumber" => $contactnumber,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_DEFAULT)
    ];

    // Redirect to fingerprint enrollment page
    echo "OK";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/style.css?v=1.5">
    <script src="terms.js"></script>
    <title>Registration | Libraprint</title>
</head>

<body class="relative flex justify-center items-center bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">
    <section id="main-content" class="w-full h-[90vh] flex items-center justify-center">
        <div class="w-full max-w-6xl">
            <form id="registrationForm" method="post" class="flex flex-col items-center relative">

                <div class="h-[570px] flex flex-row w-full text-center text-white text-lg">
                    <div class="w-1/2 border border-[#F5DEB3]/40 rounded-4xl backdrop-blur-xl shadow-2xl py-6 px-8">
                        <h1 class="text-3xl mb-6">General Information</h1>
                        <div class="flex flex-col gap-6 mt-3">
                            <div>
                                <input type="text" name="username" id="username" required
                                    class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                <label for="username">Username</label>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div>
                                    <input type="text" name="firstname" id="firstname" required
                                        class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="firstname">First Name</label>
                                </div>
                                <div>
                                    <input type="text" name="lastname" id="lastname" required
                                        class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="firstname">Last Name</label>
                                </div>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div>
                                    <input type="date" name="birthdate" id="birthdate" required
                                        class="w-62.5 bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="birthdate">Birthday</label>
                                </div>
                                <div>
                                    <select name="gender" id="gender" required
                                        class="w-62.5 bg-white rounded-3xl text-black px-4 py-2">
                                        <option value="">Select a Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Lesbian">Lesbian</option>
                                        <option value="Gay">Gay</option>
                                        <option value="Bisexual">Bisexual</option>
                                        <option value="Transgender">Transgender</option>
                                        <option value="Queer">Queer/Questioning</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label for="gender">Gender</label>
                                </div>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div class="relative w-full mb-4">
                                    <input required type="text" placeholder="Type or select..." id="city" name="city"
                                        class="w-full px-4 py-2 rounded-3xl bg-white text-black">
                                    <div class="hidden absolute w-full max-h-50 overflow-y-auto mb-2 bg-white text-black border border-gray-300 rounded-md shadow-lg bottom-full"
                                        id="city-dropdown"></div>
                                    <label for="city">City</label>
                                </div>
                                <div class="relative w-full">
                                    <input required type="text" placeholder="Type or select..." id="barangay"
                                        name="barangay" class="w-full px-4 py-2 rounded-3xl bg-white text-black">
                                    <div class="hidden absolute w-full max-h-50 overflow-y-auto mb-2 bg-white text-black border border-gray-300 rounded-md shadow-lg bottom-full"
                                        id="barangay-dropdown"></div>
                                    <label for="barangay">Barangay</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-1/2 border border-[#F5DEB3]/40 rounded-4xl backdrop-blur-xl shadow-2xl py-6 px-8">
                        <div>
                            <h1 class="text-3xl mb-6">Contact Information</h1>
                            <div class="flex flex-col gap-6 mt-3">
                                <div>
                                    <input type="tel" name="contact_number" id="contact_number" required
                                        pattern="^09\d{9}$" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        maxlength="11" class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="contact_number">Contact Number</label>
                                </div>
                                <div>
                                    <input type="email" name="email" id="email" required
                                        class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h1 class="text-3xl">Create Password</h1>
                            <div class="flex flex-col gap-3 mt-3">
                                <div>
                                    <input type="password" name="password" id="password" required minlength="8"
                                        class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="password">Password</label>
                                </div>
                                <div>
                                    <input type="password" name="confirm_password" id="confirm_password" required
                                        minlength="8" class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="password">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="submitBtn" type="submit" disabled class="absolute bottom-7 text-2xl text-white bg-gray-400 px-50 py-2 rounded-2xl opacity-70 cursor-not-allowed">
                    Submit
                </button>
                <div class="absolute left-4 bottom-3 text-white select-none">
                    <input type="checkbox" id="termsCheckbox">
                    <button type="button" id="viewTerms" class="cursor-pointer hover:underline">I agree to the terms and conditions</button>
                </div>

            </form>
        </div>
    </section>

    <!-- Overlay -->
    <div id="overlay" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-40"></div>

    <section id="fingerprint-step" class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-col items-center gap-4 p-6 w-96 rounded-3xl bg-gradient-to-b from-[#304475] to-[#0c0c0c] text-white text-center shadow-2xl z-41">
        <h1 class="text-3xl">Enroll Fingerprint</h1>
        <a href="libraprint-e://enroll?session=<?=session_id()?>"
            class="bg-[#5364a2] hover:bg-[#7a88bb] active:bg-[#6b78ac] px-5 py-1 rounded-xl">Open Fingerprint
            Scanner</a>
        <p>Onced scanned, your registration will be completed automatically</p>
    </section>

    <section id="inactivity-overlay"
        class="hidden bg-gradient-to-b from-[#304475] to-[#0c0c0c] z-50 fixed inset-0 justify-center items-center">
        <section
            class="relative w-full h-full flex justify-center items-center px-10 sm:px-32 gap-8 flex-col sm:flex-row">
            <div class="flex flex-col items-center w-fit">
                <img draggable="false" class=" pointer-events-nonew-full lg:w-2/3" src="/asset/Welcome.png"
                    alt="Welcome to Libraprint">
                <a class="block bg-cyan-700 rounded-full px-4 py-2 mt-4 text-white hover:bg-cyan-800 active:bg-cyan-900"
                    href="">Reserve Books Now!</a>
            </div>
            <div class="hidden lg:block">
                <img draggable="false" class="w-full h-[800px] object-contain pointer-events-none"
                    src="/asset/books.png" alt="">
            </div>
        </section>
    </section>

    <!-- Terms and Conditions Overlay -->
    <div id="termsOverlay" class="fixed hidden inset-0 bg-gray-900/80 items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-11/12 md:w-2/3 lg:w-1/3 max-h-[90vh] overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="relative text-center p-6 border-b border-gray-200 text-white">
                <button id="closeTerms" class="absolute top-4 right-5 text-gray-600 hover:text-gray-400 text-xl font-bold">
                    ✕
                </button>
                <div class="w-16 h-16 bg-blue-600 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i class="fas fa-scroll text-2xl text-white"></i>
                </div>
                <h1 class="text-2xl md:text-3xl text-gray-800 m font-bold mb-2">Terms & Conditions</h1>
                <div class="flex items-center justify-center space-x-2 text-gray-600 mb-2">
                    <i class="fas fa-building text-blue-800"></i>
                    <span class="font-medium">Lucena City Library</span>
                </div>
            </div>

            <!-- Scrollable Terms -->
            <div id="termsContent" class="overflow-y-auto p-8 text-gray-700 text-sm leading-relaxed space-y-4 flex-1 bg-gray-50 border-b border-gray-200">
                <section class="border-b border-gray-200 pb-3">
                    <h2 class="text-base font-bold text-blue-600 mb-2">
                    <i class="fas fa-gavel mr-2"></i>1. Terms
                    </h2>
                    <p class="text-gray-700">By registering and using the LibraPrint system, you agree to abide by these Terms and Conditions. These terms govern your use of the system, including attendance tracking, book borrowing, reservations and related services.</p>
                </section>

                <section class="border-b border-gray-200 pb-3">
                    <h2 class="text-base font-bold text-blue-600 mb-2">
                    <i class="fas fa-user-plus mr-2"></i>2. User Registration
                    </h2>
                    <p class="text-gray-700 mb-2">2.1 Only registered users with verified accounts (via fingerprint and profile registration) are allowed to use LibraPrint.</p>
                    <p class="text-gray-700">2.2 Biometric Registration is required for attendance and identification. Data is processed in compliance with the Data Privacy Act of 2012 (RA 10173).</p>
                </section>

                <section class="border-b border-gray-200 pb-3">
                    <h2 class="text-base font-bold text-blue-600 mb-2">
                    <i class="fas fa-fingerprint mr-2"></i>3. Attendance and Access
                    </h2>
                    <p class="text-gray-700 mb-2">3.1 All users must scan their registered fingerprint upon entering the library for attendance logging.</p>
                    <p class="text-gray-700">3.2 Unauthorized use of another person's fingerprint or account is prohibited.</p>
                </section>

                <section class="border-b border-gray-200 pb-3">
                    <h2 class="text-base font-bold text-blue-600 mb-2">
                    <i class="fas fa-lock mr-2"></i>4. Data Privacy and Security
                    </h2>
                    <p class="text-gray-700 mb-2">4.1 Personal and biometric data is collected solely for library services.</p>
                    <p class="text-gray-700">4.2 Users may request access, correction, or deletion of data under applicable laws.</p>
                </section>
            </div>
        </div>
    </div>

    <script src="address.js"></script>
    <script src="fingerprintScripts/es6-shim.js"></script>
    <script src="fingerprintScripts/websdk.client.bundle.min.js"></script>
    <script src="fingerprintScripts/fingerprint.sdk.min.js"></script>
    <script src="fingerprint_verify.js"></script>
</body>

</html>