<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup</title>
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png" type="image/png" <! --==========START OF BOOTSTRAP
    LINK==========-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <! --==========END OF BOOTSTRAP LINK==========-->
    <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
    <link rel="stylesheet" href="../LOGS-CSS/login.css">
    <!-- Font Awesome Free CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
</head>

<body class="green-bg">
  <div class="signup-header text-center my-4 ">
    <img class="img-fluid awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="awegreen-logo">
    <h1 class="text-white fs-30 fw-bold mb-0 mt-2">A We Green Enterprise</h1>
    <p class="semi-light-text">Building a sustainable future</p>
  </div>
  <div class="h-full">
    <div class="flex ">
      <div class="bg-white p-4 rounded-3 signup-container">

        <div class="mb-4">
          <h2 class="fs-24 mb-0">Create Account</h2>
          <p class="fs-14 light-text">Fill in your details to get started</p>
        </div>
        <form action="">
          <div class="mb-3">
            <label for="email" class="form-label fs-14 fw-500">Email</label>
            <input type="email" class="form-control fs-14" id="email" placeholder="you@example.com">
          </div>
          <div class="mb-3 position-relative">
            <label for="login-pword" class="form-label fs-14 fw-500">Password</label>
            <input type="password" class="form-control fs-14" id="login-pword" placeholder="••••••••"
              autocomplete="current-password" />
            <button type="button" id="togglePassword" class="btn password-toggle" title="Show password">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          <div class="mb-3 position-relative">
            <label for="login-confirm-pword" class="form-label fs-14 fw-500">Confirm Password</label>
            <input type="password" class="form-control fs-14" id="login-confirm-pword" placeholder="••••••••"
              autocomplete="current-password" />
            <button type="button" id="toggleConfirmPassword" class="btn password-toggle" title="Show password">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          <div class="d-grid mb-3">
            <button class="btn green-bg text-white signup-btn">Signup</button>
          </div>
          <div class="d-flex justify-content-center gap-2 fs-14 mb-4">
            <p class="light-text">Already have an account? </p>
            <a class="green-text text-decoration-none signup" href="login.php">Log in</a>
          </div>
          <div class="fs-14 -mt-2 light-text text-center back-to-home">
            <i class="fa-solid fa-arrow-left"></i>
            <a href="../../LANDING-PAGE/LP-FILES/LandingPage.php" class="text-decoration-none light-text">Back to
              Home</a>
          </div>
        </form>

      </div>
    </div>
  </div>
  <div class="mt-4 text-center fs-14 semi-light-text">
    <p>By continuing, you agree to our Terms of Serice and Privacy Policy</p>
  </div>
</body>
<script src="toggle-pword.js"></script>

</html>