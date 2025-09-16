<?php
require __DIR__ . '/helpers.php';
ensure_session();

// Only allow super admin to download
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin@admin.com') {
    die("Unauthorized access.");
}

$pdo = pdo_connect();
$batch_filter = $_GET['batch'] ?? '';

// Fetch users of the specified batch
$sql = "SELECT u.id, u.fullname, u.email, u.phone, u.batch_year, p.status AS payment_status, p.txn_id
        FROM users u
        LEFT JOIN payments p ON p.user_id = u.id";
$params = [];
if ($batch_filter) {
    $sql .= " WHERE u.batch_year = ?";
    $params[] = $batch_filter;
}
$sql .= " ORDER BY u.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$users) {
    die("No users found for batch $batch_filter");
}

// Set headers to force download as Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_batch_$batch_filter.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output column headers
$columns = array_keys($users[0]);
echo implode("\t", $columns) . "\n";

// Output data rows
foreach ($users as $row) {
    echo implode("\t", $row) . "\n";
}
exit;
