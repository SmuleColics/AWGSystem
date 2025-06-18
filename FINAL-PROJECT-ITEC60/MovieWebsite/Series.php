<?php
include 'CineVault-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Series</title>
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
        WHERE ms.category = 'Series'
          AND ms.poster LIKE '%SeriesCarousel%'
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
            // --- Modal with seasons and episodes ---
            // Fetch seasons and episodes for this series
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
      ";

            // --- SEASONS AND EPISODES DROPDOWN ---
            if (!empty($seasons)) {
              $carousel_modals .= '
        <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
            <div>
                <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
            </div>
            <div class="position-relative" style="min-width:180px; ">
                <select style="transform: translateX(-48px);" id="modal-season-select-carousel-' . $movie_series_id . '" class="form-control me-5">';
              foreach ($seasons as $s) {
                $carousel_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
              }
              $carousel_modals .= '</select>
                <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
            </div>
        </div>';
              foreach ($seasons as $idx => $s) {
                $carousel_modals .= '<div class="col-12 mb-4 modal-episodes-block"
            id="modal-episodes-carousel-' . $movie_series_id . '-season-' . $s['season_id'] . '"
            style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                foreach ($s['episodes'] as $ep_idx => $ep) {
                  $carousel_modals .= '
                <div class="mx-3 py-2 d-flex gap-2">
                    <div class="d-flex align-items-center gap-3 me-2 ms-4">
                        <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                  if (!empty($ep['episode_video'])) {
                    $carousel_modals .= '
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
                  $carousel_modals .= '
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
                  $carousel_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                }
                $carousel_modals .= '</div>';
              }
              $carousel_modals .= '
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            var select = document.getElementById("modal-season-select-carousel-' . $movie_series_id . '");
            if (select) {
                select.addEventListener("change", function () {
                    var val = this.value;';
              foreach ($seasons as $s) {
                $carousel_modals .= '
                    document.getElementById("modal-episodes-carousel-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
              }
              $carousel_modals .= '
                });
            }
        });
        </script>
        ';
            }
            $carousel_modals .= "
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

    <!-- ============== START OF SECTION TOP 10 SERIES ==============  -->
    <?php
    $select_series = mysqli_query($con, "
  SELECT 
    ms.*,
    g1.genre_name AS genre_1,
    g2.genre_name AS genre_2,
    g3.genre_name AS genre_3
  FROM tbl_movie_series ms
  LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
  LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
  LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
  WHERE ms.category = 'Series'
    AND ms.poster NOT LIKE '%HomeCarousel%'
    AND ms.poster NOT LIKE '%MovieCarousel%'
    AND ms.poster NOT LIKE '%SeriesCarousel%'
    AND ms.poster NOT LIKE '%HomeWallpaper%'
  ORDER BY ms.views DESC LIMIT 10
");
    $top10_series_rank = 1;
    $series_modals = "";
    ?>
    <section id="section-top-10-series" class="section-top-10 text-white ms-5">
      <div class="top-10-container">
        <div class="top-10-top d-flex align-items-center">
          <p class="top-10-top-text">TOP 10</p>
          <div class="ms-3 fs-2 movies-today-text">
            <p class="movies-text">SERIES</p>
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
            <?php while ($row = mysqli_fetch_assoc($select_series)):
              $series_id = $row['movie_series_id'];
              $series_title = $row['title'];
              $series_duration = $row['duration'];
              $series_poster = $row['poster'];
              $series_video = $row['video'];
              $series_modal_poster_title = $row['modal_poster_title'];
              $series_date_released = $row['date_released'];
              $series_age_rating = $row['age_rating'];
              $series_category = $row['category'];
              $series_genre_1 = $row['genre_1'];
              $series_genre_2 = $row['genre_2'];
              $series_genre_3 = $row['genre_3'];
              $series_cast = $row['cast'];
              $series_description = $row['description'];
              ?>
              <div class="top-10-images position-relative">
                <p class="top-10-text"><?php echo $top10_series_rank; ?></p>
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-top10-series-<?php echo $series_id ?>">
                  <div class="top-10-hover position-relative">
                    <img class="top-10-img rounded-3"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($series_poster); ?>"
                      alt="<?php echo htmlspecialchars($series_title); ?>">
                    <i class="fa-solid fa-play play-button-top-10"></i>
                  </div>
                </a>
              </div>
              <?php
              $series_genres = htmlspecialchars($series_genre_1);
              if (!empty($series_genre_2))
                $series_genres .= ', ' . htmlspecialchars($series_genre_2);
              if (!empty($series_genre_3))
                $series_genres .= ', ' . htmlspecialchars($series_genre_3);

              // Modal for each series, INCLUDING EPISODES/SEASONS
              $series_modals .= "
<div class='modal fade' id='modal-top10-series-{$series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-series-{$series_id}' aria-hidden='true'>
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
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($series_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
              if (!empty($series_modal_poster_title)) {
                $series_modals .= "<img class='poster-title-img top10-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($series_modal_poster_title) . "' alt=''>";
              } else {
                $series_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($series_title) . "</p>";
              }
              $series_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($series_video) . "&type=movie_series&id=" . $series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($series_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($series_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($series_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($series_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($series_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($series_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$series_genres}
              </p>
            </div>
          </div>
          ";

              // EPISODES/SEASONS block for Series
              $seasons_result = mysqli_query($con, "SELECT * FROM tbl_movie_series_seasons WHERE movie_series_id = {$series_id} ORDER BY season_number");
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
              if (!empty($seasons)) {
                $series_modals .= '
          <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
              <div>
                  <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
              </div>
              <div class="position-relative" style="min-width:180px; ">
                  <select style="transform: translateX(-48px);" id="modal-season-select-' . $series_id . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $series_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $series_modals .= '</select>
                  <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
              </div>
          </div>';
                foreach ($seasons as $idx => $s) {
                  $series_modals .= '<div class="col-12 mb-4 modal-episodes-block"
                id="modal-episodes-' . $series_id . '-season-' . $s['season_id'] . '"
                style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $series_modals .= '
                    <div class="mx-3 py-2 d-flex gap-2">
                        <div class="d-flex align-items-center gap-3 me-2 ms-4">
                            <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $series_modals .= '
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
                    $series_modals .= '
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
                    $series_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $series_modals .= '</div>';
                }
                $series_modals .= '
          <script>
          document.addEventListener("DOMContentLoaded", function () {
              var select = document.getElementById("modal-season-select-' . $series_id . '");
              if (select) {
                  select.addEventListener("change", function () {
                      var val = this.value;';
                foreach ($seasons as $s) {
                  $series_modals .= '
                      document.getElementById("modal-episodes-' . $series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $series_modals .= '
                  });
              }
          });
          </script>
          ';
              }
              $series_modals .= "
        </div>
      </div>
    </div>
  </div>
</div>
";
              $top10_series_rank++;
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
    <?php echo $series_modals; ?>
    <!-- ============== START OF SECTION TOP 10 SERIES ==============  -->

    <!-- ============== START OF SECTION SCI-FI & FANTASY ==============  -->
    <<?php
    $genre_result = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre WHERE genre_id = 23 LIMIT 1");
    $genre = mysqli_fetch_assoc($genre_result);
    $genre_id = $genre ? $genre['genre_id'] : 23;
    $genre_name = $genre ? $genre['genre_name'] : "Sci-Fi & Fantasy";
    ?>

      <!-- START OF SECTION SCI-FI & FANTASY SERIES -->
      <section id="section-popular-scifi-fantasy-series" class="section-trending ms-md-5 ms-3">

        <div id="dropdown-popular-scifi-fantasy" class="trending-this-week d-flex justify-content-between">
          <p class="trending-text text-white fs-24 fw-bold"><?php echo htmlspecialchars($genre_name); ?></p>
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
            // Query for Sci-Fi & Fantasy Series
            $scifi_query = mysqli_query(
              $con,
              "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
      FROM tbl_movie_series ms
      LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
      LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
      LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
      WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
        AND ms.category = 'Series'
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
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-scifi-<?php echo $movie_series_id ?>">
                  <div class="trending-hover position-relative">
                    <img class="trending-img rounded-3"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($poster); ?>"
                      alt="<?php echo htmlspecialchars($title); ?>">
                    <i class="fa-solid fa-play play-button"></i>
                  </div>
                </a>
              </div>
              <?php
              // --- Modal with seasons and episodes ---
              // Fetch seasons and episodes for this series
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
                    <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($video) . "\" type=\"video/mp4\">
                    Your browser does not support the video tag.
                  </video>";
              if (!empty($modal_poster_title)) {
                $scifi_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
              } else {
                $scifi_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($title) . "</p>";
              }
              $scifi_modals .= "
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
      ";

              // --- SEASONS AND EPISODES DROPDOWN ---
              if (!empty($seasons)) {
                $scifi_modals .= '
        <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
            <div>
                <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
            </div>
            <div class="position-relative" style="min-width:180px; ">
                <select style="transform: translateX(-48px);" id="modal-season-select-scifi-' . $movie_series_id . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $scifi_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $scifi_modals .= '</select>
                <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
            </div>
        </div>';
                foreach ($seasons as $idx => $s) {
                  $scifi_modals .= '<div class="col-12 mb-4 modal-episodes-block"
            id="modal-episodes-scifi-' . $movie_series_id . '-season-' . $s['season_id'] . '"
            style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $scifi_modals .= '
                <div class="mx-3 py-2 d-flex gap-2">
                    <div class="d-flex align-items-center gap-3 me-2 ms-4">
                        <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $scifi_modals .= '
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
                    $scifi_modals .= '
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
                    $scifi_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $scifi_modals .= '</div>';
                }
                $scifi_modals .= '
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            var select = document.getElementById("modal-season-select-scifi-' . $movie_series_id . '");
            if (select) {
                select.addEventListener("change", function () {
                    var val = this.value;';
                foreach ($seasons as $s) {
                  $scifi_modals .= '
                    document.getElementById("modal-episodes-scifi-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $scifi_modals .= '
                });
            }
        });
        </script>
        ';
              }
              $scifi_modals .= "
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
      <?php echo $scifi_modals; ?>
      <!-- END OF SECTION SCI-FI & FANTASY SERIES -->

      <!-- START OF SECTION CARTOONS -->
      <?php
      $genre_id = 27;
      // Fetch genre name for display
      $genre_result = mysqli_query($con, "SELECT genre_name FROM tbl_movie_series_genre WHERE genre_id = $genre_id LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_name = $genre ? $genre['genre_name'] : "Kids";

      $cartoons_query = mysqli_query(
        $con,
        "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
    FROM tbl_movie_series ms
    LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
    LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
    LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
    WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
      AND ms.category = 'Series'
      AND ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
    ORDER BY ms.date_released DESC
    LIMIT 8"
      );
      $cartoons_modals = "";
      ?>
      <section id="section-cartoons" class="ms-md-5 ms-3" style="margin-top: 60px;">
        <div class="action-movies-top d-flex justify-content-between">
          <p class="action-movies-text text-white fs-24 fw-bold">For <?php echo htmlspecialchars($genre_name); ?></p>
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
            while ($row = mysqli_fetch_assoc($cartoons_query)):
              $movie_series_id = $row['movie_series_id'];
              $cartoon_title = $row['title'];
              $cartoon_poster = $row['poster'];
              $cartoon_video = $row['video'];
              $cartoon_modal_poster_title = $row['modal_poster_title'];
              $cartoon_duration = $row['duration'];
              $cartoon_date_released = $row['date_released'];
              $cartoon_age_rating = $row['age_rating'];
              $cartoon_category = $row['category'];
              $cartoon_genre_1 = $row['genre_1'];
              $cartoon_genre_2 = $row['genre_2'];
              $cartoon_genre_3 = $row['genre_3'];
              $cartoon_cast = $row['cast'];
              $cartoon_description = $row['description'];

              $genres = htmlspecialchars($cartoon_genre_1);
              if (!empty($cartoon_genre_2))
                $genres .= ', ' . htmlspecialchars($cartoon_genre_2);
              if (!empty($cartoon_genre_3))
                $genres .= ', ' . htmlspecialchars($cartoon_genre_3);
              ?>
              <div class="comedy-images position-relative pe-2" style="overflow: visible;">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-cartoons-<?php echo $movie_series_id ?>">
                  <div class="trending-hover position-relative">
                    <img class="action-movies-img rounded-3"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($cartoon_poster); ?>"
                      alt="<?php echo htmlspecialchars($cartoon_title); ?>">
                    <i class="fa-solid fa-play play-button-comedy"></i>
                  </div>
                </a>
              </div>
              <?php
              // Fetch seasons and episodes for this series
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

              $cartoons_modals .= "
<div class='modal fade' id='modal-cartoons-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-cartoons-{$movie_series_id}' aria-hidden='true'>
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
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($cartoon_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
              if (!empty($cartoon_modal_poster_title)) {
                $cartoons_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($cartoon_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
              } else {
                $cartoons_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($cartoon_title) . "</p>";
              }
              $cartoons_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($cartoon_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($cartoon_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($cartoon_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($cartoon_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($cartoon_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($cartoon_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($cartoon_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
      ";

              // SEASONS AND EPISODES DROPDOWN
              if (!empty($seasons)) {
                $cartoons_modals .= '
      <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
          <div>
              <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
          </div>
          <div class="position-relative" style="min-width:180px; ">
              <select style="transform: translateX(-48px);" id="modal-season-select-cartoons-' . $movie_series_id . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $cartoons_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $cartoons_modals .= '</select>
              <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
          </div>
      </div>';
                foreach ($seasons as $idx => $s) {
                  $cartoons_modals .= '<div class="col-12 mb-4 modal-episodes-block"
          id="modal-episodes-cartoons-' . $movie_series_id . '-season-' . $s['season_id'] . '"
          style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $cartoons_modals .= '
                <div class="mx-3 py-2 d-flex gap-2">
                    <div class="d-flex align-items-center gap-3 me-2 ms-4">
                        <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $cartoons_modals .= '
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
                    $cartoons_modals .= '
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
                    $cartoons_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $cartoons_modals .= '</div>';
                }
                $cartoons_modals .= '
      <script>
      document.addEventListener("DOMContentLoaded", function () {
          var select = document.getElementById("modal-season-select-cartoons-' . $movie_series_id . '");
          if (select) {
              select.addEventListener("change", function () {
                  var val = this.value;';
                foreach ($seasons as $s) {
                  $cartoons_modals .= '
                  document.getElementById("modal-episodes-cartoons-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $cartoons_modals .= '
              });
          }
      });
      </script>
      ';
              }
              $cartoons_modals .= "
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
      <?php echo $cartoons_modals; ?>
      <!-- END OF SECTION CARTOONS -->

      <!-- START OF SECTION ANIME -->
      <?php
      // Anime genre_id (replace '3' if your anime genre_id is different)
      $genre_id = 3; // Example: genre_id for Animation/Anime
      $genre_result = mysqli_query($con, "SELECT genre_name FROM tbl_movie_series_genre WHERE genre_id = $genre_id LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_name = $genre ? $genre['genre_name'] : "Anime";

      $anime_query = mysqli_query(
        $con,
        "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
    FROM tbl_movie_series ms
    LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
    LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
    LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
    WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
      AND ms.category = 'Series'
      AND ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
    ORDER BY ms.date_released DESC
    LIMIT 8"
      );
      $anime_modals = "";
      ?>
      <section class="section-featured text-white ms-md-5 ms-3">
        <div class="featured-container">
          <div class="featured-top">
            <p class="featured-text text-uppercase"><?php echo htmlspecialchars($genre_name); ?></p>
            <p class="featured-movies-text fs-2">SERIES</p>
            <div class="top10-featured-wrapper position-relative">
              <div class="prev-button-featured position-absolute"
                style="left: 0; top: 50%; transform: translateY(-50%); z-index: 400;">
                <button class="btn border-1 prev-chevron-btn-featured">
                  <i class="fas fa-chevron-left fa-2xl text-white-50"></i>
                </button>
              </div>
              <div class="featured-images-container d-flex gap-3 position-relative">
                <?php
                while ($row = mysqli_fetch_assoc($anime_query)):
                  $movie_series_id = $row['movie_series_id'];
                  $anime_title = $row['title'];
                  $anime_poster = $row['poster'];
                  $anime_video = $row['video'];
                  $anime_modal_poster_title = $row['modal_poster_title'];
                  $anime_duration = $row['duration'];
                  $anime_date_released = $row['date_released'];
                  $anime_age_rating = $row['age_rating'];
                  $anime_category = $row['category'];
                  $anime_genre_1 = $row['genre_1'];
                  $anime_genre_2 = $row['genre_2'];
                  $anime_genre_3 = $row['genre_3'];
                  $anime_cast = $row['cast'];
                  $anime_description = $row['description'];

                  $genres = htmlspecialchars($anime_genre_1);
                  if (!empty($anime_genre_2))
                    $genres .= ', ' . htmlspecialchars($anime_genre_2);
                  if (!empty($anime_genre_3))
                    $genres .= ', ' . htmlspecialchars($anime_genre_3);
                  ?>
                  <div class="featured-images position-relative pe-2" style="overflow: visible;">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#modal-anime-<?php echo $movie_series_id ?>">
                      <div class="trending-hover position-relative">
                        <img class="featured-img rounded-3"
                          src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($anime_poster); ?>"
                          alt="<?php echo htmlspecialchars($anime_title); ?>">
                        <i class="fa-solid fa-play play-button-featured"></i>
                      </div>
                    </a>
                  </div>
                  <?php
                  // Fetch seasons and episodes for this anime series
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

                  $anime_modals .= "
<div class='modal fade' id='modal-anime-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-anime-{$movie_series_id}' aria-hidden='true'>
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
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($anime_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
                  if (!empty($anime_modal_poster_title)) {
                    $anime_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($anime_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
                  } else {
                    $anime_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($anime_title) . "</p>";
                  }
                  $anime_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($anime_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($anime_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($anime_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($anime_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($anime_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($anime_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($anime_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
      ";

                  // SEASONS AND EPISODES DROPDOWN
                  if (!empty($seasons)) {
                    $anime_modals .= '
      <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
          <div>
              <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
          </div>
          <div class="position-relative" style="min-width:180px; ">
              <select style="transform: translateX(-48px);" id="modal-season-select-anime-' . $movie_series_id . '" class="form-control me-5">';
                    foreach ($seasons as $s) {
                      $anime_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                    }
                    $anime_modals .= '</select>
              <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
          </div>
      </div>';
                    foreach ($seasons as $idx => $s) {
                      $anime_modals .= '<div class="col-12 mb-4 modal-episodes-block"
              id="modal-episodes-anime-' . $movie_series_id . '-season-' . $s['season_id'] . '"
              style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                      foreach ($s['episodes'] as $ep_idx => $ep) {
                        $anime_modals .= '
                  <div class="mx-3 py-2 d-flex gap-2">
                      <div class="d-flex align-items-center gap-3 me-2 ms-4">
                          <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                        if (!empty($ep['episode_video'])) {
                          $anime_modals .= '
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
                        $anime_modals .= '
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
                        $anime_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                      }
                      $anime_modals .= '</div>';
                    }
                    $anime_modals .= '
      <script>
      document.addEventListener("DOMContentLoaded", function () {
          var select = document.getElementById("modal-season-select-anime-' . $movie_series_id . '");
          if (select) {
              select.addEventListener("change", function () {
                  var val = this.value;';
                    foreach ($seasons as $s) {
                      $anime_modals .= '
                  document.getElementById("modal-episodes-anime-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                    }
                    $anime_modals .= '
              });
          }
      });
      </script>
      ';
                  }
                  $anime_modals .= "
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
      <?php echo $anime_modals; ?>
      <!-- END OF SECTION ANIME -->

      <!-- START OF MOVIES 5 -->
      <section class="movies-1 mt-80">
        <div>
          <?php
          // Fetch the movie/series for HomeWallpaper5
          $wallpaper5_query = mysqli_query($con, "
      SELECT * FROM tbl_movie_series
      WHERE poster LIKE '%HomeWallpaper5%'
      LIMIT 1
    ");
          $wallpaper5 = mysqli_fetch_assoc($wallpaper5_query);
          if ($wallpaper5):
            ?>
            <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper5['poster']); ?>"
              class="d-block w-100 wallpaper-lotr bg-danger"
              alt="<?php echo htmlspecialchars($wallpaper5['title']); ?> Wallpaper">

            <div
              class="wallpaper-description-tem d-flex flex-column justify-content-end align-items-end position-relative me-5">

              <div class="d-flex justify-content-center">
                <img class="tem-title-img position-relative mb-1"
                  src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper5['modal_poster_title']); ?>"
                  alt="<?php echo htmlspecialchars($wallpaper5['title']); ?> Title">
              </div>

              <div class="rating-text-tem my-4 position-relative text-white">
                <p><?php echo htmlspecialchars($wallpaper5['description']); ?></p>
              </div>

              <div class="watch-now-tem position-relative">
                <button class="btn btn-primary rounded-5 watch-now-small"
                  onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper5['video']); ?>&type=movie_series&id=<?php echo $wallpaper5['movie_series_id']; ?>'">
                  <i class="fa-solid fa-play mx-1"></i>
                  <span>Watch Now</span>
                </button>
              </div>

            </div>
          <?php endif; ?>
        </div>
      </section>
      <!-- END OF MOVIES 5 -->

      <!-- START OF SECTION KDRAMA -->
      <?php
      // Set the genre_id for KDrama. Change this value if your actual genre_id differs.
      $genre_id = 20;

      // Fetch genre name for display
      $genre_result = mysqli_query($con, "SELECT genre_name FROM tbl_movie_series_genre WHERE genre_id = $genre_id LIMIT 1");
      $genre = mysqli_fetch_assoc($genre_result);
      $genre_name = $genre ? $genre['genre_name'] : "KDRAMA";

      // Query for KDrama Series (category 'Series')
      $kdrama_query = mysqli_query(
        $con,
        "SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
    FROM tbl_movie_series ms
    LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
    LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
    LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
    WHERE (ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)
      AND ms.category = 'Series'
      AND ms.poster NOT LIKE '%HomeCarousel%'
      AND ms.poster NOT LIKE '%MovieCarousel%'
      AND ms.poster NOT LIKE '%SeriesCarousel%'
      AND ms.poster NOT LIKE '%HomeWallpaper%'
    ORDER BY ms.date_released DESC
    LIMIT 8"
      );
      $kdrama_modals = "";
      ?>
      <section id="section-drama" class="section-action-movies ms-md-5 ms-3" style="margin-top: -150px;">
        <div class="action-movies-top d-flex justify-content-between">
          <p class="action-movies-text text-white fs-24 fw-bold"><?php echo htmlspecialchars($genre_name); ?></p>
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
            while ($row = mysqli_fetch_assoc($kdrama_query)):
              $movie_series_id = $row['movie_series_id'];
              $kdrama_title = $row['title'];
              $kdrama_poster = $row['poster'];
              $kdrama_video = $row['video'];
              $kdrama_modal_poster_title = $row['modal_poster_title'];
              $kdrama_duration = $row['duration'];
              $kdrama_date_released = $row['date_released'];
              $kdrama_age_rating = $row['age_rating'];
              $kdrama_category = $row['category'];
              $kdrama_genre_1 = $row['genre_1'];
              $kdrama_genre_2 = $row['genre_2'];
              $kdrama_genre_3 = $row['genre_3'];
              $kdrama_cast = $row['cast'];
              $kdrama_description = $row['description'];

              $genres = htmlspecialchars($kdrama_genre_1);
              if (!empty($kdrama_genre_2))
                $genres .= ', ' . htmlspecialchars($kdrama_genre_2);
              if (!empty($kdrama_genre_3))
                $genres .= ', ' . htmlspecialchars($kdrama_genre_3);
              ?>
              <div class="comedy-images position-relative pe-2" style="overflow: visible;">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-kdrama-<?php echo $movie_series_id ?>">
                  <div class="trending-hover position-relative">
                    <img class="action-movies-img rounded-3"
                      src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($kdrama_poster); ?>"
                      alt="<?php echo htmlspecialchars($kdrama_title); ?>">
                    <i class="fa-solid fa-play play-button-comedy"></i>
                  </div>
                </a>
              </div>
              <?php
              // Fetch seasons and episodes for this series
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

              $kdrama_modals .= "
<div class='modal fade' id='modal-kdrama-{$movie_series_id}' tabindex='-1' aria-labelledby='exampleModalLabel-kdrama-{$movie_series_id}' aria-hidden='true'>
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
              <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($kdrama_video) . "\" type=\"video/mp4\">
              Your browser does not support the video tag.
            </video>";
              if (!empty($kdrama_modal_poster_title)) {
                $kdrama_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($kdrama_modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
              } else {
                $kdrama_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($kdrama_title) . "</p>";
              }
              $kdrama_modals .= "
            <a href=\"play-vid.php?video=" . urlencode($kdrama_video) . "&type=movie_series&id=" . $movie_series_id . "\" class=\"btn btn-light play-btn text-center fs-18 text-end position-absolute\" style=\"width: 120px;\">
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
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($kdrama_date_released) . "</p>
                <p class='modal-text-rating mb-0'>" . htmlspecialchars($kdrama_duration) . "</p>
              </div>
              <div class=\"d-flex gap-2 align-items-center\">
                <p class='modal-text-rating p-1' style=\"border: 1px solid #f4fff8; width: fit-content;\">" . htmlspecialchars($kdrama_age_rating) . "+</p>
                <p class='modal-text-rating mb-0' style=\"transform: translateY(-8px);\">" . htmlspecialchars($kdrama_category) . "</p>
              </div>
              <p class='modal-text-rating'>" . htmlspecialchars($kdrama_description) . "</p>
            </div>
            <div class=\"col-4 ps-0 pe-4 text-wrap\">
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: 12px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Cast: </span>
                " . htmlspecialchars($kdrama_cast) . ", more...
              </p>
              <p class=\"modal-text-rating text-wrap\" style=\"margin-top: -2px;\">
                <span class=\"text-wrap\" style=\"color: #888684;\">Genres: </span>
                {$genres}
              </p>
            </div>
          </div>
      ";

              // SEASONS AND EPISODES DROPDOWN
              if (!empty($seasons)) {
                $kdrama_modals .= '
      <div class="col-12 d-flex justify-content-between align-items-center mx-4" style="width: 100%">
          <div>
              <p class="fs-3 mb-0" style="color: #f4fff8;">Episodes</p>
          </div>
          <div class="position-relative" style="min-width:180px; ">
              <select style="transform: translateX(-48px);" id="modal-season-select-kdrama-' . $movie_series_id . '" class="form-control me-5">';
                foreach ($seasons as $s) {
                  $kdrama_modals .= '<option value="season-' . $s['season_id'] . '">' . htmlspecialchars($s['season_title']) . '</option>';
                }
                $kdrama_modals .= '</select>
              <i class="fa-solid fa-caret-down caret-season" style="position: absolute; bottom: 12px; right: 63px; color: #f4fff8; pointer-events: none;"></i>
          </div>
      </div>';
                foreach ($seasons as $idx => $s) {
                  $kdrama_modals .= '<div class="col-12 mb-4 modal-episodes-block"
          id="modal-episodes-kdrama-' . $movie_series_id . '-season-' . $s['season_id'] . '"
          style="' . ($idx === 0 ? '' : 'display:none;') . '">';
                  foreach ($s['episodes'] as $ep_idx => $ep) {
                    $kdrama_modals .= '
                <div class="mx-3 py-2 d-flex gap-2">
                    <div class="d-flex align-items-center gap-3 me-2 ms-4">
                        <p class="mb-0 fs-2 db-text-sec">' . ($ep_idx + 1) . '</p>';
                    if (!empty($ep['episode_video'])) {
                      $kdrama_modals .= '
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
                    $kdrama_modals .= '
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
                    $kdrama_modals .= '<div class="mx-4 py-2 db-text-sec">No episodes available for this season.</div>';
                  }
                  $kdrama_modals .= '</div>';
                }
                $kdrama_modals .= '
      <script>
      document.addEventListener("DOMContentLoaded", function () {
          var select = document.getElementById("modal-season-select-kdrama-' . $movie_series_id . '");
          if (select) {
              select.addEventListener("change", function () {
                  var val = this.value;';
                foreach ($seasons as $s) {
                  $kdrama_modals .= '
                  document.getElementById("modal-episodes-kdrama-' . $movie_series_id . '-season-' . $s['season_id'] . '").style.display = (val === "season-' . $s['season_id'] . '") ? "" : "none";';
                }
                $kdrama_modals .= '
              });
          }
      });
      </script>
      ';
              }
              $kdrama_modals .= "
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
        </div>
      </section>
      <?php echo $kdrama_modals; ?>
      <!-- END OF SECTION KDRAMA -->

      <!-- START OF MOVIES 6 -->
      <section class="movies-2 mt-80">
        <div>
          <?php
          // Fetch the movie/series for HomeWallpaper6
          $wallpaper6_query = mysqli_query($con, "
      SELECT * FROM tbl_movie_series
      WHERE poster LIKE '%HomeWallpaper6%'
      LIMIT 1
    ");
          $wallpaper6 = mysqli_fetch_assoc($wallpaper6_query);
          if ($wallpaper6):
            ?>
            <img src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper6['poster']); ?>"
              class="d-block w-100 wallpaper-lotr" alt="<?php echo htmlspecialchars($wallpaper6['title']); ?> Wallpaper">

            <div
              class="wallpaper-description-lotr d-flex flex-column justify-content-end align-items-end position-relative me-5">

              <div class="d-flex justify-content-center">
                <img class="lotr-title-img position-relative mb-1"
                  src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($wallpaper6['modal_poster_title']); ?>"
                  alt="<?php echo htmlspecialchars($wallpaper6['title']); ?> Title">
              </div>

              <div class="rating-text-lotr my-4 position-relative text-white">
                <p><?php echo htmlspecialchars($wallpaper6['description']); ?></p>
              </div>

              <div class="watch-now-lotr position-relative">
                <button class="btn btn-primary rounded-5 watch-now-small"
                  onclick="window.location.href='play-vid.php?video=<?php echo urlencode($wallpaper6['video']); ?>&type=movie_series&id=<?php echo $wallpaper6['movie_series_id']; ?>'">
                  <i class="fa-solid fa-play mx-1"></i>
                  <span>Watch Now</span>
                </button>
              </div>

            </div>
          <?php endif; ?>
        </div>
      </section>
      <!-- END OF MOVIES 6 -->
  </main>
  <!-- END OF THE MAIN CONTENT -->

  <footer>
    <div class="footer text-white d-flex justify-content-between mx-5 align-items-center">
      <p class="footer-long-text">This site does not store any files on it's server, It only links to the media which is
        hosted on 3rd party services like YouTube, Dailymotion, Ok.ru, Vidsrc and more.</p>
      <p>© 2025 CineVault. All rights reserved.</p>
    </div>
  </footer>
  <script src="header-scroll.js"></script>
  <script src="video-player.js"></script>
  <script src="poster-slide.js"></script>

</body>

</html>