<?php
include '../includes/dashboard-header-sidebar.php';
include '../includes/db-connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../LANDING-PAGE/PHPMailer/src/Exception.php';
require '../LANDING-PAGE/PHPMailer/src/PHPMailer.php';
require '../LANDING-PAGE/PHPMailer/src/SMTP.php';

// Handle Approve POST (for pending accounts)
if (isset($_POST['approve_id'])) {
  $pending_id = intval($_POST['approve_id']);
  $pending = mysqli_query($con, "SELECT * FROM tbl_signup_pending WHERE pending_id=$pending_id");
  if ($row = mysqli_fetch_assoc($pending)) {
    // Insert into tbl_signup_acc
    $stmt = $con->prepare("INSERT INTO tbl_signup_acc (first_name, last_name, mobile_number, signup_email, signup_password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $row['first_name'], $row['last_name'], $row['mobile_number'], $row['signup_email'], $row['signup_password']);
    $stmt->execute();
    $signup_id = $stmt->insert_id;
    $stmt->close();

    // Insert payment as approved
    $payment_amount = 99;
    $reference_no = $row['reference_no'];
    $date_created = date('Y-m-d H:i:s');
    mysqli_query($con, "INSERT INTO tbl_payment (signup_id, payment_amount, reference_no, date_created, status) VALUES ($signup_id, $payment_amount, '$reference_no', '$date_created', 'approved')");

    // Remove from pending
    mysqli_query($con, "DELETE FROM tbl_signup_pending WHERE pending_id=$pending_id");

    // Send approval email
    $to = $row['signup_email'];
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'oxy467777@gmail.com';
      $mail->Password = 'qnee ctax qhdw eebc';
      $mail->SMTPSecure = 'ssl';
      $mail->Port = 465;

      $mail->setFrom('oxy467777@gmail.com', 'CineVault Admin');
      $mail->addAddress($to);

      $mail->isHTML(true);
      $mail->Subject = "CineVault Account Approved";
      $mail->Body    = "Your CineVault account payment has been <b>approved</b>! You may now log in.";

      $mail->send();
      echo "<script>alert('Account approved and email sent.'); window.location.href='signup-accounts.php';</script>";
      exit;
    } catch (Exception $e) {
      echo "<script>alert('Account approved, but email could not be sent. Mailer Error: {$mail->ErrorInfo}'); window.location.href='signup-accounts.php';</script>";
      exit;
    }
  }
}

// Handle Deny POST (for pending accounts)
if (isset($_POST['deny_id'])) {
  $pending_id = intval($_POST['deny_id']);
  $pending = mysqli_query($con, "SELECT * FROM tbl_signup_pending WHERE pending_id=$pending_id");
  if ($row = mysqli_fetch_assoc($pending)) {
    $to = $row['signup_email'];
    mysqli_query($con, "DELETE FROM tbl_signup_pending WHERE pending_id=$pending_id");

    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'oxy467777@gmail.com';
      $mail->Password = 'qnee ctax qhdw eebc';
      $mail->SMTPSecure = 'ssl';
      $mail->Port = 465;

      $mail->setFrom('oxy467777@gmail.com', 'CineVault Admin');
      $mail->addAddress($to);

      $mail->isHTML(true);
      $mail->Subject = "CineVault Account Denied";
      $mail->Body    = "Sorry, your CineVault account payment was <b>not approved</b>. Please check your reference number or contact support for assistance.";

      $mail->send();
      echo "<script>alert('Account denied and email sent.'); window.location.href='signup-accounts.php';</script>";
      exit;
    } catch (Exception $e) {
      echo "<script>alert('Account denied, but email could not be sent. Mailer Error: {$mail->ErrorInfo}'); window.location.href='signup-accounts.php';</script>";
      exit;
    }
  }
}

// Handle Archive (Delete) POST (for already approved accounts)
if (isset($_POST['modal-delete-button'])) {
  $delete_id = (int) $_POST['delete-id'];
  $archive_query = mysqli_query($con, "UPDATE tbl_signup_acc SET is_archived=1 WHERE signup_id=$delete_id");
  if ($archive_query) {
    echo "<script>alert('Account archived successfully.'); window.location.href = 'signup-accounts.php';</script>";
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup Accounts</title>
  <link rel="stylesheet" href="../DASHBOARD-CSS/dashboard.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .sidebar-content-item:nth-child(4) {
      background-color: var(--dashboard-primary);
    }
    .sidebar-collapse-acc:nth-child(1) {
      background-color: var(--dashboard-primary);
    }
    .truncate-text {
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .bg-primary {
      background: #607BEC;
    }
  </style>
</head>

<body>
  <main class="container-lg p-0 overflow-hidden">
    <div class="me-3 mt-3 p-0 ms-md-2 ms-0 w-auto w-md-auto d-flex justify-content-end">
      <a href="archive-accounts.php" class="btn db-bg-primary db-text-sec" id="add-movie-btn">Archive Accounts</a>
    </div>
    <!-- ========== SIGN UP ACCOUNTS SECTION (APPROVED/DENIED) ========== -->
    <section class="signup-accounts-section text-left p-3">
      <div class="card bg-dark">
        <div class="card-body">

          <div class="table-task-top db-text-sec">
            <div class="py-2 ps-3">
              <p class="m-0 fs-20">Signup Accounts</p>
              <p class="m-0 fs-14" style="transform: translateY(-2px)">Authorized Sign-up Accounts</p>
            </div>
          </div>

          <div class="table-responsive mt-2">
            <table class="table table-hover display"  id="table-signup-acc">
              <thead>
                <tr>
                  <th class="db-text-primary text-align-left" scope="col">#</th>
                  <th class="db-text-primary" scope="col">First Name</th>
                  <th class="db-text-primary" scope="col">Last Name</th>
                  <th class="db-text-primary" scope="col">Mobile</th>
                  <th class="db-text-primary" scope="col">Email</th>
                  <th class="db-text-primary text-start" scope="col">Cash Tendered</th>
                  <th class="db-text-primary text-start" scope="col">Reference No.</th>
                  <th class="db-text-primary text-start" scope="col">Date Created</th>
                  <th class="db-text-primary" scope="col">Status</th>
                  <th class="db-text-primary" scope="col">Delete</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result = mysqli_query($con, "
                  SELECT 
                    s.signup_id AS id,
                    s.first_name,
                    s.last_name,
                    s.mobile_number,
                    s.signup_email,
                    p.payment_amount,
                    p.reference_no,
                    p.date_created,
                    p.status
                  FROM tbl_signup_acc AS s
                  JOIN tbl_payment AS p ON s.signup_id = p.signup_id
                  WHERE s.is_archived=0
                ");
                while ($row = mysqli_fetch_assoc($result)) {
                  $id = $row['id'];
                  $status = $row['status'];
                  ?>
                  <tr>
                    <th class="db-text-primary text-align-left" scope="row"><?php echo $id ?></th>
                    <td><?php echo htmlspecialchars($row['first_name']) ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']) ?></td>
                    <td><?php echo htmlspecialchars($row['mobile_number']) ?></td>
                    <td><?php echo htmlspecialchars($row['signup_email']) ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['payment_amount']) ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['reference_no']) ?></td>
                    <td><?php echo $row['date_created'] ?></td>
                    <td>
                      <?php
                      if ($status === 'pending') {
                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                      } elseif ($status === 'approved') {
                        echo '<span class="badge bg-success">Approved</span>';
                      } elseif ($status === 'denied') {
                        echo '<span class="badge bg-danger">Denied</span>';
                      } else {
                        echo htmlspecialchars($status);
                      }
                      ?>
                    </td>
                    <td>
                      <button class="btn text-white p-0 border-0 delete-btn" data-delete-id="<?php echo $id ?>"
                        data-bs-toggle="modal" data-bs-target="#signupDeleteModal">
                        <i class="ps-3 fa-solid fa-delete-left text-danger ps-2"></i>
                      </button>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </section>

    <!-- ========== PENDING SIGN UP ACCOUNTS SECTION ========== -->
    <section class="signup-accounts-section text-left p-3">
      <div class="card bg-dark ">
        <div class="card-body">

          <div class="table-task-top db-text-sec">
            <div class="py-2 ps-3">
              <p class="m-0 fs-20">Pending Signup Accounts</p>
              <p class="m-0 fs-14" style="transform: translateY(-2px)">Accounts awaiting admin approval</p>
            </div>
          </div>

          <div class="table-responsive mt-2">
            <table class="table table-hover display" id="table-signup-pending">
              <thead>
                <tr>
                  <th class="db-text-primary text-align-left" scope="col">#</th>
                  <th class="db-text-primary" scope="col">First Name</th>
                  <th class="db-text-primary" scope="col">Last Name</th>
                  <th class="db-text-primary" scope="col">Mobile</th>
                  <th class="db-text-primary" scope="col">Email</th>
                  <th class="db-text-primary" scope="col">Reference No.</th>
                  <th class="db-text-primary" scope="col">Date Created</th>
                  <th class="db-text-primary" scope="col">Approve/Deny</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $pending_result = mysqli_query($con, "SELECT * FROM tbl_signup_pending");
                while ($row = mysqli_fetch_assoc($pending_result)) {
                  $pending_id = $row['pending_id'];
                  ?>
                  <tr>
                    <th class="db-text-primary text-align-left" scope="row"><?php echo $pending_id ?></th>
                    <td><?php echo htmlspecialchars($row['first_name']) ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']) ?></td>
                    <td><?php echo htmlspecialchars($row['mobile_number']) ?></td>
                    <td><?php echo htmlspecialchars($row['signup_email']) ?></td>
                    <td><?php echo htmlspecialchars($row['reference_no']) ?></td>
                    <td><?php echo $row['date_created'] ?></td>
                    <td>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="approve_id" value="<?php echo $pending_id; ?>">
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                      </form>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="deny_id" value="<?php echo $pending_id; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                      </form>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </section>
  </main>

  <!-- ========== SIGNUP ACCOUNTS MODAL DELETE (ARCHIVE) ========== -->
  <div class="modal fade" id="signupDeleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5 db-text-sec" id="staticBackdropLabel">Archive Signup Account</h1>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss