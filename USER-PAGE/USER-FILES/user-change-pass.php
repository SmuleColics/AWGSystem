<?php
ob_start();
include 'user-header.php';

$errors = [];

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header('Location: login.php');
  exit;
}

// ========== CHANGE PASSWORD LOGIC ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePass'])) {
  $current_password = trim($_POST['currentPass'] ?? '');
  $new_password = trim($_POST['newPass'] ?? '');
  $confirm_password = trim($_POST['confirmPass'] ?? '');
  
  // Validation
  if (empty($current_password)) {
    $errors['currentPass'] = 'Current password is required';
  }
  
  if (empty($new_password)) {
    $errors['newPass'] = 'New password is required';
  } elseif (strlen($new_password) < 8) {
    $errors['newPass'] = 'Password must be at least 8 characters';
  }
  
  if (empty($confirm_password)) {
    $errors['confirmPass'] = 'Please confirm your new password';
  }
  
  // Check if passwords match
  if (!empty($new_password) && !empty($confirm_password) && $new_password !== $confirm_password) {
    $errors['confirmPass'] = 'New password and confirmation do not match';
  }
  
  // If no validation errors, check current password
  if (empty($errors)) {
    // Fetch current password from database
    $sql = "SELECT password, first_name, last_name FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
      $user_data = mysqli_fetch_assoc($result);
      $stored_password = $user_data['password'];
      
      // Check if current password matches (using password_verify for hashed passwords)
      if (!password_verify($current_password, $stored_password)) {
        $errors['currentPass'] = 'Current password is incorrect';
      } else {
        // Hash the new password before storing
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $hashed_password_escaped = mysqli_real_escape_string($conn, $hashed_password);
        
        $update_sql = "UPDATE users SET password = '$hashed_password_escaped' WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $update_sql)) {
          // Log the activity
          $user_full_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
          $description = "Changed account password";
          log_activity($conn, $user_id, $user_full_name, 'UPDATE', 'USERS', $user_id, $user_full_name, $description);
          
          echo "<script>
            alert('Password updated successfully!');
            window.location='user-profile.php';
          </script>";
          exit;
        } else {
          $errors['database'] = 'Database error: ' . mysqli_error($conn);
        }
      }
    } else {
      $errors['database'] = 'User not found';
    }
  }
}
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

    .btn-green {
      background-color: #16A249;
      color: white;
      border: none;
    }

    .btn-green:hover {
      background-color: #138a3d;
      color: white;
    }

    .hover-underline:hover {
      text-decoration: underline !important;
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

          <?php if (isset($errors['database'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($errors['database']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <form method="POST" id="changePasswordForm">

            <!-- Current Password -->
            <div class="mb-3 password-box">
              <label class="form-label">Current Password</label>
              <input 
                type="password" 
                name="currentPass" 
                class="form-control <?= isset($errors['currentPass']) ? 'border-danger' : '' ?>" 
                id="oldPassword" 
                placeholder="********"
                value="<?= isset($_POST['currentPass']) ? htmlspecialchars($_POST['currentPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('oldPassword', this)"></i>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['currentPass']) ? 'block' : 'none' ?>">
                <?= isset($errors['currentPass']) ? $errors['currentPass'] : '' ?>
              </p>
            </div>

            <!-- New Password -->
            <div class="mb-3 password-box">
              <label class="form-label">New Password</label>
              <input 
                type="password" 
                name="newPass" 
                class="form-control <?= isset($errors['newPass']) ? 'border-danger' : '' ?>" 
                id="newPassword" 
                placeholder="********"
                value="<?= isset($_POST['newPass']) ? htmlspecialchars($_POST['newPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('newPassword', this)" style="position: absolute; transform: translateY(-24px);"></i>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['newPass']) ? 'block' : 'none' ?>">
                <?= isset($errors['newPass']) ? $errors['newPass'] : '' ?>
              </p>
              <small class="text-muted">Password must be at least 8 characters long</small>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3 password-box">
              <label class="form-label">Confirm New Password</label>
              <input 
                type="password" 
                name="confirmPass" 
                class="form-control <?= isset($errors['confirmPass']) ? 'border-danger' : '' ?>" 
                id="confirmPassword" 
                placeholder="********"
                value="<?= isset($_POST['confirmPass']) ? htmlspecialchars($_POST['confirmPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('confirmPassword', this)"></i>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['confirmPass']) ? 'block' : 'none' ?>">
                <?= isset($errors['confirmPass']) ? $errors['confirmPass'] : '' ?>
              </p>
            </div>

            <!-- Buttons -->
            <button type="submit" name="changePass" class="btn btn-green w-100 mb-2">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Password
            </button>

            <a href="user-profile.php" class="btn btn-light border w-100 mb-3">Cancel</a>

            <!-- Forgot Password Link -->
            <div class="text-center">
              <a href="../../LOGS/LOGS-FILES/forgot-pass.php" class="text-dark hover-underline text-decoration-none">
                <i class="fas fa-question-circle me-1"></i>Forgot Password?
              </a>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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

    // Auto-scroll to first error if there are validation errors
    <?php if (!empty($errors)): ?>
    window.addEventListener('DOMContentLoaded', function() {
      const firstError = document.querySelector('.text-danger[style*="display: block"]');
      if (firstError) {
        const parentDiv = firstError.closest('.password-box') || firstError.closest('.mb-3');
        if (parentDiv) {
          const inputField = parentDiv.querySelector('input');
          if (inputField) {
            inputField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputField.focus();
          }
        }
      }
    });
    <?php endif; ?>

    // Clear error styling on input
    document.querySelectorAll('input[name]').forEach(input => {
      input.addEventListener('input', function() {
        this.classList.remove('border-danger');
        const errorMsg = this.closest('.mb-3').querySelector('.text-danger');
        if (errorMsg) {
          errorMsg.style.display = 'none';
        }
      });
    });
  </script>

</body>

</html>