<?php
include "../../INCLUDES/db-con.php";
include "../../INCLUDES/log-activity.php"; // Include logging function
session_start();

$errors = [];
$email = $password = "";

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // VALIDATION
  if (empty($email)) {
    $errors['email'] = "Email is required";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Enter a valid email address";
  }

  if (empty($password)) {
    $errors['password'] = "Password is required";
  }

  // CHECK USER OR EMPLOYEE
  if (empty($errors)) {
    $emailEsc = mysqli_real_escape_string($conn, $email);
    
    // First, check EMPLOYEES table
    $employee_query = "SELECT * FROM employees WHERE email = '$emailEsc' AND is_archived = 0 LIMIT 1";
    $employee_result = $conn->query($employee_query);

    if ($employee_result && $employee_result->num_rows === 1) {
      // EMPLOYEE LOGIN
      $employee = $employee_result->fetch_assoc();

      if (password_verify($password, $employee['password'])) {
        // Set employee session
        $_SESSION['employee_id'] = $employee['employee_id'];
        $_SESSION['first_name'] = $employee['first_name'];
        $_SESSION['last_name'] = $employee['last_name'];
        $_SESSION['email'] = $employee['email'];
        $_SESSION['position'] = $employee['position'];
        $_SESSION['user_type'] = 'employee';

        $employee_full_name = $employee['first_name'] . ' ' . $employee['last_name'];

        // LOG LOGIN ACTIVITY
        log_activity(
          $conn, 
          $employee['employee_id'], 
          $employee_full_name, 
          'LOGIN', 
          'SYSTEM', 
          null, 
          null, 
          'Employee logged in as ' . $employee['position']
        );

        echo "<script>
          alert('Welcome back, " . htmlspecialchars($employee['first_name']) . "!');
          window.location.href = '../../ADMIN-PAGE/ADMIN-FILES/admin-dashboard.php';
        </script>";
        exit();
      } else {
        $errors['password'] = "Incorrect password";
      }

    } else {
      // If not employee, check USERS table
      $user_query = "SELECT * FROM users WHERE email = '$emailEsc' LIMIT 1";
      $user_result = $conn->query($user_query);

      if ($user_result && $user_result->num_rows === 1) {
        // USER LOGIN
        $user = $user_result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
          // Set user session
          $_SESSION['user_id'] = $user['user_id'];
          $_SESSION['first_name'] = $user['first_name'];
          $_SESSION['last_name'] = $user['last_name'];
          $_SESSION['email'] = $user['email'];
          $_SESSION['user_type'] = 'user';

          $user_full_name = $user['first_name'] . ' ' . $user['last_name'];

          // LOG USER LOGIN ACTIVITY
          log_activity(
            $conn, 
            $user['user_id'], 
            $user_full_name, 
            'LOGIN', 
            'SYSTEM', 
            null, 
            null, 
            'User logged in | Email: ' . $user['email']
          );

          echo "<script>
            alert('Welcome back, " . htmlspecialchars($user['first_name']) . "!');
            window.location.href = '../../USER-PAGE/USER-FILES/user-portal.php';
          </script>";
          exit();
        } else {
          $errors['password'] = "Incorrect password";
        }
      } else {
        $errors['email'] = "Account not found";
      }
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
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
              value="<?= htmlspecialchars($email) ?>"
              required>

            <?php if (isset($errors['email'])): ?>
              <p class="fs-14 text-danger mb-0 mt-1"><?= $errors['email'] ?></p>
            <?php endif; ?>
          </div>

          <!-- PASSWORD -->
          <div class="mb-3 position-relative">
            <label class="form-label fs-14 fw-500">Password</label>
            <input type="password"
              name="password"
              class="form-control fs-14"
              id="login-pword"
              placeholder="••••••••"
              required>
            
            <?php if (isset($errors['password'])): ?>
              <p class="fs-14 text-danger mb-0 mt-1"><?= $errors['password'] ?></p>
            <?php endif; ?>

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
            <p class="light-text">Don't have an account?</p>
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