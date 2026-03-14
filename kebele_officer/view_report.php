<?php
// Kebele Officer: View Report
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

// Get kebele ID for logged-in officer
$user_id = intval($_SESSION['user_id']);
$kebele_row = $conn->query(
    "SELECT id, kebele_name FROM kebeles WHERE kebele_officer_id = $user_id"
)->fetch_assoc();
$kebele_id = $kebele_row ? $kebele_row['id'] : 0;
$kebele_name = $kebele_row ? $kebele_row['kebele_name'] : '';

// Fetch summary counts for the kebele
// Births
$birth_count = $conn->query("SELECT COUNT(*) AS total FROM birth_events WHERE place_of_birth = $kebele_id")->fetch_assoc()['total'] ?? 0;
$birth_approved = $conn->query("SELECT COUNT(*) AS total FROM birth_events WHERE place_of_birth = $kebele_id AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$birth_pending = $conn->query("SELECT COUNT(*) AS total FROM birth_events WHERE place_of_birth = $kebele_id AND status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$birth_paid = $conn->query("SELECT COUNT(*) AS total FROM birth_events WHERE place_of_birth = $kebele_id AND status = 'Paid'")->fetch_assoc()['total'] ?? 0;

// Fetch all birth records for printable table
$birth_records = $conn->query("
    SELECT child_name, father_full_name, mother_full_name, date_of_birth, sex, status, created_at 
    FROM birth_events 
    WHERE place_of_birth = $kebele_id 
    ORDER BY created_at DESC
");

// Marriages
$marriage_count = $conn->query("SELECT COUNT(*) AS total FROM marriage_events WHERE place_of_marriage = $kebele_id")->fetch_assoc()['total'] ?? 0;
$marriage_approved = $conn->query("SELECT COUNT(*) AS total FROM marriage_events WHERE place_of_marriage = $kebele_id AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$marriage_pending = $conn->query("SELECT COUNT(*) AS total FROM marriage_events WHERE place_of_marriage = $kebele_id AND status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$marriage_paid = $conn->query("SELECT COUNT(*) AS total FROM marriage_events WHERE place_of_marriage = $kebele_id AND status = 'Paid'")->fetch_assoc()['total'] ?? 0;

// Fetch all marriage records for printable table
$marriage_records = $conn->query("
    SELECT husband_name, wife_name, marriage_date, witness_1, witness_2, status, created_at 
    FROM marriage_events 
    WHERE place_of_marriage = $kebele_id 
    ORDER BY created_at DESC
");

// Deaths
$death_count = $conn->query("SELECT COUNT(*) AS total FROM death_events WHERE place_of_death = $kebele_id")->fetch_assoc()['total'] ?? 0;
$death_approved = $conn->query("SELECT COUNT(*) AS total FROM death_events WHERE place_of_death = $kebele_id AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$death_pending = $conn->query("SELECT COUNT(*) AS total FROM death_events WHERE place_of_death = $kebele_id AND status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$death_paid = $conn->query("SELECT COUNT(*) AS total FROM death_events WHERE place_of_death = $kebele_id AND status = 'Paid'")->fetch_assoc()['total'] ?? 0;

// Fetch all death records for printable table
$death_records = $conn->query("
    SELECT deceased_name, date_of_death, age, cause_of_death, sex, status, created_at 
    FROM death_events 
    WHERE place_of_death = $kebele_id 
    ORDER BY created_at DESC
");

// Divorces
$divorce_count = $conn->query("SELECT COUNT(*) AS total FROM divorce_events WHERE place_of_divorce = $kebele_id")->fetch_assoc()['total'] ?? 0;
$divorce_approved = $conn->query("SELECT COUNT(*) AS total FROM divorce_events WHERE place_of_divorce = $kebele_id AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$divorce_pending = $conn->query("SELECT COUNT(*) AS total FROM divorce_events WHERE place_of_divorce = $kebele_id AND status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$divorce_paid = $conn->query("SELECT COUNT(*) AS total FROM divorce_events WHERE place_of_divorce = $kebele_id AND status = 'Paid'")->fetch_assoc()['total'] ?? 0;

// Fetch all divorce records for printable table
$divorce_records = $conn->query("
    SELECT husband_name, wife_name, divorce_date, witness_1, witness_2, status, created_at 
    FROM divorce_events 
    WHERE place_of_divorce = $kebele_id 
    ORDER BY created_at DESC
");

// Monthly data for charts
$current_year = date('Y');
$monthly_data = [];

for ($month = 1; $month <= 12; $month++) {
    $month_start = date('Y-m-01', strtotime("$current_year-$month-01"));
    $month_end = date('Y-m-t', strtotime("$current_year-$month-01"));
    
    // Births
    $monthly_births = $conn->query("
        SELECT COUNT(*) as count FROM birth_events 
        WHERE place_of_birth = $kebele_id 
        AND created_at BETWEEN '$month_start' AND '$month_end'
    ")->fetch_assoc()['count'] ?? 0;
    
    // Marriages
    $monthly_marriages = $conn->query("
        SELECT COUNT(*) as count FROM marriage_events 
        WHERE place_of_marriage = $kebele_id 
        AND created_at BETWEEN '$month_start' AND '$month_end'
    ")->fetch_assoc()['count'] ?? 0;
    
    // Deaths
    $monthly_deaths = $conn->query("
        SELECT COUNT(*) as count FROM death_events 
        WHERE place_of_death = $kebele_id 
        AND created_at BETWEEN '$month_start' AND '$month_end'
    ")->fetch_assoc()['count'] ?? 0;
    
    // Divorces
    $monthly_divorces = $conn->query("
        SELECT COUNT(*) as count FROM divorce_events 
        WHERE place_of_divorce = $kebele_id 
        AND created_at BETWEEN '$month_start' AND '$month_end'
    ")->fetch_assoc()['count'] ?? 0;
    
    $monthly_data[] = [
        'month' => date('M', strtotime("$current_year-$month-01")),
        'births' => $monthly_births,
        'marriages' => $monthly_marriages,
        'deaths' => $monthly_deaths,
        'divorces' => $monthly_divorces
    ];
}

// Fetch last 10 events for table display
$recent_events = [];
foreach ([
    ['birth_events', 'Birth', 'child_name', 'date_of_birth', 'status'],
    ['marriage_events', 'Marriage', 'husband_name', 'marriage_date', 'status'],
    ['death_events', 'Death', 'deceased_name', 'date_of_death', 'status'],
    ['divorce_events', 'Divorce', 'husband_name', 'divorce_date', 'status']
] as $event) {
    [$table, $etype, $name_col, $date_col, $status_col] = $event;
    $events = $conn->query("
        SELECT id, '$etype' AS event_type, $name_col AS main_name, $date_col AS event_date, $status_col AS status, created_at
        FROM $table
        WHERE place_of_" . strtolower($etype) . " = $kebele_id
        ORDER BY created_at DESC
        LIMIT 10
    ");
    while ($row = $events->fetch_assoc()) $recent_events[] = $row;
}
// Sort recent_events by created_at DESC
usort($recent_events, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
$recent_events = array_slice($recent_events, 0, 10);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Vital Events Report - <?= htmlspecialchars($kebele_name) ?></title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gradient-primary: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --gradient-secondary: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: var(--gradient-secondary);
            color: white;
            border: none;
            padding: 18px 25px;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 25px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .status-paid {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .status-approved {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success-color);
        }

        .event-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-birth {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .badge-marriage {
            background: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }

        .badge-death {
            background: rgba(149, 165, 166, 0.2);
            color: #95a5a6;
        }

        .badge-divorce {
            background: rgba(231, 76, 60, 0.2);
            color: var(--accent-color);
        }

        .btn-print {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-print:hover {
            background: #219653;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 83, 0.3);
        }

        .print-only {
            display: none;
        }

        /* Print Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            
            body * {
                visibility: hidden;
                background: white !important;
                color: black !important;
            }
            
            .print-section, .print-section * {
                visibility: visible;
            }
            
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white !important;
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: block !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin-bottom: 15px;
            }
            
            .table {
                font-size: 10px;
                page-break-inside: avoid;
            }
            
            .table th {
                background-color: #f8f9fa !important;
                color: black !important;
                font-weight: bold;
                border: 1px solid #dee2e6;
            }
            
            .table td {
                border: 1px solid #dee2e6;
                padding: 6px 8px;
            }
            
            .report-header {
                border-bottom: 2px solid #333;
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            
            .badge {
                border: 1px solid #333;
                background: white !important;
                color: black !important;
                font-weight: bold;
            }
            
            h1, h2, h3, h4, h5, h6 {
                color: black !important;
                margin-bottom: 10px;
            }
            
            .text-muted {
                color: #666 !important;
            }
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 15px 12px;
        }

        .table td {
            padding: 15px 12px;
            vertical-align: middle;
        }

        .print-table {
            font-size: 11px;
        }

        .print-table th {
            background-color: #f8f9fa !important;
            color: #000 !important;
            font-weight: bold;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-chart-bar me-2"></i>Vital Events Report
                </h1>
                <p class="dashboard-subtitle">
                    Comprehensive analytics and statistics for <?= htmlspecialchars($kebele_name) ?> Kebele
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="card bg-light" style="display: inline-block; padding: 15px 20px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark">Year <?= $current_year ?></h5>
                            <h3 class="mb-0 text-primary">Report</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Print Buttons -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-print" onclick="printReport('all')">
                    <i class="fas fa-print me-2"></i>Print Full Report
                </button>
                <button class="btn btn-print" onclick="printReport('birth')">
                    <i class="fas fa-baby me-2"></i>Print Birth Report
                </button>
                <button class="btn btn-print" onclick="printReport('marriage')">
                    <i class="fas fa-ring me-2"></i>Print Marriage Report
                </button>
                <button class="btn btn-print" onclick="printReport('death')">
                    <i class="fas fa-book-dead me-2"></i>Print Death Report
                </button>
                <button class="btn btn-print" onclick="printReport('divorce')">
                    <i class="fas fa-file-contract me-2"></i>Print Divorce Report
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid no-print">
        <div class="stat-card">
            <div class="stat-icon text-primary">
                <i class="fas fa-baby"></i>
            </div>
            <div class="stat-number text-primary"><?= $birth_count ?></div>
            <div class="stat-label">Total Births</div>
            <div class="mt-2">
                <small class="text-success">Approved: <?= $birth_approved ?></small> |
                <small class="text-primary">Paid: <?= $birth_paid ?></small> |
                <small class="text-warning">Pending: <?= $birth_pending ?></small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #9b59b6;">
                <i class="fas fa-ring"></i>
            </div>
            <div class="stat-number" style="color: #9b59b6;"><?= $marriage_count ?></div>
            <div class="stat-label">Total Marriages</div>
            <div class="mt-2">
                <small class="text-success">Approved: <?= $marriage_approved ?></small> |
                <small class="text-primary">Paid: <?= $marriage_paid ?></small> |
                <small class="text-warning">Pending: <?= $marriage_pending ?></small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #95a5a6;">
                <i class="fas fa-book-dead"></i>
            </div>
            <div class="stat-number" style="color: #95a5a6;"><?= $death_count ?></div>
            <div class="stat-label">Total Deaths</div>
            <div class="mt-2">
                <small class="text-success">Approved: <?= $death_approved ?></small> |
                <small class="text-primary">Paid: <?= $death_paid ?></small> |
                <small class="text-warning">Pending: <?= $death_pending ?></small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon text-danger">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="stat-number text-danger"><?= $divorce_count ?></div>
            <div class="stat-label">Total Divorces</div>
            <div class="mt-2">
                <small class="text-success">Approved: <?= $divorce_approved ?></small> |
                <small class="text-primary">Paid: <?= $divorce_paid ?></small> |
                <small class="text-warning">Pending: <?= $divorce_pending ?></small>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-5 no-print">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Monthly Events Trend - <?= $current_year ?>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Events Distribution
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events Table -->
    <div class="card no-print">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-2"></i>Recent Event Registrations
            </div>
            <small class="text-muted">Last 10 events</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event Type</th>
                            <th>Name / Main Info</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($recent_events as $ev): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if ($ev['event_type'] === 'Birth'): ?>
                                    <span class="event-badge badge-birth">
                                        <i class="fas fa-baby me-1"></i>Birth
                                    </span>
                                <?php elseif ($ev['event_type'] === 'Marriage'): ?>
                                    <span class="event-badge badge-marriage">
                                        <i class="fas fa-ring me-1"></i>Marriage
                                    </span>
                                <?php elseif ($ev['event_type'] === 'Death'): ?>
                                    <span class="event-badge badge-death">
                                        <i class="fas fa-book-dead me-1"></i>Death
                                    </span>
                                <?php elseif ($ev['event_type'] === 'Divorce'): ?>
                                    <span class="event-badge badge-divorce">
                                        <i class="fas fa-file-contract me-1"></i>Divorce
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($ev['main_name']) ?></strong></td>
                            <td><?= date('M j, Y', strtotime($ev['event_date'])) ?></td>
                            <td>
                                <?php if ($ev['status'] === 'Pending'): ?>
                                    <span class="status-badge status-pending"><?= $ev['status'] ?></span>
                                <?php elseif ($ev['status'] === 'Paid'): ?>
                                    <span class="status-badge status-paid"><?= $ev['status'] ?></span>
                                <?php elseif ($ev['status'] === 'Approved'): ?>
                                    <span class="status-badge status-approved"><?= $ev['status'] ?></span>
                                <?php else: ?>
                                    <span class="status-badge"><?= $ev['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($ev['created_at'])) ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_events)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-3 d-block"></i>
                                No recent events registered in your kebele.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print Sections (Hidden by default) -->
<!-- Birth Report -->
<div class="print-section" id="printBirth" style="display: none;">
    <div class="container-fluid">
        <div class="report-header">
            <h2>BIRTH EVENTS REGISTER</h2>
            <h4><?= htmlspecialchars($kebele_name) ?> Kebele</h4>
            <p class="text-muted mb-0">Report Period: <?= date('F j, Y') ?></p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Child Name</th>
                        <th>Father Name</th>
                        <th>Mother Name</th>
                        <th>Date of Birth</th>
                        <th>Sex</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($birth_records && $birth_records->num_rows > 0): ?>
                        <?php $i = 1; while($row = $birth_records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['child_name']) ?></td>
                            <td><?= htmlspecialchars($row['father_full_name']) ?></td>
                            <td><?= htmlspecialchars($row['mother_full_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['date_of_birth'])) ?></td>
                            <td><?= htmlspecialchars($row['sex']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No birth records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-muted">
            <small>Generated by VERMS on <?= date('F j, Y \a\t g:i A') ?></small>
        </div>
    </div>
</div>

<!-- Marriage Report -->
<div class="print-section" id="printMarriage" style="display: none;">
    <div class="container-fluid">
        <div class="report-header">
            <h2>MARRIAGE EVENTS REGISTER</h2>
            <h4><?= htmlspecialchars($kebele_name) ?> Kebele</h4>
            <p class="text-muted mb-0">Report Period: <?= date('F j, Y') ?></p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Husband Name</th>
                        <th>Wife Name</th>
                        <th>Marriage Date</th>
                        <th>Witness 1</th>
                        <th>Witness 2</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($marriage_records && $marriage_records->num_rows > 0): ?>
                        <?php $i = 1; while($row = $marriage_records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['husband_name']) ?></td>
                            <td><?= htmlspecialchars($row['wife_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['marriage_date'])) ?></td>
                            <td><?= htmlspecialchars($row['witness_1']) ?></td>
                            <td><?= htmlspecialchars($row['witness_2']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No marriage records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-muted">
            <small>Generated by VERMS on <?= date('F j, Y \a\t g:i A') ?></small>
        </div>
    </div>
</div>

<!-- Death Report -->
<div class="print-section" id="printDeath" style="display: none;">
    <div class="container-fluid">
        <div class="report-header">
            <h2>DEATH EVENTS REGISTER</h2>
            <h4><?= htmlspecialchars($kebele_name) ?> Kebele</h4>
            <p class="text-muted mb-0">Report Period: <?= date('F j, Y') ?></p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Deceased Name</th>
                        <th>Date of Death</th>
                        <th>Age</th>
                        <th>Cause of Death</th>
                        <th>Sex</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($death_records && $death_records->num_rows > 0): ?>
                        <?php $i = 1; while($row = $death_records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['deceased_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['date_of_death'])) ?></td>
                            <td><?= htmlspecialchars($row['age']) ?></td>
                            <td><?= htmlspecialchars($row['cause_of_death']) ?></td>
                            <td><?= htmlspecialchars($row['sex']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No death records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-muted">
            <small>Generated by VERMS on <?= date('F j, Y \a\t g:i A') ?></small>
        </div>
    </div>
</div>

<!-- Divorce Report -->
<div class="print-section" id="printDivorce" style="display: none;">
    <div class="container-fluid">
        <div class="report-header">
            <h2>DIVORCE EVENTS REGISTER</h2>
            <h4><?= htmlspecialchars($kebele_name) ?> Kebele</h4>
            <p class="text-muted mb-0">Report Period: <?= date('F j, Y') ?></p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Husband Name</th>
                        <th>Wife Name</th>
                        <th>Divorce Date</th>
                        <th>Witness 1</th>
                        <th>Witness 2</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($divorce_records && $divorce_records->num_rows > 0): ?>
                        <?php $i = 1; while($row = $divorce_records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['husband_name']) ?></td>
                            <td><?= htmlspecialchars($row['wife_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['divorce_date'])) ?></td>
                            <td><?= htmlspecialchars($row['witness_1']) ?></td>
                            <td><?= htmlspecialchars($row['witness_2']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No divorce records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-muted">
            <small>Generated by VERMS on <?= date('F j, Y \a\t g:i A') ?></small>
        </div>
    </div>
</div>

<!-- Full Report -->
<div class="print-section" id="printAll" style="display: none;">
    <div class="container-fluid">
        <div class="report-header">
            <h2>COMPREHENSIVE VITAL EVENTS REGISTER</h2>
            <h4><?= htmlspecialchars($kebele_name) ?> Kebele</h4>
            <p class="text-muted mb-0">Report Period: <?= date('F j, Y') ?></p>
        </div>

        <!-- Birth Events -->
        <h5 class="mt-4">BIRTH EVENTS</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Child Name</th>
                        <th>Father Name</th>
                        <th>Date of Birth</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $birth_records_all = $conn->query("SELECT child_name, father_full_name, date_of_birth, status FROM birth_events WHERE place_of_birth = $kebele_id ORDER BY created_at DESC");
                    if ($birth_records_all && $birth_records_all->num_rows > 0): ?>
                        <?php $i = 1; while($row = $birth_records_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['child_name']) ?></td>
                            <td><?= htmlspecialchars($row['father_full_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['date_of_birth'])) ?></td>
                            <td>
                                <span class="badge"><?= $row['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No birth records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Marriage Events -->
        <h5 class="mt-4">MARRIAGE EVENTS</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Husband Name</th>
                        <th>Wife Name</th>
                        <th>Marriage Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $marriage_records_all = $conn->query("SELECT husband_name, wife_name, marriage_date, status FROM marriage_events WHERE place_of_marriage = $kebele_id ORDER BY created_at DESC");
                    if ($marriage_records_all && $marriage_records_all->num_rows > 0): ?>
                        <?php $i = 1; while($row = $marriage_records_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['husband_name']) ?></td>
                            <td><?= htmlspecialchars($row['wife_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['marriage_date'])) ?></td>
                            <td>
                                <span class="badge"><?= $row['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No marriage records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Death Events -->
        <h5 class="mt-4">DEATH EVENTS</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Deceased Name</th>
                        <th>Date of Death</th>
                        <th>Age</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $death_records_all = $conn->query("SELECT deceased_name, date_of_death, age, status FROM death_events WHERE place_of_death = $kebele_id ORDER BY created_at DESC");
                    if ($death_records_all && $death_records_all->num_rows > 0): ?>
                        <?php $i = 1; while($row = $death_records_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['deceased_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['date_of_death'])) ?></td>
                            <td><?= htmlspecialchars($row['age']) ?></td>
                            <td>
                                <span class="badge"><?= $row['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No death records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Divorce Events -->
        <h5 class="mt-4">DIVORCE EVENTS</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Husband Name</th>
                        <th>Wife Name</th>
                        <th>Divorce Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $divorce_records_all = $conn->query("SELECT husband_name, wife_name, divorce_date, status FROM divorce_events WHERE place_of_divorce = $kebele_id ORDER BY created_at DESC");
                    if ($divorce_records_all && $divorce_records_all->num_rows > 0): ?>
                        <?php $i = 1; while($row = $divorce_records_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['husband_name']) ?></td>
                            <td><?= htmlspecialchars($row['wife_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['divorce_date'])) ?></td>
                            <td>
                                <span class="badge"><?= $row['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No divorce records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-muted">
            <small>Generated by VERMS on <?= date('F j, Y \a\t g:i A') ?></small>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
// Monthly Trend Chart
const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_data, 'month')) ?>,
        datasets: [
            {
                label: 'Births',
                data: <?= json_encode(array_column($monthly_data, 'births')) ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Marriages',
                data: <?= json_encode(array_column($monthly_data, 'marriages')) ?>,
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155, 89, 182, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Deaths',
                data: <?= json_encode(array_column($monthly_data, 'deaths')) ?>,
                borderColor: '#95a5a6',
                backgroundColor: 'rgba(149, 165, 166, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Divorces',
                data: <?= json_encode(array_column($monthly_data, 'divorces')) ?>,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Monthly Events Trend'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Events'
                }
            }
        }
    }
});

// Distribution Chart
const distCtx = document.getElementById('distributionChart').getContext('2d');
const distChart = new Chart(distCtx, {
    type: 'doughnut',
    data: {
        labels: ['Births', 'Marriages', 'Deaths', 'Divorces'],
        datasets: [{
            data: [<?= $birth_count ?>, <?= $marriage_count ?>, <?= $death_count ?>, <?= $divorce_count ?>],
            backgroundColor: [
                '#3498db',
                '#9b59b6',
                '#95a5a6',
                '#e74c3c'
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
                position: 'bottom'
            },
            title: {
                display: true,
                text: 'Events Distribution'
            }
        }
    }
});

// Print functionality
function printReport(type) {
    // Hide all print sections first
    document.querySelectorAll('.print-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show the selected print section
    const printSection = document.getElementById(`print${type.charAt(0).toUpperCase() + type.slice(1)}`);
    if (printSection) {
        printSection.style.display = 'block';
    }
    
    // Print the document
    window.print();
    
    // Hide the print section after printing
    setTimeout(() => {
        if (printSection) {
            printSection.style.display = 'none';
        }
    }, 500);
}
</script>
</body>
</html>