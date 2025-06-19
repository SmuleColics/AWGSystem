<?php
include '../includes/db-connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($id && $type === 'movie_series') {
    $result = mysqli_query($con, "SELECT 
        g1.genre_name AS genre_1, 
        g2.genre_name AS genre_2, 
        g3.genre_name AS genre_3 
        FROM tbl_movie_series ms
        LEFT JOIN tbl_movie_series_genre g1 ON ms.genre_id1 = g1.genre_id
        LEFT JOIN tbl_movie_series_genre g2 ON ms.genre_id2 = g2.genre_id
        LEFT JOIN tbl_movie_series_genre g3 ON ms.genre_id3 = g3.genre_id
        WHERE ms.movie_series_id = $id LIMIT 1
    ");
    if ($row = mysqli_fetch_assoc($result)) {
        // Get the first available genre
        $genre = $row['genre_1'] ?: ($row['genre_2'] ?: $row['genre_3']);
        if ($genre) {
            // Get current preferred genres from cookie
            $preferred = [];
            if (isset($_COOKIE['preferred_genres']) && $_COOKIE['preferred_genres'] !== '') {
                $preferred = array_map('trim', explode(',', $_COOKIE['preferred_genres']));
            }
            // Remove if already present
            $preferred = array_filter($preferred, function($g) use ($genre) {
                return $g !== $genre;
            });
            // Add new genre at the end
            $preferred[] = $genre;
            // Keep only last 3
            if (count($preferred) > 3) {
                $preferred = array_slice($preferred, -3);
            }
            setcookie("preferred_genres", implode(',', $preferred), time() + 60 * 60 * 24 * 30, "/");
        }
    }
}

// Get type and id again in case you want to override defaults
$type = isset($_GET['type']) ? $_GET['type'] : 'movie_series';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

switch ($type) {
    case 'trend':
        $videoDir = "../DASHBOARD-HTML/TREND_VIDEOS/";
        break;
    case 'top10':
    case 'movie_series':
        $videoDir = "../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/";
        break;
    case 'episode':
        $videoDir = "../DASHBOARD-HTML/MOVIE_SERIES_EPISODE/";
        break;
    default:
        $videoDir = "../DASHBOARD-HTML/MOVIE_SERIES_VIDEO/";
        break;
}

$videoFile = isset($_GET['video']) ? basename($_GET['video']) : '';
$fullPath = $videoDir . $videoFile;

if ($videoFile && preg_match('/\.mp4$/i', $videoFile) && file_exists($fullPath)) {
    $videoPath = $fullPath;

    // Increment views
    if ($type === 'movie_series' || $type === 'top10') {
        if ($id) {
            mysqli_query($con, "UPDATE tbl_movie_series SET views = views + 1 WHERE movie_series_id = $id");
        }
    } else if ($type === 'episode') {
        if ($id) {
            // Increment episode views
            mysqli_query($con, "UPDATE tbl_movie_series_episodes SET views = views + 1 WHERE episode_id = $id");
            // Also increment parent movie/series views
            $result = mysqli_query($con, "SELECT season_id FROM tbl_movie_series_episodes WHERE episode_id = $id");
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $season_id = $row['season_id'];
                $result2 = mysqli_query($con, "SELECT movie_series_id FROM tbl_movie_series_seasons WHERE season_id = $season_id");
                if ($result2 && $row2 = mysqli_fetch_assoc($result2)) {
                    $movie_series_id = $row2['movie_series_id'];
                    mysqli_query($con, "UPDATE tbl_movie_series SET views = views + 1 WHERE movie_series_id = $movie_series_id");
                }
            }
        }
    }
} else {
    $videoPath = "default.mp4";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Play Video</title>
  <link rel="icon" href="../MOVIE-IMG/HEADER-IMG/CINEVAULT-LOGO.svg">
  <style>
    body { margin:0; padding:0; overflow: hidden; background-color: #1F2A37;}
    .video-container { width: 100vw; height: 100vh; }
    video { width: 100%; height: 100%; object-fit: cover; }
  </style>
</head>
<body>
  <div class="video-container">
    <video class="video" autoplay controls>
      <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </div>
</body>
</html>