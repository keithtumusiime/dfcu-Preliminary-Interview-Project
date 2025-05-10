<?php
header("Content-Type: application/json");
require 'database.php';

// Simulate payment processing for 100 milliseconds
usleep(100000);

// Check for valid request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status_code' => 405, 'message' => 'Method Not Allowed'], JSON_PRETTY_PRINT);
    exit();
}

// Get serialized input for processing
$input = file_get_contents('php://input');
$request = json_decode($input, true);

// Extract input variables with default values
$payer = $request['payer'] ?? '';
$payee = $request['payee'] ?? '';
$amount = $request['amount'] ?? 0;
$currency = $request['currency'] ?? '';
$payer_reference = $request['payer_reference'] ?? '';
$payer = trim($payer);
$payee = trim($payee);
$payer_reference = trim($payer_reference);

// Input validation
if (!preg_match('/^\d{10}$/', $payer)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['message' => 'Transaction failed: Invalid PAYER account number!'], JSON_PRETTY_PRINT);
    exit();
}
if (!preg_match('/^\d{10}$/', $payee)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['message' => 'Transaction failed: Invalid PAYEE account number!'], JSON_PRETTY_PRINT);
    exit();
}
if ($amount <= 0) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['message' => 'Transaction failed: Invalid amount!'], JSON_PRETTY_PRINT);
    exit();
}

if ($payer === $payee) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['message' => 'Transaction failed: Payer and Payee can not be the same!'], JSON_PRETTY_PRINT);
    exit();
}

// Determine transaction status
$random = rand(1, 100);
$status = 'FAILED';
$status_code = 400;
//to cater for the failed transaction simulated
$failureMessages = [
    'Transaction failed due to insufficient funds.',
    'Transaction declined: account verification failed.',
    'Transaction failed: payer account temporarily restricted.',
    'Transaction failed due to network timeout. Please retry.',
    'Transaction rejected: daily transfer limit exceeded.',
    'Transaction failed: invalid account configuration.',
    'Payment could not be processed at this time.',
    'Transaction aborted due to mismatched currency settings.',
    'Transfer failed: payee account is inactive.',
    'Transaction failed: duplicate transaction detected.',
];

$message = $failureMessages[array_rand($failureMessages)];

if ($random <= 10) {
    $status = 'PENDING';
    $status_code = 100;
    header("HTTP/1.1 202 Pending");
    $message = 'Transaction Pending';
} elseif ($random <= 95) {
    $status = 'SUCCESSFUL';
    $status_code = 200;
    header("HTTP/1.1 200 OK");
    $message = 'Transaction successfully processed';
}else {
    header("HTTP/1.1 400 Bad Request");
}

// Create transaction reference
$transaction_reference = generateTransactionReference();

// Insert into database with error handling
try {
    $stmt = $db->prepare("INSERT INTO transactions (payer_account, payee_account, amount, currency, payer_reference, transaction_reference, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$payer, $payee, $amount, $currency, $payer_reference, $transaction_reference, $status]);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['status_code' => 500, 'message' => 'Transaction failed: Internal Server Error!'], JSON_PRETTY_PRINT);
    exit();
}

// Log transaction for auditing
logTransaction($transaction_reference, $status);

echo json_encode([
    'status_code' => $status_code,
    'message' => $message,
    'transaction_reference' => $transaction_reference
], JSON_PRETTY_PRINT);

// Function to log transaction details
function logTransaction($transaction_reference, $status) {
    $logMessage = sprintf("Transaction Reference: %s, Status: %s, Time: %s\n", 
                          $transaction_reference, $status, date('Y-m-d H:i:s'));
    error_log($logMessage, 3, 'logs/logfile.log'); // Append to a log file
}

// Function to generate a unique transaction reference
function generateTransactionReference() {
    return bin2hex(random_bytes(18));
}
?>
