<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("../connection.php");
include("../function.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $username = htmlspecialchars(trim($_POST["username"]));
    $firstname = htmlspecialchars(trim($_POST["firstname"]));
    $lastname = htmlspecialchars(trim($_POST["lastname"]));
    $gender = htmlspecialchars(trim($_POST["gender"]));
    $city = htmlspecialchars(trim($_POST["city"]));
    $barangay = htmlspecialchars(trim($_POST["barangay"]));
    $birthdate = htmlspecialchars(trim($_POST["birthdate"]));
    $contactnumber = htmlspecialchars(trim($_POST["contact_number"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = htmlspecialchars(trim($_POST["password"]));

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "Please enter some valid information!";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit();
    }

    if (strlen($password) < 8) {
        echo "Password must be at least 8 characters long";
        exit();
    }

    // Combine barangay and city into the desired format
    $address = "[$barangay, $city]";

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $emailCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    $emailCheck->store_result();

    if ($emailCheck->num_rows > 0) {
        echo "This email is already registered. Please use a different email.";
        exit();
    }

    $emailCheck->close();

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (username, first_name, last_name, gender, address, birthday, contact_number, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Check if the statement was prepared successfully
    if (!$stmt) {
        error_log("SQL error: " . $conn->error);
        echo "An error occurred. Please try again later.";
        exit();
    }

    // Bind parameters
    $stmt->bind_param("sssssssss", $username, $firstname, $lastname, $gender, $address, $birthdate, $contactnumber, $email, $hashed_password);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Redirect after successful registration
        header("Location: /Login");
        exit();
    } else {
        echo "An error occurred while registering. Please try again.";
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/style.css?v=1.5">
    <title>Registration | Libraprint</title>
</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">
    <section class="w-full h-screen flex items-center justify-center">
        <div class="w-full max-w-6xl">
            <form action="" method="post" class="flex flex-col items-center relative">

                <div class="h-[550px] flex flex-row w-full text-center text-white text-lg">
                    <div class="w-1/2 border border-[#F5DEB3]/40 rounded-4xl backdrop-blur-xl shadow-2xl py-6 px-8">
                        <h1 class="text-3xl mb-6">General Information</h1>
                        <div class="flex flex-col gap-6 mt-3">
                            <div>
                                <input type="text" name="username" id="username" required class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                <label for="username">Username</label>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div>
                                    <input type="text" name="firstname" id="firstname" required class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="firstname">First Name</label>
                                </div>
                                <div>
                                    <input type="text" name="lastname" id="lastname" required class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="firstname">Last Name</label>
                                </div>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div>
                                    <input type="date" name="birthdate" id="birthdate" class="w-62.5 bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="birthdate">Birthday</label>
                                </div>
                                <div>
                                    <select name="gender" id="gender" class="w-62.5 bg-white rounded-3xl text-black px-4 py-2">
                                        <option value="">Select a Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="lesbian">Lesbian</option>
                                        <option value="gay">Gay</option>
                                        <option value="bisexual">Bisexual</option>
                                        <option value="transgender">Transgender</option>
                                        <option value="queer">Queer/Questioning</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <label for="gender">Gender</label>
                                </div>
                            </div>
                            <div class="flex flex-row gap-2">
                                <div class="relative w-full mb-4">
                                    <input type="text" placeholder="Type or select..." id="city" name="city" class="w-full px-4 py-2 rounded-3xl bg-white text-black">
                                    <div class="hidden absolute w-full max-h-50 overflow-y-auto mb-2 bg-white text-black border border-gray-300 rounded-md shadow-lg bottom-full" id="city-dropdown"></div>
                                </div>
                                <div class="relative w-full">
                                    <input type="text" placeholder="Type or select..." id="barangay" name="barangay" class="w-full px-4 py-2 rounded-3xl bg-white text-black">
                                    <div class="hidden absolute w-full max-h-50 overflow-y-auto mb-2 bg-white text-black border border-gray-300 rounded-md shadow-lg bottom-full" id="barangay-dropdown"></div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="w-1/2 border border-[#F5DEB3]/40 rounded-4xl backdrop-blur-xl shadow-2xl py-6 px-8">
                        <div>
                            <h1 class="text-3xl mb-6">Contact Information</h1>
                            <div class="flex flex-col gap-6 mt-3">
                                <div>
                                    <input type="tel" name="contact_number" id="contact_number" required pattern="^09\d{9}$" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11" class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="contact_number">Contact Number</label>
                                </div>
                                <div>
                                    <input type="email" name="email" id="email" required class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h1 class="text-3xl">Create Password</h1>
                            <div class="flex flex-col gap-3 mt-3">
                                <div>
                                    <input type="password" name="password" id="password" required minlength="8" class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="password">Password</label>
                                </div>
                                <div>
                                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" class="w-full bg-white rounded-3xl text-black px-4 py-2">
                                    <label for="password">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="absolute bottom-5 text-2xl text-white cursor-pointer bg-[#5364a2] hover:bg-[#7a88bb] active:bg-[#6b78ac] px-50 py-2 rounded-2xl">Submit</button>
                
            </form>
        </div>
    </section>
    <script src="address.js"></script>
</body>
</html>