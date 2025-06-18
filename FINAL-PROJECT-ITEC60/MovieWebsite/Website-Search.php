<?php
include 'CineVault-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Category Search</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="feature-js.css">
  <link rel="stylesheet" href="FirstProject.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<style>
  body { height: fit-content !important; }
</style>
<body>
  <main>
    <section class="section-action-movies ms-md-5 ms-3">
      <div class="action-movies-top d-flex justify-content-between align-items-center">
        <?php
        $genre_id = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : 0;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $genre_name = "Category";
        if ($genre_id > 0) {
          $genre_result = mysqli_query($con, "SELECT genre_name FROM tbl_movie_series_genre WHERE genre_id = $genre_id LIMIT 1");
          $genre = mysqli_fetch_assoc($genre_result);
          $genre_name = $genre ? $genre['genre_name'] : "Category";
        }
        ?>
        <p class="action-movies-text text-white fs-24 fw-bold mb-0"><?php echo htmlspecialchars($genre_name); ?></p>
        <!-- Search input removed as requested -->
      </div>
      <div class="top10-featured-wrapper position-relative d-flex ">
        <div class="action-images-container d-flex flex-wrap align-items-center gap-3 ">
          <?php
          // Build WHERE clause
          $where = [];
          if ($genre_id > 0) {
            $where[] = "(ms.genre_id1 = $genre_id OR ms.genre_id2 = $genre_id OR ms.genre_id3 = $genre_id)";
          }
          if ($search !== '') {
            $search_esc = mysqli_real_escape_string($con, $search);
            $where[] = "(ms.title LIKE '%$search_esc%' OR ms.description LIKE '%$search_esc%' OR ms.cast LIKE '%$search_esc%')";
          }
          $where[] = "ms.poster NOT LIKE '%HomeCarousel%'";
          $where[] = "ms.poster NOT LIKE '%MovieCarousel%'";
          $where[] = "ms.poster NOT LIKE '%SeriesCarousel%'";
          $where[] = "ms.poster NOT LIKE '%HomeWallpaper%'";

          $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

          $query = "
            SELECT ms.*, g1.genre_name AS genre_1, g2.genre_name AS genre_2, g3.genre_name AS genre_3
            FROM tbl_movie_series ms
            LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
            LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
            LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
            $where_sql
            ORDER BY ms.date_released DESC
            LIMIT 20
          ";
          $result = mysqli_query($con, $query);

          if (mysqli_num_rows($result) == 0): ?>
            <div class="text-white-50 fs-4 py-5 px-3">No results found<?php echo $search ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.</div>
          <?php else:
            $comedy_modals = "";
            while ($row = mysqli_fetch_assoc($result)):
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
            <div class="comedy-images position-relative pe-2 mt-2" style="overflow: visible;">
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal-comedy-<?php echo $movie_series_id ?>">
                <div class="trending-hover position-relative">
                  <img class="action-movies-img rounded-3"
                    src="../DASHBOARD-HTML/MOVIE_SERIES_TITLE/<?php echo htmlspecialchars($poster); ?>"
                    alt="<?php echo htmlspecialchars($title); ?>">
                  <i class="fa-solid fa-play play-button-comedy"></i>
                </div>
              </a>
            </div>
          <?php
            // Modal code
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
                <source src=\"../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/" . htmlspecialchars($video) . "\" type=\"video/mp4\">
                Your browser does not support the video tag.
              </video>";
            if (!empty($modal_poster_title)) {
              $comedy_modals .= "<img class='poster-title-img trend-title' src='../DASHBOARD-HTML/MOVIE_SERIES_TITLE/" . htmlspecialchars($modal_poster_title) . "' alt='' style='object-fit: cover; transform: scale(2.0) translate(36px, -14px);'>";
            } else {
              $comedy_modals .= "<p class='fw-bold fs-3 text-white position-absolute' style='top:10px; left:20px;'>" . htmlspecialchars($title) . "</p>";
            }
            $comedy_modals .= "
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
            // --- Series/episodes code (unchanged) ---
            if ($category === 'Series') {
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
          endif;
          ?>
        </div>
      </div>
    </section>
    <?php if (!empty($comedy_modals)) echo $comedy_modals; ?>
  </main>
  <footer style="margin-top: 270px;">
    <div class="footer text-white d-flex justify-content-between mx-5 align-items-center">
      <p class="footer-long-text">This site does not store any files on it's server, It only links to the media which is
        hosted on 3rd party services like YouTube, Dailymotion, Ok.ru, Vidsrc and more.</p>
      <p>© 2025 CineVault. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>