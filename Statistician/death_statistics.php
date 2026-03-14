<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'statistician') {
    header('Location: ../login.php');
    exit();
}

// Get all zones for filtering
$zones = [];
$zones_res = $conn->query("SELECT * FROM zones ORDER BY zone_name");
if ($zones_res) {
    while ($zone = $zones_res->fetch_assoc()) {
        $zones[] = $zone;
    }
}

// Filter parameters
$selected_zone = $_GET['zone'] ?? 'all';
$start_date = $_GET['start_date'] ?? date('Y-m-01', strtotime('-1 year'));
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Base query
$base_query = "SELECT d.*, k.kebele_name, w.woreda_name, z.zone_name 
               FROM death_events d 
               JOIN kebeles k ON d.place_of_death = k.id 
               JOIN woredas w ON k.woreda_id = w.id 
               JOIN zones z ON w.zone_id = z.id 
               WHERE d.date_of_death BETWEEN '$start_date' AND '$end_date'";

if ($selected_zone !== 'all') {
    $base_query .= " AND z.zone_name = '" . $conn->real_escape_string($selected_zone) . "'";
}

// Total deaths
$total_deaths = $conn->query("SELECT COUNT(*) as count FROM ($base_query) as sub")->fetch_assoc()['count'];

// Gender distribution
$gender_stats = $conn->query("SELECT sex, COUNT(*) as count FROM ($base_query) as sub GROUP BY sex")->fetch_all(MYSQLI_ASSOC);

// Age distribution
$age_stats = $conn->query("
    SELECT 
        CASE 
            WHEN age < 1 THEN 'Infant (0-1)'
            WHEN age BETWEEN 1 AND 5 THEN 'Toddler (1-5)'
            WHEN age BETWEEN 6 AND 12 THEN 'Child (6-12)'
            WHEN age BETWEEN 13 AND 19 THEN 'Teen (13-19)'
            WHEN age BETWEEN 20 AND 35 THEN 'Young Adult (20-35)'
            WHEN age BETWEEN 36 AND 55 THEN 'Middle Age (36-55)'
            WHEN age BETWEEN 56 AND 75 THEN 'Senior (56-75)'
            ELSE 'Elderly (75+)'
        END as age_group,
        COUNT(*) as count
    FROM ($base_query) as sub 
    GROUP BY age_group 
    ORDER BY MIN(age)
")->fetch_all(MYSQLI_ASSOC);

// Cause of death statistics
$cause_stats = $conn->query("
    SELECT cause_of_death, COUNT(*) as count 
    FROM ($base_query) as sub 
    GROUP BY cause_of_death 
    ORDER BY count DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Monthly trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(date_of_death, '%Y-%m') as month, COUNT(*) as count
    FROM ($base_query) as sub 
    GROUP BY DATE_FORMAT(date_of_death, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 12
")->fetch_all(MYSQLI_ASSOC);

// Zone-wise distribution
$zone_distribution = $conn->query("
    SELECT zone_name, COUNT(*) as count 
    FROM ($base_query) as sub 
    GROUP BY zone_name 
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Recent deaths
$recent_deaths = $conn->query("$base_query ORDER BY d.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Death Statistics | VERMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --danger-color: #e74c3c;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #eaf6fb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
        }

        .stat-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="display-5 fw-bold"><i class="fas fa-book-dead me-3"></i>Death Statistics</h1>
        <p class="lead">Comprehensive analysis of death registrations</p>
    </div>
</div>

<div class="container">
    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Zone</label>
                <select name="zone" class="form-select">
                    <option value="all">All Zones</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?= $zone['zone_name'] ?>" <?= $selected_zone == $zone['zone_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($zone['zone_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">&nbsp;</label>
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-danger mb-2">
                    <i class="fas fa-book-dead fa-2x"></i>
                </div>
                <h3 class="text-danger"><?= number_format($total_deaths) ?></h3>
                <p class="text-muted mb-0">Total Deaths</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-primary mb-2">
                    <i class="fas fa-male fa-2x"></i>
                </div>
                <h3 class="text-primary">
                    <?= number_format(array_sum(array_column(array_filter($gender_stats, fn($g) => $g['sex'] == 'Male'), 'count'))) ?>
                </h3>
                <p class="text-muted mb-0">Male Deaths</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2">
                    <i class="fas fa-female fa-2x"></i>
                </div>
                <h3 class="text-info">
                    <?= number_format(array_sum(array_column(array_filter($gender_stats, fn($g) => $g['sex'] == 'Female'), 'count'))) ?>
                </h3>
                <p class="text-muted mb-0">Female Deaths</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-success mb-2">
                    <i class="fas fa-map-marker-alt fa-2x"></i>
                </div>
                <h3 class="text-success"><?= count($zone_distribution) ?></h3>
                <p class="text-muted mb-0">Zones Covered</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-line text-danger me-2"></i>Monthly Death Trends</h4>
                <canvas id="monthlyTrendsChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-pie text-warning me-2"></i>Gender Distribution</h4>
                <canvas id="genderChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-bar text-info me-2"></i>Age Group Distribution</h4>
                <canvas id="ageChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-stethoscope text-success me-2"></i>Top Causes of Death</h4>
                <canvas id="causeChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i>Zone-wise Distribution</h4>
                <canvas id="zoneChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-list text-secondary me-2"></i>Recent Death Registrations</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Deceased Name</th>
                                <th>Date</th>
                                <th>Age</th>
                                <th>Cause</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_deaths as $death): ?>
                            <tr>
                                <td><?= htmlspecialchars($death['deceased_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($death['date_of_death'])) ?></td>
                                <td><?= $death['age'] ?> years</td>
                                <td>
                                    <span class="badge bg-danger"><?= htmlspecialchars($death['cause_of_death']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Monthly Trends Chart
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_trends, 'month')) ?>,
        datasets: [{
            label: 'Deaths',
            data: <?= json_encode(array_column($monthly_trends, 'count')) ?>,
            borderColor: '#e74c3c',
            backgroundColor: 'rgba(231, 76, 60, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Gender Distribution Chart
const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{
            data: [
                <?= array_sum(array_column(array_filter($gender_stats, fn($g) => $g['sex'] == 'Male'), 'count')) ?>,
                <?= array_sum(array_column(array_filter($gender_stats, fn($g) => $g['sex'] == 'Female'), 'count')) ?>
            ],
            backgroundColor: ['#3498db', '#e74c3c'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Age Distribution Chart
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($age_stats, 'age_group')) ?>,
        datasets: [{
            label: 'Deaths',
            data: <?= json_encode(array_column($age_stats, 'count')) ?>,
            backgroundColor: '#3498db'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Cause of Death Chart
const causeCtx = document.getElementById('causeChart').getContext('2d');
new Chart(causeCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($cause_stats, 'cause_of_death')) ?>,
        datasets: [{
            label: 'Cases',
            data: <?= json_encode(array_column($cause_stats, 'count')) ?>,
            backgroundColor: '#e74c3c'
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// Zone Distribution Chart
const zoneCtx = document.getElementById('zoneChart').getContext('2d');
new Chart(zoneCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($zone_distribution, 'zone_name')) ?>,
        datasets: [{
            label: 'Deaths',
            data: <?= json_encode(array_column($zone_distribution, 'count')) ?>,
            backgroundColor: '#f39c12'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>