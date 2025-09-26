<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // adjust path


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();

function sendEmail($toEmail, $toName, $subject, $bodyHtml) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'libraprint.lucena@gmail.com';  // your Gmail
        $mail->Password   = $_ENV['APP_PASSWORD'];    // 16-char App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // From
        $mail->setFrom('libraprint.lucena@gmail.com', 'Libraprint');

        // To
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;

        $mail->send();
        return ["status" => "success"];

    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return ["status" => "error", "message" => $mail->ErrorInfo];
    }
}
