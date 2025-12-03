<?php
// -------------------------------------------
// Change Password Logic (Currently Disabled)
// -------------------------------------------
//
include 'admin-header.php';
// session_start();
//
// $conn = mysqli_connect("localhost", "root", "", "your_database_name");
// $userEmail = $_SESSION['user_email'];
//
// $success = "";
// $error = "";
//
// if (isset($_POST['changePass'])) {
//     $current = $_POST['currentPass'];
//     $newpass = $_POST['newPass'];
//     $confirm = $_POST['confirmPass'];
//
//     $sql = "SELECT password FROM users WHERE email='$userEmail'";
//     $res = mysqli_query($conn, $sql);
//     $row = mysqli_fetch_assoc($res);
//
//     if (!$row) {
//         $error = "User not found.";
//     } else if ($row['password'] !== $current) {
//         $error = "Current password is incorrect.";
//     } else if ($newpass !== $confirm) {
//         $error = "New password and confirmation do not match.";
//     } else {
//         $update = "UPDATE users SET password='$newpass' WHERE email='$userEmail'";
//         mysqli_query($conn, $update);
//         $success = "Password updated successfully!";
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>

  <style>
    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
    }

    .hover-underline:hover {
      text-decoration: underline !important;
    }
  </style>
</head>

<body class="bg-light min-h-100vh">

  <main id="main" class="container-xxl px-4 py-5">
    <div>
      <h1 class="fs-36 mobile-fs-32">Change Password</h1>
      <p class="admin-top-desc">Update your account password</p>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">

        <form method="POST">

          <!-- Current Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Current Password</label>
            <div class="password-wrapper">
              <input type="password" name="currentPass" class="form-control" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
          </div>

          <!-- New Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">New Password</label>
            <div class="password-wrapper">
              <input type="password" name="newPass" class="form-control" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Confirm New Password</label>
            <div class="password-wrapper">
              <input type="password" name="confirmPass" class="form-control" placeholder="********" required>
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
          </div>

          <!-- Buttons -->
          <div class="d-flex justify-content-between gap-2">
            <div>
              <a href="#" class="fs-18 text-dark hover-underline text-decoration-none">Forgot Password?</a>
            </div>
            <div>
              <a href="my-profile.php" class="btn btn-outline-secondary ms-2">Cancel</a>
              <button type="submit" name="changePass" class="btn btn-green px-4">Update Password</button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </main>

  <script>
    document.querySelectorAll(".toggle-password").forEach(icon => {
      icon.addEventListener("click", function () {
        let input = this.previousElementSibling;

        if (input.type === "password") {
          input.type = "text";
          this.classList.remove("fa-eye");
          this.classList.add("fa-eye-slash");
        } else {
          input.type = "password";
          this.classList.remove("fa-eye-slash");
          this.classList.add("fa-eye");
        }
      });
    });
  </script>

</body>

</html>
