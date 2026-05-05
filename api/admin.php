<?php
require 'config.php';


// Helper function to decode JSON fields
function decodeJsonFields(&$row, $fields)
{
    foreach ($fields as $field) {
        if (isset($row[$field]) && is_string($row[$field])) {
            $decoded = json_decode($row[$field], true);
            $row[$field] = ($decoded !== null) ? $decoded : [];
        } else {
            $row[$field] = [];
        }
    }
}

// --- NEW CAPI FUNCTION (START) ---
function sendMetaCAPIPurchase($orderData)
{
    if (!defined('META_PIXEL_ID') || !defined('META_ACCESS_TOKEN')) {
        return;
    }

    $pixelId = META_PIXEL_ID;
    $accessToken = META_ACCESS_TOKEN;
    $apiUrl = "https://graph.facebook.com/v19.0/{$pixelId}/events";

    // 1. Prepare User Data (Hashing)
    $name = $orderData['name'] ?? '';
    $nameParts = explode(' ', trim($name), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';

    $phone = $orderData['phone'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $phone);

    $userData = [
        'client_ip_address' => $orderData['ipAddress'],
        'client_user_agent' => $orderData['userAgent'],
        'ph' => $phone ? hash('sha256', $phone) : null,
        'fn' => $firstName ? hash('sha256', strtolower($firstName)) : null,
        'ln' => $lastName ? hash('sha256', strtolower($lastName)) : null,
    ];

    $userData = array_filter($userData, function ($value) {
        return $value !== null && $value !== '';
    });

    // 2. Prepare Main Payload
    $payload = [
        'data' => [
            [
                'event_name' => 'Purchase',
                'event_time' => time(),
                'event_id' => $orderData['orderId'],
                'action_source' => 'system_generated', // Marking as system generated/manual
                'user_data' => $userData,
                'custom_data' => [
                    'order_id' => $orderData['orderId'],
                    'value' => (float) $orderData['totalPrice'],
                    'currency' => 'BDT',
                    'content_name' => $orderData['product']
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
    curl_close($ch);
}
// --- NEW CAPI FUNCTION (END) ---


$action = $_REQUEST['action'] ?? '';

// ... (The login, logout, and check_session actions are fine and remain the same) ...

// All actions below this point are protected and require a valid session
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Exception for public actions
    $public_actions = ['login', 'logout', 'check_session'];
    if (!in_array($action, $public_actions)) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
}


switch ($action) {
    // --- LOGIN/LOGOUT/SESSION ---
    case 'login':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // --- MODIFIED: Select email as well ---
        $stmt = $conn->prepare("SELECT email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {

                // --- ADD THIS CODE BLOCK ---

                // Set session lifetime to 30 days
                $lifetime = 60 * 60 * 24 * 30; // 30 days in seconds

                // 1. Tell the server to keep the session data for 30 days
                ini_set('session.gc_maxlifetime', $lifetime);

                // 2. Manually tell the browser to keep the cookie for 30 days
                $cookieParams = session_get_cookie_params();
                setcookie(
                    session_name(), // Get the session cookie name (e.g., "PHPSESSID")
                    session_id(),   // Get the current session ID
                    time() + $lifetime, // The new expiration time
                    $cookieParams["path"],
                    $cookieParams["domain"],
                    $cookieParams["secure"],
                    $cookieParams["httponly"]
                );

                // --- END OF ADDED CODE ---

                $_SESSION['admin_logged_in'] = true;
                // --- ADDED: Store the user's email in the session ---
                $_SESSION['admin_email'] = $user['email'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Invalid email or password.']);
            }
        } else {
            echo json_encode(['error' => 'Invalid email or password.gg']);
        }
        $stmt->close();
        break;
    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;
    case 'check_session':
        echo json_encode(['loggedIn' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true]);
        break;

    // --- ORDER MANAGEMENT ---
    // This section is mostly fine as-is
// --- ORDER MANAGEMENT ---
    case 'get_orders':
        $limit = (int) ($_GET['limit'] ?? 15);
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $offset = ($page - 1) * $limit;

        $searchWild = "%{$search}%";
        $whereClause = "";
        $params = [];
        $types = "";

        if (!empty($search)) {
            // UPDATED: Added ipAddress to the search
            $whereClause = " WHERE (name LIKE ? OR orderId LIKE ? OR product LIKE ? OR address LIKE ? OR phone LIKE ? OR ipAddress LIKE ?)";
            $types = "ssssss"; // <-- Changed from "sssss" to "ssssss"
            // UPDATED: Added one more $searchWild
            array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
        }

        // Get total count with the search filter
        $countQuery = "SELECT COUNT(*) as total FROM orders" . $whereClause;
        $countStmt = $conn->prepare($countQuery);
        if (!empty($search)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();

        // Get paginated data
        $dataQuery = "SELECT * FROM orders" . $whereClause . " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
        $dataStmt = $conn->prepare($dataQuery);

        // Add limit and offset to params
        $types .= "ii";
        array_push($params, $limit, $offset);

        $dataStmt->bind_param($types, ...$params);
        $dataStmt->execute();
        $result = $dataStmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $dataStmt->close();

        echo json_encode([
            'data' => $orders,
            'totalRecords' => $totalRecords
        ]);
        break;
    case 'delete_order':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;
    case 'update_order_status':
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 'Pending';

        $PIXEL_ID = META_PIXEL_ID;
        $ACCESS_TOKEN = META_ACCESS_TOKEN;

        // First, update the status in your database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        $response = ['success' => true, 'apiResponse' => null];

        // If the order was canceled, send the event to Meta API
        if ($status === 'Canceled') {
            // Fetch the full order details needed for the API
            $orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->bind_param("i", $id);
            $orderStmt->execute();
            $orderResult = $orderStmt->get_result();

            if ($orderResult->num_rows > 0) {
                $orderData = $orderResult->fetch_assoc();

                // --- Prepare User Data for Meta API ---
                $nameParts = explode(' ', trim($orderData['name']));
                $firstName = array_shift($nameParts);
                $lastName = implode(' ', $nameParts);

                $userData = [
                    'client_ip_address' => $orderData['ipAddress'],
                    'client_user_agent' => $orderData['userAgent'],
                    'fbp' => $orderData['fbp'],
                    'fbc' => $orderData['fbc'],
                    'ph' => hash('sha256', strtolower(preg_replace('/[^0-9]/', '', $orderData['phone']))),
                    'fn' => hash('sha256', strtolower($firstName)),
                    'ln' => hash('sha256', strtolower($lastName)),
                ];
                $userData = array_filter($userData, fn($value) => !is_null($value));

                // --- Build the Final Payload ---
                $payload = [
                    'data' => [
                        [
                            'event_name' => 'Purchase',
                            'event_time' => time(),
                            'action_source' => 'other',
                            'user_data' => $userData,
                            'custom_data' => [
                                'event_status' => 'cancelled',
                                'order_id' => $orderData['orderId'],
                                'value' => $orderData['totalPrice'],
                                'currency' => 'BDT',
                            ],
                        ]
                    ]
                ];

                // --- Send Data to Meta API using cURL ---
                $url = "https://graph.facebook.com/v19.0/" . $PIXEL_ID . "/events?access_token=" . $ACCESS_TOKEN;

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $api_call_response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code >= 200 && $http_code < 300) {
                    $response['apiResponse'] = ['status' => 'Success', 'message' => 'Cancellation event sent to Meta.'];
                } else {
                    $response['apiResponse'] = ['status' => 'Error', 'message' => 'Failed to send event to Meta.', 'response' => json_decode($api_call_response)];
                }
            }
            $orderStmt->close();
        }

        echo json_encode($response);
        break;
    case 'update_order_details':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("UPDATE orders SET name=?, product=?, totalPrice=?, address=?, phone=?, note=?, reward=? WHERE id = ?");
        $stmt->bind_param("ssdssssi", $_POST['name'], $_POST['product'], $_POST['totalPrice'], $_POST['address'], $_POST['phone'], $_POST['note'], $_POST['reward'], $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    // --- MANUAL ORDER CREATION ---
    // --- MANUAL ORDER CREATION ---
    case 'create_manual_order':
        $name = $_POST['name'] ?? 'Guest';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $product = $_POST['product'] ?? '';
        $price = $_POST['price'] ?? 0;
        $note = $_POST['note'] ?? '';

        if (empty($phone)) {
            echo json_encode(['error' => 'Phone number is required.']);
            exit();
        }

        $generatedOrderId = "Manual" . time() . rand(100, 999); // Distinct prefix
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Manual entry usually doesn't have customer IP, using admin's or placeholder
        $userAgent = "Manual Entry (Admin)";

        $stmt = $conn->prepare("INSERT INTO orders (orderId, name, address, phone, note, product, totalPrice, ipAddress, userAgent, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', NOW())");
        $stmt->bind_param("ssssssdss", $generatedOrderId, $name, $address, $phone, $note, $product, $price, $ipAddress, $userAgent);

        if ($stmt->execute()) {
            // --- Send to CAPI ---
            $capiOrderData = [
                'orderId' => $generatedOrderId,
                'ipAddress' => $ipAddress,
                'userAgent' => $userAgent, // Or $_SERVER['HTTP_USER_AGENT'] of the admin, but better to be honest 'Manual'
                'name' => $name,
                'phone' => $phone,
                'totalPrice' => $price,
                'product' => $product
            ];
            sendMetaCAPIPurchase($capiOrderData);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to create order.']);
        }
        $stmt->close();
        break;

    case 'create_bulk_manual_orders':
        $rawOrders = $_POST['orders'] ?? '[]';
        $orders = json_decode($rawOrders, true);

        if (!is_array($orders)) {
            echo json_encode(['error' => 'Invalid data format.']);
            exit();
        }

        $successCount = 0;
        $failedCount = 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = "Manual Bulk Entry (Admin)";

        $stmt = $conn->prepare("INSERT INTO orders (orderId, name, address, phone, note, product, totalPrice, ipAddress, userAgent, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', NOW())");

        foreach ($orders as $index => $order) {
            $name = $order['name'] ?? 'Guest';
            $phone = $order['phone'] ?? '';
            $address = $order['address'] ?? '';
            $product = $order['product'] ?? '';
            $price = floatval($order['price'] ?? 0);
            $note = $order['note'] ?? '';

            // Use provided Order ID or generate one. Ensuring uniqueness if generating fast.
            $orderId = !empty($order['orderId']) ? $order['orderId'] : "Manual" . time() . "_" . $index . "_" . rand(100, 999);

            if (empty($phone)) {
                $failedCount++;
                continue; // Skip invalid orders
            }

            $stmt->bind_param("ssssssdss", $orderId, $name, $address, $phone, $note, $product, $price, $ipAddress, $userAgent);

            if ($stmt->execute()) {
                $successCount++;
                // --- Send to CAPI ---
                $capiOrderData = [
                    'orderId' => $orderId,
                    'ipAddress' => $ipAddress,
                    'userAgent' => $userAgent,
                    'name' => $name,
                    'phone' => $phone,
                    'totalPrice' => $price,
                    'product' => $product
                ];
                sendMetaCAPIPurchase($capiOrderData);
            } else {
                $failedCount++;
            }
        }
        $stmt->close();

        echo json_encode(['success' => true, 'message' => "Successfully created $successCount orders. Failed: $failedCount."]);
        break;

    // --- VISITOR MANAGEMENT ---
    case 'get_visitors':
        $limit = (int) ($_GET['limit'] ?? 15);
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $offset = ($page - 1) * $limit;

        $searchWild = "%{$search}%";
        $whereClause = "";
        $params = [];
        $types = "";

        if (!empty($search)) {
            // Search by IP or Location as requested
            $whereClause = " WHERE (ipAddress LIKE ? OR location LIKE ?)";
            $types = "ss";
            array_push($params, $searchWild, $searchWild);
        }

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM visitors" . $whereClause;
        $countStmt = $conn->prepare($countQuery);
        if (!empty($search)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();

        // Get paginated data
        $dataQuery = "SELECT * FROM visitors" . $whereClause . " ORDER BY startTime DESC LIMIT ? OFFSET ?";
        $dataStmt = $conn->prepare($dataQuery);

        $types .= "ii";
        array_push($params, $limit, $offset);

        $dataStmt->bind_param($types, ...$params);
        $dataStmt->execute();
        $result = $dataStmt->get_result();

        $visitors = [];
        while ($row = $result->fetch_assoc()) {
            $visitors[] = $row;
        }
        $dataStmt->close();

        echo json_encode([
            'data' => $visitors,
            'totalRecords' => $totalRecords
        ]);
        break;
    case 'delete_visitor':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM visitors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    // --- SETTINGS MANAGEMENT ---
    case 'get_settings':
        $output = ['product' => null, 'settings' => []];
        $product_result = $conn->query("SELECT * FROM products ORDER BY createdAt DESC LIMIT 1");
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
            if (isset($output['settings']['feedbackImageUrls'])) {
                $decoded_urls = json_decode($output['settings']['feedbackImageUrls'], true);
                $output['settings']['feedbackImageUrls'] = ($decoded_urls !== null) ? $decoded_urls : [];
            }
        }
        echo json_encode($output);
        break;

    case 'save_settings':
        // Update or Insert the single product
        $conn->query("DELETE FROM products"); // Clear old products since it's a single-product site
        $stmt = $conn->prepare("INSERT INTO products (title, imageUrls, videoUrls, createdAt) VALUES (?, ?, ?, NOW())");
        $imageUrlsJson = json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['product-images-urls'])))));
        $videoUrlsJson = json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['product-video-urls'])))));
        $stmt->bind_param("sss", $_POST['product-title'], $imageUrlsJson, $videoUrlsJson);
        $stmt->execute();

        // Prepare to save all other settings in a key-value table
        $settings_to_save = [
            'whatsappNumber' => $_POST['whatsapp-number'],
            'messengerPageId' => $_POST['messenger-page-id'],
            'mainDescription' => $_POST['main-description-text'],
            'package56List' => $_POST['package-56-full-list'],
            'package32List' => $_POST['package-32-full-list'],
            'package99List' => $_POST['package-99-full-list'],
            'priceSmall56' => $_POST['price-small-56'],
            'priceLarge56' => $_POST['price-large-56'],
            'priceSmall32' => $_POST['price-small-32'],
            'priceLarge32' => $_POST['price-large-32'],
            'priceSmall99' => $_POST['price-small-99'],
            'priceLarge99' => $_POST['price-large-99'],
            'deliveryCharge' => $_POST['delivery-charge'],
            'smallSizeImageUrl' => $_POST['small-size-image-url'],
            'largeSizeImageUrl' => $_POST['large-size-image-url'],
            'isSpinningWheelEnabled' => $_POST['enable-spinning-wheel'],
            'isSmallSizeEnabled' => $_POST['enable-small-size'],
            'smallSizeLabel' => $_POST['label-small-size'],
            'isBigSizeEnabled' => $_POST['enable-big-size'],
            'bigSizeLabel' => $_POST['label-big-size'],
            'feedbackTitle' => $_POST['feedback-section-title'],
            'feedbackImageUrls' => json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['feedback-images-urls']))))),
            'feedbackDescription' => $_POST['feedback-section-description']
        ];

        for ($i = 1; $i <= 9; $i++) {
            $settings_to_save['reward' . $i] = $_POST['reward-' . $i];
        }

        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($settings_to_save as $key => $value) {
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }

        echo json_encode(['success' => true]);
        break;

    // --- NEW: PASSWORD MANAGEMENT ---
    case 'update_password':
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            echo json_encode(['error' => 'All fields are required.']);
            exit();
        }

        // Get the logged-in user's email from the session
        if (!isset($_SESSION['admin_email'])) {
            echo json_encode(['error' => 'Session expired. Please log in again.']);
            exit();
        }
        $email = $_SESSION['admin_email'];

        // 1. Get the current hashed password from the DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'User not found.']);
            $stmt->close();
            exit();
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // 2. Verify the 'current_password' from the form
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode(['error' => 'Incorrect current password.']);
            exit();
        }

        // 3. Hash the new password and update the database
        $newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $newHashedPassword, $email);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update password.']);
        }
        $updateStmt->close();
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Admin action not found.']);
        break;
}

$conn->close();
?>