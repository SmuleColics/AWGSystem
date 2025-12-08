<?php

include "../../INCLUDES/db-con.php";
session_start();

$errors = [];
$email = $password = "";

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // INPUTS
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // ========== VALIDATION ==========

  if (empty($email)) {
    $errors['email'] = "Email is required";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Enter a valid email address";
  }

  if (empty($password)) {
    $errors['password'] = "Password is required";
  }

  // ========== CHECK USER ==========

  if (empty($errors)) {

    $emailEsc = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM users WHERE email = '$emailEsc' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows === 1) {

      $user = $result->fetch_assoc();

      // Password validation
      if (password_verify($password, $user['password'])) {
        // Set your session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['email'] = $user['email'];

        header("Location: ../../USER-PAGE/USER-FILES/user-portal.php");
        exit;
      } else {
        $errors['password'] = "Incorrect password";
      }
    } else {
      $errors['email'] = "Account not found";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png" />
  <!-- ==========START OF BOOTSTRAP LINK ========== -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <! --==========END OF BOOTSTRAP LINK==========-->
    <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
    <link rel="stylesheet" href="../LOGS-CSS/login.css">
    <!-- Font Awesome Free CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
</head>

<body class="green-bg">
  <div class="login-header text-center my-4 ">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="awegreen-logo">
    <h1 class="text-white fs-30 fw-bold mb-0 mt-2">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>
  <div class="h-full">
    <div class="flex ">
      <div class="bg-white p-4 rounded-3 login-container">

        <div class="mb-4">
          <h2 class="fs-24 mb-0">Welcome Back</h2>
          <p class="fs-14 light-text">Enter your credentials to access your account</p>
        </div>
        <form action="" method="POST">

          <!-- EMAIL -->
          <div class="mb-3">
            <label class="form-label fs-14 fw-500">Email</label>
            <input type="email"
              name="email"
              class="form-control fs-14"
              placeholder="juandelacruz@gmail.com"
              value="<?php echo htmlspecialchars($email); ?>">

            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['email']) ? 'block' : 'none' ?>">
              <?= $errors['email'] ?? '' ?>
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-3 position-relative">
            <label class="form-label fs-14 fw-500">Password</label>
            <input type="password"
              name="password"
              class="form-control fs-14"
              id="login-pword"
              placeholder="••••••••">
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['password']) ? 'block' : 'none' ?>">
              <?= $errors['password'] ?? '' ?>
            </p>

            <button type="button" id="togglePassword" class="btn password-toggle <?= isset($errors['password']) ? 'password-toggle-error' : '' ?>">
              <i class="fas fa-eye"></i>
            </button>

          </div>

          <!-- SUBMIT -->
          <div class="d-grid mb-4">
            <button type="submit" class="btn green-bg text-white login-btn">Login</button>
          </div>

          <!-- FORGOT PASS -->
          <div class="mb-4 text-center">
            <a href="forgot-pass.php" class="text-decoration-none text-dark forgot-pass">Forgot password?</a>
          </div>
          <div class="divider mb-4"></div>
          <div class="d-flex justify-content-center gap-2 fs-14">
            <p class="light-text">Don't have an account? </p>
            <a class="green-text text-decoration-none signup" href="signup.php">Sign up</a>
          </div>
          <div class="fs-14 -mt-2 light-text text-center back-to-home">
            <i class="fa-solid fa-arrow-left"></i>
            <a href="../../LANDING-PAGE/LP-FILES/LandingPage.php" class="text-decoration-none light-text">Back to Home</a>
          </div>

        </form>


      </div>
    </div>
  </div>
  <div class="mt-4 text-center fs-14 semi-light-text">
    <p>By continuing, you agree to our Terms of Service and Privacy Policy</p>
  </div>
</body>
<script src="toggle-pword.js"></script>

</html>