<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/api/config/db_config.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// Get user_id from GET parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID'
    ]);
    exit();
}

// Query to count total orders for the user
// Counting only Completed orders (excluding Preparing, Cancelled)
$sql = "SELECT COUNT(*) as total_orders 
        FROM orders 
        WHERE CustomerID = ? 
        AND OrderStatus = 'Completed'";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Query preparation failed: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'total_orders' => intval($row['total_orders']),
        'user_id' => $user_id
    ]);
} else {
    // No orders found, return 0
    echo json_encode([
        'success' => true,
        'total_orders' => 0,
        'user_id' => $user_id
    ]);
}

$stmt->close();
$conn->close();
?>