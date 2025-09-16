<?php
require __DIR__ . '/helpers.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$pdo = pdo_connect();
$uid = $_SESSION['user_id'];
// get user
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$uid]);
$user = $stmt->fetch();
// get payment status
$stmt = $pdo->prepare('SELECT * FROM payments WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$uid]);
$pay = $stmt->fetch();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Reunion</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .header h2 {
      color: #0b79d0;
      font-weight: 700;
    }

    .header .nav a {
      text-decoration: none;
      color: #0b79d0;
      font-weight: 500;
      margin-left: 10px;
    }

    .card-section {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .card-section h3 {
      color: #0b79d0;
      margin-bottom: 15px;
    }

    table {
      width: 100%;
    }

    th {
      width: 30%;
      font-weight: 600;
      color: #333;
    }

    td {
      color: #555;
    }

    .btn-pay {
      background-color: #0b79d0;
      color: white;
      border-radius: 50px;
      padding: 10px 25px;
      font-size: 1rem;
      text-decoration: none;
    }

    .btn-pay:hover {
      background-color: #095a9d;
      color: white;
    }

    .payment-status {
      font-weight: 600;
      color: #28a745;
    }

    .payment-failed {
      color: #dc3545;
    }
  </style>
</head>

<body>

  <div class="container">

    <!-- Header -->
    <div class="header">
      <h2><i class="fa-solid fa-dashboard"></i> Dashboard</h2>
      <div class="nav">
        Welcome, <?= e($_SESSION['user_name']) ?> |
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a> |
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>

    <!-- User Details Card -->
    <div class="card-section">
      <h3><i class="fa-solid fa-user"></i> Your Details</h3>
      <table class="table table-borderless">
        <tr>
          <th>Fullname</th>
          <td><?= e($user['fullname']) ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><?= e($user['email']) ?></td>
        </tr>
        <tr>
          <th>Phone</th>
          <td><?= e($user['phone']) ?></td>
        </tr>
        <tr>
          <th>Batch Year</th>
          <td><?= e($user['batch_year']) ?></td>
        </tr>
        <tr>
          <th>Payment Status</th>
          <td>
            <?php if (!$pay): ?>
              <button class="btn btn-danger btn-sm">Payment Not Completed</button>
            <?php elseif ($pay['status'] === 'pending'): ?>
              <button class="btn btn-warning btn-sm">Waiting for Admin Approval</button>
            <?php elseif ($pay['status'] === 'success'): ?>
              <button class="btn btn-success btn-sm">Payment Succeeded</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!$pay || $pay['status'] !== 'success'): ?>
          <tr>
            <th>Payment Action</th>
            <td>
              <button id="give-payment-btn" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-credit-card"></i> Give Payment
              </button>
            </td>
          </tr>
        <?php endif; ?>
      </table>

    </div>


    <!-- Payment Card -->
    <div class="card-section" id="payment-section">
      <h3><i class="fa-solid fa-money-bill-wave"></i> Payment</h3>

      <?php if ($pay && $pay['status'] === 'success'): ?>
        <p>
          🎉 Your payment of <strong>BDT <?= e($pay['amount']) ?></strong> was
          <span class="payment-status">successful</span>.
          Thank you for completing your registration.
        </p>

      <?php elseif ($pay && $pay['status'] === 'pending'): ?>
        <p>
          ✅ You have already submitted your payment request with Transaction ID:
          <strong><?= e($pay['txn_id']) ?></strong>.
        </p>
        <p>
          Please wait for admin approval (01968385155 - Alamin, Batch-16).
        </p>

      <?php else: ?>
        <p>Please send your payment to one of the following accounts:</p>
        <ul>
          <li><strong>Bkash:</strong> 01968385155</li>
          <li><strong>Nagad:</strong> 01521243981</li>
          <li><strong>Rocket:</strong> 01521243981</li>
        </ul>
        <p>Reunion fee: <strong>BDT 500.00</strong></p>

        <!-- Transaction ID Form -->
        <form method="post" action="submit_payment.php" class="mt-3">
          <div class="mb-3">
            <label for="txn_id" class="form-label">Transaction ID</label>
            <input type="text" name="txn_id" id="txn_id" class="form-control" placeholder="Enter your transaction ID"
              required>
          </div>
          <button type="submit" class="btn-pay">
            <i class="fa-solid fa-paper-plane"></i> Submit Payment
          </button>
        </form>
      <?php endif; ?>
    </div>



  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('give-payment-btn').addEventListener('click', function () {
      document.getElementById('payment-section').scrollIntoView({ behavior: 'smooth' });
    });
  </script>
</body>

</html>