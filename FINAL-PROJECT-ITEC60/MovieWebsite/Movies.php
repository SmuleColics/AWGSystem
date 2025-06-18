<?php
include 'CineVault-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Movies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <link rel="stylesheet" href="FirstProject.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Override.css">
  <link rel="icon" href="../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">

</head>

<body class="bg-dark">

  <!-- START OF THE MAIN CONTENT -->
  <main>

    <!-- START OF SECTION WALLPAPER -->
    <section class="section-wallpaper">
      <div id="carouselExampleControls" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
          $carousel_modals = "";
          $carousel_query = mysqli_query($con, "
        SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
        FROM tbl_movie_series ms
        LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
        LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
        LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
        WHERE ms.category = 'Movie'
          AND ms.poster LIKE '%MovieCarousel%'
        ORDER BY ms.date_posted DESC
        LIMIT 3
      ");
          $active = true;
          while ($row = mysqli_fetch_assoc($carousel_query)):
            $movie_series_id = $row['movie_series_id'];
            $desc = htmlspecialchars($row['description']);
            $maxlen = 110;
            if (function_exists('mb_substr')) {
              if (mb_strlen($desc) > $maxlen) {
                $desc = mb_substr($desc, 0, $maxlen) . '...';
              }
            } else {
              if (strlen($desc) > $maxlen) {
                $desc = substr($desc, 0, $maxlen) . '...';
              }
            }
            $genres = htmlspecialchars($row['genre_1']);
            if (!empty($row['genre_2']))
              $genres .= ', ' . htmlspecialchars($row['genre_2']);
            if (!empty($row['genre_3']))
              $genres .= ', ' . htmlspecialchars($row['genre_3']);
            ?>
            <div class="carousel-item<?php if ($active) {
              echo ' active';
              $active = false;
            } ?> position-relative">
              <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($row['poster']); ?>"
                class="d-block w-100 wallpaper" alt="<?php echo htmlspecialchars($row['title']); ?> Wallpaper">
              <div class="wallpaper-description text-primary">
                <div class="wallpaper-left text-white d-flex flex-column">
                  <div class="d-flex justify-content-center">
                    <img class="sonic-title-img"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($row['modal_poster_title']); ?>"
                      alt="<?php echo htmlspecialchars($row['title']); ?> Title">
                  </div>
                  <div class="wallpaper-ratings d-flex align-items-center justify-content-center my-2 my-md-4">
                    <div class="rating-year">
                      <?php echo htmlspecialchars($row['date_released']); ?>
                    </div>
                    <span class="mx-2 text-white-50">|</span>
                    <div class="rating-points d-flex">
                      <?php echo htmlspecialchars($row['age_rating']); ?>+
                    </div>
                    <span class="mx-2 text-white-50">|</span>
                    <div class="rating-gender text-nowrap">
                      <?php echo htmlspecialchars($row['category']); ?>
                    </div>
                    <div class="wallpaper-line bg-secondary mx-3"></div>
                  </div>
                  <div class="rating-text">
                    <?php echo $desc; ?>
                  </div>
                </div>
                <div class="wallpaper-right mt-1">
                  <div class="wallpaper-watch d-none d-md-block">
                    <div class="d-flex align-items-center gap-3">
                      <!-- More Info Button with transform style -->
                      <button class="btn d-flex db-text-sec text-nowrap align-items-center rounded-2"
                        style="background: rgba(255,255,255,0.15); border: none; width: fit-content; font-size: 16px; height: fit-content; transform: translate(280px, 30px); z-index: 300;"
                        data-bs-toggle="modal" data-bs-target="#modal-carousel-<?php echo $movie_series_id; ?>">
                        <i class="fa-solid fa-info-circle me-1"></i> More Info
                      </button>
                      <button class="btn btn-outline-primary rounded-circle"
                        onclick="window.location.href='play-vid.php?video=<?php echo urlencode($row['video']); ?>&type=movie_series&id=<?php echo $row['movie_series_id']; ?>'">
                        <i class="fa-solid fa-play"></i>
                      </button>
                      <p class="watch-now fs-2 fw-bold text-white m-0 text-nowrap" style="transform: translateY(-10px)">
                        WATCH NOW!</p>
                    </div>
                  </div>
                  <div class="wallpaper-watch-s d-md-none d-flex flex-column gap-2">
                    <button class="btn btn-primary rounded-5 mt-3 watch-now-small"
                      onclick="window.location.href='play-vid.php?video=<?php echo urlencode($row['video']); ?>&type=movie_series&id=<?php echo $row['movie_series_id']; ?>'">
                      <i class="fa-solid fa-play mx-1"></i>
                      <span>Watch Now</span>
                    </button>
                    <!-- More Info Button for mobile -->
                    <button class="btn d-flex db-text-sec text-nowrap align-items-center rounded-2"
                      style="background: rgba(255,255,255,0.15); border: none; width: fit-content; font-size: 16px; height: fit-content; position: relative; transform: translate(12px, 10px); z-index: 300;"
                      data-bs-toggle="modal" data-bs-target="#modal-carousel-<?php echo $movie_series_id; ?>">
                      <i class="fa-solid fa-info-circle me-1"></i> More Info
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <?php
            // Carousel modal for this item
            $carousel_modals .= "
      <div class='modal fade' id='modal-carousel-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-carousel-{$movie_series_id}' aria-hidden='true'>
        <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
          <div class='modal-content bg-dark modals'>
            <div class='modal-body'>
              <div class='modal-body-content'>
                <div class='modal-pic-container m-0 position-relative'>
                  <video
                    class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
                    autoplay
                    muted
                    loop
                  >
                    <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($row['video']) . "\" type=\"video/mp4\">
                    Your browser does not support the video tag.
                  </video>";
            if (!empty($row['modal_poster_title'])) {
              $carousel_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($row['modal_poster_title']) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $carousel_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($row['title']) . "</p>";
            }
            $carousel_modals .= "
                  <a href=\"play-vid.php?video=" . urlencode($row['video']) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
                    <i class=\"fa-solid fa-play me-1\"></i> Play
                  </a>
                  <button class=\"volume-control bg-transparent position-absolute\">
                    <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
                  </button>
                  <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
                  <div class=\"col-8 text-wrap\">
                    <div class=\"d-flex gap-2\">
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($row['date_released']) . "</p>
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($row['duration']) . "</p>
                    </div>
                    <div class=\"d-flex gap-2 align-items-center\">
                      <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($row['age_rating']) . "+</p>
                      <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($row['category']) . "</p>
                    </div>
                    <p class='modal-text-rating'>" . htmlspecialchars($row['description']) . "</p>
                  </div>
                  <div class=\"col-4 ps-0 pe-4 text-wrap\">
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                      " . htmlspecialchars($row['cast']) . ", more...
                    </p>
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                      {$genres}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      ";
          endwhile;
          ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls"
          data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls"
          data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
      <?php echo $carousel_modals; ?>
    </section>
    <!-- END OF SECTION WALLPAPER -->

    <!-- START OF SECTION TOP 10 MOVIES-->
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
  WHERE ms.category = 'Movie'
    AND ms.poster NOT LIKE '%HomeCarousel%'
    AND ms.poster NOT LIKE '%MovieCarousel%'
    AND ms.poster NOT LIKE '%SeriesCarousel%'
    AND ms.poster NOT LIKE '%HomeWallpaper%'
  ORDER BY ms.views DESC LIMIT 10
");
    $top10_rank = 1;
    $modals = "";
    ?>
    <section id="section-top-10" class="section-top-10 text-white ms-5">
      <div class="top-10-container">
        <div class="top-10-top d-flex align-items-center">
          <p class="top-10-top-text">TOP 10</p>
          <div class="ms-3 fs-2 movies-today-text">
            <p class="movies-text">MOVIES</p>
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
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-top10-<?php echo $movie_series_id ?>">
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

              // Modal for each movie
              $modals .= "
<div class='modal fade' id='modal-top10-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-{$movie_series_id}' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
    <div class='modal-content bg-dark modals'>
      <div class='modal-body'>
        <div class='modal-body-content'>
          <div class='modal-pic-container m-0 position-relative'>
            <video
              class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
              autoplay
              muted
              loop
            >
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($top10_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
              if (!empty($top10_modal_poster_title)) {
                $modals .= "<img class='poster-title-img top10-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($top10_modal_poster_title) . "' alt=''>";
              } else {
                $modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($top10_title) . "</p>";
              }
              $modals .= "
            <a href=\"play-vid.php?video=" . urlencode($top10_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
              <i class=\"fa-solid fa-play me-1\"></i> Play
            </a>
            <button class=\"volume-control bg-transparent position-absolute\">
              <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
            </button>
            <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
            <div class=\"col-8 text-wrap\">
              <div class=\"d-flex gap-2\">
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($top10_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($top10_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($top10_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($top10_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($top10_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($top10_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
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
    <!-- END OF SECTION TOP 10 MOVIES -->

    <!-- START OF SECTION ADVENTURE MOVIES -->
    <section id="section-popular-adventure-movies" class="section-trending ms-md-5 ms-3">
      <?php
      $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Adventure%' LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_id = $genre ? $genre['genre_id'] : 2; // fallback if not found
      $genre_name = $genre ? $genre['genre_name'] : "Adventure";
      ?>
      <div id="dropdown-popular-adventure" class="trending-this-week d-flex justify-content-between">
        <p class="trending-text text-white fs-24 fw-bold"><?php echo htmlspecialchars($genre_name); ?> Movies</p>
      </div>
      <div class="top10-featured-wrapper position-relative">
        <div class="prev-button-trending position-absolute"
          style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
          <button class="btn border-1 prev-chevron-btn-trending">
            <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
          </button>
        </div>
        <div class="trending-images-container d-flex gap-3 position-relative">
          <?php

          $adventure_query = mysqli_query(
            $con,
            "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
        FROM tbl_movie_series ms
        LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
        LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
        LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
        WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
          AND ms.category = 'Movie'
          AND ms.poster NOT LIKE '%HomeCarousel%'
          AND ms.poster NOT LIKE '%MovieCarousel%'
          AND ms.poster NOT LIKE '%SeriesCarousel%'
          AND ms.poster NOT LIKE '%HomeWallpaper%'
        ORDER BY ms.date_released DESC
        LIMIT 8"
          );
          $adventure_modals = "";
          while ($row = mysqli_fetch_assoc($adventure_query)):
            $movie_series_id = $row['movie_series_id'];
            $title = $row['title'];
            $poster = $row['poster'];
            $video = $row['video'];
            $modal_poster_title = $row['modal_poster_title'];
            $duration = $row['duration'];
            $date_released = $row['date_released'];
            $age_rating = $row['age_rating'];
            $category = $row['category'];
            $genre_1 = $row['genre_1'];
            $genre_2 = $row['genre_2'];
            $genre_3 = $row['genre_3'];
            $cast = $row['cast'];
            $description = $row['description'];

            $genres = htmlspecialchars($genre_1);
            if (!empty($genre_2))
              $genres .= ', ' . htmlspecialchars($genre_2);
            if (!empty($genre_3))
              $genres .= ', ' . htmlspecialchars($genre_3);
            ?>
            <div class="trending-images position-relative pe-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-adventure-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="trending-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($poster); ?>"
                    alt="<?php echo htmlspecialchars($title); ?>">
                  <i class="fa-solid fa-play play-button"></i>
                </div>
              </a>
            </div>
            <?php
            $adventure_modals .= "
      <div class='modal fade' id='modal-adventure-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-adventure-{$movie_series_id}' aria-hidden='true'>
        <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
          <div class='modal-content bg-dark modals'>
            <div class='modal-body'>
              <div class='modal-body-content'>
                <div class='modal-pic-container m-0 position-relative'>
                  <video
                    class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
                    autoplay
                    muted
                    loop
                  >
                    <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($video) . "\" type=\"video/mp4\">
                    Your browser does not support the video tag.
                  </video>";
            if (!empty($modal_poster_title)) {
              $adventure_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $adventure_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($title) . "</p>";
            }
            $adventure_modals .= "
                  <a href=\"play-vid.php?video=" . urlencode($video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
                    <i class=\"fa-solid fa-play me-1\"></i> Play
                  </a>
                  <button class=\"volume-control bg-transparent position-absolute\">
                    <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
                  </button>
                  <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
                  <div class=\"col-8 text-wrap\">
                    <div class=\"d-flex gap-2\">
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($date_released) . "</p>
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($duration) . "</p>
                    </div>
                    <div class=\"d-flex gap-2 align-items-center\">
                      <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($age_rating) . "+</p>
                      <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($category) . "</p>
                    </div>
                    <p class='modal-text-rating'>" . htmlspecialchars($description) . "</p>
                  </div>
                  <div class=\"col-4 ps-0 pe-4 text-wrap\">
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                      " . htmlspecialchars($cast) . ", more...
                    </p>
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                      {$genres}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      ";
          endwhile; ?>
        </div>
        <div class="next-button-trending position-absolute"
          style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
          <button class="btn border-1 next-chevron-btn-trending">
            <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
          </button>
        </div>
      </div>
    </section>
    <?php echo $adventure_modals; ?>
    <!-- END OF SECTION ADVENTURE MOVIES -->

    <!-- START OF SECTION THRILLER -->
    <?php
    $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Thriller%' LIMIT 1");
    $genre = mysqli_fetch_assoc($genre_result);
    $genre_id = $genre ? $genre['genre_id'] : 16; // fallback if not found
    $genre_name = $genre ? $genre['genre_name'] : "Thriller";
    ?>

    <section id="section-thriller" class="ms-md-5 ms-3" style="margin-top: 60px;">
      <div class="action-movies-top d-flex justify-content-between">
        <p class="action-movies-text text-white fs-24 fw-bold"><?php echo $genre_name ?> Movies</p>
      </div>
      <div class="top10-featured-wrapper position-relative">
        <div class="prev-button-comedy position-absolute"
          style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
          <button class="btn border-1 prev-chevron-btn-comedy">
            <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
          </button>
        </div>
        <div class="action-images-container d-flex gap-3 position-relative">
          <?php
          $thriller_query = mysqli_query(
            $con,
            "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
        AND ms.category = 'Movie'
        AND ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY ms.date_released DESC
      LIMIT 7"
          );
          $thriller_modals = "";
          while ($row = mysqli_fetch_assoc($thriller_query)):
            $movie_series_id = $row['movie_series_id'];
            $thriller_title = $row['title'];
            $thriller_poster = $row['poster'];
            $thriller_video = $row['video'];
            $thriller_modal_poster_title = $row['modal_poster_title'];
            $thriller_duration = $row['duration'];
            $thriller_date_released = $row['date_released'];
            $thriller_age_rating = $row['age_rating'];
            $thriller_category = $row['category'];
            $thriller_genre_1 = $row['genre_1'];
            $thriller_genre_2 = $row['genre_2'];
            $thriller_genre_3 = $row['genre_3'];
            $thriller_cast = $row['cast'];
            $thriller_description = $row['description'];

            $genres = htmlspecialchars($thriller_genre_1);
            if (!empty($thriller_genre_2))
              $genres .= ', ' . htmlspecialchars($thriller_genre_2);
            if (!empty($thriller_genre_3))
              $genres .= ', ' . htmlspecialchars($thriller_genre_3);
            ?>
            <div class="comedy-images position-relative pe-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-thriller-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="action-movies-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($thriller_poster); ?>"
                    alt="<?php echo htmlspecialchars($thriller_title); ?>">
                  <i class="fa-solid fa-play play-button-comedy"></i>
                </div>
              </a>
            </div>
            <?php
            $thriller_modals .= "
<div class='modal fade' id='modal-thriller-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-thriller-{$movie_series_id}' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
    <div class='modal-content bg-dark modals'>
      <div class='modal-body'>
        <div class='modal-body-content'>
          <div class='modal-pic-container m-0 position-relative'>
            <video
              class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
              autoplay
              muted
              loop
            >
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($thriller_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
            if (!empty($thriller_modal_poster_title)) {
              $thriller_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($thriller_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $thriller_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($thriller_title) . "</p>";
            }
            $thriller_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($thriller_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
              <i class=\"fa-solid fa-play me-1\"></i> Play
            </a>
            <button class=\"volume-control bg-transparent position-absolute\">
              <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
            </button>
            <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
            <div class=\"col-8 text-wrap\">
              <div class=\"d-flex gap-2\">
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($thriller_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($thriller_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($thriller_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($thriller_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($thriller_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($thriller_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
";
          endwhile;
          ?>
        </div>
        <div class="next-button-comedy position-absolute"
          style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
          <button class="btn border-1 next-chevron-btn-comedy">
            <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
          </button>
        </div>
      </div>
    </section>
    <?php echo $thriller_modals; ?>
    <!-- END OF SECTION THRILLER -->

    <!-- START OF SECTION SCI-FI  -->
    <section class="section-featured text-white ms-md-5 ms-3">
      <?php
      $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Sci-Fi%' LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_id = $genre ? $genre['genre_id'] : 14; // fallback if not found
      $genre_name = $genre ? $genre['genre_name'] : "Sci-Fi";
      ?>
      <div class="featured-container">
        <div class="featured-top">
          <p class="featured-text text-uppercase"><?php echo $genre_name ?></p>
          <p class="featured-movies-text fs-2">MOVIES</p>
          <div class="top10-featured-wrapper position-relative">
            <div class="prev-button-featured position-absolute"
              style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
              <button class="btn border-1 prev-chevron-btn-featured">
                <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
              </button>
            </div>
            <div class="featured-images-container d-flex gap-3 position-relative">
              <?php

              $scifi_query = mysqli_query(
                $con,
                "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
        AND ms.category = 'Movie'
        AND ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY ms.date_released DESC
      LIMIT 8"
              );
              $scifi_modals = "";
              while ($row = mysqli_fetch_assoc($scifi_query)):
                $movie_series_id = $row['movie_series_id'];
                $scifi_title = $row['title'];
                $scifi_poster = $row['poster'];
                $scifi_video = $row['video'];
                $scifi_modal_poster_title = $row['modal_poster_title'];
                $scifi_duration = $row['duration'];
                $scifi_date_released = $row['date_released'];
                $scifi_age_rating = $row['age_rating'];
                $scifi_category = $row['category'];
                $scifi_genre_1 = $row['genre_1'];
                $scifi_genre_2 = $row['genre_2'];
                $scifi_genre_3 = $row['genre_3'];
                $scifi_cast = $row['cast'];
                $scifi_description = $row['description'];

                $genres = htmlspecialchars($scifi_genre_1);
                if (!empty($scifi_genre_2))
                  $genres .= ', ' . htmlspecialchars($scifi_genre_2);
                if (!empty($scifi_genre_3))
                  $genres .= ', ' . htmlspecialchars($scifi_genre_3);
                ?>
                <div class="featured-images position-relative pe-2" style="overflow: visible;">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#modal-scifi-<?php echo $movie_series_id ?>">
                    <div class="trending-hover position-relative">
                      <img class="featured-img rounded-3"
                        src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($scifi_poster); ?>"
                        alt="<?php echo htmlspecialchars($scifi_title); ?>">
                      <i class="fa-solid fa-play play-button-featured"></i>
                    </div>
                  </a>
                </div>
                <?php
                $scifi_modals .= "
<div class='modal fade' id='modal-scifi-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-scifi-{$movie_series_id}' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
    <div class='modal-content bg-dark modals'>
      <div class='modal-body'>
        <div class='modal-body-content'>
          <div class='modal-pic-container m-0 position-relative'>
            <video
              class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
              autoplay
              muted
              loop
            >
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($scifi_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
                if (!empty($scifi_modal_poster_title)) {
                  $scifi_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($scifi_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
                } else {
                  $scifi_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($scifi_title) . "</p>";
                }
                $scifi_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($scifi_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
              <i class=\"fa-solid fa-play me-1\"></i> Play
            </a>
            <button class=\"volume-control bg-transparent position-absolute\">
              <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
            </button>
            <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
            <div class=\"col-8 text-wrap\">
              <div class=\"d-flex gap-2\">
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($scifi_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($scifi_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($scifi_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($scifi_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($scifi_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($scifi_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
";
              endwhile;
              ?>
            </div>
            <div class="next-button-featured position-absolute"
              style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
              <button class="btn border-1 next-chevron-btn-featured">
                <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php echo $scifi_modals; ?>
    <!-- END OF SECTION SCI-FI  -->


    <!-- START OF PRINCESS MONONOKE -->
    <section class="movies-1 mt-80">
      <div>
        <?php
        // Fetch the movie/series for HomeWallpaper3
        $wallpaper3_query = mysqli_query($con, "
        SELECT * FROM tbl_movie_series
        WHERE poster LIKE '%HomeWallpaper3%'
        LIMIT 1
      ");
        $wallpaper3 = mysqli_fetch_assoc($wallpaper3_query);
        if ($wallpaper3):
          ?>
          <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper3['poster']); ?>"
            class="d-block w-100 wallpaper-lotr bg-danger"
            alt="<?php echo htmlspecialchars($wallpaper3['title']); ?> Wallpaper">

          <div
            class="wallpaper-description-tem d-flex flex-column justify-content-end align-items-end position-relative me-5">

            <div class="d-flex justify-content-center">
              <img class="tem-title-img position-relative mb-1"
                src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper3['modal_poster_title']); ?>"
                alt="<?php echo htmlspecialchars($wallpaper3['title']); ?> Title">
            </div>

            <div class="rating-text-tem my-4 position-relative text-white">
              <p><?php echo htmlspecialchars($wallpaper3['description']); ?></p>
            </div>

            <div class="watch-now-tem position-relative">
              <button class="btn btn-primary rounded-5 watch-now-small"
                onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper3['video']); ?>&type=movie_series&id=<?php echo $wallpaper3['movie_series_id']; ?>'">
                <i class="fa-solid fa-play mx-1"></i>
                <span>Watch Now</span>
              </button>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- END OF PRINCESS MONONOKE-->

    <!-- START OF SECTION DRAMA -->
    <?php
    $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Drama%' LIMIT 1");
    $genre = mysqli_fetch_assoc($genre_result);
    $genre_id = $genre ? $genre['genre_id'] : 7; // fallback if not found
    $genre_name = $genre ? $genre['genre_name'] : "Drama";
    ?>
    <section id="section-drama" class="ms-md-5 ms-3" style="margin-top: -150px;">
      <div class="action-movies-top d-flex justify-content-between">
        <p class="action-movies-text text-white fs-24 fw-bold"><?php echo $genre_name ?> Movies</p>
      </div>
      <div class="top10-featured-wrapper position-relative">
        <div class="prev-button-drama position-absolute"
          style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
          <button class="btn border-1 prev-chevron-btn-drama">
            <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
          </button>
        </div>
        <div class="action-images-container d-flex gap-3 position-relative">
          <?php


          $drama_query = mysqli_query(
            $con,
            "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
        AND ms.category = 'Movie'
        AND ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY ms.date_released DESC
      LIMIT 7"
          );
          $drama_modals = "";
          while ($row = mysqli_fetch_assoc($drama_query)):
            $movie_series_id = $row['movie_series_id'];
            $drama_title = $row['title'];
            $drama_poster = $row['poster'];
            $drama_video = $row['video'];
            $drama_modal_poster_title = $row['modal_poster_title'];
            $drama_duration = $row['duration'];
            $drama_date_released = $row['date_released'];
            $drama_age_rating = $row['age_rating'];
            $drama_category = $row['category'];
            $drama_genre_1 = $row['genre_1'];
            $drama_genre_2 = $row['genre_2'];
            $drama_genre_3 = $row['genre_3'];
            $drama_cast = $row['cast'];
            $drama_description = $row['description'];

            $genres = htmlspecialchars($drama_genre_1);
            if (!empty($drama_genre_2))
              $genres .= ', ' . htmlspecialchars($drama_genre_2);
            if (!empty($drama_genre_3))
              $genres .= ', ' . htmlspecialchars($drama_genre_3);
            ?>
            <div class="comedy-images position-relative pe-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-drama-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="action-movies-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($drama_poster); ?>"
                    alt="<?php echo htmlspecialchars($drama_title); ?>">
                  <i class="fa-solid fa-play play-button-comedy"></i>
                </div>
              </a>
            </div>
            <?php
            $drama_modals .= "
<div class='modal fade' id='modal-drama-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-drama-{$movie_series_id}' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered modal-lg modal-dark border-3'>
    <div class='modal-content bg-dark modals'>
      <div class='modal-body'>
        <div class='modal-body-content'>
          <div class='modal-pic-container m-0 position-relative'>
            <video
              class=\"w-100 position-relative rounded-3 m-0 p-0 video-player\"
              autoplay
              muted
              loop
            >
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($drama_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
            if (!empty($drama_modal_poster_title)) {
              $drama_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($drama_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $drama_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($drama_title) . "</p>";
            }
            $drama_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($drama_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
              <i class=\"fa-solid fa-play me-1\"></i> Play
            </a>
            <button class=\"volume-control bg-transparent position-absolute\">
              <i class=\"fa-solid fa-volume-xmark volume-icon\"></i>
            </button>
            <button type='button' class='btn-close btn-close-white position-absolute modal-close-button' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class=\"row modal-body-text\" style=\"margin-left: 18px;\">
            <div class=\"col-8 text-wrap\">
              <div class=\"d-flex gap-2\">
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($drama_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($drama_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($drama_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($drama_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($drama_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($drama_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
";
          endwhile;
          ?>
        </div>
        <div class="next-button-drama position-absolute"
          style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
          <button class="btn border-1 next-chevron-btn-drama">
            <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
          </button>
        </div>

    </section>
    <?php echo $drama_modals; ?>
    <!-- END OF SECTION DRAMA -->


    <!-- START OF INTERSTELLAR -->
    <section class="movies-2">
      <div>
        <?php
        // Fetch the movie/series for HomeWallpaper4
        $wallpaper4_query = mysqli_query($con, "
      SELECT * FROM tbl_movie_series
      WHERE poster LIKE '%HomeWallpaper4%'
      LIMIT 1
    ");
        $wallpaper4 = mysqli_fetch_assoc($wallpaper4_query);
        if ($wallpaper4):
          ?>
          <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper4['poster']); ?>"
            class="d-block w-100 wallpaper-lotr" alt="<?php echo htmlspecialchars($wallpaper4['title']); ?> Wallpaper">

          <div
            class="wallpaper-description-lotr d-flex flex-column justify-content-end align-items-end position-relative me-5">

            <div class="d-flex justify-content-center">
              <img class="lotr-title-img position-relative mb-1"
                src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper4['modal_poster_title']); ?>"
                alt="<?php echo htmlspecialchars($wallpaper4['title']); ?> Title">
            </div>

            <div class="rating-text-lotr my-4 position-relative text-white">
              <p><?php echo htmlspecialchars($wallpaper4['description']); ?></p>
            </div>

            <div class="watch-now-lotr position-relative">
              <button class="btn btn-primary rounded-5 watch-now-small"
                onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper4['video']); ?>&type=movie_series&id=<?php echo $wallpaper4['movie_series_id']; ?>'">
                <i class="fa-solid fa-play mx-1"></i>
                <span>Watch Now</span>
              </button>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- END OF INTERSTELLAR -->
  </main>
  <!-- END OF THE MAIN CONTENT -->

  <footer>
    <div class="footer text-white d-flex justify-content-between mx-5 align-items-center">
      <p class="footer-long-text">This site does not store any files on it's server, It only links to the media which is
        hosted on 3rd party services like YouTube, Dailymotion, Ok.ru, Vidsrc and more.</p>
      <p>© 2025 CineVault. All rights reserved.</p>
    </div>
  </footer>

</body>

<script src="header-scroll.js"></script>
<script src="video-player.js"></script>
<script src="poster-slide.js"></script>

</html>