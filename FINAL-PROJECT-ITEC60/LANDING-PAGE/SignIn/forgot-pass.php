<?php
session_start();
include 'db-con.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$email_error = "";
$email_value = "";
$valid_email = true;

if (isset($_POST['send-code-button'])) {
    $email = trim($_POST['email']);
    $email_value = htmlspecialchars($email);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Please enter a valid email address.";
        $valid_email = false;
    } else {
        $result = mysqli_query($con, "SELECT * FROM tbl_signup_acc WHERE signup_email='$email' LIMIT 1");
        if (mysqli_num_rows($result) == 1) {
            $reset_code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            mysqli_query($con, "UPDATE tbl_signup_acc SET reset_code='$reset_code', reset_expires='$expires' WHERE signup_email='$email'");

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'oxy467777@gmail.com'; // Change to your Gmail
                $mail->Password = 'qnee ctax qhdw eebc'; // Change to your App Password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('oxy467777@gmail.com', 'CineVault');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "CineVault Password Reset Code";
                $mail->Body    = "Your 4-digit password reset code is: <b>$reset_code</b>";

                $mail->send();
                $_SESSION['reset_email'] = $email;
                echo "<script>alert('The verification code has been sent to your email'); window.location.href='reset-password.php';</script>";
                exit;
            } catch (Exception $e) {
                $email_error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $valid_email = false;
            }
        } else {
            $email_error = "Email does not exist in our records.";
            $valid_email = false;
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
    .forgot-pass:hover {
      text-decoration: underline;
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
    .email-error-msg {
      color: #dc3545;
      font-size: 14px;
      text-align: left;
      margin-top: 2px;
      margin-bottom: 0;
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
        <h1 class="signin-text db-text-sec mb-4 text-start">Forgot Password</h1>
        <form class="signin-form db-text-sec p-0 mb-3" action="" method="post" autocomplete="off">
          <div class="input-container mb-0  d-flex gap-2 align-items-center  p-0">
            <input
              class="signin-inputs bg-transparent <?php echo !$valid_email ? 'invalid' : ''; ?>"
              type="text"
              name="email"
              id="email"
              placeholder=" "
              requireds
              value="<?php echo $email_value; ?>"
              aria-describedby="emailHelp"
              autocomplete="off"
            >
            <label for="email">Email address</label>
          </div>
          <?php if(!$valid_email): ?>
            <p class="email-error-msg" id="emailHelp"><?php echo $email_error ?></p>
          <?php endif; ?>
          <div class="mt-3 d-flex justify-content-end gap-2">
            <a href="LP-SignIn.php" class="btn btn-secondary">Cancel</a>
            <button class="btn db-bg-primary db-text-sec text-nowrap m-0" type="submit" name="send-code-button">Send Code</button>
          </div>
        </form>
      
      </div>
    </section>
  </main>
</body>
</html>