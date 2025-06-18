<?php
include 'CineVault-header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <!-- ========== BOOTSTRAP LINK ========== -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <link rel="stylesheet" href="feature-js.css">
  <link rel="stylesheet" href="FirstProject.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        WHERE ms.poster LIKE '%HomeCarousel%'
          AND ms.video LIKE '%HomeCarousel%'
          AND ms.modal_poster_title LIKE '%HomeCarousel%'
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
    WHERE ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
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
          ";
              // --- EPISODES/SEASONS block for Series (unchanged) ---
              if ($top10_category === 'Series') {
                $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$movie_series_id} ORDER BY season_number");
                $seasons = [];
                while ($season = mysqli_fetch_assoc($seasons_result)) {
                  $episodes_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_episodes WHERE season_id = {$season['season_id']} ORDER BY episode_id");
                  $episodes = [];
                  while ($ep = mysqli_fetch_assoc($episodes_result)) {
                    $episodes[] = $ep;
                  }
                  $season['episodes'] = $episodes;
                  $seasons[] = $season;
                }
                $modals .= '
      <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
          <div>
              <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
          </div>
          <div class="position-relative" style="min-width:180px; ">
              <select style="transform: translateX(-48px);" id="modal-season-select-' . $movie_series_id . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $modals .= '</select>
              <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
          </div>
      </div>';
                foreach ($seasons as $idx => $s) {
                  $modals .= '<div class="col-12 mb-4 modal-episodes-block"
            id="modal-episodes-' . $movie_series_id . '-season-' . $s['season_id'] . '"
            style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $modals .= '
                  <div class="mx-3 py-2 d-flex gap-2">
                      <div class="d-flex align-items-center gap-3 me-2 ms-4">
                          <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $modals .= '
                          <a href="play-vid.php?video=' . urlencode($ep['episode_video']) . '&type=episode&id=' . $ep['episode_id'] . '">
                              <video
                                  class="position-relative rounded-3 m-0 p-0 video-player video-episode"
                                  muted
                                  style="width: 120px; height: 70px; object-fit: cover;">
                                  <source src="../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/' . htmlspecialchars($ep['episode_video']) . '" type="video/mp4">
                                  Your browser does not support the video tag.
                              </video>
                          </a>';
                    }
                    $modals .= '
                      </div>
                      <div class="db-text-sec me-4">
                          <div class="d-flex align-items-center justify-content-between mb-2">
                              <p class="mb-0">' . htmlspecialchars($ep['episode_title']) . '</p>
                              <p class="mb-0">' . htmlspecialchars($ep['episode_duration']) . '</p>
                          </div>
                          <div>
                              <p class="mb-0 " style="font-size: 14px;">' . htmlspecialchars($ep['episode_description']) . '</p>
                          </div>
                      </div>
                  </div>';
                  }
                  if (empty($s['episodes'])) {
                    $modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $modals .= '</div>';
                }
                $modals .= '
      <script>
      document.addEventListener("DOMContentLoaded", function () {
          var select = document.getElementById("modal-season-select-' . $movie_series_id . '");
          if (select) {
              select.addEventListener("change", function () {
                  var val = this.value;';
                foreach ($seasons as $s) {
                  $modals .= '
                  document.getElementById("modal-episodes-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $modals .= '
              });
          }
      });
      </script>
      ';
              }
              $modals .= "
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


    <!-- START OF SECTION ROMANCE -->
    <section class="section-trending ms-md-5 ms-3">
      <?php
      $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Romance%' LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_id = $genre ? $genre['genre_id'] : 22; // fallback if not found
      $genre_name = $genre ? $genre['genre_name'] : "Romance";
      ?>
      <div class="trending-this-week">
        <p class="trending-text text-white fs-24 fw-bold">
          <?php echo htmlspecialchars($genre_name); ?>
        </p>
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

          $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Romance%' LIMIT 1");
          $genre = mysqli_fetch_assoc($genre_result);
          $genre_id = $genre ? $genre['genre_id'] : 22; // fallback if not found
          $genre_name = $genre ? $genre['genre_name'] : "Romance";
          $trend_query = mysqli_query(
            $con,
            "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
    FROM tbl_movie_series ms
    LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
    LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
    LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
    WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
      AND ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
    ORDER BY ms.date_released DESC
    LIMIT 7"
          );
          $trend_modals = "";
          $trend_rank = 1;
          while ($row = mysqli_fetch_assoc($trend_query)):
            $movie_series_id = $row['movie_series_id'];
            $trend_title = $row['title'];
            $trend_poster = $row['poster'];
            $trend_video = $row['video'];
            $trend_modal_poster_title = $row['modal_poster_title'];
            $trend_duration = $row['duration'];
            $trend_date_released = $row['date_released'];
            $trend_age_rating = $row['age_rating'];
            $trend_category = $row['category'];
            $trend_genre_1 = $row['genre_1'];
            $trend_genre_2 = $row['genre_2'];
            $trend_genre_3 = $row['genre_3'];
            $trend_cast = $row['cast'];
            $trend_description = $row['description'];

            $genres = htmlspecialchars($trend_genre_1);
            if (!empty($trend_genre_2))
              $genres .= ', ' . htmlspecialchars($trend_genre_2);
            if (!empty($trend_genre_3))
              $genres .= ', ' . htmlspecialchars($trend_genre_3);
            ?>
            <div class="trending-images position-relative pe-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-romance-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="trending-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($trend_poster); ?>"
                    alt="<?php echo htmlspecialchars($trend_title); ?>">
                  <i class="fa-solid fa-play play-button"></i>
                </div>
              </a>
            </div>
            <?php
            $trend_modals .= "
      <div class='modal fade' id='modal-romance-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-romance-{$movie_series_id}' aria-hidden='true'>
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
                    <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($trend_video) . "\" type=\"video/mp4\">
                    Your browser does not support the video tag.
                  </video>";
            if (!empty($trend_modal_poster_title)) {
              $trend_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($trend_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $trend_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($trend_title) . "</p>";
            }
            $trend_modals .= "
                  <a href=\"play-vid.php?video=" . urlencode($trend_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($trend_date_released) . "</p>
                      <p class='modal-text-rating mb-0'>" . htmlspecialchars($trend_duration) . "</p>
                    </div>
                    <div class=\"d-flex gap-2 align-items-center\">
                      <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($trend_age_rating) . "+</p>
                      <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($trend_category) . "</p>
                    </div>
                    <p class='modal-text-rating'>" . htmlspecialchars($trend_description) . "</p>
                  </div>
                  <div class=\"col-4 ps-0 pe-4 text-wrap\">
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                      " . htmlspecialchars($trend_cast) . ", more...
                    </p>
                    <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                      <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                      {$genres}
                    </p>
                  </div>
                </div>
              ";
            // --- Series/episodes code (unchanged) ---
            if ($trend_category === 'Series') {
              $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$movie_series_id} ORDER BY season_number");
              $seasons = [];
              while ($season = mysqli_fetch_assoc($seasons_result)) {
                $episodes_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_episodes WHERE season_id = {$season['season_id']} ORDER BY episode_id");
                $episodes = [];
                while ($ep = mysqli_fetch_assoc($episodes_result)) {
                  $episodes[] = $ep;
                }
                $season['episodes'] = $episodes;
                $seasons[] = $season;
              }
              $trend_modals .= '
            <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
                <div>
                    <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
                </div>
                <div class="position-relative" style="min-width:180px; ">
                    <select style="transform: translateX(-48px);" id="modal-season-select-romance-' . $movie_series_id . '" class="form-control me-5">';
              foreach ($seasons as $s) {
                $trend_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
              }
              $trend_modals .= '</select>
                    <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
                </div>
            </div>';
              foreach ($seasons as $idx => $s) {
                $trend_modals .= '<div class="col-12 mb-4 modal-episodes-block"
                id="modal-episodes-romance-' . $movie_series_id . '-season-' . $s['season_id'] . '"
                style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                foreach ($s['episodes'] as $ep_idx => $ep) {
                  $trend_modals .= '
                        <div class="mx-3 py-2 d-flex gap-2">
                            <div class="d-flex align-items-center gap-3 me-2 ms-4">
                                <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                  if (!empty($ep['episode_video'])) {
                    $trend_modals .= '
                                <a href="play-vid.php?video=' . urlencode($ep['episode_video']) . '&type=episode&id=' . $ep['episode_id'] . '">
                                    <video
                                        class="position-relative rounded-3 m-0 p-0 video-player video-episode"
                                        muted
                                        style="width: 120px; height: 70px; object-fit: cover;">
                                        <source src="../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/' . htmlspecialchars($ep['episode_video']) . '" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </a>';
                  }
                  $trend_modals .= '
                            </div>
                            <div class="db-text-sec me-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <p class="mb-0">' . htmlspecialchars($ep['episode_title']) . '</p>
                                    <p class="mb-0">' . htmlspecialchars($ep['episode_duration']) . '</p>
                                </div>
                                <div>
                                    <p class="mb-0 " style="font-size: 14px;">' . htmlspecialchars($ep['episode_description']) . '</p>
                                </div>
                            </div>
                        </div>';
                }
                if (empty($s['episodes'])) {
                  $trend_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                }
                $trend_modals .= '</div>';
              }
              $trend_modals .= '
            <script>
            document.addEventListener("DOMContentLoaded", function () {
                var select = document.getElementById("modal-season-select-romance-' . $movie_series_id . '");
                if (select) {
                    select.addEventListener("change", function () {
                        var val = this.value;';
              foreach ($seasons as $s) {
                $trend_modals .= '
                        document.getElementById("modal-episodes-romance-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
              }
              $trend_modals .= '
                    });
                }
            });
            </script>
            ';
            }
            $trend_modals .= "
              </div>
            </div>
          </div>
        </div>
      </div>
      ";
            $trend_rank++;
          endwhile;
          ?>
        </div>
        <div class="next-button-trending position-absolute"
          style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
          <button class="btn border-1 next-chevron-btn-trending">
            <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
          </button>
        </div>
      </div>
    </section>
    <?php echo $trend_modals; ?>
    <!-- END OF SECTION ROMANCE -->

    <!-- START OF SECTION COMEDY -->
    <section class="section-action-movies ms-md-5 ms-3">
      <?php
      $comedy_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Comedy%' LIMIT 1");
      $comedy = mysqli_fetch_assoc($comedy_result);
      $comedy_id = $comedy ? $comedy['genre_id'] : 4; // fallback to Comedy's genre_id
      $comedy_name = $comedy ? $comedy['genre_name'] : "Comedy";
      ?>
      <div class="action-movies-top d-flex justify-content-between">
        <p class="action-movies-text text-white fs-24 fw-bold">
          <?php echo htmlspecialchars($comedy_name); ?>
        </p>
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

          $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Comedy%' LIMIT 1");
          $genre = mysqli_fetch_assoc($genre_result);
          $genre_id = $genre ? $genre['genre_id'] : 4; // fallback if not found
          $genre_name = $genre ? $genre['genre_name'] : "Comedy";

          $comedy_query = mysqli_query(
            $con,
            "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
    FROM tbl_movie_series ms
    LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
    LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
    LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
    WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
      AND ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
    ORDER BY ms.date_released DESC
    LIMIT 7"
          );
          $comedy_modals = "";
          while ($row = mysqli_fetch_assoc($comedy_query)):
            $movie_series_id = $row['movie_series_id'];
            $comedy_title = $row['title'];
            $comedy_poster = $row['poster'];
            $comedy_video = $row['video'];
            $comedy_modal_poster_title = $row['modal_poster_title'];
            $comedy_duration = $row['duration'];
            $comedy_date_released = $row['date_released'];
            $comedy_age_rating = $row['age_rating'];
            $comedy_category = $row['category'];
            $comedy_genre_1 = $row['genre_1'];
            $comedy_genre_2 = $row['genre_2'];
            $comedy_genre_3 = $row['genre_3'];
            $comedy_cast = $row['cast'];
            $comedy_description = $row['description'];

            $genres = htmlspecialchars($comedy_genre_1);
            if (!empty($comedy_genre_2))
              $genres .= ', ' . htmlspecialchars($comedy_genre_2);
            if (!empty($comedy_genre_3))
              $genres .= ', ' . htmlspecialchars($comedy_genre_3);
            ?>
            <div class="comedy-images position-relative pe-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-comedy-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="action-movies-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($comedy_poster); ?>"
                    alt="<?php echo htmlspecialchars($comedy_title); ?>">
                  <i class="fa-solid fa-play play-button-comedy"></i>
                </div>
              </a>
            </div>
            <?php
            $comedy_modals .= "
  <div class='modal fade' id='modal-comedy-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-comedy-{$movie_series_id}' aria-hidden='true'>
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
                <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($comedy_video) . "\" type=\"video/mp4\">
                Your browser does not support the video tag.
              </video>";
            if (!empty($comedy_modal_poster_title)) {
              $comedy_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($comedy_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $comedy_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($comedy_title) . "</p>";
            }
            $comedy_modals .= "
              <a href=\"play-vid.php?video=" . urlencode($comedy_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                  <p class='modal-text-rating mb-0'>" . htmlspecialchars($comedy_date_released) . "</p>
                  <p class='modal-text-rating mb-0'>" . htmlspecialchars($comedy_duration) . "</p>
                </div>
                <div class=\"d-flex gap-2 align-items-center\">
                  <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($comedy_age_rating) . "+</p>
                  <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($comedy_category) . "</p>
                </div>
                <p class='modal-text-rating'>" . htmlspecialchars($comedy_description) . "</p>
              </div>
              <div class=\"col-4 ps-0 pe-4 text-wrap\">
                <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                  <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                  " . htmlspecialchars($comedy_cast) . ", more...
                </p>
                <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                  <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                  {$genres}
                </p>
              </div>
            </div>
          ";
            // --- Series/episodes code (unchanged) ---
            if ($comedy_category === 'Series') {
              $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$movie_series_id} ORDER BY season_number");
              $seasons = [];
              while ($season = mysqli_fetch_assoc($seasons_result)) {
                $episodes_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_episodes WHERE season_id = {$season['season_id']} ORDER BY episode_id");
                $episodes = [];
                while ($ep = mysqli_fetch_assoc($episodes_result)) {
                  $episodes[] = $ep;
                }
                $season['episodes'] = $episodes;
                $seasons[] = $season;
              }
              $comedy_modals .= '
        <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
            <div>
                <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
            </div>
            <div class="position-relative" style="min-width:180px; ">
                <select style="transform: translateX(-48px);" id="modal-season-select-comedy-' . $movie_series_id . '" class="form-control me-5">';
              foreach ($seasons as $s) {
                $comedy_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
              }
              $comedy_modals .= '</select>
                <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
            </div>
        </div>';
              foreach ($seasons as $idx => $s) {
                $comedy_modals .= '<div class="col-12 mb-4 modal-episodes-block"
            id="modal-episodes-comedy-' . $movie_series_id . '-season-' . $s['season_id'] . '"
            style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                foreach ($s['episodes'] as $ep_idx => $ep) {
                  $comedy_modals .= '
                    <div class="mx-3 py-2 d-flex gap-2">
                        <div class="d-flex align-items-center gap-3 me-2 ms-4">
                            <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                  if (!empty($ep['episode_video'])) {
                    $comedy_modals .= '
                            <a href="play-vid.php?video=' . urlencode($ep['episode_video']) . '&type=episode&id=' . $ep['episode_id'] . '">
                                <video
                                    class="position-relative rounded-3 m-0 p-0 video-player video-episode"
                                    muted
                                    style="width: 120px; height: 70px; object-fit: cover;">
                                    <source src="../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/' . htmlspecialchars($ep['episode_video']) . '" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </a>';
                  }
                  $comedy_modals .= '
                        </div>
                        <div class="db-text-sec me-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="mb-0">' . htmlspecialchars($ep['episode_title']) . '</p>
                                <p class="mb-0">' . htmlspecialchars($ep['episode_duration']) . '</p>
                            </div>
                            <div>
                                <p class="mb-0 " style="font-size: 14px;">' . htmlspecialchars($ep['episode_description']) . '</p>
                            </div>
                        </div>
                    </div>';
                }
                if (empty($s['episodes'])) {
                  $comedy_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                }
                $comedy_modals .= '</div>';
              }
              $comedy_modals .= '
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            var select = document.getElementById("modal-season-select-comedy-' . $movie_series_id . '");
            if (select) {
                select.addEventListener("change", function () {
                    var val = this.value;';
              foreach ($seasons as $s) {
                $comedy_modals .= '
                    document.getElementById("modal-episodes-comedy-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
              }
              $comedy_modals .= '
                });
            }
        });
        </script>
        ';
            }
            $comedy_modals .= "
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
    <?php echo $comedy_modals; ?>
    <!-- END OF SECTION COMEDY -->

    <!-- START OF SECTION ACTION -->
    <section class="section-featured text-white ms-md-5 ms-3">
      <div class="featured-container">
        <div class="featured-top">
          <?php
          $action_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Action%' LIMIT 1");
          $action = mysqli_fetch_assoc($action_result);
          $action_id = $action ? $action['genre_id'] : 1; // fallback to genre_id 1 if not found
          $action_name = $action ? $action['genre_name'] : "ACTION";
          ?>
          <p class="featured-text text-uppercase"><?php echo htmlspecialchars($action_name); ?></p>
          <p class="featured-movies-text fs-2">MOVIES/SERIES</p>
          <div class="top10-featured-wrapper position-relative">
            <div class="prev-button-featured position-absolute"
              style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
              <button class="btn border-1 prev-chevron-btn-featured">
                <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
              </button>
            </div>

            <div class="featured-images-container d-flex gap-3 position-relative">
              <?php
              $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_name LIKE '%Action%' LIMIT 1");
              $genre = mysqli_fetch_assoc($genre_result);
              $genre_id = $genre ? $genre['genre_id'] : 1; // fallback if not found
              $genre_name = $genre ? $genre['genre_name'] : "Action";


              $action_query = mysqli_query(
                $con,
                "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
        AND ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY ms.date_released DESC
      LIMIT 8"
              );
              $action_modals = "";
              while ($row = mysqli_fetch_assoc($action_query)):
                $movie_series_id = $row['movie_series_id'];
                $action_title = $row['title'];
                $action_poster = $row['poster'];
                $action_video = $row['video'];
                $action_modal_poster_title = $row['modal_poster_title'];
                $action_duration = $row['duration'];
                $action_date_released = $row['date_released'];
                $action_age_rating = $row['age_rating'];
                $action_category = $row['category'];
                $action_genre_1 = $row['genre_1'];
                $action_genre_2 = $row['genre_2'];
                $action_genre_3 = $row['genre_3'];
                $action_cast = $row['cast'];
                $action_description = $row['description'];

                $genres = htmlspecialchars($action_genre_1);
                if (!empty($action_genre_2))
                  $genres .= ', ' . htmlspecialchars($action_genre_2);
                if (!empty($action_genre_3))
                  $genres .= ', ' . htmlspecialchars($action_genre_3);
                ?>
                <div class="featured-images position-relative pe-2" style="overflow: visible;">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#modal-action-<?php echo $movie_series_id ?>">
                    <div class="trending-hover position-relative">
                      <img class="featured-img rounded-3"
                        src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($action_poster); ?>"
                        alt="<?php echo htmlspecialchars($action_title); ?>">
                      <i class="fa-solid fa-play play-button-featured"></i>
                    </div>
                  </a>
                </div>
                <?php
                $action_modals .= "
    <div class='modal fade' id='modal-action-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-action-{$movie_series_id}' aria-hidden='true'>
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
                  <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($action_video) . "\" type=\"video/mp4\">
                  Your browser does not support the video tag.
                </video>";
                if (!empty($action_modal_poster_title)) {
                  $action_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($action_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
                } else {
                  $action_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($action_title) . "</p>";
                }
                $action_modals .= "
                <a href=\"play-vid.php?video=" . urlencode($action_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                    <p class='modal-text-rating mb-0'>" . htmlspecialchars($action_date_released) . "</p>
                    <p class='modal-text-rating mb-0'>" . htmlspecialchars($action_duration) . "</p>
                  </div>
                  <div class=\"d-flex gap-2 align-items-center\">
                    <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($action_age_rating) . "+</p>
                    <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($action_category) . "</p>
                  </div>
                  <p class='modal-text-rating'>" . htmlspecialchars($action_description) . "</p>
                </div>
                <div class=\"col-4 ps-0 pe-4 text-wrap\">
                  <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                    <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                    " . htmlspecialchars($action_cast) . ", more...
                  </p>
                  <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                    <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                    {$genres}
                  </p>
                </div>
              </div>
            ";
                // --- Series/episodes code (unchanged) ---
                if ($action_category === 'Series') {
                  $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$movie_series_id} ORDER BY season_number");
                  $seasons = [];
                  while ($season = mysqli_fetch_assoc($seasons_result)) {
                    $episodes_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_episodes WHERE season_id = {$season['season_id']} ORDER BY episode_id");
                    $episodes = [];
                    while ($ep = mysqli_fetch_assoc($episodes_result)) {
                      $episodes[] = $ep;
                    }
                    $season['episodes'] = $episodes;
                    $seasons[] = $season;
                  }
                  $action_modals .= '
          <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
              <div>
                  <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
              </div>
              <div class="position-relative" style="min-width:180px; ">
                  <select style="transform: translateX(-48px);" id="modal-season-select-action-' . $movie_series_id . '" class="form-control me-5">';
                  foreach ($seasons as $s) {
                    $action_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                  }
                  $action_modals .= '</select>
                  <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
              </div>
          </div>';
                  foreach ($seasons as $idx => $s) {
                    $action_modals .= '<div class="col-12 mb-4 modal-episodes-block"
              id="modal-episodes-action-' . $movie_series_id . '-season-' . $s['season_id'] . '"
              style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                    foreach ($s['episodes'] as $ep_idx => $ep) {
                      $action_modals .= '
                      <div class="mx-3 py-2 d-flex gap-2">
                          <div class="d-flex align-items-center gap-3 me-2 ms-4">
                              <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                      if (!empty($ep['episode_video'])) {
                        $action_modals .= '
                              <a href="play-vid.php?video=' . urlencode($ep['episode_video']) . '&type=episode&id=' . $ep['episode_id'] . '">
                                  <video
                                      class="position-relative rounded-3 m-0 p-0 video-player video-episode"
                                      muted
                                      style="width: 120px; height: 70px; object-fit: cover;">
                                      <source src="../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/' . htmlspecialchars($ep['episode_video']) . '" type="video/mp4">
                                      Your browser does not support the video tag.
                                  </video>
                              </a>';
                      }
                      $action_modals .= '
                          </div>
                          <div class="db-text-sec me-4">
                              <div class="d-flex align-items-center justify-content-between mb-2">
                                  <p class="mb-0">' . htmlspecialchars($ep['episode_title']) . '</p>
                                  <p class="mb-0">' . htmlspecialchars($ep['episode_duration']) . '</p>
                              </div>
                              <div>
                                  <p class="mb-0 " style="font-size: 14px;">' . htmlspecialchars($ep['episode_description']) . '</p>
                              </div>
                          </div>
                      </div>';
                    }
                    if (empty($s['episodes'])) {
                      $action_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                    }
                    $action_modals .= '</div>';
                  }
                  $action_modals .= '
          <script>
          document.addEventListener("DOMContentLoaded", function () {
              var select = document.getElementById("modal-season-select-action-' . $movie_series_id . '");
              if (select) {
                  select.addEventListener("change", function () {
                      var val = this.value;';
                  foreach ($seasons as $s) {
                    $action_modals .= '
                      document.getElementById("modal-episodes-action-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                  }
                  $action_modals .= '
                  });
              }
          });
          </script>
          ';
                }
                $action_modals .= "
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
    <?php echo $action_modals; ?>
    <!-- END OF SECTION ACTION -->


    <!-- START OF SECTION WALLPAPER 1 -->
    <section class="movies-1 mt-80">
      <div>
        <?php
        // Fetch the movie/series for HomeWallpaper1
        $wallpaper1_query = mysqli_query($con, "
      SELECT * FROM tbl_movie_series
      WHERE poster LIKE '%HomeWallpaper1%'
      LIMIT 1
    ");
        $wallpaper1 = mysqli_fetch_assoc($wallpaper1_query);
        if ($wallpaper1):
          ?>
          <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper1['poster']); ?>"
            class="d-block w-100 wallpaper-lotr bg-danger"
            alt="<?php echo htmlspecialchars($wallpaper1['title']); ?> Wallpaper">

          <div
            class="wallpaper-description-tem d-flex flex-column justify-content-end align-items-end position-relative me-5">

            <div class="d-flex justify-content-center">
              <img class="tem-title-img position-relative mb-1"
                src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper1['modal_poster_title']); ?>"
                alt="<?php echo htmlspecialchars($wallpaper1['title']); ?> Title">
            </div>

            <div class="rating-text-tem my-4 position-relative text-white">
              <p><?php echo htmlspecialchars($wallpaper1['description']); ?></p>
            </div>

            <div class="watch-now-tem position-relative">
              <button class="btn btn-primary rounded-5 watch-now-small"
                onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper1['video']); ?>&type=movie_series&id=<?php echo $wallpaper1['movie_series_id']; ?>'">
                <i class="fa-solid fa-play mx-1"></i>
                <span>Watch Now</span>
              </button>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- END OF SECTION WALLPAPER 1 -->

    <!-- START OF SECTION RECOMMENDED -->
    <?php
    $recommended_items = [];
    $show_based_on_genre = false;
    $genre_for_recommend = '';

    if (isset($_COOKIE['recommended_genre']) && $_COOKIE['recommended_genre'] !== '') {
      $genre_for_recommend = mysqli_real_escape_string($con, $_COOKIE['recommended_genre']);
      $show_based_on_genre = true;
      $recommend_query = mysqli_query(
        $con,
        "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (g1.genre_name = '$genre_for_recommend'
          OR g2.genre_name = '$genre_for_recommend'
          OR g3.genre_name = '$genre_for_recommend')
        AND ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY RAND() LIMIT 8"
      );

    } else {
      $recommend_query = mysqli_query(
        $con,
        "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE ms.poster NOT LIKE '%HomeCarousel%'
        AND ms.poster NOT LIKE '%MovieCarousel%'
        AND ms.poster NOT LIKE '%SeriesCarousel%'
        AND ms.poster NOT LIKE '%HomeWallpaper%'
      ORDER BY RAND() LIMIT 8"
      );
    }
    while ($row = mysqli_fetch_assoc($recommend_query)) {
      $recommended_items[] = $row;
    }
    $recommended_modals = "";
    ?>
    <section class="section-popular text-white ms-md-5 ms-3">
      <div class="popular-container mt-4">
        <div class="popular-top mb-4">
          <p class="action-movies-text text-white fs-24 fw-bold">
            Recommended for
            You<?php if ($show_based_on_genre)
              echo " <span class='fs-6 ms-1 text-primary'>(Based on $genre_for_recommend)</span>"; ?>
          </p>
        </div>
        <div class="position-relative" style="width: 100%;">
          <div class="popular-images-container d-flex gap-3 position-relative">
            <?php foreach ($recommended_items as $item): ?>
              <div class="popular-images position-relative pe-2" style="overflow: visible;">
                <a href="#" class="recommended-link" data-bs-toggle="modal"
                  data-bs-target="#modal-recommended-<?php echo $item['movie_series_id']; ?>"
                  data-genre1="<?php echo htmlspecialchars($item['genre_1']); ?>"
                  data-genre2="<?php echo htmlspecialchars($item['genre_2']); ?>"
                  data-genre3="<?php echo htmlspecialchars($item['genre_3']); ?>">
                  <img class="popular-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($item['poster']); ?>"
                    alt="<?php echo htmlspecialchars($item['title']); ?>">
                  <i class="fa-solid fa-play play-button-popular"></i>
                </a>
              </div>
              <?php
              $genres = htmlspecialchars($item['genre_1']);
              if (!empty($item['genre_2']))
                $genres .= ', ' . htmlspecialchars($item['genre_2']);
              if (!empty($item['genre_3']))
                $genres .= ', ' . htmlspecialchars($item['genre_3']);

              $recommended_modals .= "
    <div class='modal fade' id='modal-recommended-{$item['movie_series_id']}' tabindex='-1' aria-labelledby='exampleModalLabel-recommended-{$item['movie_series_id']}' aria-hidden='true'>
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
                  <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($item['video']) . "\" type=\"video/mp4\">
                  Your browser does not support the video tag.
                </video>";
              if (!empty($item['modal_poster_title'])) {
                $recommended_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($item['modal_poster_title']) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
              } else {
                $recommended_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($item['title']) . "</p>";
              }
              $recommended_modals .= "
                <a href=\"play-vid.php?video=" . urlencode($item['video']) . "&type=movie_series&id=" . $item['movie_series_id'] . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                    <p class='modal-text-rating mb-0'>" . htmlspecialchars($item['date_released']) . "</p>
                    <p class='modal-text-rating mb-0'>" . htmlspecialchars($item['duration']) . "</p>
                  </div>
                  <div class=\"d-flex gap-2 align-items-center\">
                    <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($item['age_rating']) . "+</p>
                    <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($item['category']) . "</p>
                  </div>
                  <p class='modal-text-rating'>" . htmlspecialchars($item['description']) . "</p>
                </div>
                <div class=\"col-4 ps-0 pe-4 text-wrap\">
                  <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                    <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                    " . htmlspecialchars($item['cast']) . ", more...
                  </p>
                  <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                    <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                    {$genres}
                  </p>
                </div>
              </div>
        ";
              // --- Series/episodes code (unchanged) ---
              if ($item['category'] === 'Series') {
                $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$item['movie_series_id']} ORDER BY season_number");
                $seasons = [];
                while ($season = mysqli_fetch_assoc($seasons_result)) {
                  $episodes_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_episodes WHERE season_id = {$season['season_id']} ORDER BY episode_id");
                  $episodes = [];
                  while ($ep = mysqli_fetch_assoc($episodes_result)) {
                    $episodes[] = $ep;
                  }
                  $season['episodes'] = $episodes;
                  $seasons[] = $season;
                }
                $recommended_modals .= '
            <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
                <div>
                    <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
                </div>
                <div class="position-relative" style="min-width:180px; ">
                    <select style="transform: translateX(-48px);" id="modal-season-select-recommended-' . $item['movie_series_id'] . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $recommended_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $recommended_modals .= '</select>
                    <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
                </div>
            </div>';
                foreach ($seasons as $idx => $s) {
                  $recommended_modals .= '<div class="col-12 mb-4 modal-episodes-block"
                id="modal-episodes-recommended-' . $item['movie_series_id'] . '-season-' . $s['season_id'] . '"
                style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $recommended_modals .= '
                        <div class="mx-3 py-2 d-flex gap-2">
                            <div class="d-flex align-items-center gap-3 me-2 ms-4">
                                <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $recommended_modals .= '
                                <a href="play-vid.php?video=' . urlencode($ep['episode_video']) . '&type=episode&id=' . $ep['episode_id'] . '">
                                    <video
                                        class="position-relative rounded-3 m-0 p-0 video-player video-episode"
                                        muted
                                        style="width: 120px; height: 70px; object-fit: cover;">
                                        <source src="../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/' . htmlspecialchars($ep['episode_video']) . '" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </a>';
                    }
                    $recommended_modals .= '
                            </div>
                            <div class="db-text-sec me-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <p class="mb-0">' . htmlspecialchars($ep['episode_title']) . '</p>
                                    <p class="mb-0">' . htmlspecialchars($ep['episode_duration']) . '</p>
                                </div>
                                <div>
                                    <p class="mb-0 " style="font-size: 14px;">' . htmlspecialchars($ep['episode_description']) . '</p>
                                </div>
                            </div>
                        </div>';
                  }
                  if (empty($s['episodes'])) {
                    $recommended_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $recommended_modals .= '</div>';
                }
                $recommended_modals .= '
            <script>
            document.addEventListener("DOMContentLoaded", function () {
                var select = document.getElementById("modal-season-select-recommended-' . $item['movie_series_id'] . '");
                if (select) {
                    select.addEventListener("change", function () {
                        var val = this.value;';
                foreach ($seasons as $s) {
                  $recommended_modals .= '
                        document.getElementById("modal-episodes-recommended-' . $item['movie_series_id'] . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $recommended_modals .= '
                    });
                }
            });
            </script>
            ';
              }
              $recommended_modals .= "
              </div>
            </div>
          </div>
        </div>
      </div>
    ";
              ?>
            <?php endforeach; ?>
          </div>
          <div class="next-button-recommended position-absolute"
            style="right: 0; top: 50%; transform: translateY(-50%);z-index: 400;">
            <button class="btn border-1 next-chevron-btn-recommended">
              <i class="fas fa-chevron-right fa-2xl text-white-50"></i>
            </button>
          </div>
          <div class="prev-button-recommended position-absolute"
            style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
            <button class="btn border-1 prev-chevron-btn-recommended">
              <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
            </button>
          </div>
        </div>
      </div>
    </section>
    <?php echo $recommended_modals; ?>
    <!-- END OF SECTION RECOMMENDED -->

    <!-- START OF MOVIES 2 -->
    <section class="movies-2 mt-80">
      <div>
        <?php
        // Fetch the movie/series for HomeWallpaper2
        $wallpaper2_query = mysqli_query($con, "
      SELECT * FROM tbl_movie_series
      WHERE poster LIKE '%HomeWallpaper2%'
      LIMIT 1
    ");
        $wallpaper2 = mysqli_fetch_assoc($wallpaper2_query);
        if ($wallpaper2):
          ?>
          <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper2['poster']); ?>"
            class="d-block w-100 wallpaper-lotr" alt="<?php echo htmlspecialchars($wallpaper2['title']); ?> Wallpaper">

          <div
            class="wallpaper-description-lotr d-flex flex-column justify-content-end align-items-end position-relative me-5">

            <div class="d-flex justify-content-center">
              <img class="lotr-title-img position-relative mb-1"
                src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper2['modal_poster_title']); ?>"
                alt="<?php echo htmlspecialchars($wallpaper2['title']); ?> Title">
            </div>

            <div class="rating-text-lotr my-4 position-relative text-white">
              <p><?php echo htmlspecialchars($wallpaper2['description']); ?></p>
            </div>

            <div class="watch-now-lotr position-relative">
              <button class="btn btn-primary rounded-5 watch-now-small"
                onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper2['video']); ?>&type=movie_series&id=<?php echo $wallpaper2['movie_series_id']; ?>'">
                <i class="fa-solid fa-play mx-1"></i>
                <span>Watch Now</span>
              </button>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- END OF MOVIES 2 -->
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


<script src="video-player.js"></script>
<script src="poster-slide.js"></script>

</html>