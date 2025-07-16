<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /Login/index.html");
    exit();
}
?>