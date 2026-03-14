<?php
session_start();
require_once '../includes/db_connection.php';

// Debug log
error_log("STATISTICIAN DASHBOARD: Session check - user_id=" . ($_SESSION['user_id'] ?? 'NOT SET') . ", role=" . ($_SESSION['role'] ?? 'NOT SET'));

// Check if user is logged in and is a statistician
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'statistician') {
    error_log("STATISTICIAN DASHBOARD: Access denied. Redirecting to login.");
    header('Location: ../login.php');
    exit();
}

// Language handling
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Translations for statistician dashboard
$translations = [
    'en' => [
        'title' => 'Statistician Dashboard',
        'welcome' => 'Welcome',
        'dashboard_title' => 'Statistics Dashboard',
        'vital_events_management' => 'Vital Events Statistics & Analysis',
        'refresh' => 'Refresh',
        'notifications' => 'Notifications',
        'statistician' => 'Statistician',
        'quick_actions' => 'Quick Actions',
        'reports_analytics' => 'Reports & Analytics',
        'generate_statistical_reports' => 'Generate statistical reports',
        'data_visualization' => 'Data Visualization',
        'visualize_vital_events' => 'Visualize vital events data',
        'export_data' => 'Export Data',
        'export_to_excel_pdf' => 'Export data to Excel/PDF',
        'system_statistics' => 'System Statistics',
        'user_activity' => 'User Activity',
        'monitor_user_logins' => 'Monitor user logins and activity',
        'total_births' => 'Total Births',
        'total_deaths' => 'Total Deaths',
        'total_marriages' => 'Total Marriages',
        'total_divorces' => 'Total Divorces',
        'total_users' => 'Total Users',
        'total_kebeles' => 'Total Kebeles',
        'total_woredas' => 'Total Woredas',
        'total_zones' => 'Total Zones',
        'recent_activity' => 'Recent Activity',
        'view_all_activity' => 'View All Activity',
        'no_recent_activity' => 'No recent activity',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
        'copyright' => 'VERMS - All rights reserved'
    ],
    'am' => [
        'title' => 'የስታቲስቲክስ ባለሙያ ዳሽቦርድ',
        'welcome' => 'እንኳን ደህና መጡ',
        'dashboard_title' => 'ስታቲስቲክስ ዳሽቦርድ',
        'vital_events_management' => 'የህይወት ክስተቶች ስታቲስቲክስ እና ትንተና',
        'refresh' => 'አድስ',
        'notifications' => 'ማሳወቂያዎች',
        'statistician' => 'የስታቲስቲክስ ባለሙያ',
        'quick_actions' => 'ፈጣን እርምጃዎች',
        'reports_analytics' => 'ሪፖርቶች እና ትንተና',
        'generate_statistical_reports' => 'ስታቲስቲካዊ ሪፖርቶች ይፍጠሩ',
        'data_visualization' => 'ውሂብ ትርዒት',
        'visualize_vital_events' => 'የህይወት ክስተቶች ውሂብ ይታዩ',
        'export_data' => 'ውሂብ ወደ ውጭ ላክ',
        'export_to_excel_pdf' => 'ውሂብ ወደ Excel/PDF ላክ',
        'system_statistics' => 'የስርዓት ስታቲስቲክስ',
        'user_activity' => 'የተጠቃሚ እንቅስቃሴ',
        'monitor_user_logins' => 'የተጠቃሚ መግቢያዎች እና እንቅስቃሴ ይቆጣጠሩ',
        'total_births' => 'ጠቅላላ የትውልድ',
        'total_deaths' => 'ጠቅላላ የሞት',
        'total_marriages' => 'ጠቅላላ የጋብቻ',
        'total_divorces' => 'ጠቅላላ የፍቺ',
        'total_users' => 'ጠቅላላ ተጠቃሚዎች',
        'total_kebeles' => 'ጠቅላላ ቀበሌዎች',
        'total_woredas' => 'ጠቅላላ ወረዳዎች',
        'total_zones' => 'ጠቅላላ ዞኖች',
        'recent_activity' => 'የቅርብ ጊዜ እንቅስቃሴ',
        'view_all_activity' => 'ሁሉንም እንቅስቃሴ ይመልከቱ',
        'no_recent_activity' => 'የቅርብ ጊዜ እንቅስቃሴ የለም',
        'today' => 'ዛሬ',
        'this_week' => 'ይህ ሳምንት',
        'this_month' => 'ይህ ወር',
        'this_year' => 'ይህ ዓመት',
        'copyright' => 'ቪ.ኢ.አር.ኤም.ኤስ - ሁሉም መብቶች በህግ የተጠበቁ'
    ]
];

function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Get statistician info
$user_id = intval($_SESSION['user_id']);
$username = htmlspecialchars($_SESSION['username']);

// Get statistics data
function get_total_count($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM $table");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

function get_today_count($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM $table WHERE DATE(created_at) = CURDATE()");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

function get_this_week_count($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM $table WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

function get_this_month_count($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM $table WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

function get_this_year_count($conn, $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM $table WHERE YEAR(created_at) = YEAR(NOW())");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

// Calculate statistics
$total_births = get_total_count($conn, 'birth_events');
$total_deaths = get_total_count($conn, 'death_events');
$total_marriages = get_total_count($conn, 'marriage_events');
$total_divorces = get_total_count($conn, 'divorce_events');
$total_users = get_total_count($conn, 'users');
$total_kebeles = get_total_count($conn, 'kebeles');
$total_woredas = get_total_count($conn, 'woredas');
$total_zones = get_total_count($conn, 'zones');

// Get recent activity
$recent_activity = $conn->query("
    (SELECT 'birth' as type, id, created_at, 'New birth registration' as description FROM birth_events ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'death' as type, id, created_at, 'New death registration' as description FROM death_events ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'marriage' as type, id, created_at, 'New marriage registration' as description FROM marriage_events ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'divorce' as type, id, created_at, 'New divorce registration' as description FROM divorce_events ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('title') ?> - VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        /* REMOVED: statistician-info-card styles */
        
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border-left: 0.25rem solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .stat-card.border-primary { border-left-color: var(--primary-color); }
        .stat-card.border-success { border-left-color: var(--success-color); }
        .stat-card.border-info { border-left-color: var(--info-color); }
        .stat-card.border-warning { border-left-color: var(--warning-color); }
        .stat-card.border-danger { border-left-color: var(--danger-color); }
        .stat-card.border-dark { border-left-color: var(--dark-color); }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #858796;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-title {
            color: var(--primary-color);
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 0.5rem;
            margin: 2rem 0 1.5rem;
            font-weight: 600;
        }
        
        .action-card {
            display: block;
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s;
            border-left: 0.25rem solid var(--primary-color);
            height: 100%;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
            color: inherit;
            border-left-color: var(--success-color);
        }
        
        .action-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .action-card h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .recent-activity {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-top: 2rem;
        }
        
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item i {
            width: 2rem;
            text-align: center;
        }
        
        .activity-birth i { color: var(--primary-color); }
        .activity-death i { color: var(--danger-color); }
        .activity-marriage i { color: var(--success-color); }
        .activity-divorce i { color: var(--warning-color); }
        
        .language-switcher {
            position: absolute;
            top: 100px;
            right: 20px;
            z-index: 1000;
        }
        
        .lang-btn {
            padding: 5px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            margin-left: 5px;
            transition: all 0.3s;
        }
        
        .lang-btn.active {
            background: white;
            color: var(--primary-color);
        }
        
        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        @media (max-width: 768px) {
            .language-switcher {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 1rem;
                text-align: center;
            }
            
            .dashboard-header {
                padding: 1.5rem 0;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Set global variables for header
    $GLOBALS['lang'] = $lang;
    $GLOBALS['translations'] = $translations;
    include_once '../includes/header.php'; 
    ?>
    
    <!-- Language switcher -->
    <div class="language-switcher">
        <a href="?lang=en" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>">
            <i class="fas fa-globe-americas"></i> EN
        </a>
        <a href="?lang=am" class="lang-btn <?= $lang === 'am' ? 'active' : '' ?>">
            <i class="fas fa-globe-africa"></i> አማ
        </a>
    </div>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= t('dashboard_title') ?>
                    </h1>
                    <p class="lead mb-0"><?= t('vital_events_management') ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="btn-group">
                        <button class="btn btn-light" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> <?= t('refresh') ?>
                        </button>
                        <button class="btn btn-outline-light">
                            <i class="fas fa-bell me-1"></i> <?= t('notifications') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <!-- REMOVED: Statistician Information Card Section -->
        <!-- This section has been removed as requested -->
        
        <!-- Vital Events Statistics -->
        <h3 class="section-title">
            <i class="fas fa-chart-pie me-2"></i><?= t('system_statistics') ?>
        </h3>
        
        <div class="row g-4">
            <!-- Birth Statistics -->
            <div class="col-6 col-md-3">
                <div class="stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-baby text-primary"></i>
                        <div class="stat-value"><?= $total_births ?></div>
                        <div class="stat-label"><?= t('total_births') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Death Statistics -->
            <div class="col-6 col-md-3">
                <div class="stat-card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-book-dead text-danger"></i>
                        <div class="stat-value"><?= $total_deaths ?></div>
                        <div class="stat-label"><?= t('total_deaths') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Marriage Statistics -->
            <div class="col-6 col-md-3">
                <div class="stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-ring text-success"></i>
                        <div class="stat-value"><?= $total_marriages ?></div>
                        <div class="stat-label"><?= t('total_marriages') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Divorce Statistics -->
            <div class="col-6 col-md-3">
                <div class="stat-card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-heart-broken text-warning"></i>
                        <div class="stat-value"><?= $total_divorces ?></div>
                        <div class="stat-label"><?= t('total_divorces') ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Statistics -->
        <div class="row g-4 mt-3">
            <div class="col-6 col-md-3">
                <div class="stat-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-users text-info"></i>
                        <div class="stat-value"><?= $total_users ?></div>
                        <div class="stat-label"><?= t('total_users') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="stat-card border-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marker-alt text-dark"></i>
                        <div class="stat-value"><?= $total_kebeles ?></div>
                        <div class="stat-label"><?= t('total_kebeles') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-map text-primary"></i>
                        <div class="stat-value"><?= $total_woredas ?></div>
                        <div class="stat-label"><?= t('total_woredas') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-globe-africa text-success"></i>
                        <div class="stat-value"><?= $total_zones ?></div>
                        <div class="stat-label"><?= t('total_zones') ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <h3 class="section-title mt-5">
            <i class="fas fa-bolt me-2"></i><?= t('quick_actions') ?>
        </h3>
        
        <div class="row g-4">
            <div class="col-md-4">
                <a href="/VERMS/statistician/reports.php" class="action-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar"></i>
                        <h5><?= t('reports_analytics') ?></h5>
                        <p class="text-muted small"><?= t('generate_statistical_reports') ?></p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="/VERMS/statistician/visualization.php" class="action-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line"></i>
                        <h5><?= t('data_visualization') ?></h5>
                        <p class="text-muted small"><?= t('visualize_vital_events') ?></p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="/VERMS/statistician/export.php" class="action-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-export"></i>
                        <h5><?= t('export_data') ?></h5>
                        <p class="text-muted small"><?= t('export_to_excel_pdf') ?></p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i><?= t('recent_activity') ?></h5>
                <a href="/VERMS/statistician/activity.php" class="btn btn-sm btn-outline-primary">
                    <?= t('view_all_activity') ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            
            <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                <?php while($activity = $recent_activity->fetch_assoc()): 
                    $icon_class = '';
                    $color_class = '';
                    switch($activity['type']) {
                        case 'birth':
                            $icon = 'fa-baby';
                            $color_class = 'activity-birth';
                            break;
                        case 'death':
                            $icon = 'fa-book-dead';
                            $color_class = 'activity-death';
                            break;
                        case 'marriage':
                            $icon = 'fa-ring';
                            $color_class = 'activity-marriage';
                            break;
                        case 'divorce':
                            $icon = 'fa-heart-broken';
                            $color_class = 'activity-divorce';
                            break;
                        default:
                            $icon = 'fa-calendar';
                    }
                ?>
                    <div class="activity-item <?= $color_class ?>">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas <?= $icon ?> fa-lg me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($activity['description']) ?></h6>
                                <small class="text-muted"><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-alt fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-0"><?= t('no_recent_activity') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h5>VERMS - Vital Events Registration Management System</h5>
                    <p class="mb-0">Statistical Analysis Dashboard</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= t('copyright') ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Save language preference
        document.addEventListener('DOMContentLoaded', function() {
            const lang = '<?= $lang ?>';
            localStorage.setItem('preferred_lang', lang);
            
            // Add animation to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>