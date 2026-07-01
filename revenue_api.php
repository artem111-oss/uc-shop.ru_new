<?php
$secret = getenv('API_SECRET');
if ($_GET['key'] !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

$host = 'localhost';
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', strtotime('-1 day'));
    
    $stmt = $pdo->prepare("
        SELECT 
            uc_amount as product_name,
            SUM(price) as total_price,
            COUNT(*) as order_count
        FROM orders
        WHERE status_id = 3
          AND DATE(created_at) = :date
        GROUP BY uc_amount
    ");
    $stmt->execute([':date' => $date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'date' => $date, 'data' => $rows]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}