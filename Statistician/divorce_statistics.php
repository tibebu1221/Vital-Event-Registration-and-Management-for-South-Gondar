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

// Base query - adjusted to match your actual table structure
$base_query = "SELECT dv.*, k.kebele_name, w.woreda_name, z.zone_name 
               FROM divorce_events dv 
               JOIN kebeles k ON dv.place_of_divorce = k.id 
               JOIN woredas w ON k.woreda_id = w.id 
               JOIN zones z ON w.zone_id = z.id 
               WHERE dv.divorce_date BETWEEN '$start_date' AND '$end_date'";

if ($selected_zone !== 'all') {
    $base_query .= " AND z.zone_name = '" . $conn->real_escape_string($selected_zone) . "'";
}

// Total divorces
$total_divorces_result = $conn->query("SELECT COUNT(*) as count FROM ($base_query) as sub");
$total_divorces = $total_divorces_result ? $total_divorces_result->fetch_assoc()['count'] : 0;

// Monthly trends
$monthly_trends = [];
$monthly_result = $conn->query("
    SELECT DATE_FORMAT(divorce_date, '%Y-%m') as month, COUNT(*) as count
    FROM ($base_query) as sub 
    GROUP BY DATE_FORMAT(divorce_date, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 12
");
if ($monthly_result) {
    $monthly_trends = $monthly_result->fetch_all(MYSQLI_ASSOC);
}

// Zone-wise distribution
$zone_distribution = [];
$zone_result = $conn->query("
    SELECT zone_name, COUNT(*) as count 
    FROM ($base_query) as sub 
    GROUP BY zone_name 
    ORDER BY count DESC
");
if ($zone_result) {
    $zone_distribution = $zone_result->fetch_all(MYSQLI_ASSOC);
}

// First, let's check what columns actually exist in the divorce_events table
$table_columns = [];
$columns_result = $conn->query("SHOW COLUMNS FROM divorce_events");
if ($columns_result) {
    while ($column = $columns_result->fetch_assoc()) {
        $table_columns[] = $column['Field'];
    }
}

// Initialize variables for optional data
$reasons_stats = [];
$status_stats = [];

// Get divorce reasons statistics only if the column exists
if (in_array('divorce_reason', $table_columns)) {
    $reasons_result = $conn->query("
        SELECT divorce_reason, COUNT(*) as count 
        FROM ($base_query) as sub 
        WHERE divorce_reason IS NOT NULL AND divorce_reason != ''
        GROUP BY divorce_reason 
        ORDER BY count DESC
        LIMIT 10
    ");
    if ($reasons_result) {
        $reasons_stats = $reasons_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Status distribution only if the column exists
if (in_array('status', $table_columns)) {
    $status_result = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM ($base_query) as sub 
        WHERE status IS NOT NULL 
        GROUP BY status 
        ORDER BY count DESC
    ");
    if ($status_result) {
        $status_stats = $status_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Recent divorces
$recent_divorces = [];
$recent_result = $conn->query("$base_query ORDER BY dv.created_at DESC LIMIT 10");
if ($recent_result) {
    $recent_divorces = $recent_result->fetch_all(MYSQLI_ASSOC);
}

// Calculate divorce rate (if marriage data available)
$total_marriages_result = $conn->query("SELECT COUNT(*) as count FROM marriage_events WHERE marriage_date BETWEEN '$start_date' AND '$end_date'");
$total_marriages = $total_marriages_result ? $total_marriages_result->fetch_assoc()['count'] : 0;
$divorce_rate = $total_marriages > 0 ? round(($total_divorces / $total_marriages) * 100, 2) : 0;

// Calculate average marriage duration if possible
$avg_duration = null;
// Check if marriage_date exists in divorce_events table
if (in_array('marriage_date', $table_columns)) {
    $duration_result = $conn->query("
        SELECT AVG(DATEDIFF(divorce_date, marriage_date) / 365.25) as avg_years 
        FROM ($base_query) as sub 
        WHERE marriage_date IS NOT NULL
    ");
    if ($duration_result) {
        $avg_duration_data = $duration_result->fetch_assoc();
        $avg_duration = $avg_duration_data['avg_years'] ? round($avg_duration_data['avg_years'], 1) : null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Divorce Statistics | VERMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --warning-color: #f39c12;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #eaf6fb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="display-5 fw-bold"><i class="fas fa-file-contract me-3"></i>Divorce Statistics</h1>
        <p class="lead">Comprehensive analysis of divorce registrations</p>
        <p class="mb-0">
            <small>
                Period: <?= date('M j, Y', strtotime($start_date)) ?> - <?= date('M j, Y', strtotime($end_date)) ?>
                <?php if ($selected_zone !== 'all'): ?>
                    | Zone: <?= htmlspecialchars($selected_zone) ?>
                <?php endif; ?>
            </small>
        </p>
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
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-warning mb-2">
                    <i class="fas fa-file-contract fa-2x"></i>
                </div>
                <h3 class="text-warning"><?= number_format($total_divorces) ?></h3>
                <p class="text-muted mb-0">Total Divorces</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-danger mb-2">
                    <i class="fas fa-percentage fa-2x"></i>
                </div>
                <h3 class="text-danger"><?= $divorce_rate ?>%</h3>
                <p class="text-muted mb-0">Divorce Rate</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
                <h3 class="text-info"><?= count($monthly_trends) ?></h3>
                <p class="text-muted mb-0">Months Tracked</p>
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

    <?php if ($total_divorces > 0): ?>
        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-chart-line text-warning me-2"></i>Monthly Divorce Trends</h4>
                    <?php if (!empty($monthly_trends)): ?>
                        <canvas id="monthlyTrendsChart" height="120"></canvas>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-chart-line"></i>
                            <p>No monthly trend data available for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-chart-pie text-danger me-2"></i>Zone Distribution</h4>
                    <?php if (!empty($zone_distribution)): ?>
                        <canvas id="zonePieChart" height="120"></canvas>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-chart-pie"></i>
                            <p>No zone distribution data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <?php if (!empty($reasons_stats)): ?>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-comments text-info me-2"></i>Divorce Reasons</h4>
                    <canvas id="reasonsChart" height="200"></canvas>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="<?= !empty($reasons_stats) ? 'col-lg-6' : 'col-12' ?>">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-list text-secondary me-2"></i>Recent Divorce Registrations</h4>
                    <?php if (!empty($recent_divorces)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Couple</th>
                                        <th>Divorce Date</th>
                                        <th>Zone</th>
                                        <?php if (in_array('status', $table_columns)): ?>
                                            <th>Status</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_divorces as $divorce): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($divorce['husband_name'] ?? 'N/A') ?></strong> & 
                                            <strong><?= htmlspecialchars($divorce['wife_name'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <?= !empty($divorce['divorce_date']) ? date('M j, Y', strtotime($divorce['divorce_date'])) : 'N/A' ?>
                                        </td>
                                        <td><?= htmlspecialchars($divorce['zone_name'] ?? 'N/A') ?></td>
                                        <?php if (in_array('status', $table_columns)): ?>
                                        <td>
                                            <?php if (isset($divorce['status'])): ?>
                                                <span class="badge bg-<?= $divorce['status'] == 'Approved' ? 'success' : 'warning' ?>">
                                                    <?= $divorce['status'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-file-contract"></i>
                            <p>No recent divorce registrations found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Data Message -->
        <div class="chart-container text-center">
            <div class="no-data">
                <i class="fas fa-file-contract fa-4x mb-3"></i>
                <h4>No Divorce Data Found</h4>
                <p class="text-muted">No divorce registrations match your selected criteria.</p>
                <p class="text-muted">Try adjusting your filters or select a different time period.</p>
                <a href="?zone=all&start_date=<?= date('Y-01-01') ?>&end_date=<?= date('Y-m-t') ?>" class="btn btn-warning mt-3">
                    <i class="fas fa-calendar me-2"></i>View Current Year Data
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_divorces > 0): ?>
<script>
// Monthly Trends Chart
<?php if (!empty($monthly_trends)): ?>
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_trends, 'month')) ?>,
        datasets: [{
            label: 'Number of Divorces',
            data: <?= json_encode(array_column($monthly_trends, 'count')) ?>,
            borderColor: '#f39c12',
            backgroundColor: 'rgba(243, 156, 18, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
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
                    precision: 0
                }
            }
        }
    }
});
<?php endif; ?>

// Zone Pie Chart
<?php if (!empty($zone_distribution)): ?>
const zonePieCtx = document.getElementById('zonePieChart').getContext('2d');
new Chart(zonePieCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($zone_distribution, 'zone_name')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($zone_distribution, 'count')) ?>,
            backgroundColor: [
                '#f39c12', '#e74c3c', '#3498db', '#2ecc71', '#9b59b6',
                '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#d35400'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                }
            }
        }
    }
});
<?php endif; ?>

// Reasons Chart
<?php if (!empty($reasons_stats)): ?>
const reasonsCtx = document.getElementById('reasonsChart').getContext('2d');
new Chart(reasonsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($reasons_stats, 'divorce_reason')) ?>,
        datasets: [{
            label: 'Number of Cases',
            data: <?= json_encode(array_column($reasons_stats, 'count')) ?>,
            backgroundColor: '#3498db',
            borderColor: '#2980b9',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: {
            legend: { 
                display: false 
            }
        },
        scales: {
            x: { 
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
<?php endif; ?>

// Status Chart
<?php if (!empty($status_stats)): ?>
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($status_stats, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($status_stats, 'count')) ?>,
            backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c', '#3498db'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: {
                    padding: 20
                }
            }
        }
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>