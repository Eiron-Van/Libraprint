<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../connection.php'; // Your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['username']); // Can be username, email, or contact number
    $password = $_POST['password'];

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR contact_number = ?");
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if account is verifieds
        if ($user['is_verified'] == 0) {
            echo "<script>alert('Please check your email to verify your account.'); window.location.href='/Login';</script>";
            exit();
        }

        // Use password_verify to check the hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ✅ Record login event with location
            $location = 'Off Site'; // or 'On Site', depending on context or device
            $log = $conn->prepare("INSERT INTO login_record (user_id, location) VALUES (?, ?)");
            $log->bind_param("is", $user['id'], $location);
            $log->execute();
            $log->close();


            // ✅ Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: https://libraprintlucena.com/Admin");
            } else {
                header("Location: https://libraprintlucena.com");
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='/Login';</script>";
        }
    } else {
        echo "<script>alert('No user found with that username/email/contact number.'); window.location.href='https://libraprintlucena.com/Login';</script>";
    }
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
    <script src="show_password.js"></script>
    <title>Login | Libraprint</title>
</head>

<body class="bg-[url(/asset/books_login.jpg)] bg-cover backdrop-blur-xs lg:backdrop-blur-xl">

    <section class="w-full h-screen flex items-center justify-center px-5">
        <div class="w-full md:w-[50rem] flex justify-center md:justify-start md:bg-[url(/asset/books_login.jpg)] md:bg-cover rounded-3xl py-10 px-2 md:px-10">
            <div class="flex justify-center items-center border border-[#F5DEB3] rounded-4xl w-full max-w-md h-125 shadow-2xl text text-center py-4 px-6 sm:px-10 md:px-15 text-white bg-transparent backdrop-blur-xs">
                <form action="" method="POST" class="w-full select-none">
                    <h1 class="font-bold text-xl">Log In</h1>
                    <p class="text-sm">Enter valid details to continue</p>
    
                    <div class="text-black flex flex-col gap-3 mt-5">
                        <div class="flex items-center relative">
                            <input type="text" name="username" id="username" required
                                placeholder="Username/Email/Mobile Number"
                                class="placeholder:text-[.7rem] md:placeholder:text-[1rem] bg-white w-full rounded-2xl px-3 py-2">
                            <i class='bx bx-user absolute right-2'></i>
                        </div>
    
                        <div class="flex items-center relative">
                            <input type="password" name="password" id="password" required placeholder="Password?"
                                minlength="8" class="placeholder:text-[.7rem] md:placeholder:text-[1rem] bg-white w-full rounded-2xl px-3 py-2">
                            <i class='bx bx-lock absolute right-2'></i>
                        </div>
                    </div>
    
                    <div class="flex justify-between text-sm mt-2">
                        <label><input type="checkbox" name="show-password" id="show-password" class="mr-2">Show Password</label>
                        <a href="forgot_password.html" class="hover:underline">Forgot Password?</a>
                    </div>
    
                    <button type="submit" class="cursor-pointer textcenter bg-[#5364a2] hover:bg-[#7a88bb] active:bg-[#6b78ac] px-5 py-1 rounded-2xl mt-5">Log In</button>
                </form>
            </div>
        </div>
    </section>

</body>

</html>