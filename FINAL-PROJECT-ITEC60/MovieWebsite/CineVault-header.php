
<?php
include '../includes/db-connection.php';
session_start();

$user_id = $_SESSION['user_id'];

// Notification logic for "New Arrivals" (most recent 5 movies/series)
$notif_sql = "SELECT movie_series_id, title, date_posted 
              FROM tbl_movie_series 
              ORDER BY date_posted DESC
              LIMIT 5";
$notif_result = $con->query($notif_sql);
$notif_count = $notif_result ? $notif_result->num_rows : 0;

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header('Location: ../LANDING-PAGE/LandingPageMovie.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineVault</title>
  <link rel="icon" href="../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
  <!-- Font Awesome CDN (for icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .middle-header a.active {
      color: #607EBC !important;
    }
    .offcanvas-body .nav-link.active {
      color: #607EBC !important;
    }
    .notif-badge {
      font-size: 12px;
      padding: 2px 6px;
      position: absolute;
      top: -6px;
      right: -6px;
    }
  </style>
</head>

<body style="margin-top: 61px;">
  <!-- START OF HEADER -->
  <nav id="navbar" class="navbar navbar-dark fixed-top" style="background-color: #212529;">
    <div class="container-fluid">
      <header class="d-flex justify-content-between align-items-center w-100 px-md-5 bg-transparent">
        <div class="left-header">
          <a class="navbar-brand fw-semibold" href="website-search.php">Cine<span style="color: #607EBC">Vault</span></a>
        </div>

        <div class="middle-header position-relative d-none d-lg-block">
          <ul class="d-flex list-unstyled fs-18 m-0">
            <li>
              <a href="FirstProject.php" class="text-decoration-none text-white fw-bold me-2">Home</a>
            </li>
            <li>
              <a href="Movies.php" class="text-decoration-none text-white fw-bold mx-2">Movies</a>
            </li>
            <li>
              <a href="Series.php" class="text-decoration-none text-white fw-bold mx-2">Series</a>
            </li>
            <li>
              <a href="Categories.php" class="text-decoration-none text-white fw-bold ms-2">Categories</a>
            </li>
          </ul>
        </div>

        <div class="right-header d-flex">
          <div class="d-flex align-items-center">
            <div class="search-container">
              <form method="get" action="Website-Search.php" class="d-flex align-items-center mb-0 position-relative">
                <input class="search rounded-5 bg-transparent text-white" type="text" name="search"
                  placeholder="Search..." style="max-width: 200px;"
                  value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                <button type="submit" class="btn btn-link p-0 ms-2" style="color:inherit; position: absolute; top: 6px; left: -10px;">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </form>
            </div>

            <!-- ========== NOTIFICATIONS ========== -->
            <div class="d-flex dropdown-center ms-3">
              <div id="bell-container">
                <button class="btn border-0 p-0 flexbox-align position-relative" role="button" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <div id="button-bell" class="position-relative flexbox-align" data-bs-toggle="tooltip"
                    data-bs-placement="bottom" data-bs-title="Notifications"
                   >
                    <i class="fa-solid fa-bell fs-5 text-light"></i>
                    <?php if ($notif_count > 0): ?>
                      <span class="badge bg-danger rounded-pill notif-badge" style="position: absolute; top: -10px; right: -12px;"><?php echo $notif_count; ?></span>
                    <?php endif; ?>
                  </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark mt-1 notif-dropdown" style="transform: translate(-310px, 10px); min-width: 260px;">
                  <p class="fs-5 ps-3 mb-2 notif-text">New Arrivals</p>
                  <?php if ($notif_count > 0): ?>
                    <?php while($row = $notif_result->fetch_assoc()): ?>
                      <li>
                        <a class="dropdown-item" href="Website-Search.php?search=<?php echo urlencode($row['title']); ?>">
                          <span class="fw-bold"><?php echo htmlspecialchars($row['title']); ?></span>
                          <span class="text-secondary small">(<?php echo date('M d, Y', strtotime($row['date_posted'])); ?>)</span>
                        </a>
                      </li>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <li><a class="dropdown-item" href="#">No new arrivals</a></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
            <!-- ========== END NOTIFICATIONS ========== -->

            <!-- ========== PROFILE MENU ========== -->
            <div class="dropdown-center">
              <button class="btn p-0 border-0 ms-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="header-user rounded-circle flexbox-align ms-1" style="padding: 11px; color: #f4fff8;">
                  <i class="fa-solid fa-user db-text-sec fs-18"></i>
                </div>
              </button>
              <ul class="dropdown-menu dropdown-menu-dark mt-1 profile-dropdown" style="transform: translateX(-130px);">
                <li class="dropdown-profile-top d-flex mb-1" style="height: 50px;">
                  <a href="user-view-profile.php" class="dropdown-item d-flex align-items-center" href="">
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center ms-1"
                      style="height: 32px; width: 32px; transform: translateX(-9px);">
                      <i class="fa-solid fa-user db-text-sec fs-18"></i>
                    </div>
                    <div class="dropdown-profile-text" style="margin-left: -4px;">
                      <p class="fs-18 view-profile-text" style="transform: translateY(10px)">Manage Profile</p>
                      <p class="m-0 fs-14 db-text-secondary" style="transform: translateY(-10px)">User</p>
                    </div>
                  </a>
                </li>
                <li class="mb-1">
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <i class="fa-solid fa-question ms-1 me-2 fs-22"></i>
                    <span class="fs-18 d-inline-block ms-1">Help & Support</span>
                  </a>
                </li>
                <li class="mb-1" style="transform: translateX(-12px);">
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <i class="fa-solid fa-gear me-2 fs-22 "></i>
                    <span class="fs-18 d-inline-block">Settings</span>
                  </a>
                </li>
                <li class="mb-1">
                  <a href="../LANDING-PAGE/LandingPageMovie.php" class="dropdown-item d-flex align-items-center">
                    <i class="fa-solid fa-right-from-bracket me-2 fs-22"></i>
                    <span class="fs-18 d-inline-block">Log out</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <!-- ========== END PROFILE MENU ========== -->
          <button class="navbar-toggler text-light d-lg-none ms-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>
      </header>

      <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar"
        aria-labelledby="offcanvasDarkNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">
            Cine<span class="text-primary">Vault</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
        </div>
        <div class="offcanvas-body text-center">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link text-white" href="FirstProject.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="Movies.php">Movies</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="Series.php">Series</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="Categories.php">Categories</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <!-- END OF HEADER -->

  <script src="header-scroll.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // For desktop
      const desktopLinks = document.querySelectorAll('.middle-header a');
      // For offcanvas
      const offcanvasLinks = document.querySelectorAll('.offcanvas-body .nav-link');

      function setActive(links) {
        let found = false;
        links.forEach(link => {
          const linkPage = link.getAttribute('href').split('?')[0];
          const currentPage = window.location.pathname.split('/').pop();
          if (linkPage === currentPage) {
            link.classList.add('active');
            found = true;
          } else {
            link.classList.remove('active');
          }
        });
        // If no match, set Home as active by default
        if (!found && links.length > 0) {
          links[0].classList.add('active');
        }
      }

      setActive(desktopLinks);
      setActive(offcanvasLinks);
    });
  </script>
</body>
</html>