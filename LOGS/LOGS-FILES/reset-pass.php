<?php
session_start();

// SET TIMEZONE - IMPORTANT!
date_default_timezone_set('Asia/Manila');

include '../../INCLUDES/db-con.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../INCLUDES/PHPMailer/src/Exception.php';
require '../../INCLUDES/PHPMailer/src/PHPMailer.php';
require '../../INCLUDES/PHPMailer/src/SMTP.php';

$errors = [];

// RESEND CODE LOGIC (must come first to avoid conflicts)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
  if (!isset($_SESSION['reset_email'])) {
    $errors['email'] = "Please enter your email first.";
  } else {
    $email = $_SESSION['reset_email'];
    $email_escaped = mysqli_real_escape_string($conn, $email);

    $sql = "SELECT user_id, first_name, last_name FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
      $user = mysqli_fetch_assoc($result);
      $user_id = $user['user_id'];

      $reset_code = sprintf("%06d", mt_rand(1, 999999));

      // FIXED: Use MySQL's DATE_ADD instead of PHP date()
      $insert_sql = "
        INSERT INTO password_resets (user_id, reset_code, reset_code_expiry)
        VALUES ('$user_id', '$reset_code', DATE_ADD(NOW(), INTERVAL 15 MINUTE))
      ";
      
      if (mysqli_query($conn, $insert_sql)) {
        $mail = new PHPMailer(true);
        try {
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'johnlowe1598@gmail.com';
          $mail->Password = 'msbo sibo cwyy lrpd';
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port = 587;

          $mail->setFrom('awegreen@gmail.com', 'A We Green Enterprise');
          $mail->addAddress($email, $user['first_name'] . ' ' . $user['last_name']);

          $mail->isHTML(true);
          $mail->Subject = 'Your New Password Reset Code - A We Green Enterprise';
          $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
              <h2 style='color: #16A249;'>New Password Reset Code</h2>
              <p>Hi {$user['first_name']},</p>
              <p>You requested a new password reset code:</p>
              <div style='text-align: center; margin: 20px 0;'>
                <h1 style='background: #f0f0f0; padding: 20px; color: #16A249; letter-spacing: 5px; font-size: 32px;'>
                  $reset_code
                </h1>
              </div>
              <p>This code will expire in <strong>15 minutes</strong>.</p>
              <p style='color: #666; font-size: 12px; margin-top: 30px;'>
                If you didn't request this, please ignore this email.
              </p>
            </div>
          ";

          $mail->send();
          echo "<script>alert('A new reset code has been sent to your email!');</script>";
        } catch (Exception $e) {
          $errors['email'] = "Failed to resend code.";
        }
      } else {
        $errors['database'] = "Database Error: " . mysqli_error($conn);
      }
    }
  }
}

// RESET PASSWORD LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['resend_code'])) {

  if (!isset($_SESSION['reset_email'])) {
    die("Unauthorized access. Please start from the forgot password page.");
  }

  $email = $_SESSION['reset_email'];
  $email_escaped = mysqli_real_escape_string($conn, $email);
  $code = trim($_POST['reset_code'] ?? '');
  $new_pass = $_POST['new_password'] ?? '';
  $confirm_pass = $_POST['confirm_password'] ?? '';

  // Validation
  if (empty($code)) {
    $errors['code'] = "Reset code is required!";
  }

  if (empty($new_pass)) {
    $errors['pass'] = "New password is required!";
  } elseif (strlen($new_pass) < 6) {
    $errors['pass'] = "Password must be at least 6 characters long!";
  } elseif ($new_pass !== $confirm_pass) {
    $errors['pass'] = "Passwords do not match!";
  }

  // If no errors so far, proceed
  if (empty($errors)) {
    // Get user ID
    $u = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email_escaped'");
    
    if ($u && mysqli_num_rows($u) > 0) {
      $user = mysqli_fetch_assoc($u);
      $uid = $user['user_id'];

      // Validate reset code
      $code_escaped = mysqli_real_escape_string($conn, $code);
      $sql = "
        SELECT * FROM password_resets
        WHERE user_id = '$uid'
        AND reset_code = '$code_escaped'
        AND reset_code_expiry >= NOW()
        ORDER BY reset_id DESC
        LIMIT 1
      ";

      $res = mysqli_query($conn, $sql);

      if ($res && mysqli_num_rows($res) > 0) {
        // Valid code - update password
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        $update_sql = "UPDATE users SET password='$hashed' WHERE user_id='$uid'";
        if (mysqli_query($conn, $update_sql)) {
          // Delete used codes
          mysqli_query($conn, "DELETE FROM password_resets WHERE user_id='$uid'");

          // Clear session
          unset($_SESSION['reset_email']);

          echo "<script>
            alert('Password reset successful! You can now login with your new password.');
            window.location='login.php';
          </script>";
          exit;
        } else {
          $errors['database'] = "Failed to update password: " . mysqli_error($conn);
        }
      } else {
        $errors['code'] = "Invalid or expired code! Please request a new one.";
      }
    } else {
      $errors['email'] = "User not found!";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png">
</head>

<body class="green-bg">

  <div class="login-header text-center my-4">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="awegreen-logo">
    <h1 class="text-white fs-30 fw-bold mb-0 mt-2">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>

  <div class="h-full">
    <div class="flex">
      <div class="bg-white p-4 rounded-3 login-container">

        <div class="mb-4 text-start">
          <h2 class="fs-24 mb-1">Reset Password</h2>
          <p class="fs-14 light-text">Enter the reset code and your new password</p>
        </div>

        <form action="" method="post">

          <!-- Reset Code -->
          <div class="mb-3">
            <label for="reset_code" class="form-label fs-14 fw-500">6-digit code</label>
            <input
              type="text"
              maxlength="6"
              class="form-control fs-14"
              id="reset_code"
              placeholder="123456"
              name="reset_code"
              value="<?= htmlspecialchars($_POST['reset_code'] ?? '') ?>"
              >
            <?php if (isset($errors['code'])): ?>
              <p class="fs-14 text-danger mt-1 mb-0"><?= $errors['code'] ?></p>
            <?php endif; ?>
          </div>

          <!-- New Password -->
          <div class="mb-3 position-relative">
            <label for="new_password" class="form-label fs-14 fw-500">New Password</label>
            <input
              type="password"
              class="form-control fs-14"
              id="new_password"
              placeholder="••••••••"
              name="new_password"
              minlength="6"
              >
          </div>

          <!-- Confirm Password -->
          <div class="mb-3 position-relative">
            <label for="confirm_password" class="form-label fs-14 fw-500">Confirm Password</label>
            <input
              type="password"
              class="form-control fs-14"
              id="confirm_password"
              placeholder="••••••••"
              name="confirm_password"
              minlength="6"
              >
            <?php if (isset($errors['pass'])): ?>
              <p class="text-danger fs-14 mt-1 mb-0"><?= $errors['pass'] ?></p>
            <?php endif; ?>
          </div>

          <!-- Resend Code -->
          <div class="mb-3">
            <p class="fs-14 mb-0">
              Didn't receive the code?
              <button type="submit" name="resend_code" class="btn btn-link p-0 fs-14 text-decoration-none">
                Resend Code
              </button>
            </p>
          </div>

          <!-- Buttons -->
          <div class="mt-4 d-flex justify-content-between gap-2">
            <a href="forgot-pass.php" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-green text-white px-4">
              Reset Password
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>

</body>

</html>