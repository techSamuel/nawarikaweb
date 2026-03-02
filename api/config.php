<?php
require 'db_connect.php';
// Set headers for CORS and content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');


define('WEBSITE_URL', 'https://duya.nawarika.shop');

// SMTP Email Configuration
define('SMTP_HOST', 'smtp.hostinger.com');      // Your SMTP server (e.g., smtp.gmail.com)
define('SMTP_USER', 'support@nawarika.shop');     // Your SMTP username
define('SMTP_PASS', '$=&d/R~rG7Sj');    // Your SMTP password
define('SMTP_PORT', 465);                     // Use 465 for SSL or 587 for TLS
define('SMTP_SECURE', 'ssl');                 // Use 'ssl' or 'tls'
define('SMTP_FROM_EMAIL', 'support@nawarika.shop'); // The "From" email address
define('SMTP_FROM_NAME', 'Nawarika Duya Order');
define('SMTP_TO_ADMIN_MAIL', 'rodalsoft@gmail.com');
define('SMTP_TO_ADMIN_NAME', 'Shamuel Hossain');// The "From" name
// --- END OF NEW SECTION ---

define('META_PIXEL_ID', '2248228862286550');
define('META_ACCESS_TOKEN', 'EAAP31bjA638BPKy0Np2fOSTZBERrl627xZA1Pvkxliqa8zhXGbj0LL01oXImZBHxXmHdMSdQZAFn434qYZCVeKmBfDzyv9KxEGXNlnNGPhZCiRQyPaHZCPPhgZBr7VZCTRzmSNtZBlL2Nabls5v8xIAD3wC6HiRpuGui34XnWIeZCpqyVaKnZA36NhfLotZB4aAgtpwZDZD');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Start the session for admin functionality
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


?>