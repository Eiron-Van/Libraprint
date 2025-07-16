<?php
    session_start();
    
    include("connection.php");
    include("function.php");

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        //something was posted
        $username = $_POST["username"];
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $gender = $_POST["gender"];
        $address = $_POST["address"];
        $birthdate = $_POST["birthdate"];
        $contactnumber = $_POST["contactnumber"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        

        if (!empty($username) && !empty($password)){
            //save to database
            $user_id = random_num(20);
            $query = "insert into users(username, firstname, lastname, gender, address, birthdate, contactnumber, email, password) values('$username', '$firstname', '$lastname', '$gender', '$address', '$birthdate', '$contactnumber', '$email', '$password')";

            mysqli_query($conn, $query);
            header("Location: /Login");
        }else{
            echo "Please enter some valid information!";
        }

    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Document</title>
</head>
<body class="">
    <form action="" method="post" class="grid grid-cols-2 gap-4">
        <label for="username">username</label>
        <input type="text" name="username" id="username" placeholder="Username">

        <label for="firstname">firstname</label>
        <input type="text" name="firstname" id="firstname" placeholder="Firstname">

        <label for="lastname">lastname</label>
        <input type="text" name="lastname" id="lastname" placeholder="Lastname">

        <label for="gender">gender</label>
        <input type="text" name="gender" id="gender" placeholder="Gender">

        <label for="address">Address</label>
        <input type="text" name="address" id="address" placeholder="Address">

        <label for="birthdate">birthdate</label>
        <input type="date" name="birthdate" id="birthdate" placeholder="Birthdate">

        <label for="contactnumber">Contact Number</label>
        <input type="text" name="contactnumber" id="contactnumber" placeholder="Contact Number">

        <label for="email">Email</label>
        <input type="text" name="email" id="email" placeholder="Email">

        <label for="password">password</label>
        <input type="password" name="password" id="password" placeholder="Password">

        
        <button type="submit">Register</button>


    </form>
</body>
</html>