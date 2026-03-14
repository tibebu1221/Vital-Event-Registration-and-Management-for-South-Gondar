<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'statistician') {
    header('Location: ../login.php');
    exit();
}

// Safe count function with error handling
function safeCount($conn, $query, $params = []) {
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return intval($row['count'] ?? 0);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

// Get overall statistics
$total_births = safeCount($conn, "SELECT COUNT(*) as count FROM birth_events");
$total_deaths = safeCount($conn, "SELECT COUNT(*) as count FROM death_events");
$total_marriages = safeCount($conn, "SELECT COUNT(*) as count FROM marriage_events");
$total_divorces = safeCount($conn, "SELECT COUNT(*) as count FROM divorce_events");
$total_citizens = safeCount($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'citizen'");
$total_zones = safeCount($conn, "SELECT COUNT(*) as count FROM zones");
$total_woredas = safeCount($conn, "SELECT COUNT(*) as count FROM woredas");
$total_kebeles = safeCount($conn, "SELECT COUNT(*) as count FROM kebeles");

// Yearly trends
$yearly_trends = [];
$stmt = $conn->prepare("
    SELECT 
        YEAR(created_at) as year,
        SUM(CASE WHEN type = 'birth' THEN 1 ELSE 0 END) as births,
        SUM(CASE WHEN type = 'death' THEN 1 ELSE 0 END) as deaths,
        SUM(CASE WHEN type = 'marriage' THEN 1 ELSE 0 END) as marriages,
        SUM(CASE WHEN type = 'divorce' THEN 1 ELSE 0 END) as divorces
    FROM (
        SELECT created_at, 'birth' as type FROM birth_events
        UNION ALL SELECT created_at, 'death' FROM death_events
        UNION ALL SELECT created_at, 'marriage' FROM marriage_events
        UNION ALL SELECT created_at, 'divorce' FROM divorce_events
    ) as all_events
    GROUP BY YEAR(created_at)
    ORDER BY year DESC
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
$yearly_trends = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Zone performance
$zone_performance = [];
$stmt = $conn->prepare("SELECT id, zone_name FROM zones ORDER BY zone_name");
$stmt->execute();
$zones_res = $stmt->get_result();
while ($zone = $zones_res->fetch_assoc()) {
    $zone_id = $zone['id'];
    $zone_performance[$zone['zone_name']] = [
        'births' => safeCount($conn, "SELECT COUNT(*) as count FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id JOIN woredas w ON k.woreda_id = w.id WHERE w.zone_id = ?", [$zone_id]),
        'deaths' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events d JOIN kebeles k ON d.place_of_death = k.id JOIN woredas w ON k.woreda_id = w.id WHERE w.zone_id = ?", [$zone_id]),
        'marriages' => safeCount($conn, "SELECT COUNT(*) as count FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id JOIN woredas w ON k.woreda_id = w.id WHERE w.zone_id = ?", [$zone_id]),
        'divorces' => safeCount($conn, "SELECT COUNT(*) as count FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id JOIN woredas w ON k.woreda_id = w.id WHERE w.zone_id = ?", [$zone_id]),
        'woredas' => safeCount($conn, "SELECT COUNT(*) as count FROM woredas WHERE zone_id = ?", [$zone_id])
    ];
}
$stmt->close();

// Monthly trends for current year
$current_year = date('Y');
$monthly_trends = [];
for ($i = 1; $i <= 12; $i++) {
    $month = sprintf("%04d-%02d", $current_year, $i);
    $month_name = date('M', strtotime($month));
    $monthly_trends[$month_name] = [
        'births' => safeCount($conn, "SELECT COUNT(*) as count FROM birth_events WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]),
        'deaths' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]),
        'marriages' => safeCount($conn, "SELECT COUNT(*) as count FROM marriage_events WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]),
        'divorces' => safeCount($conn, "SELECT COUNT(*) as count FROM divorce_events WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
    ];
}

// Gender distribution from birth events
$gender_distribution = [
    'Male' => safeCount($conn, "SELECT COUNT(*) as count FROM birth_events WHERE sex = 'Male'"),
    'Female' => safeCount($conn, "SELECT COUNT(*) as count FROM birth_events WHERE sex = 'Female'")
];

// Age distribution from death events
$age_distribution = [
    '0-18' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events WHERE age <= 18"),
    '19-35' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events WHERE age BETWEEN 19 AND 35"),
    '36-60' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events WHERE age BETWEEN 36 AND 60"),
    '60+' => safeCount($conn, "SELECT COUNT(*) as count FROM death_events WHERE age > 60")
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Analytics Dashboard | VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #6f42c1;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            --gradient-primary: linear-gradient(135deg, #6f42c1 0%, #8e44ad 100%);
            --gradient-success: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --gradient-danger: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            --gradient-warning: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-header {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            opacity: 0.3;
        }

        .stat-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid transparent;
        }

        .stat-card.births { border-bottom-color: var(--info-color); }
        .stat-card.marriages { border-bottom-color: var(--success-color); }
        .stat-card.deaths { border-bottom-color: var(--danger-color); }
        .stat-card.divorces { border-bottom-color: var(--warning-color); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .chart-container:hover {
            box-shadow: var(--hover-shadow);
        }

        .section-header {
            border-left: 5px solid var(--secondary-color);
            padding-left: 1rem;
            margin: 2rem 0 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .analytics-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 1rem;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            height: 100%;
        }

        .chart-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--hover-shadow);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .chart-title i {
            margin-right: 0.5rem;
            color: var(--secondary-color);
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .btn-analytics {
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-analytics:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            color: white;
        }

        .progress-thin {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
        }

        .progress-bar {
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(111, 66, 193, 0.05);
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
            }
            .dashboard-header, .filter-section, .btn-analytics, .no-print {
                display: none !important;
            }
            .stat-card, .chart-card, .chart-container {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
            }
            .section-header {
                border-left: none;
                padding-left: 0;
                margin: 1rem 0;
            }
            .chart-canvas {
                max-width: 100%;
                height: auto !important;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }
            .stat-icon {
                font-size: 2rem;
            }
            .chart-container {
                padding: 1rem;
            }
            .dashboard-header {
                padding: 2rem 0;
            }
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .section-header {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 1rem;
            }
            .stat-number {
                font-size: 1.75rem;
            }
            .chart-card {
                padding: 1rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5a359a;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="fas fa-chart-line me-3"></i>Advanced Analytics Dashboard
                </h1>
                <p class="lead mb-0">Comprehensive Insights for Vital Events Registration</p>
                <p class="text-white-50 mt-2">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    Covering <?= number_format($total_zones) ?> Zones, <?= number_format($total_woredas) ?> Woredas, and <?= number_format($total_kebeles) ?> Kebeles
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Filter Section -->
    <div class="filter-section no-print">
        <h5 class="section-header mb-4"><i class="fas fa-filter me-2"></i>Data Filters</h5>
        <form id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="zoneFilter" class="form-label fw-semibold">Select Zone</label>
                <select id="zoneFilter" class="form-select">
                    <option value="">All Zones</option>
                    <?php foreach ($zone_performance as $zone_name => $data): ?>
                        <option value="<?= htmlspecialchars($zone_name) ?>"><?= htmlspecialchars($zone_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="yearFilter" class="form-label fw-semibold">Select Year</label>
                <select id="yearFilter" class="form-select">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $current_year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-analytics me-2">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Key Performance Indicators -->
    <h3 class="section-header">
        <i class="fas fa-tachometer-alt me-2"></i>Key Performance Indicators
        <span class="analytics-badge">Real-time Data</span>
    </h3>
    <div class="row mb-5 g-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card births">
                <div class="stat-icon text-info">
                    <i class="fas fa-baby"></i>
                </div>
                <div class="stat-number"><?= number_format($total_births) ?></div>
                <div class="text-muted fw-bold">Total Births</div>
                <small class="text-success fw-semibold">
                    <i class="fas fa-arrow-up me-1"></i>
                    <?= $total_citizens > 0 ? round(($total_births / $total_citizens) * 1000, 2) : 0 ?> per 1000
                </small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card marriages">
                <div class="stat-icon text-success">
                    <i class="fas fa-ring"></i>
                </div>
                <div class="stat-number"><?= number_format($total_marriages) ?></div>
                <div class="text-muted fw-bold">Total Marriages</div>
                <small class="text-info fw-semibold">
                    <i class="fas fa-chart-line me-1"></i>
                    <?= $total_citizens > 0 ? round(($total_marriages / $total_citizens) * 1000, 2) : 0 ?> per 1000
                </small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card deaths">
                <div class="stat-icon text-danger">
                    <i class="fas fa-book-dead"></i>
                </div>
                <div class="stat-number"><?= number_format($total_deaths) ?></div>
                <div class="text-muted fw-bold">Total Deaths</div>
                <small class="text-warning fw-semibold">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <?= $total_citizens > 0 ? round(($total_deaths / $total_citizens) * 1000, 2) : 0 ?> per 1000
                </small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card divorces">
                <div class="stat-icon text-warning">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-number"><?= number_format($total_divorces) ?></div>
                <div class="text-muted fw-bold">Total Divorces</div>
                <small class="text-secondary fw-semibold">
                    <i class="fas fa-balance-scale me-1"></i>
                    <?= $total_marriages > 0 ? round(($total_divorces / $total_marriages) * 100, 1) : 0 ?>% rate
                </small>
            </div>
        </div>
    </div>

    <!-- Zone Performance -->
    <h3 class="section-header">
        <i class="fas fa-map-marked-alt me-2"></i>Zone Performance Analysis
        <span class="analytics-badge">Regional Comparison</span>
    </h3>
    <div class="chart-container mb-5">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th><i class="fas fa-map me-2"></i>Zone</th>
                        <th><i class="fas fa-baby me-2"></i>Births</th>
                        <th><i class="fas fa-ring me-2"></i>Marriages</th>
                        <th><i class="fas fa-book-dead me-2"></i>Deaths</th>
                        <th><i class="fas fa-file-contract me-2"></i>Divorces</th>
                        <th><i class="fas fa-layer-group me-2"></i>Woredas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zone_performance as $zone_name => $data): ?>
                    <tr>
                        <td><strong class="text-primary"><?= htmlspecialchars($zone_name) ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2"><?= number_format($data['births']) ?></span>
                                <small class="text-muted">(<?= $total_births > 0 ? round(($data['births'] / $total_births) * 100, 1) : 0 ?>%)</small>
                            </div>
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar" style="width: <?= $total_births > 0 ? ($data['births'] / $total_births) * 100 : 0 ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2"><?= number_format($data['marriages']) ?></span>
                                <small class="text-muted">(<?= $total_marriages > 0 ? round(($data['marriages'] / $total_marriages) * 100, 1) : 0 ?>%)</small>
                            </div>
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar" style="width: <?= $total_marriages > 0 ? ($data['marriages'] / $total_marriages) * 100 : 0 ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2"><?= number_format($data['deaths']) ?></span>
                                <small class="text-muted">(<?= $total_deaths > 0 ? round(($data['deaths'] / $total_deaths) * 100, 1) : 0 ?>%)</small>
                            </div>
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar" style="width: <?= $total_deaths > 0 ? ($data['deaths'] / $total_deaths) * 100 : 0 ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2"><?= number_format($data['divorces']) ?></span>
                                <small class="text-muted">(<?= $total_divorces > 0 ? round(($data['divorces'] / $total_divorces) * 100, 1) : 0 ?>%)</small>
                            </div>
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar" style="width: <?= $total_divorces > 0 ? ($data['divorces'] / $total_divorces) * 100 : 0 ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary fs-6"><?= number_format($data['woredas']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($zone_performance)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <h5>No zone data available</h5>
                            <p class="mb-0">Zone performance data will appear here once available</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Section -->
    <h3 class="section-header">
        <i class="fas fa-chart-bar me-2"></i>Statistical Visualizations
        <span class="analytics-badge">Interactive Charts</span>
    </h3>
    <div class="chart-grid">
        <!-- Monthly Trends -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-calendar-alt"></i>
                Monthly Trends - <?= $current_year ?>
            </div>
            <div style="height: 300px;">
                <canvas id="monthlyTrendsChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <!-- Gender Distribution -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-venus-mars"></i>
                Birth Gender Distribution
            </div>
            <div style="height: 300px;">
                <canvas id="genderDistributionChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <!-- Yearly Comparison -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-chart-line"></i>
                5-Year Trend Analysis
            </div>
            <div style="height: 300px;">
                <canvas id="yearlyTrendsChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <!-- Age Distribution -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-user-clock"></i>
                Age Distribution at Death
            </div>
            <div style="height: 300px;">
                <canvas id="ageDistributionChart" class="chart-canvas"></canvas>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Monthly Trends Chart - Line Chart
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($monthly_trends)) ?>,
        datasets: [
            {
                label: 'Births',
                data: <?= json_encode(array_column($monthly_trends, 'births')) ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Marriages',
                data: <?= json_encode(array_column($monthly_trends, 'marriages')) ?>,
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderColor: 'rgba(39, 174, 96, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Deaths',
                data: <?= json_encode(array_column($monthly_trends, 'deaths')) ?>,
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderColor: 'rgba(231, 76, 60, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Divorces',
                data: <?= json_encode(array_column($monthly_trends, 'divorces')) ?>,
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                borderColor: 'rgba(243, 156, 18, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { 
                    font: { size: 12 },
                    usePointStyle: true
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Events',
                    font: { weight: 'bold' }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Gender Distribution Chart - Doughnut Chart
const genderCtx = document.getElementById('genderDistributionChart').getContext('2d');
const genderChart = new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{
            data: [<?= $gender_distribution['Male'] ?>, <?= $gender_distribution['Female'] ?>],
            backgroundColor: [
                'rgba(52, 152, 219, 0.8)',
                'rgba(231, 76, 60, 0.8)'
            ],
            borderColor: [
                'rgba(52, 152, 219, 1)',
                'rgba(231, 76, 60, 1)'
            ],
            borderWidth: 2,
            hoverOffset: 15
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { 
                    font: { size: 12 },
                    padding: 20
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '60%'
    }
});

// Yearly Trends Chart - Bar Chart
const yearlyCtx = document.getElementById('yearlyTrendsChart').getContext('2d');
const yearlyChart = new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($yearly_trends, 'year')) ?>,
        datasets: [
            {
                label: 'Births',
                data: <?= json_encode(array_column($yearly_trends, 'births')) ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.8)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            },
            {
                label: 'Marriages',
                data: <?= json_encode(array_column($yearly_trends, 'marriages')) ?>,
                backgroundColor: 'rgba(39, 174, 96, 0.8)',
                borderColor: 'rgba(39, 174, 96, 1)',
                borderWidth: 1
            },
            {
                label: 'Deaths',
                data: <?= json_encode(array_column($yearly_trends, 'deaths')) ?>,
                backgroundColor: 'rgba(231, 76, 60, 0.8)',
                borderColor: 'rgba(231, 76, 60, 1)',
                borderWidth: 1
            },
            {
                label: 'Divorces',
                data: <?= json_encode(array_column($yearly_trends, 'divorces')) ?>,
                backgroundColor: 'rgba(243, 156, 18, 0.8)',
                borderColor: 'rgba(243, 156, 18, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { 
                    font: { size: 12 },
                    usePointStyle: true
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Events',
                    font: { weight: 'bold' }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            }
        }
    }
});

// Age Distribution Chart - Bar Chart
const ageCtx = document.getElementById('ageDistributionChart').getContext('2d');
const ageChart = new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: ['0-18 Years', '19-35 Years', '36-60 Years', '60+ Years'],
        datasets: [{
            label: 'Number of Deaths',
            data: [<?= $age_distribution['0-18'] ?>, <?= $age_distribution['19-35'] ?>, <?= $age_distribution['36-60'] ?>, <?= $age_distribution['60+'] ?>],
            backgroundColor: [
                'rgba(52, 152, 219, 0.8)',
                'rgba(39, 174, 96, 0.8)',
                'rgba(243, 156, 18, 0.8)',
                'rgba(231, 76, 60, 0.8)'
            ],
            borderColor: [
                'rgba(52, 152, 219, 1)',
                'rgba(39, 174, 96, 1)',
                'rgba(243, 156, 18, 1)',
                'rgba(231, 76, 60, 1)'
            ],
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                        return `${context.parsed} deaths (${percentage}%)`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Deaths',
                    font: { weight: 'bold' }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Filter Form Submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const zone = document.getElementById('zoneFilter').value;
    const year = document.getElementById('yearFilter').value;
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Filtering...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual implementation)
    setTimeout(() => {
        alert(`Filtering for Zone: ${zone || 'All'}, Year: ${year}. (AJAX implementation required)`);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 1000);
});

// Export to CSV function
function exportToCSV() {
    const data = [
        ['Metric', 'Value'],
        ['Total Births', <?= $total_births ?>],
        ['Total Marriages', <?= $total_marriages ?>],
        ['Total Deaths', <?= $total_deaths ?>],
        ['Total Divorces', <?= $total_divorces ?>],
        ['Total Citizens', <?= $total_citizens ?>],
        ['Total Zones', <?= $total_zones ?>],
        ['Total Woredas', <?= $total_woredas ?>],
        ['Total Kebeles', <?= $total_kebeles ?>],
        ['', ''],
        ['Zone Performance', '', '', '', '', ''],
        ['Zone', 'Births', 'Marriages', 'Deaths', 'Divorces', 'Woredas'],
        <?php foreach ($zone_performance as $zone_name => $data): ?>
        ['<?= addslashes($zone_name) ?>', <?= $data['births'] ?>, <?= $data['marriages'] ?>, <?= $data['deaths'] ?>, <?= $data['divorces'] ?>, <?= $data['woredas'] ?>],
        <?php endforeach; ?>
    ];
    
    const csv = data.map(row => 
        row.map(cell => `"${cell}"`).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `analytics_report_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation to charts
    const canvases = document.querySelectorAll('.chart-canvas');
    canvases.forEach(canvas => {
        canvas.style.opacity = '0';
        setTimeout(() => {
            canvas.style.transition = 'opacity 0.5s ease';
            canvas.style.opacity = '1';
        }, 100);
    });
});
</script>
</body>
</html>