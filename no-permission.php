<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        h1 { color: #c0392b; }
        a { color: #2980b9; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Access Denied</h1>
    <p>You don’t have permission to view this page.</p>
    <p><a href="/Login/login.php">Go back to login</a></p>
</body>
</html>
