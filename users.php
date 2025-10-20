<?php
require __DIR__ . '/helpers.php';
ensure_session();
$isSuperAdmin = false;
if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@admin.com') {
    $isSuperAdmin = true;
}

$pdo = pdo_connect();

// Batch + Phone filter
$batch_filter = $_GET['batch'] ?? '';
$phone_filter = trim($_GET['phone'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 2;
$offset = ($page - 1) * $limit;

$params = [];

// Count total users
$sql_count = "SELECT COUNT(*) FROM users WHERE 1=1";
if ($batch_filter) {
    $sql_count .= " AND batch_year = ?";
    $params[] = $batch_filter;
}
if ($phone_filter) {
    $sql_count .= " AND phone LIKE ?";
    $params[] = "%$phone_filter%";
}
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Fetch users
$sql = "SELECT u.*, p.status AS payment_status, p.txn_id
        FROM users u
        LEFT JOIN payments p ON p.user_id = u.id
        WHERE 1=1";

$params = [];
if ($batch_filter) {
    $sql .= " AND u.batch_year = ?";
    $params[] = $batch_filter;
}
if ($phone_filter) {
    $sql .= " AND u.phone LIKE ?";
    $params[] = "%$phone_filter%";
}
$sql .= " ORDER BY u.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Total registered users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$overall_total_registrations = $stmt->fetchColumn();

// Total successful payments
$stmt = $pdo->query("SELECT COUNT(*) 
                     FROM payments 
                     WHERE status = 'success'");
$overall_total_paid = $stmt->fetchColumn();

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Registered Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            margin-top: 40px;
        }

        .table th {
            background: #0b79d0;
            color: #fff;
        }

        .pagination a {
            margin: 0 3px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa-solid fa-users"></i> Registered Users</h2>
            <div>
                <a href="index.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-house"></i> Home
                </a>
                <a href="dashboard.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>


        <!-- Batch + Phone Filter -->
        <form method="get" class="mb-3 d-flex">
            <select name="batch" class="form-select w-auto me-2">
                <option value="">All Batches</option>
                <?php for ($y = 1980; $y <= 2025; $y++): ?>
                    <option value="<?= $y ?>" <?= ($batch_filter == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <input type="text" name="phone" value="<?= e($_GET['phone'] ?? '') ?>" class="form-control w-auto me-2"
                placeholder="Search by Phone">

            <button type="submit" class="btn btn-primary me-2">Filter</button>

            <!-- Clear filter button -->
            <a href="users.php" class="btn btn-secondary">Clear</a>
        </form>
        <!-- Overall Summary Section -->
        <div class="mb-3">
            <span class="badge bg-dark">Overall Registrations: <?= e($overall_total_registrations) ?></span>
            <span class="badge bg-success">Overall Successful Payments: <?= e($overall_total_paid) ?></span>
        </div>

        <?php if ($isSuperAdmin): ?>
            <a href="download_excel.php?batch=<?= e($batch_filter) ?>" class="btn btn-success mb-3">
                <i class="fa-solid fa-file-excel"></i> Download Excel
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash'])): ?>
            <div id="flash-msg" class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show"
                role="alert">
                <?= $_SESSION['flash']['msg'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); // clear flash message ?>
        <?php endif; ?>

        <!-- Users Table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Batch</th>
                    <th>Payment</th>
                    <?php if ($isSuperAdmin): ?>
                        <th>Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= e($u['id']) ?></td>
                            <td><?= e($u['fullname']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['phone']) ?></td>
                            <td><?= e($u['batch_year']) ?></td>
                            <td>
                                <?php if ($u['payment_status']): ?>
                                    <?= e($u['payment_status']) ?>
                                    <?php if ($u['txn_id']): ?><br><small>ID: <?= e($u['txn_id']) ?></small><?php endif; ?>
                                <?php else: ?>
                                    Not Paid
                                <?php endif; ?>
                            </td>
                            <?php if ($isSuperAdmin): ?>
                                <td>
                                    <form method="post" action="update_payment.php" class="d-flex">
                                        <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
                                        <select name="status" class="form-select form-select-sm me-2">
                                            <option value="pending" <?= ($u['payment_status'] === 'pending') ? 'selected' : '' ?>>
                                                Pending</option>
                                            <option value="success" <?= ($u['payment_status'] === 'success') ? 'selected' : '' ?>>
                                                Success</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success">Update</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $isSuperAdmin ? 7 : 6 ?>" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&batch=<?= e($batch_filter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

    </div>
</body>

</html>