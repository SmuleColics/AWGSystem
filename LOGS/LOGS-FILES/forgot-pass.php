<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../LOGS-CSS/login.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
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
          <h2 class="fs-24 mb-0">Forgot Password</h2>
          <p class="fs-14 light-text">Enter your email to receive a reset code</p>
        </div>

        <!-- Form -->
        <form>
          <div class="mb-3">
            <label for="forgot-email" class="form-label fs-14 fw-500">Email</label>
            <input
              type="email"
              class="form-control fs-14"
              id="forgot-email"
              placeholder="you@example.com"
              required>
          </div>

          <!-- Send Button -->
          <div class="d-grid mb-4">
            <button type="submit" class="btn green-bg text-white login-btn">
              Send Reset Code
            </button>
          </div>

          <!-- Back to Login -->
          <div class="mb-4 text-center">
            <a href="login.php" class="text-decoration-none text-dark forgot-pass">
              <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>


</body>

</html>