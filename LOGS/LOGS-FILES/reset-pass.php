<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Global Styles -->
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">

  <!-- Logo -->
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png">
</head>

<body class="green-bg">

  <!-- Header -->
  <div class="login-header text-center my-4">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="awegreen-logo">
    <h1 class="text-white fs-30 fw-bold mb-0 mt-2">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>

  <div class="h-full">
    <div class="flex">
      <div class="bg-white p-4 rounded-3 login-container">

        <!-- Title -->
        <div class="mb-4 text-start">
          <h2 class="fs-24 mb-1">Reset Password</h2>
          <p class="fs-14 light-text">Enter the reset code and your new password</p>
        </div>

        <form action="" method="post">

          <!-- Reset Code -->
          <div class="mb-3">
            <label for="reset_code" class="form-label fs-14 fw-500">4-digit code</label>
            <input 
              type="text" 
              maxlength="4" 
              class="form-control fs-14"
              id="reset_code"
              placeholder="1234"
              required>
          </div>

          <!-- New Password -->
          <div class="mb-3 position-relative">
            <label for="new_password" class="form-label fs-14 fw-500">New Password</label>
            <input
              type="password"
              class="form-control fs-14"
              id="new_password"
              placeholder="••••••••"
              required>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3 position-relative">
            <label for="confirm_password" class="form-label fs-14 fw-500">Confirm Password</label>
            <input
              type="password"
              class="form-control fs-14"
              id="confirm_password"
              placeholder="••••••••"
              required>
          </div>

          <!-- Buttons -->
          <div class="mt-4 d-flex justify-content-between">
            <a href="forgot-pass.php" class="btn btn-outline-secondary px-4">Cancel</a>

            <button type="submit" class="btn green-bg text-white px-4">
              Reset Password
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <div class="mt-4 text-center fs-14 semi-light-text">
    <p>By continuing, you agree to our Terms of Service and Privacy Policy</p>
  </div>

</body>
</html>
