<?php
// Woreda Officer Dashboard 
session_start();
require_once '../includes/db_connection.php';

// Language handling
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Translations array (keep your existing translations)

$translations = [
    'en' => [
        'title' => 'Woreda Officer Dashboard',
        'dashboard_title' => 'Woreda Officer Dashboard',
        'vital_events_management' => 'Vital Events Management System',
        'welcome' => 'Welcome',
        'woreda' => 'Woreda',
        'zone' => 'Zone',
        'phone' => 'Phone',
        'officer_id' => 'Officer ID',
        'kebeles_under_management' => 'kebeles under management',
        'woreda_overview' => 'Woreda Overview',
        'birth_events' => 'Birth Events',
        'marriage_events' => 'Marriage Events',
        'death_events' => 'Death Events',
        'divorce_events' => 'Divorce Events',
        'notices' => 'Notices',
        'kebeles' => 'Kebeles',
        'pending_events' => 'Pending Events',
        'add_notice' => 'Add Notice',
        'quick_actions' => 'Quick Actions',
        'manage_birth_registrations' => 'Manage birth registrations',
        'manage_death_registrations' => 'Manage death registrations',
        'manage_marriage_registrations' => 'Manage marriage registrations',
        'manage_divorce_registrations' => 'Manage divorce registrations',
        'reports_analytics' => 'Reports & Analytics',
        'generate_statistical_reports' => 'Generate statistical reports',
        'manage_public_notices' => 'Manage public notices',
        'manage_kebeles_in_woreda' => 'Manage kebeles in woreda',
        'refresh' => 'Refresh',
        'notifications' => 'Notifications',
        'logout' => 'Logout',
        'home' => 'Home',
        'profile' => 'Profile',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'copyright' => 'Woreda Dashboard. All rights reserved.',
        'view_all_notices' => 'View All Notices',
        'no_recent_notices' => 'No recent notices',
        'recent_notices' => 'Recent Notices',
        'view_details' => 'View Details',
        'n_a' => 'N/A',
        'woreda_administration' => 'Woreda Administration',
        'south_gondar_zone_vital_events' => 'South Gondar Zone Vital Events System',
        'view_all_events' => 'View All Events',
        'total_events' => 'Total Events',
        'approved_events' => 'Approved Events',
        'pending_approval' => 'Pending Approval',
        'rejected_events' => 'Rejected Events',
        'system_health' => 'System Health',
        'active_users' => 'Active Users',
        'system_status' => 'System Status',
        'operational' => 'Operational',
        'maintenance' => 'Maintenance',
        'downtime' => 'Downtime',
        'monthly_statistics' => 'Monthly Statistics',
        'event_distribution' => 'Event Distribution',
        'activity_log' => 'Activity Log'
    ],
    'am' => [
        'title' => 'የወረዳ ባለስልጣን ዳሽቦርድ',
        'dashboard_title' => 'የወረዳ ባለስልጣን ዳሽቦርድ',
        'vital_events_management' => 'የህይወት ክስተቶች አስተዳደር ስርዓት',
        'welcome' => 'እንኳን ደህና መጡ',
        'woreda' => 'ወረዳ',
        'zone' => 'ዞን',
        'phone' => 'ስልክ',
        'officer_id' => 'የባለስልጣን መለያ',
        'kebeles_under_management' => 'ቀበሌዎች በአስተዳደር ሥር',
        'woreda_overview' => 'የወረዳ አጠቃላይ እይታ',
        'birth_events' => 'የትውልድ ክስተቶች',
        'marriage_events' => 'የጋብቻ ክስተቶች',
        'death_events' => 'የሞት ክስተቶች',
        'divorce_events' => 'የፍቺ ክስተቶች',
        'notices' => 'ማስታወቂያዎች',
        'kebeles' => 'ቀበሌዎች',
        'pending_events' => 'በመጠባበቅ ላይ ያሉ ክስተቶች',
        'add_notice' => 'ማስታወቂያ ጨምር',
        'quick_actions' => 'ፈጣን ድርጊቶች',
        'manage_birth_registrations' => 'የትውልድ ምዝገባዎችን አስተዳድር',
        'manage_death_registrations' => 'የሞት ምዝገባዎችን አስተዳድር',
        'manage_marriage_registrations' => 'የጋብቻ ምዝገባዎችን አስተዳድር',
        'manage_divorce_registrations' => 'የፍቺ ምዝገባዎችን አስተዳድር',
        'reports_analytics' => 'ሪፖርቶች እና ትንተና',
        'generate_statistical_reports' => 'ስታቲስቲካዊ ሪፖርቶችን ፍጠር',
        'manage_public_notices' => 'የህዝብ ማስታወቂያዎችን አስተዳድር',
        'manage_kebeles_in_woreda' => 'በወረዳ ውስጥ ያሉትን ቀበሌዎች አስተዳድር',
        'refresh' => 'አዲስ አድርግ',
        'notifications' => 'ማስታወቂያዎች',
        'logout' => 'ውጣ',
        'home' => 'መነሻ',
        'profile' => 'መገለጫ',
        'reports' => 'ሪፖርቶች',
        'settings' => 'ቅንብሮች',
        'copyright' => 'የወረዳ ዳሽቦርድ. መብቱ በህግ የተጠበቀ ነው።',
        'view_all_notices' => 'ሁሉንም ማስታወቂያዎች አይት',
        'no_recent_notices' => 'የቅርብ ጊዜ ማስታወቂያዎች የሉም',
        'recent_notices' => 'የቅርብ ጊዜ ማስታወቂያዎች',
        'view_details' => 'ዝርዝሮችን አይት',
        'n_a' => 'አይገኝም',
        'woreda_administration' => 'የወረዳ አስተዳደር',
        'south_gondar_zone_vital_events' => 'ደቡብ ጎንደር ዞን የህይወት ክስተቶች ስርዓት',
        'view_all_events' => 'ሁሉንም ክስተቶች አይት',
        'total_events' => 'ጠቅላላ ክስተቶች',
        'approved_events' => 'የተፈቀዱ ክስተቶች',
        'pending_approval' => 'ለፈቃድ በመጠባበቅ ላይ',
        'rejected_events' => 'የተቀበሉ ክስተቶች',
        'system_health' => 'የስርዓት ጤና',
        'active_users' => 'ንቁ ተጠቃሚዎች',
        'system_status' => 'የስርዓት ሁኔታ',
        'operational' => 'ስራ ላይ',
        'maintenance' => 'ጥገና',
        'downtime' => 'ከስራ መውጣት',
        'monthly_statistics' => 'ወርሃዊ ስታቲስቲክስ',
        'event_distribution' => 'የክስተቶች ስርጭት',
        'activity_log' => 'የእንቅስቃሴ መዝገብ'
    ]
];

function t($key, $params = []) {
    global $lang, $translations;
    $text = $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
    if (!empty($params)) {
        $text = vsprintf($text, $params);
    }
    return $text;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'woreda') {
    header('Location: ../login.php');
    exit();
}

$officer_id = $_SESSION['user_id'];

// Get woreda info
$woreda = $conn->query("SELECT w.*, z.zone_name FROM woredas w 
                       JOIN zones z ON w.zone_id = z.id 
                       WHERE w.woreda_officer_id = $officer_id")->fetch_assoc();
if (!$woreda) die("Woreda not found.");
$woreda_id = $woreda['id'];
$woreda_name = $woreda['woreda_name'];
$zone_name = $woreda['zone_name'];

// ========== CORRECTED FUNCTIONS ==========

// Get count of events in ALL kebeles of this woreda
function get_count($conn, $table, $woreda_id, $col='place_of_birth') {
    $col_map = [
        'birth_events' => 'place_of_birth',
        'death_events' => 'place_of_death',
        'marriage_events' => 'place_of_marriage',
        'divorce_events' => 'place_of_divorce',
        'notices' => 'woreda_id'
    ];
    $column = $col_map[$table] ?? $col;
    
    // For notices table, it's direct woreda_id match
    if ($table === 'notices') {
        $res = $conn->query("SELECT COUNT(*) AS cnt FROM $table WHERE $column = $woreda_id");
    } 
    // For event tables, we need to join through kebeles
    else {
        $res = $conn->query("
            SELECT COUNT(*) AS cnt 
            FROM $table e
            JOIN kebeles k ON e.$column = k.id
            WHERE k.woreda_id = $woreda_id
        ");
    }
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

// Get count of kebeles in this woreda
function get_kebeles_count($conn, $woreda_id) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM kebeles WHERE woreda_id = $woreda_id");
    return $res ? (int)$res->fetch_assoc()['cnt'] : 0;
}

// Get pending events from ALL kebeles in this woreda
function get_pending_events($conn, $woreda_id) {
    $total = 0;
    
    // Birth events pending
    $res = $conn->query("SELECT COUNT(*) as cnt FROM birth_events be 
                        JOIN kebeles k ON be.place_of_birth = k.id 
                        WHERE k.woreda_id = $woreda_id AND be.status = 'Pending'");
    $total += $res ? (int)$res->fetch_assoc()['cnt'] : 0;
    
    // Death events pending
    $res = $conn->query("SELECT COUNT(*) as cnt FROM death_events de 
                        JOIN kebeles k ON de.place_of_death = k.id 
                        WHERE k.woreda_id = $woreda_id AND de.status = 'Pending'");
    $total += $res ? (int)$res->fetch_assoc()['cnt'] : 0;
    
    // Marriage events pending
    $res = $conn->query("SELECT COUNT(*) as cnt FROM marriage_events me 
                        JOIN kebeles k ON me.place_of_marriage = k.id 
                        WHERE k.woreda_id = $woreda_id AND me.status = 'Pending'");
    $total += $res ? (int)$res->fetch_assoc()['cnt'] : 0;
    
    // Divorce events pending
    $res = $conn->query("SELECT COUNT(*) as cnt FROM divorce_events dve 
                        JOIN kebeles k ON dve.place_of_divorce = k.id 
                        WHERE k.woreda_id = $woreda_id AND dve.status = 'Pending'");
    $total += $res ? (int)$res->fetch_assoc()['cnt'] : 0;
    
    return $total;
}

// ========== GET STATISTICS ==========

// Use the CORRECTED functions
$births = get_count($conn, 'birth_events', $woreda_id);
$deaths = get_count($conn, 'death_events', $woreda_id);
$marriages = get_count($conn, 'marriage_events', $woreda_id);
$divorces = get_count($conn, 'divorce_events', $woreda_id);
$notices = get_count($conn, 'notices', $woreda_id, 'woreda_id');
$kebeles = get_kebeles_count($conn, $woreda_id);
$pending_events = get_pending_events($conn, $woreda_id);

// Debug: Show which kebeles have events (remove after testing)
$debug_kebeles = $conn->query("
    SELECT k.kebele_name, 
           COUNT(DISTINCT be.id) as birth_count,
           COUNT(DISTINCT de.id) as death_count,
           COUNT(DISTINCT me.id) as marriage_count,
           COUNT(DISTINCT dve.id) as divorce_count
    FROM kebeles k
    LEFT JOIN birth_events be ON k.id = be.place_of_birth
    LEFT JOIN death_events de ON k.id = de.place_of_death
    LEFT JOIN marriage_events me ON k.id = me.place_of_marriage
    LEFT JOIN divorce_events dve ON k.id = dve.place_of_divorce
    WHERE k.woreda_id = $woreda_id
    GROUP BY k.id, k.kebele_name
");

// Get recent notices
$recent_notices = $conn->query("SELECT * FROM notices WHERE woreda_id = $woreda_id ORDER BY created_at DESC LIMIT 3");

$username = htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Officer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= t('title') ?> - <?= htmlspecialchars($zone_name) ?> <?= t('zone') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #6c5ce7;
            --success: #27ae60;
            --info: #17a2b8;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
            min-height: 100vh;
            font-family: <?= $lang === 'am' ? "'Ethiopia Jiret', 'Nyala', sans-serif" : "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" ?>;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
            box-shadow: 0 4px 20px rgba(44, 62, 80, 0.15);
        }
        
        .stat-card {
            border-radius: 1.2rem;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.1);
            background: #fff;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.15);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0;
        }
        
        .chart-container {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: none;
        }
        
        .chart-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .chart-title i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        .section-title {
            font-weight: 700;
            color: var(--dark);
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            font-size: <?= $lang === 'am' ? '1.3rem' : '1.5rem' ?>;
        }
        
        .card-row {
            margin-bottom: 2rem;
        }
        
        .recent-notices {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .notice-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .notice-item:last-child {
            border-bottom: none;
        }
        
        .woreda-info-card {
            background: linear-gradient(135deg, var(--success) 0%, #2ecc71 100%);
            color: white;
            border-radius: 1.2rem;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.1);
            margin-bottom: 2rem;
        }
        
        .action-card {
            border-radius: 1.2rem;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.1);
            background: #fff;
            transition: all 0.3s ease;
            border: none;
            height: 100%;
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.15);
            color: inherit;
        }
        
        .action-card .card-body {
            padding: 2rem 1.5rem;
            text-align: center;
        }
        
        .action-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        /* Language switcher */
        .language-switcher {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 5px;
            display: flex;
            gap: 5px;
        }
        .lang-btn {
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            background: transparent;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #2c3e50;
            font-size: 0.8rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .lang-btn.active {
            background: #0d6efd;
            color: white;
        }
        .lang-btn:hover:not(.active) {
            background: rgba(44, 62, 80, 0.1);
        }
        
        /* Font adjustments for Amharic */
        .stat-label {
            font-size: <?= $lang === 'am' ? '0.8rem' : '0.9rem' ?>;
        }
        
        .action-card h5 {
            font-size: <?= $lang === 'am' ? '1rem' : '1.2rem' ?>;
        }
        
        .action-card p {
            font-size: <?= $lang === 'am' ? '0.8rem' : '0.9rem' ?>;
        }
        
        .dashboard-header h1 {
            font-size: <?= $lang === 'am' ? '1.8rem' : '2.2rem' ?>;
        }
        
        .dashboard-header p {
            font-size: <?= $lang === 'am' ? '0.9rem' : '1.1rem' ?>;
        }
        
        /* Debug panel */
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .debug-panel h6 {
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .stat-card .card-body {
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.7rem;
            }
            
            .dashboard-header {
                padding: 1.5rem 0;
                border-radius: 0 0 1.5rem 1.5rem;
            }
            
            .language-switcher {
                top: 70px;
                right: 10px;
                padding: 4px;
            }
            .lang-btn {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .language-switcher {
                top: 60px;
                right: 5px;
                flex-direction: column;
                gap: 3px;
            }
            .lang-btn {
                padding: 3px 6px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Update header include to pass translations
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
                        <i class="fas fa-user-tie me-2"></i>
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
        <!-- Woreda Information -->
        <div class="woreda-info-card p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-2"><?= t('welcome') ?>, <?= $username ?>!</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fas fa-map me-2"></i> <strong><?= t('woreda') ?>:</strong> <?= htmlspecialchars($woreda_name) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fas fa-globe me-2"></i> <strong><?= t('zone') ?>:</strong> <?= htmlspecialchars($zone_name) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fas fa-phone me-2"></i> <strong><?= t('phone') ?>:</strong> <?= htmlspecialchars($woreda['phone'] ?? t('n_a')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="bg-white text-dark rounded p-3 shadow-sm">
                        <p class="mb-1"><strong><?= t('officer_id') ?>:</strong> <?= $officer_id ?></p>
                        <p class="mb-0"><strong><?= t('kebeles') ?>:</strong> <?= $kebeles ?> <?= t('kebeles_under_management') ?></p>
                        <p class="mb-0"><strong>Total Events:</strong> <?= $births + $deaths + $marriages + $divorces ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards Section -->
        <h3 class="section-title">
            <i class="fas fa-tachometer-alt me-2"></i><?= t('woreda_overview') ?>
        </h3>
        
        <!-- First Row of Cards -->
        <div class="row card-row g-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-baby text-primary"></i>
                        <div class="stat-label"><?= t('birth_events') ?></div>
                        <div class="stat-value"><?= $births ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-ring text-success"></i>
                        <div class="stat-label"><?= t('marriage_events') ?></div>
                        <div class="stat-value"><?= $marriages ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-book-dead text-danger"></i>
                        <div class="stat-label"><?= t('death_events') ?></div>
                        <div class="stat-value"><?= $deaths ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-heart-broken text-warning"></i>
                        <div class="stat-label"><?= t('divorce_events') ?></div>
                        <div class="stat-value"><?= $divorces ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Second Row of Cards -->
        <div class="row card-row g-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-bullhorn text-info"></i>
                        <div class="stat-label"><?= t('notices') ?></div>
                        <div class="stat-value"><?= $notices ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-map-signs text-dark"></i>
                        <div class="stat-label"><?= t('kebeles') ?></div>
                        <div class="stat-value"><?= $kebeles ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-clock text-warning"></i>
                        <div class="stat-label"><?= t('pending_events') ?></div>
                        <div class="stat-value"><?= $pending_events ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <a href="notices.php" class="text-decoration-none">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-plus-circle text-success"></i>
                            <div class="stat-label"><?= t('add_notice') ?></div>
                            <div class="stat-value"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <h3 class="section-title">
            <i class="fas fa-bolt me-2"></i><?= t('quick_actions') ?>
        </h3>
        
        <div class="row g-4">
            <div class="col-md-3">
                <a href="/VERMS/woreda_officer/birth.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-baby text-primary"></i>
                        <h5><?= t('birth_events') ?></h5>
                        <p class="text-muted small"><?= t('manage_birth_registrations') ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/VERMS/woreda_officer/death.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-book-dead text-danger"></i>
                        <h5><?= t('death_events') ?></h5>
                        <p class="text-muted small"><?= t('manage_death_registrations') ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/VERMS/woreda_officer/marriage.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-ring text-success"></i>
                        <h5><?= t('marriage_events') ?></h5>
                        <p class="text-muted small"><?= t('manage_marriage_registrations') ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/VERMS/woreda_officer/divorce.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-file-contract text-warning"></i>
                        <h5><?= t('divorce_events') ?></h5>
                        <p class="text-muted small"><?= t('manage_divorce_registrations') ?></p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Second Row of Quick Actions -->
        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <a href="/VERMS/woreda_officer/report.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-chart-bar text-info"></i>
                        <h5><?= t('reports_analytics') ?></h5>
                        <p class="text-muted small"><?= t('generate_statistical_reports') ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/VERMS/woreda_officer/notices.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-bullhorn text-secondary"></i>
                        <h5><?= t('notices') ?></h5>
                        <p class="text-muted small"><?= t('manage_public_notices') ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/VERMS/woreda_officer/kebeles.php" class="action-card">
                    <div class="card-body">
                        <i class="fas fa-map-marked-alt text-dark"></i>
                        <h5><?= t('kebeles') ?></h5>
                        <p class="text-muted small"><?= t('manage_kebeles_in_woreda') ?></p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Recent Notices Section -->
        <div class="recent-notices">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i><?= t('recent_notices') ?></h5>
                <a href="/VERMS/woreda_officer/notices.php" class="btn btn-sm btn-outline-primary">
                    <?= t('view_all_notices') ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php if ($recent_notices && $recent_notices->num_rows > 0): ?>
                <?php while($notice = $recent_notices->fetch_assoc()): ?>
                    <div class="notice-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($notice['title']) ?></h6>
                                <p class="mb-1 text-muted small"><?= htmlspecialchars(substr($notice['description'], 0, 100)) ?>...</p>
                                <small class="text-muted"><?= date('M d, Y', strtotime($notice['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-3">
                    <i class="fas fa-bullhorn fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0"><?= t('no_recent_notices') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= htmlspecialchars($woreda_name) ?> <?= t('woreda_administration') ?></h5>
                    <p class="mb-0"><?= t('south_gondar_zone_vital_events') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
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
        });
    </script>
</body>
</html>