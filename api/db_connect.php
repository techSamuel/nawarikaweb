<?php
$servername = "localhost";
$username = "u374415227_nawarika";
$password = "Ki;0;oAN7";
$dbname = "u374415227_nawarika";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Set character set
$conn->set_charset("utf8mb4");

?>