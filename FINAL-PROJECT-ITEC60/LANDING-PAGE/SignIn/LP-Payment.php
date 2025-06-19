<?php
session_start();
include 'db-con.php';

$reference_error = "";
$valid_reference = true;

if (isset($_POST['confirm']) && isset($_SESSION['pending_id'])) {
    $reference_no = mysqli_real_escape_string($con, $_POST['reference-no']);
    $pending_id = $_SESSION['pending_id'];

    // Check for duplicate reference number in both pending and payment tables
    $check_query = mysqli_query($con, "SELECT reference_no FROM tbl_payment WHERE reference_no = '$reference_no' LIMIT 1");
    $check_pending = mysqli_query($con, "SELECT reference_no FROM tbl_signup_pending WHERE reference_no = '$reference_no' LIMIT 1");

    if (mysqli_num_rows($check_query) > 0 || mysqli_num_rows($check_pending) > 0) {
        $reference_error = "This reference number is already registered. Please double check it.";
        $valid_reference = false;
    }

    if ($valid_reference) {
        // Save reference number to pending table
        mysqli_query($con, "UPDATE tbl_signup_pending SET reference_no='$reference_no' WHERE pending_id=$pending_id");

        // Optionally, unset session to prevent resubmission
        session_unset();
        session_destroy();

        echo "<script>
            alert('Please wait for your payment to be verified by the admin.');
            window.location.href='../LandingPageMovie.php';
        </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;40t0;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="SignIn.css">
  <link rel="stylesheet" href="../../DASHBOARD-CSS/for-all.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link rel="icon" href="../../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">

</head>

<body class="bg-dark" id="body">
  <header class="signin-header fixed-top d-flex align-items-center ms-5 fw-semibold">
    <div class="left-header fs-20">
      <a class="navbar-brand fw-semibold db-text-sec ms-5" href="../LandingPageMovie.php">Cine<span
          class="db-text-primary">Vault</span></a>
    </div>
  </header>

  <main>
    <section class="position-relative">
      <img class="landing-page-img" src="../ImagesLP/LandingPageWallpaper.jpg" alt="">

      <div id="payment-container" class="signin-container db-text-sec">
        <div class="go-back-container text-start mt-2">
          <a href="LP-SignUp.php" class="db-text-sec text-decoration-none">
            <i class="fa-solid fa-chevron-left"></i>
            Go Back
          </a>
        </div>

        <h1 class="signin-text text-start mb-4 text-center" style="padding: 0; margin-top: 30px;">Complete Your Payment
        </h1>
        <p class="text-start p-0 create-acc text-center"><span class="db-text-primary">99</span> Pesos Only</p>
        <form class="mx-30" action="" method="post">
          <div class="d-grid ">

            <div class="d-flex align-items-center justify-content-center">
              <div class="bg-white">
                <img src="gcash-link.png" id="gcash-link" alt="">
              </div>
            </div>
            <p class="mt-1 mb-0">GCash Link</p>

            <div id="reference-container" class="d-none">
              <div class="d-grid">
                <p class="mt-5 text-start mb-0">Enter Reference No.</p>

                <input class="border-bottom border-0 bg-transparent db-text-sec input-payment" type="text"
                  name="reference-no" id="reference-no" placeholder="0" autocomplete="off">
                <p id="ref-dup" class="text-start mt-1 mb-0 text-danger"
                  style="font-size: 14px; <?php if (!(isset($_POST['confirm']) && !$valid_reference)) {
                    echo 'display: none;';
                  } ?>">
                  <?php if (isset($_POST['confirm']) && !$valid_reference) {
                    echo $reference_error;
                  } ?>
                </p>
                <p class="text-start mt-1 mb-0 text-danger" id="valid-reference" style="font-size: 14px;">
                  The reference number must be exactly 13 characters long.
                </p>


                <input type="submit" class="btn db-text-sec db-bg-primary my-4 button-payment" name="confirm"
                  id="confirm" value="Confirm">
              </div>
            </div>

          </div>
        </form>
      </div>
    </section>

    <!-- <footer class="footer-signin pb-5">
          <div class="row text-white">
            <p class="pt-5 color-light-white">Questions? <a href="#" class="color-light-white text-decoration-none">Contact us.</a></p>
            <div class="col-sm-6 col-md-3">
              <div class="pb-2">
                <a class="color-light-white" href="#">FAQ</a>
              </div>
              <div class="pb-2">
                <a class="color-light-white" href="#">Cookie Preferences</a>
              </div>
            </div>

            <div class="col-sm-6 col-md-3">
              <div class="pb-2">
                <a class="color-light-white" href="#">Help Center</a>
              </div>
              <div class="pb-2">
                <a class="color-light-white" href="#">Corporate Information</a>
              </div>
            </div>

            <div class="col-sm-6 col-md-3">
              <div class="pb-2">
                <a class="color-light-white" href="#">Terms of Use</a>
              </div>
            </div>

            <div class="col-sm-6 col-md-3">
              <div class="pb-2">
                <a class="color-light-white" href="#">Privacy</a>
              </div>
            </div>
            
          </div>
        </footer> -->

  </main>
  <script>
    const referenceNumber = document.getElementById("reference-no");
    const confirm = document.getElementById("confirm");
    const validAmount = document.getElementById("valid-reference");
    const amountInput = document.getElementById("reference-no"); // Assuming this is the input you're referring to

    document.getElementById("reference-container").classList.remove('d-none');
    document.getElementById("payment-container").style.marginTop = '30px';
    document.getElementById("body").style.marginBottom = '80px';

    confirm.disabled = true;
    validAmount.style.display = 'none';

    referenceNumber.addEventListener("input", function () {
      let value = this.value.trim();

      value = value.replace(/[^0-9]/g, "");

      this.value = value;

      document.getElementById("ref-dup").style.display = "none";

      // Allow user to type but stop typing once length reaches 11 characters
      if (value.length > 13) {
        this.value = value.slice(0, 13); // Limit to 11 characters
      }

      // Check if the input is exactly 11 digits long
      if (value.length === 0) {
        confirm.disabled = true;
        validAmount.style.display = "none";   // Hide error if field is empty
      } else if (value.length === 13 && !isNaN(value)) {
        confirm.disabled = false;
        validAmount.style.display = "none";   // Hide error if length is exactly 11
        referenceNumber.classList.remove('border-danger');
      } else {
        confirm.disabled = true;
        validAmount.style.display = "block";  // Show error if length is not 11
        referenceNumber.classList.add('border-danger');
      }
    });

    referenceNumber.addEventListener("keydown", function (e) {
      if (this.value.length === 13 && e.key !== "Backspace" && e.key !== "Delete") {
        e.preventDefault(); // Prevent any key press if the input has 11 characters
      }
    });
  </script>

</body>

</html>