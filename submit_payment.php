<?php
require __DIR__ . '/helpers.php';
ensure_session();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = pdo_connect();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $txn_id = trim($_POST['txn_id'] ?? '');

    if ($txn_id === '') {
        flash_set("Transaction ID is required.");
        header('Location: dashboard.php');
        exit;
    }

    // Insert payment with status = pending
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, status, txn_id) VALUES (?, ?, 'pending', ?)");
    $stmt->execute([$user_id, 500.00, $txn_id]);

    flash_set("Your payment request has been submitted. Please wait for admin approval.");
    header('Location: dashboard.php');
    exit;
}
