<?php
require __DIR__ . '/helpers.php';
ensure_session();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
  header("Location: login.php");
  exit;
}

$super_admin_email = "admin@admin.com";
if ($_SESSION['user_email'] !== $super_admin_email) {
  die("Unauthorized access.");
}

$pdo = pdo_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = intval($_POST['user_id'] ?? 0);
  $status = $_POST['status'] ?? 'pending';

  if ($user_id) {
    $stmt = $pdo->prepare("
      UPDATE payments
      SET status = ?
      WHERE id = (
        SELECT id FROM (
          SELECT id FROM payments WHERE user_id = ? ORDER BY id DESC LIMIT 1
        ) AS latest
      )
    ");
    $stmt->execute([$status, $user_id]);

    if ($stmt->rowCount() > 0) {
      $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment status updated successfully!'];
    } else {
      $_SESSION['flash'] = ['type' => 'danger', 'msg' => '⚠️ Failed to update payment. Please try again.'];
    }
  } else {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => '⚠️ Invalid user selected.'];
  }

  header("Location: users.php");
  exit;
}
