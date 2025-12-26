<?php
include 'admin-header.php';

// Fetch statistics based on user role
// Users count
$users_query = "SELECT COUNT(*) as total FROM users";
$users_result = mysqli_query($conn, $users_query);
$total_users = mysqli_fetch_assoc($users_result)['total'];

// Assessment requests
$assessments_query = "SELECT COUNT(*) as total FROM assessments WHERE status = 'Pending'";
$assessments_result = mysqli_query($conn, $assessments_query);
$pending_assessments = mysqli_fetch_assoc($assessments_result)['total'];

// Completed projects
$projects_query = "SELECT COUNT(*) as total FROM projects WHERE status = 'Completed'";
$projects_result = mysqli_query($conn, $projects_query);
$completed_projects = mysqli_fetch_assoc($projects_result)['total'];

// Revenue (Admin only)
$revenue = 0;
if ($is_admin) {
  $revenue_query = "SELECT SUM(amount_paid) as total FROM projects WHERE status = 'Completed'";
  $revenue_result = mysqli_query($conn, $revenue_query);
  $revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;
}

// Get monthly assessments for chart
$monthly_assessments = [];
$months = [];
for ($i = 5; $i >= 0; $i--) {
  $month = date('Y-m', strtotime("-$i months"));
  $month_name = date('M Y', strtotime("-$i months"));
  $months[] = $month_name;
  
  $query = "SELECT COUNT(*) as count FROM assessments WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
  $result = mysqli_query($conn, $query);
  $monthly_assessments[] = mysqli_fetch_assoc($result)['count'];
}

// Get monthly users registration
$monthly_users = [];
for ($i = 5; $i >= 0; $i--) {
  $month = date('Y-m', strtotime("-$i months"));
  $query = "SELECT COUNT(*) as count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
  $result = mysqli_query($conn, $query);
  $monthly_users[] = mysqli_fetch_assoc($result)['count'];
}

// Get project status distribution (FIXED ORDER)
$project_status_query = "SELECT status, COUNT(*) as count 
                        FROM projects 
                        GROUP BY status 
                        ORDER BY FIELD(status, 'Pending', 'In Progress', 'Completed')";
$project_status_result = mysqli_query($conn, $project_status_query);
$project_statuses = [];
$project_counts = [];
while ($row = mysqli_fetch_assoc($project_status_result)) {
  $project_statuses[] = $row['status'];
  $project_counts[] = $row['count'];
}

// Get recent activities (for employees, only their own)
if ($is_admin) {
  $activities_query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5";
} else {
  $activities_query = "SELECT * FROM activity_logs WHERE employee_id = $employee_id ORDER BY created_at DESC LIMIT 5";
}
$activities_result = mysqli_query($conn, $activities_query);

// Get task data for employees
if (!$is_admin) {
  $total_tasks_query = "SELECT COUNT(*) as total FROM tasks WHERE assigned_to_id = $employee_id";
  $completed_tasks_query = "SELECT COUNT(*) as total FROM tasks WHERE assigned_to_id = $employee_id AND status = 'Completed'";
  $pending_tasks_query = "SELECT COUNT(*) as total FROM tasks WHERE assigned_to_id = $employee_id AND status = 'Pending'";
  $in_progress_tasks_query = "SELECT COUNT(*) as total FROM tasks WHERE assigned_to_id = $employee_id AND status = 'In Progress'";
  
  $total_tasks = mysqli_fetch_assoc(mysqli_query($conn, $total_tasks_query))['total'];
  $completed_tasks = mysqli_fetch_assoc(mysqli_query($conn, $completed_tasks_query))['total'];
  $pending_tasks = mysqli_fetch_assoc(mysqli_query($conn, $pending_tasks_query))['total'];
  $in_progress_tasks = mysqli_fetch_assoc(mysqli_query($conn, $in_progress_tasks_query))['total'];
  
  $task_completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 1) : 0;
}

// Get monthly revenue for admin (last 6 months)
$monthly_revenue = [];
if ($is_admin) {
  for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $query = "SELECT SUM(amount_paid) as total FROM projects 
              WHERE status = 'Completed' AND DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = mysqli_query($conn, $query);
    $monthly_revenue[] = mysqli_fetch_assoc($result)['total'] ?? 0;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title><?= $is_admin ? 'Admin Dashboard' : 'Dashboard' ?></title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
  .sidebar-content-item:nth-child(1) {
    background-color: #f2f2f2 !important;
  }
  .sidebar-content-item:nth-child(1) .sidebar-anchor,
  .sidebar-content-item:nth-child(1) .sidebar-anchor span {
    color: #16A249 !important;
  }
  .chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
  }
  </style>

</head>

<body class="pt-0">
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 mt-5 min-vh-100">
    <div class="admin-top-text my-3">
      <h1 class="fs-36"><?= $is_admin ? 'Admin Dashboard' : 'Dashboard' ?></h1>
      <p class="admin-top-desc">
        <?= $is_admin ? 'Monitor system performance, manage operations, and track business metrics' : 'Track your tasks, activities, and performance' ?>
      </p>
    </div>

    <!-- DASHBOARD STATUS -->
    <div class="row g-3">
      <!-- USERS -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="daily-users d-flex justify-content-between align-items-center">
            <div class="green-bg dashboard-icon-con flex rounded mt-2">
              <i class="fa-solid fa-users dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Users</p>
              <p class="fs-24 mobile-fs-22 light-text"><?= number_format($total_users) ?></p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> Total registered</p>
        </div>
      </div>

      <!-- ASSESSMENT REQUESTS -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="daily-users d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <i class="fa-solid fa-clipboard-list dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Pending Requests</p>
              <p class="fs-24 mobile-fs-22 light-text"><?= number_format($pending_assessments) ?></p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> Awaiting review</p>
        </div>
      </div>

      <!-- COMPLETED PROJECTS -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <i class="fa-solid fa-check-circle dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Completed Projects</p>
              <p class="fs-24 mobile-fs-22 light-text"><?= number_format($completed_projects) ?></p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> All time</p>
        </div>
      </div>

      <!-- REVENUE (Admin only) -->
      <?php if ($is_admin): ?>
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <span class="text-white dashboard-icon">₱</span>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Total Revenue</p>
              <p class="fs-24 mobile-fs-22 light-text">₱<?= number_format($revenue, 2) ?></p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> From completed projects</p>
        </div>
      </div>
      <?php else: ?>
      <!-- TASK COMPLETION (Employee only) -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <i class="fa-solid fa-tasks dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Task Completion</p>
              <p class="fs-24 mobile-fs-22 light-text"><?= $task_completion_rate ?>%</p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> <?= $completed_tasks ?> of <?= $total_tasks ?> tasks</p>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- CHARTS SECTION -->
    <div class="row g-3 mt-2">
      <?php if ($is_admin): ?>
      <!-- ADMIN CHARTS -->
      
      <!-- Monthly Users Registration -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">User Registrations (Last 6 Months)</h5>
          <div class="chart-container">
            <canvas id="usersChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Monthly Assessments -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">Assessment Requests (Last 6 Months)</h5>
          <div class="chart-container">
            <canvas id="assessmentsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Project Status Distribution -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">Project Status Distribution</h5>
          <div class="chart-container">
            <canvas id="projectStatusChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">Monthly Revenue (Last 6 Months)</h5>
          <div class="chart-container">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>

      <?php else: ?>
      <!-- EMPLOYEE CHARTS -->
      
      <!-- Monthly Assessments -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">Assessment Requests (Last 6 Months)</h5>
          <div class="chart-container">
            <canvas id="assessmentsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- My Tasks Status -->
      <div class="col-md-6">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text">My Tasks Status</h5>
          <div class="chart-container">
            <canvas id="myTasksChart"></canvas>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="p-4 rounded dashboard-one">
          <h5 class="mb-3 green-text"><?= $is_admin ? 'Recent System Activity' : 'My Recent Activity' ?></h5>
          
          <?php if (mysqli_num_rows($activities_result) > 0): ?>
          <div class="list-group">
            <?php while ($activity = mysqli_fetch_assoc($activities_result)): ?>
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1"><?= htmlspecialchars($activity['employee_name']) ?></h6>
                  <p class="mb-1 fs-14"><?= htmlspecialchars($activity['description']) ?></p>
                  <small class="text-muted">
                    <?= htmlspecialchars($activity['module']) ?> - <?= htmlspecialchars($activity['action']) ?>
                  </small>
                </div>
                <small class="text-muted text-nowrap ms-3">
                  <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                </small>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
          <?php else: ?>
          <p class="text-center text-muted py-4">No recent activity</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <script>
    // Data from PHP
    const months = <?= json_encode($months) ?>;
    const monthlyAssessments = <?= json_encode($monthly_assessments) ?>;
    const monthlyUsers = <?= json_encode($monthly_users) ?>;
    
    <?php if ($is_admin): ?>
    const projectStatuses = <?= json_encode($project_statuses) ?>;
    const projectCounts = <?= json_encode($project_counts) ?>;
    const monthlyRevenue = <?= json_encode($monthly_revenue) ?>;
    <?php else: ?>
    const pendingTasks = <?= $pending_tasks ?>;
    const inProgressTasks = <?= $in_progress_tasks ?>;
    const completedTasks = <?= $completed_tasks ?>;
    <?php endif; ?>

    // Chart.js default settings
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    <?php if ($is_admin): ?>
    // Users Registration Line Chart
    const usersCtx = document.getElementById('usersChart');
    if (usersCtx) {
      new Chart(usersCtx, {
        type: 'line',
        data: {
          labels: months,
          datasets: [{
            label: 'New Users',
            data: monthlyUsers,
            borderColor: '#16A249',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }
    <?php endif; ?>

    // Assessments Line Chart
    const assessmentsCtx = document.getElementById('assessmentsChart');
    if (assessmentsCtx) {
      new Chart(assessmentsCtx, {
        type: 'line',
        data: {
          labels: months,
          datasets: [{
            label: 'Assessment Requests',
            data: monthlyAssessments,
            borderColor: '#16A249',
            backgroundColor: 'rgba(22, 162, 73, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }

    <?php if ($is_admin): ?>
    // Project Status Doughnut Chart (Fixed order: Pending, In Progress, Completed)
    const projectStatusCtx = document.getElementById('projectStatusChart');
    if (projectStatusCtx) {
      new Chart(projectStatusCtx, {
        type: 'doughnut',
        data: {
          labels: projectStatuses,
          datasets: [{
            data: projectCounts,
            backgroundColor: [
              '#ffc107',  // Pending - Yellow
              '#16A249'   // Completed - Green (on the right)

            ],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 15,
                font: {
                  size: 13
                }
              }
            }
          }
        }
      });
    }

    // Revenue Bar Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      new Chart(revenueCtx, {
        type: 'bar',
        data: {
          labels: months,
          datasets: [{
            label: 'Revenue (₱)',
            data: monthlyRevenue,
            backgroundColor: '#16A249',
            borderRadius: 5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '₱' + value.toLocaleString();
                }
              }
            }
          }
        }
      });
    }
    <?php else: ?>
    // Employee Tasks Status Doughnut Chart
    const myTasksCtx = document.getElementById('myTasksChart');
    if (myTasksCtx) {
      new Chart(myTasksCtx, {
        type: 'doughnut',
        data: {
          labels: ['Pending', 'In Progress', 'Completed'],
          datasets: [{
            data: [pendingTasks, inProgressTasks, completedTasks],
            backgroundColor: [
              '#ffc107',  // Pending - Yellow
              '#17a2b8',  // In Progress - Blue
              '#16A249'   // Completed - Green
            ],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 15,
                font: {
                  size: 13
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                  return label + ': ' + value + ' (' + percentage + '%)';
                }
              }
            }
          }
        }
      });
    }
    <?php endif; ?>
  </script>

</body>

</html>