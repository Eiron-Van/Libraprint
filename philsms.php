<?php
// helper to send SMS via PhilSMS REST API

require __DIR__ . '/vendor/autoload.php';

// load env (safeLoad so it doesn't throw if .env missing)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

/**
 * Normalize Philippine phone numbers to PhilSMS format (example: 63917xxxxxxx)
 */
function format_phone_for_philsms(string $raw): string {
    $digits = preg_replace('/\D+/', '', $raw); // strip non digits

    // remove leading + if any, already removed by preg_replace
    if ($digits === '') return '';

    if (substr($digits, 0, 2) === '63') {
        return $digits;
    }
    if (substr($digits, 0, 1) === '0') {
        // 09171234567 => 639171234567
        return '63' . substr($digits, 1);
    }
    if (substr($digits, 0, 1) === '9') {
        // 9171234567 => 639171234567
        return '63' . $digits;
    }
    // fallback: return as-is (caller should validate)
    return $digits;
}

/**
 * Send SMS using PhilSMS API
 * returns associative array with status and response
 */
function sendPhilSMS(string $recipient_raw, string $message): array {
    $token = $_ENV['PHILSMS_API_TOKEN'] ?? getenv('PHILSMS_API_TOKEN');
    $sender = $_ENV['PHILSMS_SENDER_ID'] ?? getenv('PHILSMS_SENDER_ID') ?? 'Libraprint';

    if (empty($token)) {
        return ['status'=>'error', 'message'=>'PHILSMS_API_TOKEN is not set in environment'];
    }

    $recipient = format_phone_for_philsms($recipient_raw);
    if (empty($recipient)) {
        return ['status'=>'error','message'=>'Invalid recipient phone number'];
    }

    $payload = [
        'recipient' => $recipient,
        'sender_id' => $sender,
        'message'   => $message
        // optionally: 'schedule_time' => '2025-10-15 14:00' (RFC3339-like), 'type' => 'sms' if needed
    ];

    $ch = curl_init('https://app.philsms.com/api/v3/');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        return ['status'=>'error', 'message' => 'curl_error: ' . $curlErr];
    }

    $decoded = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['status'=>'error','message'=>'invalid_json_response','raw'=>$resp];
    }

    if ($http >= 200 && $http < 300 && isset($decoded['status']) && $decoded['status'] === 'success') {
        return ['status'=>'success','data'=>$decoded];
    }

    // API returned error
    return ['status'=>'error','http'=>$http,'response'=>$decoded];
}
