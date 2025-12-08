<?php
include '../../INCLUDES/db-con.php';

$errors = [];
$first = $last = $phone = $email = $password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // INPUTS
  $first = trim($_POST['first_name'] ?? '');
  $last = trim($_POST['last_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // ========== VALIDATION ==========

  if (empty($first)) $errors['first_name'] = "This field is required";
  if (empty($last)) $errors['last_name'] = "This field is required";

  // PHONE VALIDATION
  if (empty($phone)) {
    $errors['phone'] = "This field is required";
  } elseif (!preg_match("/^09\d{9}$/", $phone)) {
    $errors['phone'] = "Invalid phone format (ex: 09XXXXXXXXX)";
  }

  // EMAIL VALIDATION
  if (empty($email)) {
    $errors['email'] = "This field is required";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email address";
  }

  // PASSWORD VALIDATION
  if (empty($password)) {
    $errors['password'] = "This field is required";
  } elseif (strlen($password) < 6) {
    $errors['password'] = "Password must be at least 6 characters";
  }

  // ========== CHECK IF EMAIL EXISTS ==========
  if (empty($errors)) {

    $emailEsc = mysqli_real_escape_string($conn, $email);

    $query = "SELECT email FROM users WHERE email = '$emailEsc'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
      $errors['email'] = "Email already exists";
    }
  }

  // ========== INSERT INTO DATABASE ==========
  if (empty($errors)) {

    // Escape inputs
    $firstEsc = mysqli_real_escape_string($conn, $first);
    $lastEsc = mysqli_real_escape_string($conn, $last);
    $phoneEsc = mysqli_real_escape_string($conn, $phone);
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $passwordEsc = mysqli_real_escape_string($conn, $hashed);

    $sql = "
            INSERT INTO users (first_name, last_name, phone, email, password)
            VALUES ('$firstEsc', '$lastEsc', '$phoneEsc', '$emailEsc', '$passwordEsc')
        ";

    if ($conn->query($sql)) {
      echo "<script>
                alert('Signup successful!');
                window.location = 'login.php';
            </script>";
      exit;
    } else {
      echo "<script>alert('Error inserting record!');</script>";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup</title>

  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png" />

  <!--========== START OF BOOTSTRAP LINK ==========-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <!--========== END OF BOOTSTRAP LINK ==========-->

  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">

  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
</head>

<body class="green-bg">

  <div class="signup-header text-center my-4">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="awegreen-logo">
    <h1 class="text-white fs-30 fw-bold mb-0 mt-2">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>

  <div class="h-full">
    <div class="flex">
      <div class="bg-white p-4 rounded-3 signup-container">

        <div class="mb-4">
          <h2 class="fs-24 mb-0">Create Account</h2>
          <p class="fs-14 light-text">Fill in your details to get started</p>
        </div>

        <form action="signup.php" method="POST">

          <!-- FIRST & LAST NAME -->
          <div class="row">
            <div class="mb-3 col-md-6">
              <label class="form-label fs-14 fw-500">First Name</label>
              <input type="text" name="first_name" class="form-control fs-14" value="<?= $first ?? '' ?>" placeholder="Juan">
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['first_name']) ? 'block' : 'none' ?>">
                <?= $errors['first_name'] ?? '' ?>
              </p>
            </div>

            <div class="mb-3 col-md-6">
              <label class="form-label fs-14 fw-500">Last Name</label>
              <input type="text" name="last_name" class="form-control fs-14" value="<?= $last ?? '' ?>" placeholder="Dela Cruz">
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['last_name']) ? 'block' : 'none' ?>">
                <?= $errors['last_name'] ?? '' ?>
              </p>
            </div>
          </div>

          <!-- PHONE -->
          <div class="mb-3">
            <label class="form-label fs-14 fw-500">Phone Number</label>
            <input type="text" name="phone" class="form-control fs-14" value="<?= $phone ?? '' ?>" placeholder="09*********">
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['phone']) ? 'block' : 'none' ?>">
              <?= $errors['phone'] ?? '' ?>
            </p>
          </div>

          <!-- EMAIL -->
          <div class="mb-3">
            <label class="form-label fs-14 fw-500">Email</label>
            <input type="email" name="email" class="form-control fs-14" value="<?= $email ?? '' ?>" placeholder="juandelacruz@gmail.com">
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['email']) ? 'block' : 'none' ?>">
              <?= $errors['email'] ?? '' ?>
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-3 position-relative">
            <label class="form-label fs-14 fw-500">Password</label>
            <input type="password" name="password" id="login-pword" class="form-control fs-14" placeholder="********">

            <button type="button" id="togglePassword" class="btn password-toggle <?= isset($errors['password']) ? 'password-toggle-error' : '' ?>">
              <i class="fas fa-eye"></i>
            </button>

            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['password']) ? 'block' : 'none' ?>">
              <?= $errors['password'] ?? '' ?>
            </p>
          </div>

          <div class="d-grid mb-3">
            <button class="btn green-bg text-white signup-btn">Signup</button>
          </div>

          <div class="d-flex justify-content-center gap-2 fs-14 mb-4">
            <p class="light-text">Already have an account?</p>
            <a class="green-text text-decoration-none signup" href="login.php">Log in</a>
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