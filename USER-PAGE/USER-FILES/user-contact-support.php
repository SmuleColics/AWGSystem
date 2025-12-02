<?php
// include header
include 'user-header.php';

// Handle form submission
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST["name"] ?? "";
  $email = $_POST["email"] ?? "";
  $subject = $_POST["subject"] ?? "";
  $message = $_POST["message"] ?? "";

  // TODO: Replace with your DB insert code
  // Example only:
  // $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
  // mysqli_query($conn, $query);

  // Simulate success
  $success = "Message sent successfully! We'll get back to you soon.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Support</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .bg-gradient-custom {
      min-height: 100vh;
      padding-top: 80px;
      padding-bottom: 40px;
    }

    .shadow-elegant {
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
  </style>
</head>
<style>
  .nav-contact {
    color: #fff !important;
  }
</style>

<body class="bg-gradient-custom bg-light">

  <div class="container-xxl">
    <div class="mx-auto" style="max-width: 650px;">

      <div class="card shadow-elegant border-0 rounded-4">
        <div class="card-header border-0 bg-white pt-4 pb-0">
          <h2 class="mb-0">Contact Support</h2>
          <p class="text-muted mb-0">Need help? Send us a message.</p>
        </div>

        <div class="card-body border-0">

          <!-- SUCCESS ALERT -->
          <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>

          <!-- ERROR ALERT -->
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>

          <!-- FORM -->
          <form method="POST">

            <div class="mb-3">
              <label class="form-label">Full Name *</label>
              <input
                type="text"
                name="name"
                class="form-control"
                placeholder="John Doe"
                required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email *</label>
              <input
                type="email"
                name="email"
                class="form-control"
                placeholder="you@example.com"
                required>
            </div>

            <div class="mb-3">
              <label class="form-label">Subject *</label>
              <input
                type="text"
                name="subject"
                class="form-control"
                placeholder="How can we help you?"
                required>
            </div>

            <div class="mb-3">
              <label class="form-label">Message *</label>
              <textarea
                name="message"
                class="form-control"
                placeholder="Please describe your issue in detail..."
                rows="6"
                required></textarea>
            </div>

            <button type="submit" class="btn btn-green w-100 py-2">
              Send Message
            </button>

          </form>
        </div>
      </div>

    </div>
  </div>

</body>

</html>