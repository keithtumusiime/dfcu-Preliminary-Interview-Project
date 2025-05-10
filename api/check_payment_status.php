<?php
header("Content-Type: application/json");
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("HTTP/1.1 400 Method Not Allowed");
    echo json_encode(['message' => 'Method Not Allowed'], JSON_PRETTY_PRINT);
    exit();
}

$transaction_reference = $_GET['transaction_reference'] ?? '';

// Input validation
if (empty($transaction_reference)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['status_code'=>400,'message' => 'Transaction reference is required'], JSON_PRETTY_PRINT);
    exit();
}

// Fetch transaction status
$stmt = $db->prepare("SELECT payer_account, payee_account, amount, currency,payer_reference,transaction_reference,status FROM transactions WHERE transaction_reference = ?");
$stmt->execute([$transaction_reference]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if ($transaction) {
    header("HTTP/1.1 200 OK");
    echo json_encode([
        'status_code'=>200,
        'status' => $transaction['status'],
        'data' => $transaction
    ]);
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status_code'=>404,'message' => 'Transaction with reference '.$transaction_reference.' not found'], JSON_PRETTY_PRINT);
}
?>
