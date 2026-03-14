<?php
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

// Translations array for dashboard
$translations = [
    'en' => [
        'title' => 'Zone Dashboard',
        'dashboard_title' => 'Zone Dashboard',
        'analytics_subtitle' => 'Vital Events Registration Analytics',
        'natural_population' => 'Natural Population',
        'births_minus_deaths' => 'Births (%s) - Deaths (%s)',
        'woredas' => 'Woredas',
        'kebeles' => 'Kebeles',
        'registered_citizens' => 'Registered Citizens',
        'births' => 'Births',
        'birth_rate' => 'Rate: %s‰',
        'deaths' => 'Deaths',
        'death_rate' => 'Rate: %s‰',
        'marriages' => 'Marriages',
        'divorces' => 'Divorces',
        'birth_death_ratio' => 'Birth/Death Ratio',
        'births_per_death' => 'Births per death',
        'marriage_divorce_ratio' => 'Marriage/Divorce',
        'marriages_per_divorce' => 'Marriages per divorce',
        'gender_ratio' => 'Gender Ratio',
        'male_female' => 'Male:Female',
        'growth_rate' => 'Growth Rate',
        'natural_increase_rate' => 'Natural increase rate',
        'events_trends' => 'Events Trends (6 months)',
        'birth_gender_distribution' => 'Birth Gender Distribution',
        'male' => 'Male',
        'female' => 'Female',
        'logout' => 'Logout',
        'home' => 'Home',
        'profile' => 'Profile',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'welcome' => 'Welcome',
        'copyright' => 'VERMS &copy; 2025. All rights reserved.'
    ],
    'am' => [
        'title' => 'ዞን ዳሽቦርድ',
        'dashboard_title' => 'ዞን ዳሽቦርድ',
        'analytics_subtitle' => 'የህይወት ክስተቶች ምዝገባ ትንተና',
        'natural_population' => 'የተፈጥሯዊ ህዝብ',
        'births_minus_deaths' => 'የተወለዱ (%s) - የሞቱ (%s)',
        'woredas' => 'ወረዳዎች',
        'kebeles' => 'ቀበሌዎች',
        'registered_citizens' => 'የተመዘገቡ ዜጎች',
        'births' => 'የተወለዱ',
        'birth_rate' => 'መጠን: %s‰',
        'deaths' => 'የሞቱ',
        'death_rate' => 'መጠን: %s‰',
        'marriages' => 'የተዋለዱ',
        'divorces' => 'የተፋቀሩ',
        'birth_death_ratio' => 'የትውልድ/ሞት ሬሾ',
        'births_per_death' => 'በአንድ ሞት የሚወለዱ',
        'marriage_divorce_ratio' => 'ጋብቻ/ፍቺ ሬሾ',
        'marriages_per_divorce' => 'በአንድ ፍቺ የሚዋለዱ',
        'gender_ratio' => 'ጾታ ሬሾ',
        'male_female' => 'ወንድ:ሴት',
        'growth_rate' => 'ዕድገት መጠን',
        'natural_increase_rate' => 'የተፈጥሮ ጭማሪ መጠን',
        'events_trends' => 'የክስተቶች አዝማሚያ (6 ወራት)',
        'birth_gender_distribution' => 'የትውልድ ጾታ ስርጭት',
        'male' => 'ወንድ',
        'female' => 'ሴት',
        'logout' => 'ውጣ',
        'home' => 'መነሻ',
        'profile' => 'መገለጫ',
        'reports' => 'ሪፖርቶች',
        'settings' => 'ቅንብሮች',
        'welcome' => 'እንኳን ደህና መጡ',
        'copyright' => 'ቪ.ኢ.አር.ኤም.ኤስ &copy; 2025. መብቱ በህግ የተጠበቀ ነው።'
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

// Only allow zone/statistician roles
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['zone', 'statistician'])) {
    header('Location: ../login.php');
    exit();
}

$officer_id = $_SESSION['user_id'];

// Get user's zone
$zone = $conn->query("SELECT * FROM zones WHERE zone_officer_id = $officer_id")->fetch_assoc();
if (!$zone) die("Zone not found.");

$zone_id = $zone['id'];
$zone_name = $zone['zone_name'];

// Stats
$woredas = $conn->query("SELECT COUNT(*) as cnt FROM woredas WHERE zone_id = $zone_id")->fetch_assoc()['cnt'];
$kebeles = $conn->query("SELECT COUNT(*) as cnt FROM kebeles WHERE zone_id = $zone_id")->fetch_assoc()['cnt'];
$citizens = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE zone = '$zone_name' AND role = 'citizen'")->fetch_assoc()['cnt'];

$births = $conn->query("SELECT COUNT(*) as cnt FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id WHERE k.zone_id = $zone_id")->fetch_assoc()['cnt'];
$marriages = $conn->query("SELECT COUNT(*) as cnt FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id WHERE k.zone_id = $zone_id")->fetch_assoc()['cnt'];
$deaths = $conn->query("SELECT COUNT(*) as cnt FROM death_events d JOIN kebeles k ON d.place_of_death = k.id WHERE k.zone_id = $zone_id")->fetch_assoc()['cnt'];
$divorces = $conn->query("SELECT COUNT(*) as cnt FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id WHERE k.zone_id = $zone_id")->fetch_assoc()['cnt'];

// Gender
$gender = $conn->query("SELECT 
    SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male,
    SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female
    FROM birth_events b 
    JOIN kebeles k ON b.place_of_birth = k.id 
    WHERE k.zone_id = $zone_id")->fetch_assoc();

// CORRECTED: Population calculation based on births minus deaths (natural change)
$natural_population = $births - $deaths;

// Calculate ratios
$birth_death_ratio = $deaths > 0 ? round($births / $deaths, 2) : $births;
$marriage_divorce_ratio = $divorces > 0 ? round($marriages / $divorces, 2) : $marriages;
$birth_rate = $natural_population > 0 ? round(($births / $natural_population) * 1000, 2) : 0;
$death_rate = $natural_population > 0 ? round(($deaths / $natural_population) * 1000, 2) : 0;
$natural_increase = $births - $deaths;
$growth_rate = $natural_population > 0 ? round((($births - $deaths) / $natural_population) * 100, 2) : 0;

// Monthly trends (last 6 months)
$monthly_labels = [];
$monthly_births = [];
$monthly_marriages = [];
$monthly_deaths = [];
$monthly_divorces = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $label;
    $monthly_births[] = (int)$conn->query("SELECT COUNT(*) as cnt FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id WHERE k.zone_id = $zone_id AND DATE_FORMAT(b.created_at, '%Y-%m') = '$ym'")->fetch_assoc()['cnt'];
    $monthly_marriages[] = (int)$conn->query("SELECT COUNT(*) as cnt FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id WHERE k.zone_id = $zone_id AND DATE_FORMAT(m.created_at, '%Y-%m') = '$ym'")->fetch_assoc()['cnt'];
    $monthly_deaths[] = (int)$conn->query("SELECT COUNT(*) as cnt FROM death_events d JOIN kebeles k ON d.place_of_death = k.id WHERE k.zone_id = $zone_id AND DATE_FORMAT(d.created_at, '%Y-%m') = '$ym'")->fetch_assoc()['cnt'];
    $monthly_divorces[] = (int)$conn->query("SELECT COUNT(*) as cnt FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id WHERE k.zone_id = $zone_id AND DATE_FORMAT(dv.created_at, '%Y-%m') = '$ym'")->fetch_assoc()['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($zone_name) ?> - <?= t('title') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg,#f5f7fa 0%,#eaf6fb 100%); 
            min-height:100vh; 
            font-family: <?= $lang === 'am' ? "'Ethiopia Jiret', 'Nyala', sans-serif" : "'Segoe UI', sans-serif" ?>;
        }
        .dashboard-header { 
            background: linear-gradient(135deg,#8e44ad 0%,#9b59b6 100%); 
            color: white; 
            padding: 2.5rem 0 2rem 0; 
            margin-bottom: 2rem; 
            border-radius: 0 0 2.5rem 2.5rem; 
            box-shadow: 0 4px 20px rgba(44,62,80,0.08);
        }
        .dashboard-header h1 { 
            font-size: 2.6rem; 
            font-weight: 700;
        }
        .dashboard-header .lead { 
            font-size: 1.3rem;
        }
        .stat-card { 
            border-radius: 1rem; 
            box-shadow: 0 3px 18px rgba(44,62,80,0.08); 
            background: #fff; 
            padding: 1.2rem; 
            border: none;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card i { 
            font-size: 2.2rem; 
            margin-bottom: 0.6rem;
        }
        .stat-label { 
            font-weight: 600; 
            color: #8e44ad;
            font-size: <?= $lang === 'am' ? '0.9rem' : '1rem' ?>;
        }
        .stat-value { 
            font-size: 2.1rem; 
            font-weight:700;
        }
        .stat-ratio { 
            font-size: 0.9rem; 
            color: #6c757d; 
            margin-top: 0.2rem;
            font-size: <?= $lang === 'am' ? '0.8rem' : '0.9rem' ?>;
        }
        .chart-container { 
            background: #fff; 
            border-radius: 1.2rem; 
            box-shadow: 0 2px 10px rgba(44,62,80,0.09); 
            padding: 2rem; 
            margin-bottom: 2rem;
        }
        .population-positive { color: #28a745; }
        .population-negative { color: #dc3545; }
        .population-neutral { color: #6c757d; }
        
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
        
        @media (max-width: 768px) { 
            .stat-card { padding: 0.8rem;}
            .stat-value { font-size: 1.5rem;}
            .dashboard-header { padding: 1.5rem 0; border-radius: 0 0 1.5rem 1.5rem;}
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
    include '../includes/header.php'; 
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

    <div class="dashboard-header">
        <div class="container">
            <h1 class="mb-2"><i class="fas fa-globe-africa me-2"></i><?= htmlspecialchars($zone_name) ?> <?= t('dashboard_title') ?></h1>
            <div class="lead"><?= t('analytics_subtitle') ?></div>
        </div>
    </div>
    
    <div class="container pb-5">
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-users text-primary"></i>
                    <div class="stat-label"><?= t('natural_population') ?></div>
                    <div class="stat-value <?= $natural_population > 0 ? 'population-positive' : ($natural_population < 0 ? 'population-negative' : 'population-neutral') ?>">
                        <?= number_format($natural_population) ?>
                    </div>
                    <div class="stat-ratio"><?= t('births_minus_deaths', [$births, $deaths]) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-map-marked-alt text-info"></i>
                    <div class="stat-label"><?= t('woredas') ?></div>
                    <div class="stat-value"><?= $woredas ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-map-pin text-success"></i>
                    <div class="stat-label"><?= t('kebeles') ?></div>
                    <div class="stat-value"><?= $kebeles ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-user-friends text-secondary"></i>
                    <div class="stat-label"><?= t('registered_citizens') ?></div>
                    <div class="stat-value"><?= $citizens ?></div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-baby text-warning"></i>
                    <div class="stat-label"><?= t('births') ?></div>
                    <div class="stat-value"><?= $births ?></div>
                    <div class="stat-ratio"><?= t('birth_rate', [$birth_rate]) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-book-dead text-dark"></i>
                    <div class="stat-label"><?= t('deaths') ?></div>
                    <div class="stat-value"><?= $deaths ?></div>
                    <div class="stat-ratio"><?= t('death_rate', [$death_rate]) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-ring text-danger"></i>
                    <div class="stat-label"><?= t('marriages') ?></div>
                    <div class="stat-value"><?= $marriages ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-file-contract text-secondary"></i>
                    <div class="stat-label"><?= t('divorces') ?></div>
                    <div class="stat-value"><?= $divorces ?></div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-balance-scale text-info"></i>
                    <div class="stat-label"><?= t('birth_death_ratio') ?></div>
                    <div class="stat-value"><?= $birth_death_ratio ?>:1</div>
                    <div class="stat-ratio"><?= t('births_per_death') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-heart text-danger"></i>
                    <div class="stat-label"><?= t('marriage_divorce_ratio') ?></div>
                    <div class="stat-value"><?= $marriage_divorce_ratio ?>:1</div>
                    <div class="stat-ratio"><?= t('marriages_per_divorce') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-mars-double text-primary"></i>
                    <div class="stat-label"><?= t('gender_ratio') ?></div>
                    <div class="stat-value"><?= $gender['male'] ?>:<?= $gender['female'] ?></div>
                    <div class="stat-ratio"><?= t('male_female') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-chart-line <?= $growth_rate > 0 ? 'text-success' : ($growth_rate < 0 ? 'text-danger' : 'text-secondary') ?>"></i>
                    <div class="stat-label"><?= t('growth_rate') ?></div>
                    <div class="stat-value <?= $growth_rate > 0 ? 'population-positive' : ($growth_rate < 0 ? 'population-negative' : 'population-neutral') ?>">
                        <?= $growth_rate ?>%
                    </div>
                    <div class="stat-ratio"><?= t('natural_increase_rate') ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="fas fa-chart-bar text-primary"></i> <?= t('events_trends') ?></h5>
                    <canvas id="eventsTrendsChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="fas fa-chart-pie text-success"></i> <?= t('birth_gender_distribution') ?></h5>
                    <canvas id="citizenGenderPieChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Save language preference
        document.addEventListener('DOMContentLoaded', function() {
            const lang = '<?= $lang ?>';
            localStorage.setItem('preferred_lang', lang);
        });
        
        const ctx = document.getElementById('eventsTrendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthly_labels) ?>,
                datasets: [
                    { 
                        label: '<?= t('births') ?>', 
                        data: <?= json_encode($monthly_births) ?>, 
                        backgroundColor:'rgba(243,156,18,0.7)', 
                        borderColor:'#f39c12', 
                        borderWidth: 1 
                    },
                    { 
                        label: '<?= t('marriages') ?>', 
                        data: <?= json_encode($monthly_marriages) ?>, 
                        backgroundColor:'rgba(231,76,60,0.7)', 
                        borderColor:'#e74c3c', 
                        borderWidth: 1 
                    },
                    { 
                        label: '<?= t('deaths') ?>', 
                        data: <?= json_encode($monthly_deaths) ?>, 
                        backgroundColor:'rgba(52,73,94,0.7)', 
                        borderColor:'#34495e', 
                        borderWidth: 1 
                    },
                    { 
                        label: '<?= t('divorces') ?>', 
                        data: <?= json_encode($monthly_divorces) ?>, 
                        backgroundColor:'rgba(142,68,173,0.7)', 
                        borderColor:'#8e44ad', 
                        borderWidth: 1 
                    }
                ]
            },
            options: { 
                plugins: { legend: { position: 'top' } }, 
                scales: { 
                    y: { beginAtZero: true },
                    x: { stacked: false }
                } 
            }
        });
        
        const ctxPie = document.getElementById('citizenGenderPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['<?= t('male') ?>','<?= t('female') ?>'],
                datasets: [{
                    data: [<?= $gender['male'] ?>,<?= $gender['female'] ?>],
                    backgroundColor: ['#3498db','#e74c3c'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: { 
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            font: {
                                family: '<?= $lang === 'am' ? "Ethiopia Jiret, Nyala, sans-serif" : "Segoe UI, sans-serif" ?>'
                            }
                        }
                    } 
                } 
            }
        });
    </script>
    
    <?php 
    // Update footer include to pass translations
    $GLOBALS['lang'] = $lang;
    $GLOBALS['translations'] = $translations;
    include '../includes/footer.php'; 
    ?>
</body>
</html>