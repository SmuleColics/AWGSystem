<?php
include('SignIn/db-con.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Landing Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="LandingPage.css">
  <link rel="stylesheet" href="../DASHBOARD-CSS/for-all.css">
  <link rel="stylesheet" href="../MovieWebsite/FirstProject.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="icon" href="../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
</head>
<body class="bg-dark" style="height: fit-content">
  <!-- START OF HEADER -->: 1
  <header class="d-flex justify-content-between align-items-center mx-5 bg-transparent landing-page-header fixed-top">
    <div class="left-header ">
      <a class="navbar-brand fs-20 text-white fw-semibold ms-md-5" href="#">Cine<span
          class="db-text-primary">Vault</span></a>
    </div>
    <div class="right-header">
      <button class="btn db-bg-primary db-text-sec me-md-5"><a class="text-white text-decoration-none"
          href="SignIn/LP-Signin.php">Sign
          In</a></button>
    </div>
  </header>
  <!-- END OF HEADER -->

  <!-- START OF MAIN CONTENT -->
  <main>

    <section class="section-movie-poster" style="transform: translateY(-25px);">
      <img class="landing-page-img position-relative" src="ImagesLP/LandingPageWallpaper.jpg" alt="">
      <div class="movie-poster-text db-text-sec text-center position-absolute">
        <h1 class="endless-movies fs-56 fw-bold text-nowrap text-center">Endless movies, TV <span
            class="d-block text-nowrap text-center">series, and more.</span></h1>
        <h2 class="starting-at fs-20 fw-semibold mt-2 mb-4">Starting at just ₱99. Cancel anytime.</h2>
        <p class="excited-to">Excited to start streaming? <a href="SignIn/LP-Signup.php" class="db-text-primary">Sign
            up</a> first to subscribe or
          renew your membership.</p>
        <!-- <button class="btn btn-primary sign-up-btn">Sign up</button> -->
      </div>
    </section>

    <!-- START OF SECTION TOP 10 -->
    <?php
    $select = mysqli_query($con, "
  SELECT 
    ms.*,
    g1.genre_name AS genre_1,
    g2.genre_name AS genre_2,
    g3.genre_name AS genre_3
  FROM tbl_movie_series ms
  LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
  LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
  LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
  ORDER BY ms.views DESC LIMIT 10
");
    $top10_rank = 1;
    $modals = "";
    ?>
    <section class="section-top-10 tewhite ms-5">
      <div class="top-10-container">

        <div class="top-10-top d-flex align-items-center">
          <p class="top-10-top-text">TOP 10</p>
          <div class="ms-3 fs-2 movies-today-text" style="color: #f4fff8">
            <p class="movies-text">MOVIES/SERIES</p>
            <p class="today-text">TODAY</p>
          </div>
        </div>

        <div class="top10-featured-wrapper position-relative">
          <div class="prev-button-top10"
            style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);z-index: 300;">
            <button class="btn border-1 prev-chevron-btn-top10">
              <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
            </button>
          </div>
          <div class="top-10-images-container d-flex gap-8 position-relative overflow-visible">
            <?php while ($row = mysqli_fetch_assoc($select)):
              $movie_series_id = $row['movie_series_id'];
              $top10_title = $row['title'];
              $top10_duration = $row['duration'];
              $top10_poster = $row['poster'];
              $top10_video = $row['video'];
              $top10_modal_poster_title = $row['modal_poster_title'];
              $top10_date_released = $row['date_released'];
              $top10_age_rating = $row['age_rating'];
              $top10_category = $row['category'];
              $top10_genre_1 = $row['genre_1'];
              $top10_genre_2 = $row['genre_2'];
              $top10_genre_3 = $row['genre_3'];
              $top10_cast = $row['cast'];
              $top10_description = $row['description'];
              ?>
              <div class="top-10-images position-relative">
                <p class="top-10-text"><?php echo $top10_rank; ?></p>
                <a href='#' data-bs-toggle='modal' data-bs-target='#modal-top10-<?php echo $movie_series_id ?>'>
                  <div class="top-10-hover position-relative">
                    <img class="top-10-img rounded-3"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($top10_poster); ?>"
                      alt="<?php echo htmlspecialchars($top10_title); ?>">
                    <i class="fa-solid fa-play play-button-top-10"></i>
                  </div>
                </a>
              </div>
              <?php
              $genres = htmlspecialchars($top10_genre_1);
              if (!empty($top10_genre_2))
                $genres .= ', ' . htmlspecialchars($top10_genre_2);
              if (!empty($top10_genre_3))
                $genres .= ', ' . htmlspecialchars($top10_genre_3);

              // === MODAL DESIGN IS COPIED FROM YOUR SECTION TRENDING MODAL ===
              $modals .= "
<div class='modal fade' id='modal-top10-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-top10-{$movie_series_id}' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
    <div class='modal-content bg-dark modals'>
      <div class='modal-body'>
        <div class='modal-body-content'>
          <div class='modal-pic-container m-0 position-relative'>
            <img class='w-100 position-relative rounded-3 m-0 p-0 sosyal-wallpaper' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($top10_poster) . "' alt='' >

            <img class='position-relative'  src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($top10_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(.5) translate(-260px, -440px);'>

            <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>

          <div class='modal-body-text p-4 mt-1' style='position: absolute; bottom: -30px;'>
            <div class='modal-text-ratings d-flex gap-1 text-white'>
              <p class='modal-text-rating'>" . htmlspecialchars($top10_date_released) . "</p>
              <p class='modal-text-rating'>" . htmlspecialchars($top10_age_rating) . "+</p>
              <p class='modal-text-rating'>" . htmlspecialchars($top10_category) . "</p>
              <p class='modal-text-rating'>" . htmlspecialchars($top10_genre_1) . "</p>";
              if (!empty($top10_genre_2)) {
                $modals .= "<p class='modal-text-rating'>" . htmlspecialchars($top10_genre_2) . "</p>";
              }
              if (!empty($top10_genre_3)) {
                $modals .= "<p class='modal-text-rating'>" . htmlspecialchars($top10_genre_3) . "</p>";
              }
              $modals .= "
            </div>
            <p class='text-white'>" . htmlspecialchars($top10_description) . "</p>
            <div>
              <button class='btn btn-primary fs-4 mb-3'>
                <a class='text-white text-decoration-none' href='SignIn/LP-Signup.php'>
                  Get started <i class='fa-solid fa-chevron-right text-white'></i>
                </a>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
";
              $top10_rank++;
            endwhile; ?>
          </div>
          <div class="next-button-top10 position-absolute"
            style="right: 0; top: 50%; transform: translateY(-50%);z-index: 300;">
            <button class="btn border-1 next-chevron-btn-top10">
              <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
            </button>
          </div>
        </div>
      </div>
    </section>
    <?php echo $modals; ?>
    <!-- END OF SECTION TOP 10 -->

    <!-- START OF SECTION MORE REASONS TO JOIN -->
    <section class="section-more-reasons mx-5" style="margin-top: 140px; margin-bottom: 140px;">
      <div class="more-reasons-top">
        <p class="trending-text db-text-sec fs-24 fw-bold">More Reasons to Join</p>
      </div>
      <div class="more-reasons-bottom db-text-sec d-flex">
        <div class="row">
          <div class="col-sm-12 col-md-6 col-lg-4">

            <div class="more-reasons-box p-4 d-flex flex-column justify-content-between mb-3">
              <div>
                <h3 class="py-2">Enjoy on your TV</h3>
                <p class="box-text">Watch on Smart TVs, gaming consoles like PlayStation and Xbox, streaming devices
                  such as Chromecast and Apple TV, Blu-ray players, and more.</p>
              </div>
              <div class="d-flex justify-content-end align-items-end box-icon-container">
                <i class=" box-icon fa-solid fa-tv"></i>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 col-lg-4 third-col">
            <div class="more-reasons-box p-4 d-flex flex-column justify-content-between mb-3">
              <div>
                <h3 class="py-2">Endless Variety of Entertainment</h3>
                <p class="box-text">
                  Explore a vast library of movies and series across all genres—from timeless classics to the latest
                  releases. Whether you're in the mood for action, romance, comedy, or documentaries, there's always
                  something new to discover.
                </p>
              </div>
              <div class="d-flex justify-content-end align-items-end box-icon-container">
                <i class="fa-solid fa-film box-icon"></i>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="more-reasons-box p-4 d-flex flex-column justify-content-between mb-3">
              <div>
                <h3 class="py-2">Stream anywhere, anytime</h2>
                  <p class="box-text">Watch endless movies and TV shows on your phone, tablet, laptop, and TV anytime.
                  </p>
              </div>
              <div class="d-flex justify-content-end align-items-end box-icon-container-m">
                <i class="fas fa-mobile-screen box-icon"></i>
              </div>
            </div>
          </div>

        </div>
      </div>

    </section>
    <!-- END OF SECTION MORE REASONS TO JOIN -->
            
    <!-- START OF FOOTER -->
    <footer class="footer-lp">
      <div class="row db-text-sec">

        <div class="col-sm-6 col-md-3">
          <div class="pb-2">
            <a class="color-light-white" href="#">FAQ</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Investor Relations</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Ways to watch</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Corporate Information</a>
          </div>
        </div>

        <div class="col-sm-6 col-md-3">
          <div class="pb-2">
            <a class="color-light-white" href="#">Help Center</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Jobs</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Terms of use</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Contact Us</a>
          </div>
        </div>

        <div class="col-sm-6 col-md-3">
          <div class="pb-2">
            <a class="color-light-white" href="#">Account</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Redeem Gift Cards</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Piracy</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Speed Test</a>
          </div>
        </div>

        <div class="col-sm-6 col-md-3">
          <div class="pb-2">
            <a class="color-light-white" href="#">Media Center</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Buy Gift Cards</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Cookie Preferences</a>
          </div>
          <div class="pb-2">
            <a class="color-light-white" href="#">Legal Notices</a>
          </div>
        </div>
        <p class="pt-5 color-light-white">CineVault Philippines</p>
      </div> 
    </footer> 
    <!-- END OF FOOTER -->
  
  </main>
  <!-- END OF MAIN CONTENT -->
  <script>

    // --- Top 10 Slider Logic ---
    const top10Slider = document.querySelector(".top-10-images-container");
    const top10NextButton = document.querySelector(".next-chevron-btn-top10");
    const top10PrevButton = document.querySelector(".prev-chevron-btn-top10");
    let top10ScrollAmount = 0;
    let top10ScrollStep = 500;
    top10Slider.style.transition = "transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)";

    function getTop10MaxScroll() {
      return top10Slider.scrollWidth - top10Slider.clientWidth;
    }
    function updateTop10Buttons() {
      if (top10ScrollAmount <= 0) {
        top10PrevButton.closest(".prev-button-top10").style.display = "none";
      } else {
        top10PrevButton.closest(".prev-button-top10").style.display = "";
      }
      if (
        getTop10MaxScroll() <= 0 ||
        top10ScrollAmount >= getTop10MaxScroll() - 5
      ) {
        top10NextButton.closest(".next-button-top10").style.display = "none";
      } else {
        top10NextButton.closest(".next-button-top10").style.display = "";
      }
    }
    top10NextButton.addEventListener("click", function () {
      top10ScrollAmount += top10ScrollStep;
      if (top10ScrollAmount > getTop10MaxScroll())
        top10ScrollAmount = getTop10MaxScroll();
      top10Slider.style.transform = `translateX(-${top10ScrollAmount}px)`;
      updateTop10Buttons();
    });
    top10PrevButton.addEventListener("click", function () {
      top10ScrollAmount -= top10ScrollStep;
      if (top10ScrollAmount < 0) top10ScrollAmount = 0;
      top10Slider.style.transform = `translateX(-${top10ScrollAmount}px)`;
      updateTop10Buttons();
    });
    window.addEventListener("resize", function () {
      if (top10ScrollAmount > getTop10MaxScroll()) {
        top10ScrollAmount = getTop10MaxScroll();
        top10Slider.style.transform = `translateX(-${top10ScrollAmount}px)`;
      }
      updateTop10Buttons();
    });
    updateTop10Buttons();


  </script>
</body>

</html>