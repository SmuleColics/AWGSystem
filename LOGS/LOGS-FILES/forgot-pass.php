<?php
session_start();

// SET TIMEZONE - IMPORTANT!
date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../INCLUDES/PHPMailer/src/Exception.php';
require '../../INCLUDES/PHPMailer/src/PHPMailer.php';
require '../../INCLUDES/PHPMailer/src/SMTP.php';

include '../../INCLUDES/db-con.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_code'])) {
  $email = trim($_POST['email'] ?? '');

  // -------- VALIDATION -------- //
  if (empty($email)) {
    $errors['email'] = 'Email is required';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
  } else {

    $email_escaped = mysqli_real_escape_string($conn, $email);

    // Check if email exists
    $sql = "SELECT user_id, first_name, last_name FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {

      // FETCH USER DATA
      $user = mysqli_fetch_assoc($result);
      $user_id = $user['user_id'];

      // -------- GENERATE RESET CODE -------- //
      $reset_code = sprintf("%06d", mt_rand(1, 999999));
      
      // Use NOW() + INTERVAL for MySQL compatibility
      $insert_sql = "
        INSERT INTO password_resets (user_id, reset_code, reset_code_expiry)
        VALUES ('$user_id', '$reset_code', DATE_ADD(NOW(), INTERVAL 15 MINUTE))
      ";

      if (mysqli_query($conn, $insert_sql)) {

        // -------- SEND EMAIL -------- //
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
          $mail->Subject = 'Password Reset Code - A We Green Enterprise';
          $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
              <h2 style='color: #16A249;'>Your Password Reset Code</h2>
              <p>Hi {$user['first_name']},</p>
              <p>You requested a password reset code:</p>
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

          $mail->AltBody = "Your password reset code is $reset_code. Expires in 15 minutes.";

          $mail->send();

          $_SESSION['reset_email'] = $email;

          echo "<script>
                  alert('Reset code sent to your email!');
                  window.location='reset-pass.php';
                </script>";
          exit;
        } catch (Exception $e) {
          $errors['email'] = "Failed to send email. Error: " . $mail->ErrorInfo;
        }
      } else {
        $errors['database'] = "Database Error: " . mysqli_error($conn);
      }
    } else {
      $errors['email'] = "No account found with this email";
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
</head>

<body class="green-bg">

  <div class="login-header text-center my-4">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="logo">
    <h1 class="text-white fw-bold fs-30 mt-2 mb-0">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>

  <div class="h-full">
    <div class="flex">
      <div class="bg-white p-4 rounded-3 login-container">

        <div class="mb-4 text-start">
          <h2 class="fs-24 mb-0">Forgot Password</h2>
          <p class="fs-14 light-text">Enter your email to receive a reset code</p>
        </div>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label fs-14 fw-500">Email</label>
            <input 
              type="email" 
              class="form-control fs-14" 
              placeholder="juandelacruz@gmail.com" 
              name="email" 
              required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <?php if (isset($errors['email'])): ?>
              <p class="fs-14 text-danger mt-1"><?= $errors['email'] ?></p>
            <?php endif; ?>
          </div>

          <div class="d-grid mb-3">
            <button type="submit" name="send_code" class="btn green-bg text-white login-btn">
              Send Reset Code
            </button>
          </div>

          <div class="text-center">
            <a href="login.php" class="text-dark text-decoration-none">
              <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>

</body>

</html>