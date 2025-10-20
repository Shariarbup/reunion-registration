<?php
require __DIR__ . '/helpers.php';
$f_flash = flash_get();
$pdo = pdo_connect();
$f = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!$email || !$password) {
    $f = 'Provide email and password.';
  } else {
    $stmt = $pdo->prepare('SELECT id,password_hash,fullname,email FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      session_start();
      $_SESSION['user_id'] = $u['id'];
      $_SESSION['user_name'] = $u['fullname'];
      $_SESSION['user_email'] = $u['email'];
      header('Location: dashboard.php');
      exit;
    } else {
      $f = 'Invalid credentials.';
    }
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Reunion</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .login-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      background: #fff;
      border-radius: 12px;
      padding: 40px 30px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 420px;
    }

    .login-card h2 {
      color: #0b79d0;
      font-weight: 700;
      margin-bottom: 25px;
      text-align: center;
    }

    .login-card .form-label {
      font-weight: 500;
    }

    .login-card .btn {
      width: 100%;
      padding: 12px;
      font-size: 1.1rem;
      border-radius: 50px;
    }

    .login-card .nav-links {
      margin-top: 15px;
      text-align: center;
    }

    .login-card .nav-links a {
      text-decoration: none;
      color: #0b79d0;
      font-weight: 500;
    }

    .error {
      margin-bottom: 15px;
      padding: 12px 15px;
      background: #fde2e2;
      border: 1px solid #f5c2c2;
      color: #842029;
      border-radius: 8px;
      text-align: center;
    }
  </style>
</head>

<body>

  <div class="login-container">
    <div class="login-card">
      <h2><i class="fa-solid fa-right-to-bracket"></i> Login</h2>

      <!-- Flash/Error message -->
      <!-- Flash/Error message -->
      <?php if ($f_flash): ?>
        <div class="alert alert-success text-center mb-3">
          <?= e($f_flash) ?>
        </div>
      <?php elseif ($f): ?>
        <div class="error"><?= e($f) ?></div>
      <?php endif; ?>


      <!-- Login Form -->
      <form method="post">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" required
              placeholder="Enter your password">
            <span class="input-group-text" id="togglePassword" style="cursor:pointer;">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>
        </div>


        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
      </form>

      <div class="nav-links mt-3">
        <a href="register.php"><i class="fa-solid fa-user-plus"></i> Register</a> |
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const password = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword").querySelector("i");

    document.getElementById("togglePassword").addEventListener("click", () => {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);

      // Toggle the eye / eye-slash
      togglePassword.classList.toggle("fa-eye");
      togglePassword.classList.toggle("fa-eye-slash");
    });
  </script>


</body>

</html>