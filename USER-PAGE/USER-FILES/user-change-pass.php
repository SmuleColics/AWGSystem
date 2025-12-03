<?php
include 'user-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Change Password</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .password-box {
      position: relative;
    }

    .toggle-eye {
      position: absolute;
      right: 12px;
      top: 72%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
  </style>
</head>

<body class="bg-light py-5 px-4">

  <div class="container-xxl">
    <div class="mx-auto" style="max-width: 500px;">

      <!-- Header -->
      <div class="mb-4">
        <h1 class="fs-36 mobile-fs-32">Change Password</h1>
        <p class="text-muted">Update your account password by entering your current and new password.</p>
      </div>

      <!-- Change Password Card -->
      <div class="card shadow-sm">
        <div class="card-body">

          <form onsubmit="return validatePassword()">

            <!-- Old Password -->
            <div class="mb-3 password-box">
              <label class="form-label">Current Password</label>
              <input type="password" class="form-control" id="oldPassword" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('oldPassword', this)"></i>
            </div>

            <!-- New Password -->
            <div class="mb-3 password-box">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" id="newPassword" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('newPassword', this)"></i>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3 password-box">
              <label class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirmPassword" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('confirmPassword', this)"></i>
            </div>

            <!-- Error message -->
            <div id="errorMsg" class="text-danger mb-3 d-none">
              Passwords do not match.
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-green w-100 mb-2">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Password
            </button>

            <a href="user-profile.php" class="btn btn-light border w-100">Cancel</a>

          </form>

        </div>
      </div>

    </div>
  </div>

  <script>
    // Show/Hide Password
    function togglePassword(id, icon) {
      const field = document.getElementById(id);
      if (field.type === "password") {
        field.type = "text";
        icon.classList.add("fa-eye-slash");
        icon.classList.remove("fa-eye");
      } else {
        field.type = "password";
        icon.classList.add("fa-eye");
        icon.classList.remove("fa-eye-slash");
      }
    }

    // Validation
    function validatePassword() {
      const newPass = document.getElementById("newPassword").value;
      const confirmPass = document.getElementById("confirmPassword").value;

      if (newPass !== confirmPass) {
        document.getElementById("errorMsg").classList.remove("d-none");
        return false; // prevent submit
      }

      document.getElementById("errorMsg").classList.add("d-none");

      alert("Password changed successfully! (connect backend next)");
      return false; // remove this when adding PHP backend
    }
  </script>

</body>

</html>
