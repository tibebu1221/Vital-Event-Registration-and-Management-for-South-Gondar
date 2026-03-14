<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'statistician') {
    header('Location: ../login.php');
    exit();
}

// Debug: Check what tables exist
$tables_result = $conn->query("SHOW TABLES LIKE '%birth%'");
$tables = $tables_result->fetch_all(MYSQLI_ASSOC);

// Get all zones for filtering
$zones = [];
$zones_res = $conn->query("SELECT * FROM zones ORDER BY zone_name");
if ($zones_res) {
    while ($zone = $zones_res->fetch_assoc()) {
        $zones[] = $zone;
    }
}

// Filter parameters with validation
$selected_zone = $_GET['zone'] ?? 'all';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Validate dates
if (!strtotime($start_date)) $start_date = date('Y-m-01');
if (!strtotime($end_date)) $end_date = date('Y-m-t');

// Ensure end date is not before start date
if (strtotime($end_date) < strtotime($start_date)) {
    $end_date = $start_date;
}

// Let's first check the structure of birth_events table
$table_structure = [];
$structure_result = $conn->query("DESCRIBE birth_events");
if ($structure_result) {
    $table_structure = $structure_result->fetch_all(MYSQLI_ASSOC);
}

// Build base query based on actual table structure
$base_query = "SELECT b.*, k.kebele_name, w.woreda_name, z.zone_name 
               FROM birth_events b 
               JOIN kebeles k ON b.place_of_birth = k.id 
               JOIN woredas w ON k.woreda_id = w.id 
               JOIN zones z ON w.zone_id = z.id 
               WHERE b.date_of_birth BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "'";

if ($selected_zone !== 'all') {
    $base_query .= " AND z.zone_name = '" . $conn->real_escape_string($selected_zone) . "'";
}

// Debug: Check if we get any data
$debug_result = $conn->query("$base_query LIMIT 5");
$debug_data = $debug_result ? $debug_result->fetch_all(MYSQLI_ASSOC) : [];

// Total births
$total_births_result = $conn->query("SELECT COUNT(*) as count FROM ($base_query) as sub");
$total_births = $total_births_result ? $total_births_result->fetch_assoc()['count'] : 0;

// Gender distribution - check what gender column is called
$gender_column = 'sex'; // default
foreach ($table_structure as $column) {
    if (in_array(strtolower($column['Field']), ['sex', 'gender', 'child_gender'])) {
        $gender_column = $column['Field'];
        break;
    }
}

$gender_stats = [];
$gender_result = $conn->query("SELECT $gender_column as sex, COUNT(*) as count FROM ($base_query) as sub GROUP BY $gender_column");
if ($gender_result) {
    $gender_stats = $gender_result->fetch_all(MYSQLI_ASSOC);
}

// Monthly trends
$monthly_trends = [];
$monthly_result = $conn->query("
    SELECT DATE_FORMAT(date_of_birth, '%Y-%m') as month, 
           COUNT(*) as count,
           SUM(CASE WHEN $gender_column = 'Male' THEN 1 ELSE 0 END) as male,
           SUM(CASE WHEN $gender_column = 'Female' THEN 1 ELSE 0 END) as female
    FROM ($base_query) as sub 
    GROUP BY DATE_FORMAT(date_of_birth, '%Y-%m') 
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

// Recent births
$recent_births = [];
// Check what timestamp column exists
$timestamp_column = 'created_at'; // default
foreach ($table_structure as $column) {
    if (in_array(strtolower($column['Field']), ['created_at', 'registration_date', 'timestamp'])) {
        $timestamp_column = $column['Field'];
        break;
    }
}

$recent_result = $conn->query("$base_query ORDER BY b.$timestamp_column DESC LIMIT 10");
if ($recent_result) {
    $recent_births = $recent_result->fetch_all(MYSQLI_ASSOC);
}

// Calculate gender counts safely
$male_count = 0;
$female_count = 0;
$unknown_gender = 0;

foreach ($gender_stats as $gender) {
    $gender_value = $gender['sex'];
    if (stripos($gender_value, 'male') !== false || $gender_value === 'M') {
        $male_count = (int)$gender['count'];
    } elseif (stripos($gender_value, 'female') !== false || $gender_value === 'F') {
        $female_count = (int)$gender['count'];
    } else {
        $unknown_gender += (int)$gender['count'];
    }
}

// Calculate percentages for display
$male_percentage = $total_births > 0 ? round(($male_count / $total_births) * 100, 1) : 0;
$female_percentage = $total_births > 0 ? round(($female_count / $total_births) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Birth Statistics | VERMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #eaf6fb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
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

        .gender-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="display-5 fw-bold"><i class="fas fa-baby me-3"></i>Birth Statistics</h1>
        <p class="lead">Comprehensive analysis of birth registrations</p>
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
    <!-- Debug Information -->
    <?php if (empty($debug_data) && $total_births == 0): ?>
    <div class="debug-info">
        <strong>Debug Info:</strong> 
        No data found with current query. 
        <?php if (!empty($table_structure)): ?>
            <br>Table structure: 
            <?php foreach ($table_structure as $col): ?>
                <?= $col['Field'] ?>(<?= $col['Type'] ?>) 
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Zone</label>
                <select name="zone" class="form-select">
                    <option value="all">All Zones</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?= htmlspecialchars($zone['zone_name']) ?>" <?= $selected_zone == $zone['zone_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($zone['zone_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-primary mb-2">
                    <i class="fas fa-baby fa-2x"></i>
                </div>
                <h3 class="text-primary"><?= number_format($total_births) ?></h3>
                <p class="text-muted mb-0">Total Births</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2">
                    <i class="fas fa-male fa-2x"></i>
                </div>
                <h3 class="text-info"><?= number_format($male_count) ?></h3>
                <p class="text-muted mb-0">Male (<?= $male_percentage ?>%)</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-danger mb-2">
                    <i class="fas fa-female fa-2x"></i>
                </div>
                <h3 class="text-danger"><?= number_format($female_count) ?></h3>
                <p class="text-muted mb-0">Female (<?= $female_percentage ?>%)</p>
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

    <!-- Charts and Data -->
    <?php if ($total_births > 0): ?>
        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-chart-line text-primary me-2"></i>Monthly Birth Trends</h4>
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
                    <h4 class="mb-4"><i class="fas fa-chart-pie text-success me-2"></i>Gender Distribution</h4>
                    <?php if ($male_count > 0 || $female_count > 0): ?>
                        <canvas id="genderChart" height="120"></canvas>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <span class="badge bg-primary gender-badge">Male: <?= $male_count ?> (<?= $male_percentage ?>%)</span>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-danger gender-badge">Female: <?= $female_count ?> (<?= $female_percentage ?>%)</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-chart-pie"></i>
                            <p>No gender distribution data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-chart-bar text-warning me-2"></i>Zone-wise Distribution</h4>
                    <?php if (!empty($zone_distribution)): ?>
                        <canvas id="zoneChart" height="200"></canvas>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-chart-bar"></i>
                            <p>No zone distribution data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4"><i class="fas fa-list text-info me-2"></i>Recent Birth Registrations</h4>
                    <?php if (!empty($recent_births)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Child Name</th>
                                        <th>Date of Birth</th>
                                        <th>Zone</th>
                                        <th>Gender</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_births as $birth): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($birth['child_name'] ?? $birth['child_first_name'] ?? 'Not Provided') ?></strong>
                                        </td>
                                        <td>
                                            <?= !empty($birth['date_of_birth']) ? date('M j, Y', strtotime($birth['date_of_birth'])) : 'Not Provided' ?>
                                        </td>
                                        <td><?= htmlspecialchars($birth['zone_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (isset($birth['sex'])): ?>
                                                <span class="badge bg-<?= $birth['sex'] == 'Male' ? 'primary' : 'danger' ?> gender-badge">
                                                    <?= $birth['sex'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary gender-badge">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-baby"></i>
                            <p>No recent birth registrations found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detailed Data Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-table text-primary me-2"></i>All Birth Records</h4>
                <div>
                    <small class="text-muted">Showing <?= min(100, $total_births) ?> of <?= number_format($total_births) ?> record(s)</small>
                    <?php if ($total_births > 100): ?>
                        <small class="text-warning ms-2"><i class="fas fa-info-circle"></i> Showing latest 100 records</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php 
            // Get all birth records for the detailed table (limited to 100 for performance)
            $all_births_result = $conn->query("$base_query ORDER BY b.date_of_birth DESC, b.$timestamp_column DESC LIMIT 100");
            $all_births = $all_births_result ? $all_births_result->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            
            <?php if (!empty($all_births)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Child Name</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                                <th>Zone</th>
                                <th>Woreda</th>
                                <th>Kebele</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_births as $index => $birth): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($birth['child_name'] ?? $birth['child_first_name'] ?? 'Not Provided') ?></strong>
                                    <?php if (empty($birth['child_name']) && empty($birth['child_first_name'])): ?>
                                        <small class="text-muted d-block">Name not provided</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($birth['date_of_birth']) ? date('M j, Y', strtotime($birth['date_of_birth'])) : 'Not Provided' ?>
                                </td>
                                <td>
                                    <?php if (isset($birth['sex'])): ?>
                                        <span class="badge bg-<?= $birth['sex'] == 'Male' ? 'primary' : 'danger' ?> gender-badge">
                                            <?= $birth['sex'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary gender-badge">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($birth['zone_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($birth['woreda_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($birth['kebele_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?= !empty($birth[$timestamp_column]) ? date('M j, Y', strtotime($birth[$timestamp_column])) : 'Not Provided' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-database"></i>
                    <p>No birth records found for the selected criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- No Data Message -->
        <div class="chart-container text-center">
            <div class="no-data">
                <i class="fas fa-baby fa-4x mb-3"></i>
                <h4>No Birth Data Found</h4>
                <p class="text-muted">No birth registrations match your selected criteria.</p>
                <p class="text-muted">Try adjusting your filters or select a different time period.</p>
                
                <!-- Test different date ranges -->
                <div class="mt-3">
                    <a href="?zone=all&start_date=<?= date('Y-01-01') ?>&end_date=<?= date('Y-m-t') ?>" class="btn btn-primary me-2">
                        <i class="fas fa-calendar me-2"></i>Current Year
                    </a>
                    <a href="?zone=all&start_date=2020-01-01&end_date=<?= date('Y-m-t') ?>" class="btn btn-warning">
                        <i class="fas fa-history me-2"></i>All Time
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_births > 0): ?>
<script>
// Monthly Trends Chart
<?php if (!empty($monthly_trends)): ?>
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($month) {
            return date('M Y', strtotime($month['month'] . '-01'));
        }, $monthly_trends)) ?>,
        datasets: [
            {
                label: 'Total Births',
                data: <?= json_encode(array_column($monthly_trends, 'count')) ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
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

// Gender Distribution Chart
<?php if ($male_count > 0 || $female_count > 0): ?>
const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{
            data: [<?= $male_count ?>, <?= $female_count ?>],
            backgroundColor: ['#3498db', '#e74c3c'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

// Zone Distribution Chart
<?php if (!empty($zone_distribution)): ?>
const zoneCtx = document.getElementById('zoneChart').getContext('2d');
new Chart(zoneCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($zone_distribution, 'zone_name')) ?>,
        datasets: [{
            label: 'Number of Births',
            data: <?= json_encode(array_column($zone_distribution, 'count')) ?>,
            backgroundColor: '#f39c12',
            borderColor: '#d35400',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                display: false 
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
</script>
<?php endif; ?>

</body>
</html>