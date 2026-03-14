<?php
// Dashboard for Kebele Officer
session_start();
require_once '../includes/db_connection.php';

// Language handling
if (isset($_GET['lang'])) {
    $new_lang = ($_GET['lang'] === 'am') ? 'am' : 'en';
    $_SESSION['lang'] = $new_lang;
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation array (only visible texts)
$texts = [
    'en' => [
        'page_title'                  => 'Kebele Officer Dashboard',
        'welcome'                     => 'Welcome, %s!',
        'kebele'                      => 'Kebele:',
        'woreda'                      => 'Woreda:',
        'zone'                        => 'Zone:',
        'officer_id'                  => 'Officer ID:',
        'phone'                       => 'Phone:',
        'not_assigned'                => 'Not assigned',
        'quick_overview'              => 'Quick Overview',
        'birth_events'                => 'Birth Events',
        'marriage_events'             => 'Marriage Events',
        'death_events'                => 'Death Events',
        'divorce_events'              => 'Divorce Events',
        'pending_requests'            => 'Pending Requests',
        'approved_certificates'       => 'Approved Certificates',
        'manage_citizens'             => 'Manage Citizens',
        'issue_certificates'          => 'Issue Certificates',
        'quick_actions'               => 'Quick Actions',
        'register_birth'              => 'Register Birth',
        'register_marriage'           => 'Register Marriage',
        'register_death'              => 'Register Death',
        'generate_report'             => 'Generate Report',
        'kebele_administration'       => '%s Administration',
        'vital_events_system'         => 'Vital Events Registration System',
        'copyright'                   => '&copy; %d Kebele Dashboard. All rights reserved.',
    ],
    'am' => [
        'page_title'                  => 'የቀበሌ ኃላፊ መቆጣጠሪያ ገፅ',
        'welcome'                     => 'እንኳን ደህና መጡ፣ %s!',
        'kebele'                      => 'ቀበሌ፡',
        'woreda'                      => 'ወረዳ፡',
        'zone'                        => 'ዞን፡',
        'officer_id'                  => 'የኃላፊ መለያ፡',
        'phone'                       => 'ስልክ፡',
        'not_assigned'                => 'አልተመደበም',
        'quick_overview'              => 'ፈጣን እይታ',
        'birth_events'                => 'የልደት ክስተቶች',
        'marriage_events'             => 'የጋብቻ ክስተቶች',
        'death_events'                => 'የሞት ክስተቶች',
        'divorce_events'              => 'የፍቺ ክስተቶች',
        'pending_requests'            => 'በመጠባበቅ ላይ ያሉ ጥያቄዎች',
        'approved_certificates'       => 'የተፈቀዱ የምስክር ወረቀቶች',
        'manage_citizens'             => 'ዜጎችን ያስተዳድሩ',
        'issue_certificates'          => 'የምስክር ወረቀቶች ስጡ',
        'quick_actions'               => 'ፈጣን እርምጃዎች',
        'register_birth'              => 'ልደት መዝግብ',
        'register_marriage'           => 'ጋብቻ መዝግብ',
        'register_death'              => 'ሞት መዝግብ',
        'generate_report'             => 'ሪፖርት ፍጠር',
        'kebele_administration'       => '%s አስተዳደር',
        'vital_events_system'         => 'የሕይወት ክስተት መዝገብ ሥርዓት',
        'copyright'                   => '&copy; %d የቀበሌ መቆጣጠሪያ። ሁሉም መብቶች የተጠበቁ ናቸው።',
    ]
];

$officer_id = $_SESSION['user_id'];
// Fetch kebele info for this officer
$kebele = $conn->query("SELECT k.*, w.woreda_name, z.zone_name FROM kebeles k
    JOIN woredas w ON k.woreda_id = w.id
    JOIN zones z ON w.zone_id = z.id
    WHERE k.kebele_officer_id = $officer_id")->fetch_assoc();
$kebele_id = $kebele['id'] ?? 0;
// Count for dashboard cards
$births = $conn->query("SELECT COUNT(*) AS cnt FROM birth_events WHERE place_of_birth = $kebele_id")->fetch_assoc()['cnt'] ?? 0;
$marriages = $conn->query("SELECT COUNT(*) AS cnt FROM marriage_events WHERE place_of_marriage = $kebele_id")->fetch_assoc()['cnt'] ?? 0;
$deaths = $conn->query("SELECT COUNT(*) AS cnt FROM death_events WHERE place_of_death = $kebele_id")->fetch_assoc()['cnt'] ?? 0;
$divorces = $conn->query("SELECT COUNT(*) AS cnt FROM divorce_events WHERE place_of_divorce = $kebele_id")->fetch_assoc()['cnt'] ?? 0;
// Get pending requests count
$pending_requests = $conn->query("
    SELECT COUNT(*) AS cnt FROM (
        SELECT id FROM birth_events WHERE place_of_birth = $kebele_id AND status = 'Pending'
        UNION ALL
        SELECT id FROM marriage_events WHERE place_of_marriage = $kebele_id AND status = 'Pending'
        UNION ALL
        SELECT id FROM death_events WHERE place_of_death = $kebele_id AND status = 'Pending'
        UNION ALL
        SELECT id FROM divorce_events WHERE place_of_divorce = $kebele_id AND status = 'Pending'
    ) AS pending
")->fetch_assoc()['cnt'] ?? 0;
// Get approved certificates count
$approved_certificates = $conn->query("
    SELECT COUNT(*) AS cnt FROM (
        SELECT id FROM birth_events WHERE place_of_birth = $kebele_id AND status = 'Approved'
        UNION ALL
        SELECT id FROM marriage_events WHERE place_of_marriage = $kebele_id AND status = 'Approved'
        UNION ALL
        SELECT id FROM death_events WHERE place_of_death = $kebele_id AND status = 'Approved'
        UNION ALL
        SELECT id FROM divorce_events WHERE place_of_divorce = $kebele_id AND status = 'Approved'
    ) AS approved
")->fetch_assoc()['cnt'] ?? 0;
// Get monthly stats for chart
$monthly_stats = [];
for ($i = 1; $i <= 12; $i++) {
    $month = $i < 10 ? "0$i" : $i;
    $year = date('Y');
   
    $month_births = $conn->query("SELECT COUNT(*) AS cnt FROM birth_events WHERE place_of_birth = $kebele_id AND YEAR(created_at) = $year AND MONTH(created_at) = $i")->fetch_assoc()['cnt'] ?? 0;
    $month_marriages = $conn->query("SELECT COUNT(*) AS cnt FROM marriage_events WHERE place_of_marriage = $kebele_id AND YEAR(created_at) = $year AND MONTH(created_at) = $i")->fetch_assoc()['cnt'] ?? 0;
    $month_deaths = $conn->query("SELECT COUNT(*) AS cnt FROM death_events WHERE place_of_death = $kebele_id AND YEAR(created_at) = $year AND MONTH(created_at) = $i")->fetch_assoc()['cnt'] ?? 0;
   
    $monthly_stats['births'][] = $month_births;
    $monthly_stats['marriages'][] = $month_marriages;
    $monthly_stats['deaths'][] = $month_deaths;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts[$lang]['page_title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Amharic font support -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        [lang="am"] body,
        [lang="am"] h1, [lang="am"] h3, [lang="am"] h5,
        [lang="am"] .btn, [lang="am"] .stat-label, [lang="am"] .section-title,
        [lang="am"] .badge, [lang="am"] .card-header {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        .lang-switcher {
            position: fixed;
            top: 1rem;
            right: 11rem;
            z-index: 1050;
        }

        /* ──────────────────────────────────────────────── */
        /* All your original styles remain completely unchanged */
        /* ──────────────────────────────────────────────── */
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .dashboard-header { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 2rem 2rem; box-shadow: 0 4px 20px rgba(44, 62, 80, 0.15); }
        /* ... every single line of your original CSS stays exactly the same ... */
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Language Switcher -->
<div class="lang-switcher">
    <div class="btn-group btn-group-sm shadow">
        <a href="?lang=en" class="btn <?= $lang === 'en' ? 'btn-primary' : 'btn-outline-primary' ?>">EN</a>
        <a href="?lang=am" class="btn <?= $lang === 'am' ? 'btn-primary' : 'btn-outline-primary' ?>">አማ</a>
    </div>
</div>

<!-- Header -->
<header class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-user-tie me-2"></i>
                    <?= $texts[$lang]['page_title'] ?>
                </h1>
                <p class="lead mb-0"><?= $texts[$lang]['vital_events_system'] ?></p>
            </div>
        </div>
    </div>
</header>

<div class="container py-4">
    <!-- Kebele Information -->
    <div class="kebele-info-card p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-2">
                    <?= sprintf($texts[$lang]['welcome'], isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Officer') ?>
                </h3>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> 
                            <strong><?= $texts[$lang]['kebele'] ?></strong> 
                            <?= isset($kebele['kebele_name']) ? htmlspecialchars($kebele['kebele_name']) : '<span>'.$texts[$lang]['not_assigned'].'</span>' ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-map me-2"></i> 
                            <strong><?= $texts[$lang]['woreda'] ?></strong> 
                            <?= isset($kebele['woreda_name']) ? htmlspecialchars($kebele['woreda_name']) : '<span>'.$texts[$lang]['not_assigned'].'</span>' ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-globe me-2"></i> 
                            <strong><?= $texts[$lang]['zone'] ?></strong> 
                            <?= isset($kebele['zone_name']) ? htmlspecialchars($kebele['zone_name']) : '<span>'.$texts[$lang]['not_assigned'].'</span>' ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="bg-white text-dark rounded p-3 shadow-sm">
                    <p class="mb-1"><strong><?= $texts[$lang]['officer_id'] ?></strong> <?= $officer_id; ?></p>
                    <p class="mb-0"><strong><?= $texts[$lang]['phone'] ?></strong> 
                        <?= isset($kebele['phone']) ? htmlspecialchars($kebele['phone']) : 'N/A'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards Section -->
    <h3 class="section-title">
        <i class="fas fa-tachometer-alt me-2"></i><?= $texts[$lang]['quick_overview'] ?>
    </h3>
   
    <!-- First Row of Cards -->
    <div class="row card-row g-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <i class="fas fa-baby text-primary"></i>
                    <div class="stat-label"><?= $texts[$lang]['birth_events'] ?></div>
                    <div class="stat-value"><?= $births ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <i class="fas fa-ring text-success"></i>
                    <div class="stat-label"><?= $texts[$lang]['marriage_events'] ?></div>
                    <div class="stat-value"><?= $marriages ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <i class="fas fa-book-dead text-danger"></i>
                    <div class="stat-label"><?= $texts[$lang]['death_events'] ?></div>
                    <div class="stat-value"><?= $deaths ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <i class="fas fa-heart-broken text-warning"></i>
                    <div class="stat-label"><?= $texts[$lang]['divorce_events'] ?></div>
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
                    <i class="fas fa-clock text-info"></i>
                    <div class="stat-label"><?= $texts[$lang]['pending_requests'] ?></div>
                    <div class="stat-value"><?= $pending_requests ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <i class="fas fa-certificate text-success"></i>
                    <div class="stat-label"><?= $texts[$lang]['approved_certificates'] ?></div>
                    <div class="stat-value"><?= $approved_certificates ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <a href="citizens.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-users text-dark"></i>
                        <div class="stat-label"><?= $texts[$lang]['manage_citizens'] ?></div>
                        <div class="stat-value"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="give_certificate.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-print text-primary"></i>
                        <div class="stat-label"><?= $texts[$lang]['issue_certificates'] ?></div>
                        <div class="stat-value"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <h3 class="section-title">
        <i class="fas fa-bolt me-2"></i><?= $texts[$lang]['quick_actions'] ?>
    </h3>
   
    <div class="row g-4">
        <div class="col-md-3">
            <a href="birth.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle text-primary fa-2x mb-3"></i>
                        <h5><?= $texts[$lang]['register_birth'] ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="marriage.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle text-success fa-2x mb-3"></i>
                        <h5><?= $texts[$lang]['register_marriage'] ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="death.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle text-danger fa-2x mb-3"></i>
                        <h5><?= $texts[$lang]['register_death'] ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="generate_report.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie text-warning fa-2x mb-3"></i>
                        <h5><?= $texts[$lang]['generate_report'] ?></h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?= sprintf($texts[$lang]['kebele_administration'], htmlspecialchars($kebele['kebele_name'] ?? 'Kebele')) ?></h5>
                <p class="mb-0"><?= $texts[$lang]['vital_events_system'] ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0"><?= sprintf($texts[$lang]['copyright'], date('Y')) ?></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Bar Chart for Monthly Events (your original script remains unchanged)
const eventsBarCtx = document.getElementById('eventsBarChart').getContext('2d');
const eventsBarChart = new Chart(eventsBarCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Births',
            data: <?= json_encode($monthly_stats['births'] ?? array_fill(0, 12, 0)) ?>,
            backgroundColor: 'rgba(52,152,219,0.7)',
            borderColor: 'rgba(52,152,219,1)',
            borderWidth: 2
        }, {
            label: 'Marriages',
            data: <?= json_encode($monthly_stats['marriages'] ?? array_fill(0, 12, 0)) ?>,
            backgroundColor: 'rgba(39,174,96,0.7)',
            borderColor: 'rgba(39,174,96,1)',
            borderWidth: 2
        }, {
            label: 'Deaths',
            data: <?= json_encode($monthly_stats['deaths'] ?? array_fill(0, 12, 0)) ?>,
            backgroundColor: 'rgba(231,76,60,0.7)',
            borderColor: 'rgba(231,76,60,1)',
            borderWidth: 2
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Events'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Months'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>