<?php
session_start();

// Language management
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default language
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Translations array
$translations = [
    'en' => [
        // Page title and headers
        'page_title' => 'Admin Dashboard - VERMS',
        'dashboard_header' => 'Admin Dashboard',
        'welcome_message' => 'Welcome back! Here\'s what\'s happening with your system today.',
        'quick_stats' => 'Quick Stats',
        'citizens' => 'Citizens',
        'kebeles' => 'Kebeles',
        'regions' => 'Regions',
        
        // Quick Actions
        'quick_actions' => 'Quick Actions',
        'manage_officers' => 'Manage Officers',
        'manage_officers_desc' => 'User accounts & permissions',
        'view_citizens' => 'View Citizens',
        'view_citizens_desc' => 'All citizen records',
        'manage_zones' => 'Manage Zones',
        'manage_zones_desc' => 'Regional administration',
        'manage_woredas' => 'Manage Woredas',
        'manage_woredas_desc' => 'District management',
        
        // Statistics Cards
        'total_citizens' => 'Total Citizens',
        'total_kebeles' => 'Total Kebeles',
        'total_woredas' => 'Total Woredas',
        'total_zones' => 'Total Zones',
        'view_details' => 'View Details',
        'manage' => 'Manage',
        
        // Charts
        'citizens_per_kebele' => 'Citizens per Kebele',
        'recent_citizens' => 'Recent Citizens',
        'name' => 'Name',
        'username' => 'Username',
        'joined' => 'Joined',
        'registered_on' => 'Registered on',
        'no_citizens' => 'No citizens registered yet',
        
        // Chart labels
        'number_of_citizens' => 'Number of Citizens',
        'kebele_label' => 'Kebele'
    ],
    'am' => [
        // Page title and headers
        'page_title' => 'የአስተዳዳሪ ዳሽቦርድ - ቪ.ኢ.አር.ኤም.ኤስ',
        'dashboard_header' => 'የአስተዳዳሪ ዳሽቦርድ',
        'welcome_message' => 'እንኳን ደህና መጡ! ዛሬ በስርዓትዎ ውስጥ የሚከሰተው ይኸው ነው።',
        'quick_stats' => 'ፈጣን ስታቲስቲክስ',
        'citizens' => 'ዜጎች',
        'kebeles' => 'ቀበሌዎች',
        'regions' => 'ክልሎች',
        
        // Quick Actions
        'quick_actions' => 'ፈጣን እርምጃዎች',
        'manage_officers' => 'አሰልጣኞችን ያስተዳድሩ',
        'manage_officers_desc' => 'የተጠቃሚ መለያዎች እና ፈቃዶች',
        'view_citizens' => 'ዜጎችን ይመልከቱ',
        'view_citizens_desc' => 'ሁሉም የዜጎች መዝገቦች',
        'manage_zones' => 'ዞኖችን ያስተዳድሩ',
        'manage_zones_desc' => 'ክልላዊ አስተዳደር',
        'manage_woredas' => 'ወረዳዎችን ያስተዳድሩ',
        'manage_woredas_desc' => 'ወረዳ አስተዳደር',
        
        // Statistics Cards
        'total_citizens' => 'ጠቅላላ ዜጎች',
        'total_kebeles' => 'ጠቅላላ ቀበሌዎች',
        'total_woredas' => 'ጠቅላላ ወረዳዎች',
        'total_zones' => 'ጠቅላላ ዞኖች',
        'view_details' => 'ዝርዝሮችን ይመልከቱ',
        'manage' => 'ያስተዳድሩ',
        
        // Charts
        'citizens_per_kebele' => 'በቀበሌ ዜጎች',
        'recent_citizens' => 'አዲስ ዜጎች',
        'name' => 'ስም',
        'username' => 'የተጠቃሚ ስም',
        'joined' => 'ተጠቃሚ ሆነ',
        'registered_on' => 'ተመዝግቧል በ',
        'no_citizens' => 'ገና ዜጎች አልተመዘገቡም',
        
        // Chart labels
        'number_of_citizens' => 'የዜጎች ብዛት',
        'kebele_label' => 'ቀበሌ'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connection.php';

// Get counts
$citizen_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'citizen'")->fetch_row()[0];
$zone_count = $conn->query("SELECT COUNT(*) FROM zones")->fetch_row()[0];
$woreda_count = $conn->query("SELECT COUNT(*) FROM woredas")->fetch_row()[0];
$kebele_count = $conn->query("SELECT COUNT(*) FROM kebeles")->fetch_row()[0];

// Fetch recent citizens (last 5)
$recent_citizens = $conn->query("SELECT fullname, username, created_at FROM users WHERE role = 'citizen' ORDER BY created_at DESC LIMIT 5");

// Fetch total citizens per kebele for graph
$citizen_per_kebele = [];
$kebele_labels = [];
$result = $conn->query("SELECT k.kebele_name, COUNT(u.id) as total FROM kebeles k LEFT JOIN users u ON u.kebele = k.kebele_name AND u.role = 'citizen' GROUP BY k.kebele_name ORDER BY k.kebele_name ASC");
while ($row = $result->fetch_assoc()) {
    $kebele_labels[] = $row['kebele_name'];
    $citizen_per_kebele[] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('page_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php if($lang === 'am'): ?>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: justify;
        }
        
        .dashboard-header h1,
        .dashboard-header p,
        .welcome-card h5,
        .welcome-card small,
        .stat-card .stat-number,
        .stat-card .stat-label,
        .btn,
        .card-header,
        .btn-action div,
        .recent-item h6,
        .recent-item small,
        .chart-container h5,
        .lead,
        .display-5,
        .fw-bold {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        <?php endif; ?>
        
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4bb543;
            --warning: #ff9e00;
            --danger: #dc3545;
            --info: #17a2b8;
            --light: #2d2d2eff;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            padding-top: 60px; /* Added padding for fixed header */
        }
        
        .dashboard-header {
            background: var(--gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border: 1px solid var(--light-gray);
            height: 100%;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 16px 16px 0 0 !important;
            border: none;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        
        .card-header i {
            margin-right: 0.5rem;
        }
        
        .btn-action {
            background: white;
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
            text-align: left;
            width: 100%;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }
        
        .btn-action:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }
        
        .btn-action i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
        }
        
        .recent-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
            transition: background 0.3s;
        }
        
        .recent-item:hover {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        /* Language switcher - Fixed positioning to prevent overlap */
        .language-switcher {
            position: fixed;
            top: 35px; /* Positioned below header */
            right: 170px;
            z-index: 1000;
            background: white;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 5px;
            display: flex;
            gap: 5px;
        }
        
        .lang-btn {
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            background: transparent;
            font-weight: 500;
            transition: all 0.3s;
            color: var(--primary);
            font-size: 0.85rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .lang-btn.active {
            background: var(--gradient);
            color: white;
        }
        
        .lang-btn:hover:not(.active) {
            background: rgba(67, 97, 238, 0.1);
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem 0;
                border-radius: 0 0 15px 15px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .language-switcher {
                top: 60px;
                right: 10px;
                padding: 4px;
            }
            
            .lang-btn {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
            
            body {
                padding-top: 80px; /* More padding for mobile header */
            }
        }
        
        @media (max-width: 576px) {
            .language-switcher {
                top: 55px;
                right: 5px;
                flex-direction: column;
                gap: 3px;
            }
            
            .lang-btn {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Language Switcher - Positioned below header -->
<div class="language-switcher">
    <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
        <i class="fas fa-globe-americas"></i> EN
    </a>
    <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">
        <i class="fas fa-globe-africa"></i> አማ
    </a>
</div>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold"><?php echo t('dashboard_header'); ?></h1>
                <p class="lead mb-0"><?php echo t('welcome_message'); ?></p>
            </div>
            <div class="col-md-4 text-end">
                <div class="welcome-card">
                    <h5 class="fw-bold text-primary mb-1"><?php echo t('quick_stats'); ?></h5>
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <div class="fw-bold fs-4"><?= $citizen_count ?></div>
                            <small class="text-muted"><?php echo t('citizens'); ?></small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold fs-4"><?= $kebele_count ?></div>
                            <small class="text-muted"><?php echo t('kebeles'); ?></small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold fs-4"><?= $woreda_count + $zone_count ?></div>
                            <small class="text-muted"><?php echo t('regions'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-lg-3 col-md-6 mb-4">
            <button class="btn-action" onclick="window.location.href='account_management.php'">
                <i class="fas fa-user-cog text-primary"></i>
                <div>
                    <div class="fw-bold"><?php echo t('manage_officers'); ?></div>
                    <small class="text-muted"><?php echo t('manage_officers_desc'); ?></small>
                </div>
            </button>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <button class="btn-action" onclick="window.location.href='view_citizens.php'">
                <i class="fas fa-users text-success"></i>
                <div>
                    <div class="fw-bold"><?php echo t('view_citizens'); ?></div>
                    <small class="text-muted"><?php echo t('view_citizens_desc'); ?></small>
                </div>
            </button>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <button class="btn-action" onclick="window.location.href='register_zone.php'">
                <i class="fas fa-globe-africa text-warning"></i>
                <div>
                    <div class="fw-bold"><?php echo t('manage_zones'); ?></div>
                    <small class="text-muted"><?php echo t('manage_zones_desc'); ?></small>
                </div>
            </button>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <button class="btn-action" onclick="window.location.href='register_woreda.php'">
                <i class="fas fa-map text-info"></i>
                <div>
                    <div class="fw-bold"><?php echo t('manage_woredas'); ?></div>
                    <small class="text-muted"><?php echo t('manage_woredas_desc'); ?></small>
                </div>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(67, 97, 238, 0.1); color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" style="color: var(--primary);"><?= $citizen_count ?></div>
                <div class="stat-label"><?php echo t('total_citizens'); ?></div>
                <a href="view_citizens.php" class="btn btn-outline-primary btn-sm"><?php echo t('view_details'); ?></a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(75, 181, 67, 0.1); color: var(--success);">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-number" style="color: var(--success);"><?= $kebele_count ?></div>
                <div class="stat-label"><?php echo t('total_kebeles'); ?></div>
                <a href="register_kebele.php" class="btn btn-outline-success btn-sm"><?php echo t('manage'); ?></a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 158, 0, 0.1); color: var(--warning);">
                    <i class="fas fa-map"></i>
                </div>
                <div class="stat-number" style="color: var(--warning);"><?= $woreda_count ?></div>
                <div class="stat-label"><?php echo t('total_woredas'); ?></div>
                <a href="register_woreda.php" class="btn btn-outline-warning btn-sm"><?php echo t('manage'); ?></a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(23, 162, 184, 0.1); color: var(--info);">
                    <i class="fas fa-globe-africa"></i>
                </div>
                <div class="stat-number" style="color: var(--info);"><?= $zone_count ?></div>
                <div class="stat-label"><?php echo t('total_zones'); ?></div>
                <a href="register_zone.php" class="btn btn-outline-info btn-sm"><?php echo t('manage'); ?></a>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="row mb-5">
        <!-- Citizens per Kebele Chart -->
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <h5 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2"></i><?php echo t('citizens_per_kebele'); ?></h5>
                <canvas id="citizenKebeleChart" height="250"></canvas>
            </div>
        </div>
        
        <!-- Recent Citizens -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-clock"></i><?php echo t('recent_citizens'); ?>
                </div>
                <div class="card-body">
                    <?php if ($recent_citizens->num_rows > 0): ?>
                        <?php while ($citizen = $recent_citizens->fetch_assoc()): ?>
                            <div class="recent-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($citizen['fullname']) ?></h6>
                                        <small class="text-muted"><?php echo t('username'); ?>: <?= htmlspecialchars($citizen['username']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?php echo t('joined'); ?></small><br>
                                        <small><?= date('M d', strtotime($citizen['created_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-2x text-muted mb-3"></i>
                            <p class="text-muted"><?php echo t('no_citizens'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Format date in Amharic if needed
function formatDate(dateString) {
    const lang = '<?php echo $lang; ?>';
    const date = new Date(dateString);
    
    if (lang === 'am') {
        const months = ['ጃንዩ', 'ፌብሩ', 'ማርች', 'ኤፕሪ', 'ሜይ', 'ጁን', 'ጁላይ', 'ኦገስ', 'ሴፕቴ', 'ኦክቶ', 'ኖቬም', 'ዲሴም'];
        const month = months[date.getMonth()];
        const day = date.getDate();
        return `${month} ${day}`;
    } else {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[date.getMonth()];
        const day = date.getDate();
        return `${month} ${day}`;
    }
}

// Update recent citizen dates
document.querySelectorAll('.recent-item small:last-child').forEach(el => {
    const dateText = el.textContent.trim();
    if (dateText) {
        // Extract date from text like "May 15"
        const parts = dateText.split(' ');
        if (parts.length === 2) {
            const month = parts[0];
            const day = parts[1];
            const currentYear = new Date().getFullYear();
            const formattedDate = formatDate(`${month} ${day}, ${currentYear}`);
            el.textContent = formattedDate;
        }
    }
});

// Citizens per kebele bar chart
const ctx = document.getElementById('citizenKebeleChart').getContext('2d');
const citizenKebeleChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($kebele_labels) ?>,
        datasets: [{
            label: '<?php echo t("citizens"); ?>',
            data: <?= json_encode($citizen_per_kebele) ?>,
            backgroundColor: 'rgba(67, 97, 238, 0.7)',
            borderColor: '#4361ee',
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                display: true,
                labels: {
                    font: {
                        family: '<?php echo $lang === "am" ? "Noto Sans Ethiopic" : "Segoe UI" ?>'
                    }
                }
            },
            title: { 
                display: false 
            }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                title: { 
                    display: true, 
                    text: '<?php echo t("number_of_citizens"); ?>',
                    font: {
                        family: '<?php echo $lang === "am" ? "Noto Sans Ethiopic" : "Segoe UI" ?>',
                        size: 14
                    }
                },
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    font: {
                        family: '<?php echo $lang === "am" ? "Noto Sans Ethiopic" : "Segoe UI" ?>'
                    }
                }
            },
            x: { 
                title: { 
                    display: true, 
                    text: '<?php echo t("kebele_label"); ?>',
                    font: {
                        family: '<?php echo $lang === "am" ? "Noto Sans Ethiopic" : "Segoe UI" ?>',
                        size: 14
                    }
                },
                grid: { display: false },
                ticks: {
                    font: {
                        family: '<?php echo $lang === "am" ? "Noto Sans Ethiopic" : "Segoe UI" ?>',
                        size: 12
                    }
                }
            }
        }
    }
});

// Add click events to action buttons (redundant as we now have onclick directly in buttons)
document.querySelectorAll('.btn-action').forEach(button => {
    button.addEventListener('click', function() {
        const text = this.querySelector('.fw-bold').textContent;
        // Already handled by onclick attribute
    });
});
</script>
</body>
</html>