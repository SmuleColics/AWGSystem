<?php
include 'CineVault-header.php';
include '../includes/db-connection.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;
$user = null;
$alert = "";

// Handle profile update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $mobile = trim($_POST['mobile_number'] ?? '');
    $email = trim($_POST['signup_email'] ?? '');

    if ($user_id && $first_name && $last_name && $mobile && $email) {
        $stmt = $con->prepare("UPDATE tbl_signup_acc SET first_name=?, last_name=?, mobile_number=?, signup_email=? WHERE signup_id=?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $mobile, $email, $user_id);
        $stmt->execute();
        $stmt->close();
        // Alert with JS and redirect after
        echo '<script>alert("Profile updated successfully!"); window.location.href="user-view-profile.php";</script>';
        exit;
    } else {
        $alert = "Please fill out all fields.";
    }
}

// Fetch current user data
if ($user_id) {
    $query = mysqli_query($con, "SELECT * FROM tbl_signup_acc WHERE signup_id = $user_id LIMIT 1");
    $user = mysqli_fetch_assoc($query);
}
$fullname = $user ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : 'N/A';
$first_name = $user['first_name'] ?? '';
$last_name = $user['last_name'] ?? '';
$mobile = $user['mobile_number'] ?? '';
$email = $user['signup_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="FirstProject.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="Override.css" />
  <link rel="stylesheet" href="../DASHBOARD-CSS/dashboard.css">
  <link rel="stylesheet" href="../DASHBOARD-CSS/for-all.css">
  <link rel="icon" href="../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
  <style>
    body { height: fit-content !important; }
    main { height: 475px; }
    @media (max-width: 575.98px) {
      #profile-con { transform: translateY(-100px) !important; }
    }
  </style>
</head>
<body class="bg-dark">
  <main>
    <section class="container my-profile-section p-3">
      <?php if ($alert): ?>
        <div class="alert alert-danger mt-2" role="alert">
          <?php echo $alert; ?>
        </div>
      <?php endif; ?>
      <div id="profile-con" class="row g-3" style="transform: translateY(40px);">
        <div class="col-lg-4 col-md-4">
          <div class="card" style="background-color:#2a2f34">
            <div class="card-body">
              <div class="mp-top-container flexbox-align flex-column">
                <div class="admin-user-container">
                  <div class="fa-user-container db-bg-primary flexbox-align rounded-circle">
                    <i class="fa-solid fa-user db-text-sec"></i>
                  </div>
                </div>
                <p class="db-text-sec fs-18 mt-3 mb-0"><?php echo $fullname; ?></p>
                <p class="db-text-secondary">User</p>
                <div class="d-flex align-items-center gap-2">
                  <i class="fa-solid fa-envelope db-text-sec"></i>
                  <p class="db-text-sec mb-0"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mb-3">
                  <i class="fa-solid fa-phone db-text-sec"></i>
                  <p class="db-text-sec mb-0"><?php echo htmlspecialchars($mobile); ?></p>
                </div>
                <div class="d-grid db-bg-primary w-100 rounded-1">
                  <center class="py-1">
                    <a href="#" class="db-text-sec text-decoration-none" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                      <i class="fa-solid fa-pen-to-square"></i>
                      Edit Profile
                    </a>
                  </center>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-8 col-md-8 mt-5">
          <div class="emp-info-con db-text-sec fs-18 mt-3">
            <i class="fa-solid fa-user db-text-primary"></i>
            Personal Information
          </div>
          <div class="card mt-2" style="background-color:#2a2f34;">
            <div class="card-body d-flex">
              <div class="d-flex flex-column mp-info">
                <p class="db-text-sec">Full Name</p>
                <p class="db-text-sec text-nowrap">Mobile Number</p>
                <p class="db-text-sec mb-0">Email</p>
              </div>
              <div class="d-flex flex-column">
                <p class="text-white"><?php echo $fullname; ?></p>
                <p class="text-white"><?php echo htmlspecialchars($mobile); ?></p>
                <p class="text-white mb-0"><?php echo htmlspecialchars($email); ?></p>
              </div>
            </div>
          </div>
        </div>
    </section>
  </main>
  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background:#23272b;">
        <form method="post" action="">
          <div class="modal-header">
            <h5 class="modal-title db-text-sec" id="editProfileLabel">Edit Profile</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
              <input type="hidden" name="signup_id" id="signup_id" value="<?php echo htmlspecialchars($user_id); ?>">
              <div class="mb-3">
                <label for="firstName" class="form-label db-text-sec mb-0">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
              </div>
              <div class="mb-3">
                <label for="lastName" class="form-label db-text-sec mb-0">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
              </div>
              <div class="mb-3">
                <label for="mobile" class="form-label db-text-sec mb-0">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile_number" value="<?php echo htmlspecialchars($mobile); ?>" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label db-text-sec mb-0">Email address</label>
                <input type="email" class="form-control" id="email" name="signup_email" value="<?php echo htmlspecialchars($email); ?>" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn db-bg-primary db-text-sec" style="color: #f4fff8" name="update-profile">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <footer>
    <div id="footer-categories" class="footer text-white d-flex justify-content-between mx-5 align-items-center">
      <p class="footer-long-text">
        This site does not store any files on its server, It only links to
        the media which is hosted on 3rd party services like YouTube,
        Dailymotion, Ok.ru, Vidsrc and more.
      </p>
      <p>© 2025 CineVault. All rights reserved.</p>
    </div>
  </footer>
  <script src="header-scroll.js"></script>
</body>
</html>