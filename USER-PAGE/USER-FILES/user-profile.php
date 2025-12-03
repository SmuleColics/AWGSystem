<?php
include 'user-header.php';

// Sample profile data (replace with DB fetch later)
$profile = [
  'full_name' => 'John Doe',
  'email' => 'johndoe@gmail.com',
  'phone' => '09123456789',
  'house_no' => 'Block 1 Lot 33 Alfredo Diaz St.',
  'brgy' => 'Granados',
  'city' => 'Carmona City',
  'province' => 'Cavite',
  'zip_code' => '4117',
];

function getInitials($name)
{
  $parts = explode(' ', trim($name));
  $initials = '';
  foreach ($parts as $p) {
    $initials .= strtoupper($p[0]);
  }
  return substr($initials, 0, 2);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .avatar-circle {
      width: 80px;
      height: 80px;
      background-color: #e9ecef;
      border-radius: 50%;
      font-size: 28px;
      font-weight: 500;
    }

    .readonly-box {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      padding: 8px 10px;
      border-radius: 6px;
      font-size: 14px;
      color: #555;
    }
  </style>
</head>

<body class="bg-light py-5 px-4">

  <div class="container-xxl">
    <div class="mx-auto" style="max-width: 700px;">

      <!-- Header -->
      <div class="mb-4 d-flex align-items-center justify-content-between gap-3">
        <div>
          <h1 class="fs-36 mobile-fs-32">My Profile</h1>
          <p class="text-muted">Manage your personal details and account information</p>
        </div>
        <div>
          <a href="user-change-pass.php" class="btn btn-light border d-flex align-items-center">
            <i class="fas fa-key me-1 d-none d-md-block"></i> Change Password</a>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">

          <!-- Profile Info -->
          <div class="d-flex justify-content-between align-items-center mb-4 px-2">

            <div class="d-flex align-items-center gap-3">
              <div class="avatar-circle flex my-profile-icon">
                <?= getInitials($profile['full_name']); ?>
              </div>

              <div>
                <h4 class="mb-0" id="profileName"><?= $profile['full_name']; ?></h4>
                <small class="text-muted" id="profileEmail"><?= $profile['email']; ?></small>
              </div>
            </div>

            <!-- Buttons -->
            <div id="editButtons">
              <button class="btn btn-outline-success d-flex align-items-center" onclick="enableEdit()">
                <i class="fa-solid fa-user-pen me-1 d-none d-md-block"></i> Edit Profile
              </button>
            </div>

            <div id="saveCancelButtons" class="d-none d-flex flex-column flex-md-row gap-2">
              <button class="btn btn-green  d-flex align-items-center" onclick="saveProfile()">
                <i class="fa-solid fa-floppy-disk me-1 d-none d-md-block"></i>Save
              </button>

              <button class="btn btn-light border d-flex align-items-center" onclick="cancelEdit()"><i class="fa-solid fa-xmark me-1 d-none d-md-block"></i>Cancel</button>
            </div>

          </div>

          <hr>

          <!-- Personal Information -->
          <h5 class="fw-bold mb-3 px-2">Personal Information</h5>

          <div class="row g-3 mb-3 px-2">

            <!-- Full Name -->
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control d-none" id="inputFullName">
              <div class="readonly-box" id="displayFullName"><?= $profile['full_name']; ?></div>
            </div>

            <!-- Phone -->
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control d-none" id="inputPhone">
              <div class="readonly-box" id="displayPhone"><?= $profile['phone']; ?></div>
            </div>

            <!-- Address Fields -->
            <div class="col-md-6">
              <label class="form-label">Street Name, Bldg, House No</label>
              <input type="text" class="form-control d-none" id="inputHouseNo">
              <div class="readonly-box" id="displayHouseNo"><?= $profile['house_no']; ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Barangay</label>
              <input type="text" class="form-control d-none" id="inputBrgy">
              <div class="readonly-box" id="displayBrgy"><?= $profile['brgy']; ?></div>
            </div>

            <div class="col-md-4">
              <label class="form-label">City</label>
              <input type="text" class="form-control d-none" id="inputCity">
              <div class="readonly-box" id="displayCity"><?= $profile['city']; ?></div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Province</label>
              <input type="text" class="form-control d-none" id="inputProvince">
              <div class="readonly-box" id="displayProvince"><?= $profile['province']; ?></div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Zip Code</label>
              <input type="text" class="form-control d-none" id="inputZip">
              <div class="readonly-box" id="displayZip"><?= $profile['zip_code']; ?></div>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Inline Edit JS -->
  <script>
    function enableEdit() {
      document.getElementById("editButtons").classList.add("d-none");
      document.getElementById("saveCancelButtons").classList.remove("d-none");

      let fields = ["FullName", "Phone", "HouseNo", "Brgy", "City", "Province", "Zip"];

      fields.forEach(f => {
        document.getElementById("display" + f).classList.add("d-none");
        document.getElementById("input" + f).classList.remove("d-none");
        document.getElementById("input" + f).value =
          document.getElementById("display" + f).textContent.trim();
      });
    }

    function cancelEdit() {
      document.getElementById("editButtons").classList.remove("d-none");
      document.getElementById("saveCancelButtons").classList.add("d-none");

      let fields = ["FullName", "Phone", "HouseNo", "Brgy", "City", "Province", "Zip"];

      fields.forEach(f => {
        document.getElementById("display" + f).classList.remove("d-none");
        document.getElementById("input" + f).classList.add("d-none");
      });
    }

    function saveProfile() {
      let fields = ["FullName", "Phone", "HouseNo", "Brgy", "City", "Province", "Zip"];

      fields.forEach(f => {
        document.getElementById("display" + f).textContent =
          document.getElementById("input" + f).value;
      });

      cancelEdit();
    }
  </script>

</body>

</html>