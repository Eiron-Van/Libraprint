<?php
    session_start();
    
    include("connection.php");
    include("function.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check user
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        header("Location: test.html"); // <-- redirect to a page after login
    } else {
        echo "<script>alert('Invalid Username or Password'); window.history.back();</script>";
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
    <link rel="stylesheet" href="/style.css?v=1.4">
    <script src="/script.js"></script>
    <title>Login | Libraprint</title>
</head>

<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed">

    <section class="w-full h-screen flex items-center justify-center px-5">
        <div
            class="flex justify-center items-center border border-white rounded-4xl w-full max-w-md h-125 shadow-md text text-center py-4 px-20 text-white bg-transparent back">
            <form method="post" class="w-full">
                <h1 class="font-bold text-xl">Sign In</h1>
                <p class="text-sm">Enter valid details to continue</p>

                <div class="text-black flex flex-col gap-3 mt-5">
                    <div class="flex items-center relative">
                        <input type="text" name="username" id="username" required
                            placeholder="Username/Email/Mobile Number"
                            class="placeholder:text-[.8rem] bg-white w-full rounded-2xl px-3 py-2">
                        <i class='bx bx-user absolute right-2'></i>
                    </div>

                    <div class="flex items-center relative">
                        <input type="password" name="password" id="password" required placeholder="Password?"
                            minlength="8" class="placeholder:text-[.8rem] bg-white w-full rounded-2xl px-3 py-2">
                        <i class='bx bx-lock absolute right-2'></i>
                    </div>
                </div>

                <div class="flex justify-between text-sm mt-2">
                    <label><input type="checkbox" name="" id="" class="mr-2">Remember Me</label>
                    <a href="" class="hover:underline">Forgot Password?</a>
                </div>

                <button type="submit"
                    class="cursor-pointer bg-[#5364a2] hover:bg-[#7a88bb] active:bg-[#6b78ac] px-5 py-1 rounded-2xl mt-5">Sign
                    In</button>
            </form>
        </div>
    </section>

</body>

</html>