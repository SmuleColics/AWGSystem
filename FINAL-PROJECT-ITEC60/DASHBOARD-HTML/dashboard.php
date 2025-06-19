<?php
include '../includes/db-connection.php';
include '../includes/dashboard-header-sidebar.php';

// USERS: Count distinct non-archived accounts with at least one approved payment
$result = mysqli_query($con, "
  SELECT COUNT(DISTINCT s.signup_id)
  FROM tbl_signup_acc s
  JOIN tbl_payment p ON s.signup_id = p.signup_id
  WHERE p.status = 'approved' AND s.is_archived = 0
");
$row = mysqli_fetch_array($result);

// REVENUE: Sum all approved payments for non-archived accounts
$revenue_query = mysqli_query($con, "
  SELECT SUM(p.payment_amount)
  FROM tbl_payment p
  JOIN tbl_signup_acc s ON p.signup_id = s.signup_id
  WHERE p.status = 'approved' AND s.is_archived = 0
");
$revenue = mysqli_fetch_array($revenue_query);

// MOVIES/SERIES: All (no archive filter, just content listing)
$movie_query = mysqli_query($con, "SELECT COUNT(*) FROM tbl_movie_series WHERE category = 'Movie'");
$movie = mysqli_fetch_array($movie_query);

$series_query = mysqli_query($con, "SELECT COUNT(*) FROM tbl_movie_series WHERE category = 'Series'");
$series = mysqli_fetch_array($series_query);

// LINE CHART: Approved, non-archived signups by payment date (one per day per signup)
$users_quantity = array(0, 0, 0, 0, 0, 0, 0);
$sql = mysqli_query($con, "
  SELECT DATE(p.date_created) as paydate
  FROM tbl_payment p
  JOIN tbl_signup_acc s ON p.signup_id = s.signup_id
  WHERE p.status = 'approved' AND s.is_archived = 0
");
while ($row_day = mysqli_fetch_assoc($sql)) {
  $day_index = date('w', strtotime($row_day['paydate']));
  $users_quantity[$day_index]++;
}
$labels = array("Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat");

// GENRES: get list of all genres for bar chart X axis
$genres = [];
$genreResult = mysqli_query($con, "SELECT genre_id, genre_name FROM tbl_movie_series_genre ORDER BY genre_name");
while ($rowg = mysqli_fetch_assoc($genreResult)) {
  $genres[$rowg['genre_id']] = $rowg['genre_name'];
}
$bar_labels = array_values($genres);

// PIE CHART: Most viewed genres (sum of views for movies in each genre)
$pie_labels = [];
$pie_data = [];
$pie_colors = [];
$genre_color_map = [
  "Action" => "rgb(96, 123, 236)",  // Base color
  "Comedy" => "rgb(106, 133, 246)", // Slightly brighter
  "Drama" => "rgb(86, 113, 226)",  // Slightly darker
  "Thriller" => "rgb(91, 118, 231)",  // Slight variation
  "Romance" => "rgb(101, 128, 241)", // Softened tone
  "Sci-Fi" => "rgb(111, 138, 251)", // Lighter and cooler
  "Horror" => "rgb(76, 103, 216)",  // Darker, moodier shade
  "Adventure" => "rgb(116, 143, 236)", // Bright and vibrant
];

// Sum views for each genre (for all movies, using genre1/2/3)
$view_genre_counts = [];
$viewGenreQuery = mysqli_query($con, "
  SELECT genre_id1, genre_id2, genre_id3, views
  FROM tbl_movie_series
  WHERE category = 'Movie'
");
while ($m = mysqli_fetch_assoc($viewGenreQuery)) {
  foreach (['genre_id1', 'genre_id2', 'genre_id3'] as $gid) {
    $genre_id = $m[$gid];
    if ($genre_id && isset($genres[$genre_id])) {
      if (!isset($view_genre_counts[$genre_id]))
        $view_genre_counts[$genre_id] = 0;
      $view_genre_counts[$genre_id] += (int) $m['views'];
    }
  }
}
arsort($view_genre_counts);
foreach (array_slice($view_genre_counts, 0, 5, true) as $gid => $cnt) {
  $pie_labels[] = $genres[$gid];
  $pie_data[] = $cnt;
  $pie_colors[] = $genre_color_map[$genres[$gid]] ?? "rgb(" . rand(50, 200) . "," . rand(100, 220) . "," . rand(180, 255) . ")";
}
if (empty($pie_labels)) {
  $pie_labels = ['Action', 'Comedy', 'Drama'];
  $pie_data = [5, 10, 8];
  $pie_colors = ['rgb(96, 126, 188)', 'rgb(120, 170, 250)', 'rgb(80, 140, 220)'];
}

// BAR CHART: Number of movies and series per genre (all genres shown)
$bar_movie_data = [];
$bar_series_data = [];
foreach ($genres as $gid => $gname) {
  $bar_movie_data[$gname] = 0;
  $bar_series_data[$gname] = 0;
}

// Count number of movies per genre (any of the 3 genre columns)
$movieGenreQuery = mysqli_query($con, "
  SELECT genre_id1, genre_id2, genre_id3
  FROM tbl_movie_series
  WHERE category = 'Movie'
");
while ($m = mysqli_fetch_assoc($movieGenreQuery)) {
  foreach (['genre_id1', 'genre_id2', 'genre_id3'] as $gid) {
    $genre_id = $m[$gid];
    if ($genre_id && isset($genres[$genre_id])) {
      $bar_movie_data[$genres[$genre_id]]++;
    }
  }
}

// Count number of series per genre (any of the 3 genre columns)
$seriesGenreQuery = mysqli_query($con, "
  SELECT genre_id1, genre_id2, genre_id3
  FROM tbl_movie_series
  WHERE category = 'Series'
");
while ($m = mysqli_fetch_assoc($seriesGenreQuery)) {
  foreach (['genre_id1', 'genre_id2', 'genre_id3'] as $gid) {
    $genre_id = $m[$gid];
    if ($genre_id && isset($genres[$genre_id])) {
      $bar_series_data[$genres[$genre_id]]++;
    }
  }
}
$bar_colors = [];
foreach (array_keys($bar_movie_data) as $gname) {
  $bar_colors[] = $genre_color_map[$gname] ?? "rgb(" . rand(60, 180) . "," . rand(120, 180) . "," . rand(180, 255) . ")";
}

// BAR CHART: Combine views of movies and series per genre
$bar_views_movie_data = [];
$bar_views_series_data = [];
foreach ($genres as $gid => $gname) {
  $bar_views_movie_data[$gname] = 0;
  $bar_views_series_data[$gname] = 0;
}

// Movie views per genre
$movieViewsQuery = mysqli_query($con, "
  SELECT genre_id1, genre_id2, genre_id3, views
  FROM tbl_movie_series
  WHERE category = 'Movie'
");
while ($m = mysqli_fetch_assoc($movieViewsQuery)) {
  foreach (['genre_id1', 'genre_id2', 'genre_id3'] as $gid) {
    $genre_id = $m[$gid];
    if ($genre_id && isset($genres[$genre_id])) {
      $bar_views_movie_data[$genres[$genre_id]] += (int) $m['views'];
    }
  }
}
// Series views per genre
$seriesViewsQuery = mysqli_query($con, "
  SELECT genre_id1, genre_id2, genre_id3, views
  FROM tbl_movie_series
  WHERE category = 'Series'
");
while ($m = mysqli_fetch_assoc($seriesViewsQuery)) {
  foreach (['genre_id1', 'genre_id2', 'genre_id3'] as $gid) {
    $genre_id = $m[$gid];
    if ($genre_id && isset($genres[$genre_id])) {
      $bar_views_series_data[$genres[$genre_id]] += (int) $m['views'];
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Main Dashboard</title>
  <link rel="stylesheet" href="../DASHBOARD-CSS/dashboard.css">
  <link rel="stylesheet" href="../DASHBOARD-CSS/for-all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .sidebar-content-item:nth-child(1) {
      background-color: var(--dashboard-primary);
    }
  </style>
</head>

<body>
  <main class="container-lg p-0 overflow-hidden">
    <div class="me-3 mt-3 p-0 ms-md-2 ms-0 w-auto w-md-auto d-flex justify-content-end">
      <a class="btn db-bg-primary db-text-sec" href="../libs/generate-payment-report.php" target="_blank"><i class="fa-solid fa-download"></i> Generate Report</a>
    </div>
    <!-- CARD SECTION -->
    <section class="card-section p-3">
      <div class="row g-3">
        <!-- USERS -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
          <div class="card bg-dark">
            <div class="card-body">
              <div class="card-top d-flex align-items-center justify-content-between">
                <div class="card-top-left">
                  <div class="card-icon-container flexbox-align" style="background-color: #607BEC;">
                    <i class="fa-solid fa-user db-card-icon"></i>
                  </div>
                </div>
                <div class="card-top-right w-100 text-end mt-3 db-text-sec">
                  <h6 class="card-subtitle fs-14 mb-1">Users</h6>
                  <h5 class="card-title text-end fs-24">
                    <?php echo isset($row[0]) && $row[0] !== null ? $row[0] : 0; ?>
                  </h5>
                </div>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-bottom d-flex align-items-center gap-2 db-text-secondary fs-12">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Total Approved Users</p>
              </div>
            </div>
          </div>
        </div>
        <!-- REVENUE -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
          <div class="card bg-dark">
            <div class="card-body">
              <div class="card-top d-flex align-items-center justify-content-between">
                <div class="card-top-left">
                  <div class="card-icon-container flexbox-align" style="background-color: #607BEC;">
                    <i class="fa-solid fa-peso-sign db-card-icon"></i>
                  </div>
                </div>
                <div class="card-top-right w-100 text-end mt-3 db-text-sec">
                  <h6 class="card-subtitle fs-14 mb-1">Revenue</h6>
                  <h5 class="card-title fs-24">
                    ₱<?php echo isset($revenue[0]) && $revenue[0] !== null ? number_format($revenue[0], 2) : '0.00'; ?>
                  </h5>
                </div>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-bottom d-flex align-items-center gap-2 db-text-secondary fs-12">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Total Revenue (Approved, Active)</p>
              </div>
            </div>
          </div>
        </div>
        <!-- MOVIES -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
          <div class="card bg-dark">
            <div class="card-body">
              <div class="card-top d-flex align-items-center justify-content-between">
                <div class="card-top-left">
                  <div class="card-icon-container flexbox-align" style="background-color: #607BEC;">
                    <i class="fa-solid fa-film db-card-icon"></i>
                  </div>
                </div>
                <div class="card-top-right w-100 text-end mt-3 db-text-sec">
                  <h6 class="card-subtitle fs-14 mb-1">Movies</h6>
                  <h5 class="card-title fs-24"><?php echo isset($movie[0]) && $movie[0] !== null ? $movie[0] : 0; ?>
                  </h5>
                </div>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-bottom d-flex align-items-center gap-2 db-text-secondary fs-12">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Total Movies</p>
              </div>
            </div>
          </div>
        </div>
        <!-- SERIES -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
          <div class="card bg-dark">
            <div class="card-body">
              <div class="card-top d-flex align-items-center justify-content-between">
                <div class="card-top-left">
                  <div class="card-icon-container flexbox-align" style="background-color: #607BEC;">
                    <i class="fa-solid fa-video db-card-icon"></i>
                  </div>
                </div>
                <div class="card-top-right w-100 text-end mt-3 db-text-sec">
                  <h6 class="card-subtitle fs-14 mb-1">Series</h6>
                  <h5 class="card-title fs-24"><?php echo isset($series[0]) && $series[0] !== null ? $series[0] : 0; ?>
                  </h5>
                </div>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-bottom d-flex align-items-center gap-2 db-text-secondary fs-12">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Total Series</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CHART SECTION -->
    <section class="chart-section px-3 py-2">
      <div class="row g-3">
        <!-- LINE GRAPH USERS -->
        <div class="col-xl-6 col-lg-6">
          <div class="card bg-dark">
            <center class="card-chart-container" style="height: 257px; position: relative;">
              <canvas id="lineChart" style="width: 100%; height: 100%;"></canvas>
            </center>
            <div class="card-body">
              <div class="card-chart-top db-text-sec">
                <h6 class="card-subtitle fs-18">Daily Approved Users</h6>
                <h5 class="card-title text-start fs-14 db-text-secondary">Approved users activity this week</h5>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-chart-bottom db-text-secondary d-flex align-items-center gap-2 fs-14">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Daily Users</p>
              </div>
            </div>
          </div>
        </div>
        <!-- PIE GRAPH GENRES -->
        <div class="col-xl-6 col-lg-6 h-100">
          <div class="card bg-dark">
            <div class="card-chart-container">
              <center>
                <canvas id="pieGraph" class="p-2"></canvas>
              </center>
            </div>
            <div class="card-body">
              <div class="card-chart-top db-text-sec">
                <h6 class="card-subtitle fs-18">Most Viewed Genres</h6>
                <h5 class="card-title text-start fs-14 db-text-secondary">Based on movie views</h5>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-chart-bottom db-text-secondary d-flex align-items-center gap-2 fs-14">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Genre Popularity</p>
              </div>
            </div>
          </div>
        </div>
        <!-- BAR GRAPH MOVIES/SERIES GENRES (ALL GENRES) -->
        <div class="col-xl-12 mt-3">
          <div class="card bg-dark">
            <div class="card-chart-container">
              <canvas id="barGraphMoviesSeries" class="p-2 w-100"></canvas>
            </div>
            <div class="card-body">
              <div class="card-chart-top db-text-sec">
                <h6 class="card-subtitle fs-18">Movies & Series per Genre (All)</h6>
                <h5 class="card-title text-start fs-14 db-text-secondary">Number of Movies and Series per Genre</h5>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-chart-bottom db-text-secondary d-flex align-items-center gap-2 fs-14">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Genre Statistics</p>
              </div>
            </div>
          </div>
        </div>
        <!-- BAR GRAPH VIEWS FOR MOVIES AND SERIES PER GENRE -->
        <div class="col-xl-12 mt-3">
          <div class="card bg-dark">
            <div class="card-chart-container">
              <canvas id="barGraphViews" class="p-2 w-100"></canvas>
            </div>
            <div class="card-body">
              <div class="card-chart-top db-text-sec">
                <h6 class="card-subtitle fs-18">Total Views per Genre (Movies & Series)</h6>
                <h5 class="card-title text-start fs-14 db-text-secondary">Sum of all views for movies and series per
                  genre</h5>
              </div>
              <div class="card-divider my-3"></div>
              <div class="card-chart-bottom db-text-secondary d-flex align-items-center gap-2 fs-14">
                <i class="fa-solid fa-clock"></i>
                <p class="m-0">Views by Genre</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // LINE CHART USERS
    new Chart(document.getElementById('lineChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
          label: 'Daily Approved Users',
          data: <?php echo json_encode($users_quantity); ?>,
          fill: false,
          borderColor: '#607EBC',
          backgroundColor: '#607EBC',
          tension: 0.1
        }]
      },
      options: {
        maintainAspectRatio: true,
        responsive: true,
        plugins: {
          legend: { labels: { color: 'white' } },
          title: {
            display: true,
            text: 'Approved User Activity Over Time',
            color: 'white'
          }
        },
        scales: {
          x: { ticks: { color: 'white' } },
          y: {
            ticks: { color: 'white' },
            min: 0,
            suggestedMax: Math.max(...<?php echo json_encode($users_quantity); ?>, 10)
          }
        }
      }
    });

    // PIE CHART MOST VIEWED GENRES
    new Chart(document.getElementById('pieGraph'), {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($pie_labels); ?>,
        datasets: [{
          label: 'Views',
          data: <?php echo json_encode($pie_data); ?>,
          backgroundColor: <?php echo json_encode($pie_colors); ?>,
          hoverOffset: 4
        }]
      },
      options: {
        maintainAspectRatio: true,
        responsive: true,
        plugins: {
          tooltip: { enabled: true },
          legend: { labels: { color: 'white' } },
          title: {
            display: true,
            text: 'Most Viewed Movie Genres',
            color: 'white'
          }
        },
      }
    });

    // BAR CHART: NUMBER OF MOVIES AND SERIES PER GENRE
    new Chart(document.getElementById('barGraphMoviesSeries'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_keys($bar_movie_data)); ?>,
        datasets: [
          {
            label: 'Movies',
            data: <?php echo json_encode(array_values($bar_movie_data)); ?>,
            backgroundColor: 'rgba(96, 126, 188, 0.8)',
            borderColor: 'rgba(96, 126, 188, 1)',
            borderWidth: 1
          },
          {
            label: 'Series',
            data: <?php echo json_encode(array_values($bar_series_data)); ?>,
            backgroundColor: 'rgba(120, 170, 250, 0.7)',
            borderColor: 'rgba(120, 170, 250, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
          legend: { labels: { color: 'white' } },
          title: {
            display: true,
            text: 'Number of Movies and Series Per Genre',
            color: 'white'
          }
        },
        scales: {
          x: { ticks: { color: 'white' }, stacked: true },
          y: { beginAtZero: true, ticks: { color: 'white' }, stacked: true }
        }
      }
    });

    // BAR CHART: TOTAL VIEWS (MOVIES AND SERIES) PER GENRE
    new Chart(document.getElementById('barGraphViews'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_keys($bar_views_movie_data)); ?>,
        datasets: [
          {
            label: 'Movie Views',
            data: <?php echo json_encode(array_values($bar_views_movie_data)); ?>,
            backgroundColor: 'rgba(96, 126, 188, 0.8)',
            borderColor: 'rgba(120, 170, 250, 0.7)',
            borderWidth: 1
          },
          {
            label: 'Series Views',
            data: <?php echo json_encode(array_values($bar_views_series_data)); ?>,
            backgroundColor: 'rgba(120, 170, 250, 0.7)',
            borderColor: 'rgba(120, 170, 250, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
          legend: { labels: { color: 'white' } },
          title: {
            display: true,
            text: 'Total Views of Movies and Series Per Genre',
            color: 'white'
          }
        },
        scales: {
          x: { ticks: { color: 'white' }, stacked: true },
          y: { beginAtZero: true, ticks: { color: 'white' }, stacked: true }
        }
      }
    });
  </script>
</body>

</html>