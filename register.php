<?php
require __DIR__ . '/helpers.php';
$pdo = pdo_connect();
$f = null;
$errors = []; // array to hold input-specific errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $batch = intval($_POST['batch_year'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // PHP server-side validation
    if (strlen($fullname) < 2) {
        $errors['fullname'] = 'Full name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email.';
    }
    if ($phone && !preg_match('/^\d{11}$/', $phone)) {
        $errors['phone'] = 'Phone number must be 11 digits.';
    }
    if (!$password || strlen($password) < 3) {
        $errors['password'] = 'Password must be at least 3 characters.';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords did not match.';
    }

    // Check for duplicate email or phone in database
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, 
                               (SELECT COUNT(*) FROM users WHERE email=?) AS email_exists,
                               (SELECT COUNT(*) FROM users WHERE phone=?) AS phone_exists");
        $stmt->execute([$email, $phone]);
        $result = $stmt->fetch();

        if ($result['email_exists'] > 0) {
            $errors['email'] = 'Email is already registered.';
        }
        if ($phone && $result['phone_exists'] > 0) {
            $errors['phone'] = 'Phone number is already registered.';
        }
    }

    // If no errors, insert new user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (fullname,email,phone,batch_year,password_hash) VALUES (?,?,?,?,?)');
        try {
            $stmt->execute([$fullname, $email, $phone, $batch ? $batch : null, $hash]);
            flash_set('Registration successful. Please login and complete your payment.');
            header('Location: login.php'); // redirect to login page
            exit;
        } catch (Exception $e) {
            $f = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Reunion</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 600px;
      margin-top: 50px;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
    }

    .header h2 {
      color: #0b79d0;
    }

    .error-msg {
      color: #dc3545;
      font-size: 0.9rem;
      margin-top: 5px;
    }

    .btn-submit {
      background-color: #0b79d0;
      color: #fff;
      border-radius: 50px;
      padding: 10px 25px;
    }

    .btn-submit:hover {
      background-color: #095a9d;
      color: #fff;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="header text-center mb-5 p-4 bg-primary text-white rounded-3 shadow-sm">
      <h2 class="fw-bold mb-2 text-white">
        <i class="fa-solid fa-user-plus me-2"></i>SSC 1994 Reunion
      </h2>
      <p class="mb-3">Register to reconnect with your classmates and celebrate together.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="index.php" class="btn btn-light btn-sm fw-bold">
          <i class="fa-solid fa-house me-1"></i> Home
        </a>
        <a href="login.php" class="btn btn-outline-light btn-sm fw-bold">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Login
        </a>
      </div>
    </div>



    <?php if ($f): ?>
      <div class="alert alert-danger"><?= e($f) ?></div><?php endif; ?>

    <form method="post" id="registerForm" novalidate>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" value="<?= e($_POST['fullname'] ?? '') ?>" required placeholder="Enter your full name">
        <?php if (isset($errors['fullname'])): ?>
          <div class="error-msg"><?= e($errors['fullname']) ?></div><?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required placeholder="Enter your email">
        <?php if (isset($errors['email'])): ?>
          <div class="error-msg"><?= e($errors['email']) ?></div><?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>" required
          placeholder="11-digit phone number">
        <?php if (isset($errors['phone'])): ?>
          <div class="error-msg"><?= e($errors['phone']) ?></div><?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Batch Year</label>
        <select name="batch_year" class="form-select">
          <?php
          for ($y = 1980; $y <= 2025; $y++) {
            $selected = ($y == ($_POST['batch_year'] ?? 1994)) ? 'selected' : '';
            echo "<option value='$y' $selected>$y</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
        <?php if (isset($errors['password'])): ?>
          <div class="error-msg"><?= e($errors['password']) ?></div><?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
        <?php if (isset($errors['confirm_password'])): ?>
          <div class="error-msg"><?= e($errors['confirm_password']) ?></div><?php endif; ?>
      </div>

      <button type="submit" class="btn btn-submit w-100">Register</button>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Client-side validation -->
  <script>
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      let form = e.target;
      let fullname = form.fullname.value.trim();
      let email = form.email.value.trim();
      let phone = form.phone.value.trim();
      let password = form.password.value;
      let confirm_password = form.confirm_password.value;
      let valid = true;

      // Clear previous messages
      document.querySelectorAll('.error-msg').forEach(el => el.innerText = '');

      // Full name validation
      if (fullname.length < 2) {
        document.querySelector('[name="fullname"]').nextElementSibling.innerText = 'Full name must be at least 2 characters.';
        valid = false;
      }

      // Email validation
      if (!/^\S+@\S+\.\S+$/.test(email)) {
        document.querySelector('[name="email"]').nextElementSibling.innerText = 'Enter a valid email.';
        valid = false;
      }

      // Phone validation
      if (phone && !/^\d{11}$/.test(phone)) {
        document.querySelector('[name="phone"]').nextElementSibling.innerText = 'Phone number must be 11 digits.';
        valid = false;
      }

      // Password validation
      if (password.length < 6) {
        document.querySelector('[name="password"]').nextElementSibling.innerText = 'Password must be at least 6 characters.';
        valid = false;
      }

      // Confirm password match
      if (password !== confirm_password) {
        document.querySelector('[name="confirm_password"]').nextElementSibling.innerText = 'Passwords do not match.';
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  </script>

</body>

</html>