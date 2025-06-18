<?php
include 'db-con.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="SignIn.css">
  <link rel="stylesheet" href="../../DASHBOARD-CSS/for-all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="icon" href="../../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
</head>

<body class="bg-dark overflow-hidden">
  <header class="signin-header fixed-top d-flex align-items-center ms-5 fw-semibold">
    <div class="left-header fs-20">
      <a class="navbar-brand fw-semibold db-text-sec ms-5" href="../LandingPageMovie.php">Cine<span
          class="db-text-primary">Vault</span></a>
    </div>
  </header>

  <main>
    <section class="position-relative">
      <img class="landing-page-img" src="../ImagesLP/LandingPageWallpaper.jpg" alt="">

      <div class="signin-container">
        <?php
        $email_error = "";
        $pass_error = "";
        $approval_error = "";
        $valid_email = true;
        $valid_pass = true;
        $valid_approval = true;

        if (isset($_POST['signin-button'])) {
          $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
          $pass = filter_input(INPUT_POST, "password", FILTER_SANITIZE_EMAIL);

          $email = mysqli_real_escape_string($con, $email);

          // Find user by email
          $result = mysqli_query($con, "SELECT * FROM tbl_signup_acc WHERE signup_email = '$email'");
          if ($row = mysqli_fetch_assoc($result)) {
            if ($row['is_archived'] == 1) {
              $email_error = "Your account has been archived. Please contact support.";
              $valid_email = false;
            } else {
              // Check payment/approval status
              $signup_id = $row['signup_id'];
              $paymentResult = mysqli_query($con, "SELECT status FROM tbl_payment WHERE signup_id = $signup_id LIMIT 1");
              $paymentRow = mysqli_fetch_assoc($paymentResult);

              if (!$paymentRow) {
                $approval_error = "Your payment status is unavailable. Please contact support.";
                $valid_approval = false;
              } elseif ($paymentRow['status'] === "denied") {
                $approval_error = "Your account has been denied. Please contact support for more information.";
                $valid_approval = false;
              } elseif ($paymentRow['status'] !== "approved") {
                $approval_error = "Your account is not approved yet. Please complete payment or wait for admin approval.";
                $valid_approval = false;
              } elseif (!password_verify($pass, $row['signup_password'])) {
                $pass_error = "The password you’ve entered is incorrect.";
                $valid_pass = false;
              } else {
                $_SESSION['user_id'] = $row['signup_id'];
                $_SESSION['user_email'] = $row['signup_email'];
                $_SESSION['is_logged_in'] = true;

                // Redirect
                echo "<script>
                        alert('Login successful!');
                        window.location.href = '../../MovieWebsite/FirstProject.php';
                      </script>";
                exit;
              }
            }
          } else {
            $email_error = "The email or mobile number you entered isn’t connected to an account. Find your account and log in.";
            $valid_email = false;
          }
        }
        ?>
        <h1 class="signin-text db-text-sec text-start mb-4">Sign In</h1>
        <form class="signin-form db-text-sec d-flex flex-column gap-3" action="" method="post">
          <div class="d-grid input-container">
            <input class="signin-inputs bg-transparent" type="text" name="email" placeholder="Email or mobile number"
              required <?php if (!$valid_email)
                echo "style='border-color: red;'" ?>>
              <label for="email">Email or mobile number</label>
            <?php if (!$valid_email): ?>
              <p class="mb-2 mt-1 text-danger text-start" style="font-size: 14px;"><?php echo $email_error ?></p>
            <?php endif; ?>
          </div>
          <div class="d-grid input-container">
            <input class="signin-inputs bg-transparent" type="password" name="password" placeholder="Password" required
              <?php if (!$valid_pass)
                echo "style='border-color: red;'" ?>>
              <label for="password">Password</label>
            <?php if (!$valid_pass): ?>
              <p class="mb-2 mt-1 text-danger text-start" style="font-size: 14px;"><?php echo $pass_error ?></p>
            <?php endif; ?>
            <?php if (!$valid_approval): ?>
              <p class="mb-2 mt-1 text-danger text-start" style="font-size: 14px;"><?php echo $approval_error ?></p>
            <?php endif; ?>
          </div>
          <div class="d-grid mb-1">
            <input class="btn db-bg-primary db-text-sec fs-20" type="submit" name="signin-button" value="Sign In">
          </div>
          <p class="text-center">
            <a href="forgot-pass.php" class="forgot-pass db-text-sec text-decoration-none">Forgot your password?</a>
          </p>
          <p class="text-start">
            <span class="box-text db-text-sec">New to CineVault?</span>
            <a href="LP-SignUp.php" class="db-text-primary text-decoration-none signup-now">Sign up now.</a>
          </p>
        </form>
      </div>
    </section>
  </main>
</body>

</html>