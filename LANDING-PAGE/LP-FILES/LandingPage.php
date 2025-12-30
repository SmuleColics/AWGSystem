<?php
include 'LP-Header.php';
include '../../INCLUDES/db-con.php';

// Fetch recent public projects (limit to 3 most recent)
$projects_sql = "SELECT * FROM projects 
                WHERE visibility = 'Public' 
                AND is_archived = 0 
                ORDER BY created_at DESC 
                LIMIT 3";
$projects_result = mysqli_query($conn, $projects_sql);

// Store projects in array
$recent_projects = [];
if ($projects_result && mysqli_num_rows($projects_result) > 0) {
  while ($row = mysqli_fetch_assoc($projects_result)) {
    $recent_projects[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../LP-CSS/scroll-animation.css">
  <link rel="stylesheet" href="../LP-CSS/LandingPage.css">
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
  <style>
    .choose-con-icon {
      background-color: #56b378 !important;
    }

    .choose-con {
      background-color: #2ca257;
      border-color: #a1d6b4;
    }

    .get-quotation-btn:hover {
      background-color: #fff !important;
      color: #2ca257 !important;
    }

    .no-project-image {
      width: 100%;
      height: 200px;
      background-color: #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 48px;
      color: #ccc;
    }

    .project-badge {
      font-size: 12px;
      padding: 4px 12px;
    }
  </style>
</head>

<body>
  <main class="container-xxl p-0">

    <!--==========START OF HERO SECTION==========-->
    <section class="lp-hero-sec position-relative" id="hero">
      <img class="main-bg img-fluid w-100" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="A We Green Banner">
      <div class="hero-sec-container position-absolute top-0 start-0 w-100 h-100 text-light d-flex flex-column align-items-center justify-content-center text-center gap-2">

        <h1 class="green-text ">Building a Sustainable Future, One Project at a Time</h1>
        <p>A We Green Enterprise offers comprehensive solutions in security systems, renewable energy, and interior design to transform your spaces.</p>
        <a href="../../LOGS/LOGS-FILES/signup.php" class="btn green-bg btn-started text-light">Get Started</a>
      </div>
    </section>
    <!--==========END OF HERO SECTION========== -->

    <!--==========START OF ABOUT US SECTION========== -->
    <section class="lp-about-sec" id="about-us">
      <div class="section-container">
        <div class="lp-title-con text-center pb-5">
          <h2 class="green-text">About Us</h2>
          <p>Building tomorrow's sustainable solutions today</p>
        </div>

        <div class="row">
          <div class="col-md-6 pe-4 animate-left-on-scroll">
            <h3 class="awe-history green-text">A We Green Enterprise - Your Partner in Sustainable Innovation</h3>
            <p class="mt-4 light-text">Founded with a vision to create a greener, more sustainable future, We Green Enterprise has been at the forefront of delivering comprehensive solutions in security systems, renewable energy, and interior design for over a decade. <br> <br>

              Our team of certified professionals combines technical expertise with creative innovation to transform spaces and empower businesses and homeowners with cutting-edge technology. We believe in quality, sustainability, and building lasting relationships with our clients.
            </p>

            <div class="d-flex mt-5">
              <div class="w-50">
                <h4 class="text-success mb-0 about-stats">500+</h4>
                <p class="light-text">Projects Completed</p>
              </div>
              <div class="w-50">
                <h4 class="text-success mb-0 about-stats">98%</h4>
                <p class="light-text">Client Satisfaction</p>
              </div>
            </div>
          </div>

          <div class="col-md-6 animate-right-on-scroll">
            <div class="d-flex h-100 justify-content-center align-items-center gap-3 img-container">
              <div class="about-img-con mt-4">
                <img class="img-fluid rounded mb-3" src="../../INCLUDES/LP-IMAGES/solar-project.jpg" alt="">
                <img class="img-fluid rounded" src="../../INCLUDES/LP-IMAGES/cctv-project.jpg" alt="">
              </div>
              <div class="about-img-con -mt-4">
                <img class="about-img img-fluid rounded  mb-3" src="../../INCLUDES/LP-IMAGES/ceiling-project.webp" alt="">
                <img class="manlift-img img-fluid rounded object-fit-cover object" src="../../INCLUDES/LP-IMAGES/cctv-project-2.jpg" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
    <!--==========END OF ABOUT US SECTION========== -->

    <!--==========START OF SERVICES SECTION========== -->
    <section class="lp-services-sec light-bg" id="services">
      <div class="section-container">
        <div class="lp-title-con text-center pb-5">
          <h2 class="green-text">Our Services</h2>
          <p>Building tomorrow's sustainable solutions today</p>
        </div>
        <div class="row services-container animate-on-scroll">
          <div class="col-md-4">

            <div class="services-con border rounded-3 p-4 bg-white">
              <div class="services-icon-container rounded bg-success flex ">
                <i class="fas fa-video text-white fs-24"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">CCTV Installation</p>
              <p class="services-text light-text mb-0">Professional security camera systems with 24/7 monitoring capabilities and advanced analytics.</p>
            </div>

          </div>
          <div class="col-md-4">
            <div class="services-con border rounded-3 p-4 bg-white">
              <div class="services-icon-container rounded bg-success flex ">
                <i class="fas fa-sun text-white fs-24"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">Solar Panel Installation</p>
              <p class="services-text light-text mb-0">Eco-friendly and reliable solar solutions to efficiently reduce energy costs and promote sustainable living.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="services-con border rounded-3 p-4 bg-white">
              <div class="services-icon-container rounded bg-success flex ">
                <i class="fas fa-hammer text-white fs-24"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">Room Renovation</p>
              <p class="services-text light-text mb-0">Complete interior transformation services from initial design to flawless execution with premium quality finishes.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!--==========END OF SERVICES SECTION========== -->

    <!--==========START OF RECENT PROJECTS SECTION==========-->
    <section class="lp-projects-sec" id="projects">
      <div class="section-container">
        <div class="lp-title-con text-center pb-5">
          <h2 class="green-text">Our Recent Projects</h2>
          <p class="w-60 mx-auto">See how we've helped businesses and homeowners achieve their goals with our expert solutions</p>
        </div>
        <div class="row projects-container animate-on-scroll">

          <?php if (!empty($recent_projects)): ?>
            <?php foreach ($recent_projects as $project): ?>
              <?php
              // Process project image path
              $project_image_path = '';
              $image_exists = false;

              if (!empty($project['project_image'])) {
                // Remove all leading ../ or ./ or / from the path
                $clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $project['project_image']);

                // Prepend the correct base path
                $project_image_path = '../../ADMIN-PAGE/' . $clean_path;

                // Check if file exists
                if (file_exists('../../ADMIN-PAGE/' . $clean_path)) {
                  $image_exists = true;
                }
              }

              $badge_class = 'bg-success';
              ?>

              <div class="col-md-4">
                <div class="card">
                  <div class="projects-img-con position-relative overflow-hidden">
                    <?php if ($image_exists): ?>
                      <img src="<?= htmlspecialchars($project_image_path) ?>" 
                          class="card-img-top" 
                          alt="<?= htmlspecialchars($project['project_name']) ?>">
                    <?php else: ?>
                      <div class="no-project-image">
                        <i class="fas fa-image"></i>
                      </div>
                    <?php endif; ?>
                    <p class="position-absolute start-0 top-0 <?= $badge_class ?> rounded-pill px-3 py-1 text-white project-badge m-3">
                      <?= htmlspecialchars($project['project_type']) ?>
                    </p>
                  </div>
                  <div class="card-body">
                    <h5 class="card-title green-text"><?= htmlspecialchars($project['project_name']) ?></h5>
                    <p class="card-text fs-14">
                      <?php 
                        // Limit description to 100 characters
                        $description = $project['description'] ?? 'No description available.';
                        echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description);
                      ?>
                    </p>

                    <div class="divider mb-3"></div>

                    <div class="row">
                      <div class="col-8">
                        <p class="text-muted fs-12 mb-0">
                          <?php if (!empty($project['location'])): ?>
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($project['location']) ?>
                          <?php else: ?>
                            <?= htmlspecialchars($project['category'] ?? 'Project Details') ?>
                          <?php endif; ?>
                        </p>
                      </div>
                      <div class="col-4 flex">
                        <a href="../../LOGS/LOGS-FILES/signup.php" class="btn fs-14 flex gap-1 view-details-btn">
                          <span class="view-details">View Details </span>
                          <i class="fas fa-arrow-right ps-1"></i>
                        </a>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            <?php endforeach; ?>

          <?php else: ?>
            <!-- Fallback to default projects if no database projects -->
            <div class="col-md-4">
              <div class="card">
                <div class="projects-img-con position-relative overflow-hidden">
                  <img src="../../INCLUDES/LP-IMAGES/center-island-cctv.jpg" class="card-img-top" alt="...">
                  <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">CCTV Installation</p>
                </div>
                <div class="card-body">
                  <h5 class="card-title green-text">Center Island CCTV </h5>
                  <p class="card-text fs-14">Complete surveillance solution for a corporate facility with 32 high-definition cameras and advanced monitoring.</p>

                  <div class="divider mb-3"></div>

                  <div class="row">
                    <div class="col-8">
                      <p class="text-muted fs-12 mb-0">24 Cameras • 4K Resolution • 30-Day Storage</p>
                    </div>
                    <div class="col-4 flex">
                      <a href="../../LOGS/LOGS-FILES/signup.php" class="btn fs-14 flex gap-1 view-details-btn">
                        <span class="view-details">View Details </span>
                        <i class="fas fa-arrow-right ps-1"></i>
                      </a>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="projects-img-con position-relative overflow-hidden">
                  <img src="../../INCLUDES/LP-IMAGES/20-panel-solar.jpg" class="card-img-top" alt="...">
                  <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">Solar Panel Installation</p>
                </div>
                <div class="card-body">
                  <h5 class="card-title green-text">Residential Solar Installation</h5>
                  <p class="card-text fs-14">20-panel solar system reducing energy costs and providing sustainable energy for the home.</p>

                  <div class="divider mb-3"></div>

                  <div class="row">
                    <div class="col-8">
                      <p class="text-muted fs-12 mb-0">20 Panels • Renewable Energy • ROI in 6 Years</p>
                    </div>
                    <div class="col-4">
                      <a href="../../LOGS/LOGS-FILES/signup.php" class="btn fs-14 p-0 flex gap-1 view-details-btn d-inline-block">
                        <span class="view-details">View Details </span>
                        <i class="fas fa-arrow-right ps-1"></i>
                      </a>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card projects-card">
                <div class="projects-img-con position-relative overflow-hidden">
                  <img src="../../INCLUDES/LP-IMAGES/wpc-ceiling.webp" class="card-img-top img-fluid" alt="...">
                  <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">Room Renovation</p>
                </div>
                <div class="card-body">
                  <h5 class="card-title green-text">WPC Ceiling Installation</h5>
                  <p class="card-text fs-14">Modern and eco-friendly ceiling installation using durable Wood-Plastic Composite materials.</p>

                  <div class="divider mb-3"></div>

                  <div class="row">
                    <div class="col-8">
                      <p class="text-muted fs-12 mb-0">WPC Material • Eco-Friendly • Modern Style</p>
                    </div>
                    <div class="col-4">
                      <a href="#" class="btn fs-14 p-0 flex gap-1 view-details-btn d-inline-block">
                        <span class="view-details">View Details </span>
                        <i class="fas fa-arrow-right ps-1"></i>
                      </a>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </section>
    <!--==========END RECENT PROJECTS SECTION==========-->

    <!--==========START OF CHOOSE US SECTION========== -->
    <section class="lp-choose-sec green-bg" id="choose-us">
      <div class="section-container">
        <div class="lp-title-con text-center pb-5 text-white">
          <h2>Why Choose Us?</h2>
          <p class="w-80 mx-auto">We combine expertise, technology, and commitment to deliver exceptional results Schedule an assessment today and take the first step towards your dream project.</p>
        </div>
        <div class="row services-container w-80 mx-auto animate-on-scroll">
          <div class="col-md-4">

            <div class="choose-con text-white text-center border rounded-3 p-4">
              <div class="choose-icon-container">
                <i class="fa-solid fa-clipboard-check p-3 fs-28 rounded-circle 
                choose-con-icon"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">Free Assessment</p>
              <p class="services-text text-white mb-0">Get a professional evaluation of your project needs at no cost</p>
            </div>

          </div>
          <div class="col-md-4">

            <div class="choose-con text-white text-center border rounded-3 p-4">
              <div class="choose-icon-container">
                <i class="fa-solid fa-tags p-3 fs-28 rounded-circle 
                choose-con-icon"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">Transparent Pricing</p>
              <p class="services-text text-white mb-0">Detailed quotations with itemized costs and no hidden fees</p>
            </div>

          </div>
          <div class="col-md-4">

            <div class="choose-con text-white text-center border rounded-3 p-4">
              <div class="choose-icon-container">
                <i class="fa-solid fa-toolbox p-3 fs-28 rounded-circle 
                choose-con-icon"></i>
              </div>
              <p class="services-title fs-20 fw-semibold mt-3 mb-2">Quality Materials</p>
              <p class="services-text text-white mb-0">Premium materials ensuring long-lasting project quality.</p>
            </div>

          </div>
          <div class="choose-us-buttons mx-auto mt-5 flex">
            <a href="../../LOGS/LOGS-FILES/login.php">
              <button class="btn btn-light border me-3 btn-get-quotation px-4">Schedule Assessment 
                <i class="fa-solid fa-arrow-right ms-1"></i>
              </button>
              <button class="btn bg-transparent border me-3 btn-get-quotation px-4 text-white get-quotation-btn" style="border-color: #a1d6b4;">Get a Quotation 
              </button>
            </a>
          </div>

        </div>
      </div>
    </section>
    <!--==========END OF CHOOSE US SECTION========== -->

    <footer class="footer light-bg ">
      <div class="row section-container">
        <div class="col-md-3">
          <div class="d-flex flex-column align-items-start">
            <a href="#" class="fw-bold text-start text-decoration-none flex gap-1 fs-18">
              <img class="awegreen-logo-footer" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Logo" />
              <span class="green-text ">A We Green Enterprise</span>
            </a>
            <p class="mt-2 fs-14 light-text">Building a sustainable future through innovative green solutions and professional service excellence.</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column">
            <p class="fw-500">Quick Links</p>
            <div class="d-flex flex-column gap-2">
              <a href="#" class="nav-link fs-14 light-text">Home</a>
              <a href="#about-us" class="nav-link fs-14 light-text">About</a>
              <a href="#services" class="nav-link fs-14 light-text">Services</a>
              <a href="#choose-us" class="nav-link fs-14 light-text">Choose Us</a>
              <a href="#projects" class="nav-link fs-14 light-text">Projects</a>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column gap-2">
            <p>Our Services</p>
            <p class="fs-14 mb-0 light-text">CCTV Installation</p>
            <p class="fs-14 mb-0 light-text">Solar Panel Installation</p>
            <p class="fs-14 mb-0 light-text">Room Renovation</p>

          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column gap-2">
            <p>Contact Us</p>
            <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-envelope me-1"></i> awegreenenterprise@gmail.com</p>
            <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-phone me-1"></i>
              Globe: 0917 752 3343 Smart: 0998 884 5671</p>

            <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-location-dot me-1"></i>
              Main Office: ATH Phase 4 Blk 51 lot 30 Brgy. A. Olaes, GMA, Cavite 4117</p>
            <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-location-dot me-1"></i>
              Satellite Office: ATH Phase 5 Blk 14 lot 5 Brgy. F. de Castro, GMA, Cavite 4117</p>
          </div>
        </div>
      </div>
      <div class="section-container">
        <div class="divider my-5"></div>
      </div>
      <p class="text-center fs-14 light-text mx-auto">© 2025 We Green Enterprise. All rights reserved.</p>
    </footer>
  </main>

  <script src="scroll&header.js"></script>

</body>

</html>