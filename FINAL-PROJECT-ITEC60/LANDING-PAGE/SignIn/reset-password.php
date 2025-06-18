<?php
session_start();
include 'db-con.php';

$code_error = "";
$pass_error = "";
$success_msg = "";
$reset_code_value = "";
$new_pass_value = "";
$confirm_pass_value = "";
$valid_code = true;
$valid_pass = true;

// Redirect if no email in session
if (!isset($_SESSION['reset_email'])) {
    echo "<div style='color:red;text-align:center;margin-top:40px;'>Session expired. Please <a href='forgot-pass.php'>start again</a>.</div>";
    exit;
}

$email = $_SESSION['reset_email'];

if (isset($_POST['reset-pass-button'])) {
    $reset_code_value = trim($_POST['reset_code']);
    $new_pass_value = $_POST['new_password'];
    $confirm_pass_value = $_POST['confirm_password'];

    // Fetch stored code and expiry
    $query = mysqli_query($con, "SELECT reset_code, reset_expires FROM tbl_signup_acc WHERE signup_email='$email' LIMIT 1");
    $row = mysqli_fetch_assoc($query);

    $now = date('Y-m-d H:i:s');
    if (!$row || !$row['reset_code'] || !$row['reset_expires'] || $row['reset_expires'] < $now) {
        $code_error = "Reset code expired or invalid. Please request a new code.";
        $valid_code = false;
    } elseif ($reset_code_value !== $row['reset_code']) {
        $code_error = "Incorrect reset code.";
        $valid_code = false;
    } elseif (strlen($new_pass_value) < 6) {
        $pass_error = "Password must be at least 6 characters.";
        $valid_pass = false;
    } elseif ($new_pass_value !== $confirm_pass_value) {
        $pass_error = "Passwords do not match.";
        $valid_pass = false;
    } else {
        // Update password, clear reset_code & reset_expires
        $hashed = password_hash($new_pass_value, PASSWORD_DEFAULT);
        mysqli_query($con, "UPDATE tbl_signup_acc SET signup_password='$hashed', reset_code=NULL, reset_expires=NULL WHERE signup_email='$email'");
        unset($_SESSION['reset_email']);
        echo "<script>alert('Password reset successful!'); window.location.href='LP-SignIn.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="SignIn.css">
  <link rel="stylesheet" href="../../DASHBOARD-CSS/for-all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="icon" href="../../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
  <style>
    :root {
      --bg-color: #212529;
      --box-text: #e9e0e0;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background: #181a1b;
    }
    .signin-inputs:focus + label,
    .signin-inputs:not(:placeholder-shown) + label {
      top: 2px;
      left: 15px;
      font-size: 12px;
      opacity: 0.7;
    }
    .signin-inputs::placeholder {
      color: transparent;
    }
    .code-error-msg,
    .pass-error-msg {
      color: #dc3545;
      font-size: 14px;
      text-align: left;
      margin-top: 2px;
      margin-bottom: 0;
      padding-left: 2px;
    }
    .success-msg {
      color: #28a745;
      font-size: 15px;
      text-align: left;
      margin-top: 8px;
      margin-bottom: 8px;
      padding-left: 2px;
    }
    .invalid {
      border-color: #dc3545 !important;
    }
    .signin-header {
      height: 55px;
    }
    .landing-page-img {
      width: 100%;
      max-height: 735px;
      opacity: .7;
      object-fit: cover;
      filter: brightness(40%);
      -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1), rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0));
      mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1), rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0));
    }
    .signin-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: var(--bg-color);
      padding: 30px 32px 20px 32px;
      border-radius: 10px;
      color: white;
      width: 400px;
      text-align: center;
      z-index: 200;
    }
    .signin-text {
      font-size: 32px;
      text-align: left;
    }
    .signin-form {
      padding: 0;
      margin-bottom: 0;
    }
    .input-container {
      position: relative;
      margin-bottom: 0.5rem;
      width: 100%;
      display: flex;
      align-items: flex-end;
      gap: 0.5rem;
    }
    .signin-inputs {
      flex: 1 1 auto;
      padding: 16px 14px 16px 14px;
      font-size: 18px;
      border: 1px solid var(--box-text);
      border-radius: 6px;
      color: white;
      background: transparent;
      outline: none;
      transition: border-color 0.2s;
    }
    .input-container label {
      position: absolute;
      left: 16px;
      top: 16px;
      color: white;
      font-size: 18px;
      pointer-events: none;
      transition: 0.2s ease-in-out;
      background: transparent;
    }
    @media (max-width: 576px) {
      .signin-container {
        width: 100%;
        top: 70%;
        left: 50%;
        padding: 18px 8px;
        min-width: 0;
      }
      .signin-header {
        justify-content: flex-end;
        margin-right: 30px;
      }
    }
  </style>
</head>

<body class="bg-dark overflow-hidden">
  <header class="signin-header fixed-top d-flex align-items-center ms-5 fw-semibold">
    <div class="left-header fs-20">
      <a class="navbar-brand fw-semibold db-text-sec ms-5"  href="../LandingPageMovie.php">Cine<span class="db-text-primary">Vault</span></a>
    </div>
  </header>

  <main>
    <section class="position-relative">
      <img class="landing-page-img" src="../ImagesLP/LandingPageWallpaper.jpg" alt="">
      <div class="signin-container">
        <h1 class="signin-text db-text-sec mb-4 text-start">Reset Password</h1>
        <form class="signin-form db-text-sec p-0 mb-3" action="" method="post" autocomplete="off">
          <div class="input-container mt-3 d-flex gap-2 align-items-center p-0">
            <input
              class="signin-inputs bg-transparent <?php echo !$valid_code ? 'invalid' : ''; ?>"
              type="text"
              name="reset_code"
              id="reset_code"
              maxlength="4"
              placeholder=" "
              required
              value="<?php echo htmlspecialchars($reset_code_value); ?>"
              autocomplete="off"
            >
            <label for="reset_code">4-digit code</label>
          </div>
          <?php if(!$valid_code): ?>
            <p class="code-error-msg"><?php echo $code_error ?></p>
          <?php endif; ?>

          <div class="input-container mt-3 d-flex gap-2 align-items-center p-0">
            <input
              class="signin-inputs bg-transparent <?php echo !$valid_pass ? 'invalid' : ''; ?>"
              type="password"
              name="new_password"
              id="new_password"
              placeholder=" "
              required
              value="<?php echo htmlspecialchars($new_pass_value); ?>"
              autocomplete="off"
            >
            <label for="new_password">New password</label>
          </div>

          <div class="input-container mt-3 d-flex gap-2 align-items-center p-0">
            <input
              class="signin-inputs bg-transparent mb-2 <?php echo !$valid_pass ? 'invalid' : ''; ?>"
              type="password"
              name="confirm_password"
              id="confirm_password"
              placeholder=" "
              required
              value="<?php echo htmlspecialchars($confirm_pass_value); ?>"
              autocomplete="off"
            >
            <label for="confirm_password">Confirm password</label>
          </div>
          <?php if(!$valid_pass): ?>
            <p class="pass-error-msg"><?php echo $pass_error ?></p>
          <?php endif; ?>
          <?php if($success_msg): ?>
            <p class="success-msg"><?php echo $success_msg ?></p>
          <?php endif; ?>
          <div class="mt-3 d-flex justify-content-end gap-2">
            <a href="forgot-pass.php" class="btn btn-secondary">Cancel</a>
            <button class="btn db-bg-primary db-text-sec text-nowrap m-0" type="submit" name="reset-pass-button">Reset Password</button>
          </div>
        </form>
    
      </div>
    </section>
  </main>
</body>
</html>