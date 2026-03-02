<?php
require 'config.php';

// Add these lines at the top of the file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'src/Exception.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';

// Helper function to decode JSON fields
function decodeJsonFields(&$row, $fields) {
    foreach ($fields as $field) {
        if (isset($row[$field]) && is_string($row[$field])) {
            $decoded = json_decode($row[$field], true);
            // If json_decode fails, it returns null. Keep the original string in that case.
            $row[$field] = ($decoded !== null) ? $decoded : [];
        } else {
             $row[$field] = [];
        }
    }
}


// --- NEW CAPI FUNCTION (START) ---
// This function replicates the logic from your Node.js send-purchase-event.js
function sendMetaCAPIPurchase($orderData) {
    // Check if CAPI constants are defined
    if (!defined('META_PIXEL_ID') || !defined('META_ACCESS_TOKEN')) {
        error_log('Meta CAPI constants (META_PIXEL_ID, META_ACCESS_TOKEN) are not defined.');
        return; // Silently fail if not configured
    }

    $pixelId = META_PIXEL_ID;
    $accessToken = META_ACCESS_TOKEN;
    $apiUrl = "https://graph.facebook.com/v19.0/{$pixelId}/events";

    // 1. Prepare User Data (Hashing)
    $name = $orderData['name'] ?? '';
    $nameParts = explode(' ', trim($name), 2); // Split into max 2 parts (first name, rest)
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
    
    $phone = $orderData['phone'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $phone); // Clean phone number, keep only digits

    $userData = [
        'client_ip_address' => $orderData['ipAddress'],
        'client_user_agent' => $orderData['userAgent'],
        'fbp' => $orderData['fbp'] ?: null, // Use null if empty
        'fbc' => $orderData['fbc'] ?: null, // Use null if empty
        'ph' => $phone ? hash('sha256', $phone) : null,
        'fn' => $firstName ? hash('sha256', strtolower($firstName)) : null,
        'ln' => $lastName ? hash('sha256', strtolower($lastName)) : null,
    ];
    
    // Remove null values, as Meta API prefers
    $userData = array_filter($userData, function($value) {
        return $value !== null && $value !== '';
    });

    // 2. Prepare Main Payload
    $payload = [
        'data' => [
            [
                'event_name' => 'Purchase',
                'event_time' => time(), // Current timestamp in seconds
                'event_id' => $orderData['orderId'], // Your generated Order ID
                'action_source' => 'website',
                'user_data' => $userData,
                'custom_data' => [
                    'order_id' => $orderData['orderId'], // Custom Order ID
                    'value' => (float)$orderData['totalPrice'],
                    'currency' => 'BDT',
                ],
            ]
        ],
    ];

    // 3. Send with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?access_token=' . $accessToken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Optional: Log the response for debugging
    if ($httpCode != 200) {
        error_log("Meta CAPI Error (HTTP {$httpCode}) for Order {$orderData['orderId']}: " . $response);
    } else {
        error_log("Meta CAPI Success for Order {$orderData['orderId']}: " . $response);
    }
}
// --- NEW CAPI FUNCTION (END) ---


$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_initial_data':
        $output = [
            'product' => null,
            'settings' => [],
            'allOrders' => []
        ];
        
        $product_result = $conn->query("SELECT title, imageUrls, videoUrls FROM products ORDER BY createdAt DESC LIMIT 1");
        if ($product_result && $product_result->num_rows > 0) {
            $product_row = $product_result->fetch_assoc();
            decodeJsonFields($product_row, ['imageUrls', 'videoUrls']);
            $output['product'] = $product_row;
        }
        
        $settings_result = $conn->query("SELECT setting_key, setting_value FROM settings");
        if ($settings_result) {
            while ($row = $settings_result->fetch_assoc()) {
                $output['settings'][$row['setting_key']] = $row['setting_value'];
            }
            // Also decode the feedback image URLs
            if(isset($output['settings']['feedbackImageUrls'])){
                $decoded_urls = json_decode($output['settings']['feedbackImageUrls'], true);
                $output['settings']['feedbackImageUrls'] = ($decoded_urls !== null) ? $decoded_urls : [];
            }
        }
        
        $orders_result = $conn->query("SELECT name, address, phone, product, timestamp FROM orders WHERE status != 'Canceled' ORDER BY timestamp DESC LIMIT 100");
        if ($orders_result) {
            while ($row = $orders_result->fetch_assoc()) {
                $output['allOrders'][] = $row;
            }
        }
        echo json_encode($output);
        break;

    // The rest of the cases (place_order, track_order, etc.) in your api.php file are mostly correct and can remain as they are.
    // I am including them here for completeness.

    case 'place_order':
        $generatedOrderId = "Nawarika" . time();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt = $conn->prepare("INSERT INTO orders (orderId, name, address, phone, note, product, price, deliveryFee, totalPrice, fbp, fbc, ipAddress, userAgent, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("ssssssiiissss", $generatedOrderId, $_POST['name'], $_POST['address'], $_POST['phone'], $_POST['note'], $_POST['selectedProduct'], $_POST['productPriceInput'], $_POST['deliveryFee'], $_POST['totalPriceInput'], $_POST['fbp'], $_POST['fbc'], $ipAddress, $userAgent);
        
        if ($stmt->execute()) {
            $order_id_db = $stmt->insert_id; // Get the auto-incremented ID

            // --- START PHPMailer LOGIC ---
            $mail = new PHPMailer(true);
            try {
                //Server settings - REPLACE WITH YOUR SMTP CREDENTIALS
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = (SMTP_SECURE === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = SMTP_PORT;


                //Recipients
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress(SMTP_TO_ADMIN_MAIL, SMTP_TO_ADMIN_NAME); // Where you want to receive order notifications
                
                // Content
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'New Order Received: ' . $generatedOrderId;

                // --- NEW: Define variables for the email body ---
                date_default_timezone_set('Asia/Dhaka'); // Set to GMT+6
                $orderTimeFormatted = date('d F Y, h:i A'); // e.g., 07 November 2025, 08:15 PM
                
                // Cleans the phone number for the 'tel:' link
                $safePhone = preg_replace('/[^+0-9]/', '', $_POST['phone']); 
                
                // !!! IMPORTANT: Replace with your actual URLs !!!
                $trackUrl = WEBSITE_URL."/?orderId=" . urlencode($generatedOrderId);
                $adminUrl = WEBSITE_URL."/admin.php"; // Link to your admin panel

                // --- NEW: Modern Email Body ---
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>New Order</title>
                </head>
                <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4;'>
                    <table width='100%' border='0' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4;'>
                        <tr>
                            <td align='center'>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' style='max-width: 600px; width: 100%; background-color: #ffffff; margin-top: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                                    <tr>
                                        <td align='center' style='padding: 20px 0; background-color: #007bff; color: #ffffff; border-top-left-radius: 8px; border-top-right-radius: 8px;'>
                                            <h1 style='margin: 0; font-size: 24px;'>New Order Received!</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 30px;'>
                                            <p style='font-size: 16px; margin-bottom: 20px;'>You have received a new order. Please review the details below:</p>
                                            
                                            <table width='100%' border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse;'>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee; width: 30%;'>Order Time (GMT+6):</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'>{$orderTimeFormatted}</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee;'>Order ID:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'><a href='{$trackUrl}' target='_blank' style='color: #007bff; text-decoration: none;'>{$generatedOrderId}</a></td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee;'>Name:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'>" . htmlspecialchars($_POST['name']) . "</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee;'>Phone:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'><a href='tel:{$safePhone}' style='color: #007bff; text-decoration: none;'>" . htmlspecialchars($_POST['phone']) . "</a></td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee; vertical-align: top;'>Address:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'>" . nl2br(htmlspecialchars($_POST['address'])) . "</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee;'>Product:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee;'>" . htmlspecialchars($_POST['selectedProduct']) . "</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; border-bottom: 1px solid #eeeeee;'>Total Price:</td>
                                                    <td style='padding: 12px; border-bottom: 1px solid #eeeeee; font-weight: bold; color: #d9534f;'>" . htmlspecialchars($_POST['totalPriceInput']) . " BDT</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 12px; font-weight: bold; vertical-align: top;'>Note:</td>
                                                    <td style='padding: 12px;'>" . nl2br(htmlspecialchars($_POST['note'])) . "</td>
                                                </tr>
                                            </table>
                                            
                                            <table width='100%' border='0' cellpadding='0' cellspacing='0' style='margin-top: 30px;'>
                                                <tr>
                                                    <td align='center'>
                                                        <a href='{$adminUrl}' target='_blank' style='display: inline-block; padding: 12px 25px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>
                                                            Go to Admin Panel
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align='center' style='padding: 20px; color: #888888; font-size: 12px;'>
                                            This is an automated notification.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Email failed, but the order was placed. You can log this error.
                // error_log("Mailer Error: {$mail->ErrorInfo}");
            }
            // --- END PHPMailer LOGIC ---
            
            // --- NEW CAPI CALL (START) ---
            // We call the function we defined at the top of the file
            $capiOrderData = [
                'orderId' => $generatedOrderId,
                'ipAddress' => $ipAddress,
                'userAgent' => $userAgent,
                'fbp' => $fbp,
                'fbc' => $fbc,
                'name' => $name,
                'phone' => $phone,
                'totalPrice' => $totalPriceInput
            ];
            sendMetaCAPIPurchase($capiOrderData); // Call the new function
            // --- NEW CAPI CALL (END) ---

            echo json_encode(['success' => true, 'id' => $order_id_db, 'orderId' => $generatedOrderId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to place order: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'track_order':
        $query = $_GET['query'] ?? '';
        if (empty($query)) {
            echo json_encode(['order' => null]);
            exit();
        }
        $stmt = $conn->prepare("SELECT * FROM orders WHERE orderId = ? OR phone = ? ORDER BY timestamp DESC LIMIT 1");
        $stmt->bind_param("ss", $query, $query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(['order' => $result->fetch_assoc()]);
        } else {
            echo json_encode(['order' => null]);
        }
        $stmt->close();
        break;

    case 'update_order_info':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            if (isset($data['reward'])) {
                $stmt = $conn->prepare("UPDATE orders SET reward = ? WHERE id = ?");
                $stmt->bind_param("si", $data['reward'], $id);
            } elseif (isset($data['name'])) {
                $stmt = $conn->prepare("UPDATE orders SET name = ?, address = ?, phone = ?, note = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $data['name'], $data['address'], $data['phone'], $data['note'], $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No valid data provided for update.']);
                exit();
            }
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Update failed: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID.']);
        }
        break;

    case 'track_visitor':
        $data = json_decode(file_get_contents('php://input'), true);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        if (isset($data['id']) && isset($data['durationMillis'])) {
            $stmt = $conn->prepare("UPDATE visitors SET durationMillis = ? WHERE id = ?");
            $stmt->bind_param("ii", $data['durationMillis'], $data['id']);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        }
        $userAgent = $data['userAgent'] ?? 'Unknown';
        $stmt = $conn->prepare("SELECT id FROM visitors WHERE ipAddress = ? AND userAgent = ?");
        $stmt->bind_param("ss", $ipAddress, $userAgent);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $visitorId = $row['id'];
            $updateStmt = $conn->prepare("UPDATE visitors SET visitCount = visitCount + 1, startTime = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $visitorId);
            $updateStmt->execute();
            $updateStmt->close();
            echo json_encode(['id' => $visitorId]);
        } else {
            $location = 'Unknown';
            // You can optionally add back the IPData API call here if needed
            $insertStmt = $conn->prepare("INSERT INTO visitors (ipAddress, userAgent, location, visitCount, startTime) VALUES (?, ?, ?, 1, NOW())");
            $insertStmt->bind_param("sss", $ipAddress, $userAgent, $location);
            $insertStmt->execute();
            echo json_encode(['id' => $insertStmt->insert_id]);
            $insertStmt->close();
        }
        $stmt->close();
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found.']);
        break;
}

$conn->close();
?>