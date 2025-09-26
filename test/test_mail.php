<?php
require 'mailer.php';

if (sendEmail("youremail@gmail.com", "Test User", "Test Email", "<b>Hello</b> this is a test email!")) {
    echo "✅ Email sent!";
} else {
    echo "❌ Failed. Check logs.";
}
