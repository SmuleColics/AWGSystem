<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>

  <main id="main" class="container-xxl px-4 py-5" style=" background-color: #ebf0ed ">
    <a href="admin-employees.php " class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">My Profile</h1>
        <p class="admin-top-desc">Manage your personal information</p>
      </div>
      <div>
        <a href="admin-change-pass.php" class="btn btn-light border d-flex align-items-center">
          <i class="fas fa-key me-1 d-none d-md-block"></i> Change Password</a>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center gap-3">
            <div
              class="rounded-circle text-dark d-flex align-items-center justify-content-center my-profile-icon"
              style="width: 80px; height: 80px; font-size: 28px;   background-color: #ebf0ed;"
              id="avatarFallback">
              A
            </div>

            <div>
              <h4 class="mb-0 mobile-fs-20" id="profileName">Employee Name</h4>
              <p class="light-text mb-0 " id="profileEmail">employee@example.com</p>
            </div>
          </div>

          <div id="editButtons">
            <button class="btn btn-outline-success d-flex align-items-center" onclick="enableEdit()">
              <i class="fa-solid fa-user-pen me-1 d-none d-md-block"></i> Edit Profile
            </button>
          </div>

          <div class="d-none" id="saveCancelButtons">
            <div class="d-flex flex-md-row flex-column gap-2">
              <button class="btn btn-green d-flex align-items-center" onclick="saveProfile()"><i class="fa-solid fa-floppy-disk me-1 d-none d-md-block"></i>Save</button>
              <button class="btn btn-outline-secondary d-flex align-items-center" onclick="cancelEdit()"><i class="fa-solid fa-xmark me-1 d-none d-md-block"></i>Cancel</button>
            </div>
          </div>
        </div>

        <hr />

        <!-- Personal Information -->
        <h5 class="fw-semibold mb-3">Personal Information</h5>
        <div class="row g-4">

          <!-- Full Name -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name</label>
            <input
              type="text"
              class="form-control d-none"
              id="inputFullName"
              placeholder="Enter full name" />
            <p class="border rounded p-2 light-text" id="displayFullName">Not provided</p>
          </div>

          <!-- Email -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email Address</label>
            <p class="border rounded p-2 bg-light light-text mb-2" id="displayEmail">
              employee@example.com
            </p>
            <small class="light-text">Email cannot be changed</small>
          </div>

          <!-- Phone -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone Number</label>
            <input
              type="text"
              class="form-control d-none"
              id="inputPhone"
              placeholder="Enter phone number" />
            <p class="border rounded p-2 light-text" id="displayPhone">Not provided</p>
          </div>
        </div>

        <hr class="my-4" />

        <!-- Employment Details (Static) -->
        <h5 class="fw-semibold mb-3">Employment Details</h5>
        <div class="row g-4">

          <div class="col-md-6">
            <label class="form-label fw-semibold">Position</label>
            <p class="border rounded p-2 bg-light light-text">Electrician</p>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Date Hired</label>
            <p class="border rounded p-2 bg-light light-text">January 15, 2023</p>
          </div>

          <!-- <div class="col-md-6">
            <label class="form-label fw-semibold">Department</label>
            <p class="border rounded p-2 bg-light light-text">Installation Services</p>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Employee ID</label>
            <p class="border rounded p-2 bg-light light-text">EMP-12345678</p>
          </div> -->
        </div>
      </div>
    </div>
  </main>


</body>
<script>
  function enableEdit() {
    document.getElementById("editButtons").classList.add("d-none");
    document.getElementById("saveCancelButtons").classList.remove("d-none");

    // Show input fields
    document.getElementById("displayFullName").classList.add("d-none");
    document.getElementById("displayPhone").classList.add("d-none");

    document.getElementById("inputFullName").classList.remove("d-none");
    document.getElementById("inputPhone").classList.remove("d-none");

    // Set values
    document.getElementById("inputFullName").value =
      document.getElementById("displayFullName").textContent.trim();

    document.getElementById("inputPhone").value =
      document.getElementById("displayPhone").textContent.trim();
  }

  function saveProfile() {
    // Update texts
    document.getElementById("displayFullName").textContent =
      document.getElementById("inputFullName").value;

    document.getElementById("displayPhone").textContent =
      document.getElementById("inputPhone").value;

    cancelEdit();
  }

  function cancelEdit() {
    document.getElementById("editButtons").classList.remove("d-none");
    document.getElementById("saveCancelButtons").classList.add("d-none");

    // Hide inputs
    document.getElementById("displayFullName").classList.remove("d-none");
    document.getElementById("displayPhone").classList.remove("d-none");

    document.getElementById("inputFullName").classList.add("d-none");
    document.getElementById("inputPhone").classList.add("d-none");
  }
</script>

</html>