<?php
require __DIR__ . '/helpers.php';
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}
$f = flash_get();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bogra Cantonment Board HS - SSC 1994 Reunion</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, #0b79d0, #3bb4f2);
      color: white;
      padding: 80px 20px;
      text-align: center;
      border-radius: 0.75rem;
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
    }

    .hero h1 {
      font-weight: 700;
      font-size: 3rem;
    }

    .hero h2 {
      font-size: 1.8rem;
      margin-top: 10px;
    }

    .hero .btn {
      margin-top: 20px;
      padding: 12px 25px;
      font-size: 1.1rem;
      border-radius: 50px;
    }

    /* Countdown */
    .countdown {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
    }

    .countdown .time-box {
      background: rgba(255, 255, 255, 0.2);
      padding: 15px 20px;
      border-radius: 10px;
      text-align: center;
      min-width: 70px;
    }

    .countdown .time-box h3 {
      font-size: 1.5rem;
      margin-bottom: 5px;
    }

    /* Reunion Info Cards */
    .info-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      padding: 30px;
      margin-bottom: 30px;
      transition: transform 0.3s;
    }

    .info-card:hover {
      transform: translateY(-5px);
    }

    .info-card h3 {
      color: #0b79d0;
      margin-bottom: 15px;
    }

    .info-card ul li {
      margin-bottom: 10px;
      font-size: 1rem;
    }

    /* Flash message */
    .flash-message {
      margin-top: 20px;
    }

    footer {
      text-align: center;
      padding: 20px 0;
      color: #666;
      margin-top: 40px;
    }
  </style>
</head>

<body>

  <div class="container my-5">

    <!-- Hero Section -->
    <div class="hero">
      <h1>Bogra Cantonment Board HS</h1>
      <h2>SSC 1994 Reunion</h2>

      <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="register.php" class="btn btn-light btn-lg"><i class="fa-solid fa-user-plus"></i> Register</a>
          <a href="login.php" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
          <a href="users.php" class="btn btn-warning btn-lg text-white">
            <i class="fa-solid fa-user-check"></i> Already Registered?
          </a>
        <?php else: ?>
          <a href="dashboard.php" class="btn btn-light btn-lg"><i class="fa-solid fa-user"></i> Profile</a>
          <a href="users.php" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-users"></i> All
            Registered</a>
          <a href="logout.php" class="btn btn-danger btn-lg"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <?php endif; ?>
      </div>

      <!-- Registration Time Left Heading -->
      <h3 class="mt-5 mb-3"><i class="fa-solid fa-hourglass-half"></i> Registration Time Left</h3>

      <!-- Countdown -->
      <div class="countdown mt-3" id="countdown">
        <div class="time-box">
          <h3 id="days">0</h3>
          <span>Days</span>
        </div>
        <div class="time-box">
          <h3 id="hours">0</h3>
          <span>Hours</span>
        </div>
        <div class="time-box">
          <h3 id="minutes">0</h3>
          <span>Minutes</span>
        </div>
        <div class="time-box">
          <h3 id="seconds">0</h3>
          <span>Seconds</span>
        </div>
      </div>
    </div>


    <!-- Flash Message -->
    <?php if ($f): ?>
      <div class="alert alert-success flash-message" role="alert">
        <?= e($f) ?>
      </div>
    <?php endif; ?>

    <!-- Reunion Date Card -->
    <div class="info-card">
      <h3><i class="fa-solid fa-calendar-days"></i> Reunion Date</h3>
      <p class="fs-5"><strong>Saturday, 25th October 2025</strong></p>
    </div>

    <!-- Guidelines Card -->
    <div class="info-card">
      <h3><i class="fa-solid fa-book"></i> Guidelines for Attendees</h3>
      <ul>
        <li>Register online before 10th October 2025.</li>
        <li>Reunion fee: BDT 500 (pay online after login).</li>
        <li>Bring your ID card or SSC certificate for verification.</li>
        <li>Follow all COVID-19 safety protocols.</li>
        <li>Parking available on campus premises.</li>
      </ul>
    </div>

    <!-- Footer -->
    <footer>
      &copy; 2025 Bogra Cantonment Board High School. All rights reserved.
    </footer>

  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Countdown Script -->
  <script>
    const countdownDate = new Date("October 25, 2025 09:00:00").getTime();

    const countdownFunction = setInterval(() => {
      const now = new Date().getTime();
      const distance = countdownDate - now;

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      document.getElementById("days").innerText = days;
      document.getElementById("hours").innerText = hours;
      document.getElementById("minutes").innerText = minutes;
      document.getElementById("seconds").innerText = seconds;

      if (distance < 0) {
        clearInterval(countdownFunction);
        document.getElementById("countdown").innerHTML = "<span class='fs-4'>The reunion is happening now!</span>";
      }
    }, 1000);
  </script>

</body>

</html>