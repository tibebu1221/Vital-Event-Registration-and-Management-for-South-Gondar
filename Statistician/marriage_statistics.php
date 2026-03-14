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
$base_query = "SELECT m.*, k.kebele_name, w.woreda_name, z.zone_name 
               FROM marriage_events m 
               JOIN kebeles k ON m.place_of_marriage = k.id 
               JOIN woredas w ON k.woreda_id = w.id 
               JOIN zones z ON w.zone_id = z.id 
               WHERE m.marriage_date BETWEEN '$start_date' AND '$end_date'";

if ($selected_zone !== 'all') {
    $base_query .= " AND z.zone_name = '" . $conn->real_escape_string($selected_zone) . "'";
}

// Total marriages
$total_marriages = $conn->query("SELECT COUNT(*) as count FROM ($base_query) as sub")->fetch_assoc()['count'];

// Monthly trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(marriage_date, '%Y-%m') as month, COUNT(*) as count
    FROM ($base_query) as sub 
    GROUP BY DATE_FORMAT(marriage_date, '%Y-%m') 
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

// Seasonal analysis
$seasonal_stats = $conn->query("
    SELECT 
        MONTH(marriage_date) as month,
        COUNT(*) as count
    FROM ($base_query) as sub 
    GROUP BY MONTH(marriage_date) 
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

// Recent marriages
$recent_marriages = $conn->query("$base_query ORDER BY m.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Calculate average marriages per month
$avg_per_month = $total_marriages / max(count($monthly_trends), 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marriage Statistics | VERMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --success-color: #27ae60;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #eaf6fb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
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
        <h1 class="display-5 fw-bold"><i class="fas fa-ring me-3"></i>Marriage Statistics</h1>
        <p class="lead">Comprehensive analysis of marriage registrations</p>
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
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-success mb-2">
                    <i class="fas fa-ring fa-2x"></i>
                </div>
                <h3 class="text-success"><?= number_format($total_marriages) ?></h3>
                <p class="text-muted mb-0">Total Marriages</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-primary mb-2">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
                <h3 class="text-primary"><?= number_format($avg_per_month, 1) ?></h3>
                <p class="text-muted mb-0">Avg/Month</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-warning mb-2">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <h3 class="text-warning"><?= count($monthly_trends) ?></h3>
                <p class="text-muted mb-0">Months Tracked</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2">
                    <i class="fas fa-map-marker-alt fa-2x"></i>
                </div>
                <h3 class="text-info"><?= count($zone_distribution) ?></h3>
                <p class="text-muted mb-0">Zones Covered</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-line text-success me-2"></i>Monthly Marriage Trends</h4>
                <canvas id="monthlyTrendsChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-calendar text-primary me-2"></i>Seasonal Analysis</h4>
                <canvas id="seasonalChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-bar text-warning me-2"></i>Zone-wise Distribution</h4>
                <canvas id="zoneChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-list text-info me-2"></i>Recent Marriage Registrations</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Couple</th>
                                <th>Marriage Date</th>
                                <th>Zone</th>
                                <th>Woreda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_marriages as $marriage): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($marriage['husband_name']) ?></strong> & 
                                    <strong><?= htmlspecialchars($marriage['wife_name']) ?></strong>
                                </td>
                                <td><?= date('M j, Y', strtotime($marriage['marriage_date'])) ?></td>
                                <td><?= htmlspecialchars($marriage['zone_name']) ?></td>
                                <td><?= htmlspecialchars($marriage['woreda_name']) ?></td>
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
            label: 'Marriages',
            data: <?= json_encode(array_column($monthly_trends, 'count')) ?>,
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)',
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

// Seasonal Analysis Chart
const seasonalCtx = document.getElementById('seasonalChart').getContext('2d');
new Chart(seasonalCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Marriages',
            data: <?= json_encode(array_fill(0, 12, 0)) ?>,
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

// Populate seasonal data
<?php foreach ($seasonal_stats as $stat): ?>
seasonalCtx.data.datasets[0].data[<?= $stat['month'] - 1 ?>] = <?= $stat['count'] ?>;
<?php endforeach; ?>
seasonalCtx.update();

// Zone Distribution Chart
const zoneCtx = document.getElementById('zoneChart').getContext('2d');
new Chart(zoneCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($zone_distribution, 'zone_name')) ?>,
        datasets: [{
            label: 'Marriages',
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