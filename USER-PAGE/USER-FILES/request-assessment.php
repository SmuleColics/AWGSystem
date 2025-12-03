<?php
// If you're using sessions for login
// session_start();
// $userEmail = $_SESSION['email'] ?? "";
include 'user-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Schedule Assessment</title>

  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light py-5">

  <div class="container-xxl py-3">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">

        <div class="card shadow">
          <div class="card-header bg-white p-4">
            <h3 class="mb-0 green-text">Schedule an Assessment</h3>
            <p class="text-muted mb-0">Book a site assessment to receive a detailed quotation.</p>
          </div>

          <div class="card-body p-4">
            <form action="save_schedule.php" method="POST">

              <div class="row g-3">

                <div class="col-md-6">
                  <label for="full-name" class="form-label">Full Name *</label>
                  <input id="full-name" type="text" name="clientName" class="form-control" placeholder="James Macalintal" required>
                </div>

                <div class="col-md-6">
                  <label for="phone-no" class="form-label">Phone Number *</label>
                  <input id="phone-no" type="tel" name="phone" class="form-control" placeholder="09XXXXXXXXX" required>
                </div>

              </div>

              <div class="mt-3">
                <label for="email" class="form-label">Email *</label>
                <input id="email" type="email" name="email" class="form-control" value="" placeholder="jamesmacalintal@gmail.com" required>
              </div>

              <div class="row">
                <div class="mt-3 col-md-6">
                  <label for="assess-type" class="form-label">Assessment Type *</label>
                  <select id="assess-type" name="serviceType" class="form-select" required>
                    <option value="">Select assessment type</option>
                    <option value="cctv">CCTV Installation Assessment</option>
                    <option value="solar">Solar Panel Installation Assessment</option>
                    <option value="renovation">Renovation Assessment</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <!-- PAYMENT METHOD  -->
                <div class="mt-3 col-md-6">
                <label class="form-label">Payment Method *</label>
                
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paymentMethod" id="pay-cash" value="cash" required checked>
                  <label class="form-check-label" for="pay-cash">
                    Cash (Pay on Site)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paymentMethod" id="pay-gcash" value="gcash" required>
                  <label class="form-check-label" for="pay-gcash">
                    GCash
                  </label>
                </div>

              </div>

              <div class="row g-3 mt-1">

                <div class="col-md-6 mt-3">
                  <label for="pref-date" class="form-label">Preferred Date *</label>
                  <input id="pref-date" type="date" name="preferredDate" class="form-control"
                    min="<?= date('Y-m-d'); ?>" required>
                </div>

                <div class="col-md-6 mt-3">
                  <label for="pref-time" class="form-label">Preferred Time *</label>
                  <select id="pref-time" name="preferredTime" class="form-select" required>
                    <option value="">Select time</option>
                    <option value="morning">Morning (8AM - 12PM)</option>
                    <option value="afternoon">Afternoon (12PM - 5pm)</option>
                  </select>
                </div>

              </div>
              <div class="row">
                <div class="mt-4 col-md-6">
                  <label for="house-no" class="form-label">Street Name, Bldg, House No *</label>
                  <input id="house-no" type="text" name="house-no" class="form-control"
                    placeholder="Block 1 Lot 33 Alfredo Diaz St." required>
                </div>
                <div class="mt-4 col-md-3">
                  <label for="brgy" class="form-label">Barangay *</label>
                  <input id="brgy" type="text" name="brgy" class="form-control"
                    placeholder="Granados" required>
                </div>
                <div class="mt-4 col-md-3">
                  <label for="city" class="form-label">City *</label>
                  <input id="city" type="text" name="city" class="form-control"
                    placeholder="Carmona City" required>
                </div>
                <div class="mt-4 col-md-3">
                  <label for="province" class="form-label">Province *</label>
                  <input id="province" type="text" name="province" class="form-control"
                    placeholder="Cavite" required>
                </div>
                <div class="mt-4 col-md-3">
                  <label for="zip-code" class="form-label">Zip Code *</label>
                  <input id="zip-code" type="text" name="zip-code" class="form-control"
                    placeholder="4117" required>
                </div>
                <div class="mt-4 col-md-6">
                  <label class="form-label">Estimated Budget (Optional)</label>
                  <input type="number" name="estimatedBudget" class="form-control" placeholder="Enter budget in PHP">
                </div>

              </div>
              



              <div class="mt-3">
                <label for="notes" class="form-label">Additional Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="Any specific requirements or questions?"></textarea>
              </div>

              <button type="submit" class="btn btn-green w-100 mt-4">
                Schedule Assessment
              </button>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>