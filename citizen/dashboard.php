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
        'page_title' => 'Citizen Dashboard - VERMS',
        'dashboard_title' => 'Citizen Dashboard',
        'welcome_back' => 'Welcome back',
        'manage_vital_events' => 'Manage your vital events registrations and certificates',
        'citizen' => 'Citizen',
        
        // Statistics
        'pending_requests' => 'Pending Requests',
        'certificates_ready' => 'Certificates Ready',
        'recent_activities' => 'Recent Activities',
        
        // Quick Actions
        'quick_actions' => 'Quick Actions',
        'request_event_registration' => 'Request Event Registration',
        'register_events' => 'Register for birth, marriage, death, or divorce events',
        'get_started' => 'Get Started',
        'receive_certificate' => 'Receive Certificate',
        'collect_approved_certificates' => 'Collect your approved certificates',
        'view_certificates' => 'View Certificates',
        'make_payment' => 'Make Payment',
        'pay_for_registrations' => 'Pay for your event registrations',
        'pay_now' => 'Pay Now',
        'feedback' => 'Feedback',
        'share_experience' => 'Share your experience with us',
        'give_feedback' => 'Give Feedback',
        
        // Status badges
        'pending' => 'Pending',
        'approved' => 'Approved',
        'paid' => 'Paid',
        
        // Event types
        'birth' => 'Birth',
        'marriage' => 'Marriage',
        'death' => 'Death',
        'divorce' => 'Divorce',
        
        // Recent events
        'recent_events' => 'Recent Events',
        'event' => 'Event',
        'name' => 'Name',
        'status' => 'Status',
        'date' => 'Date',
        'no_recent_events' => 'No recent events found',
        
        // Profile info
        'profile_information' => 'Profile Information',
        'full_name' => 'Full Name',
        'username' => 'Username',
        'email' => 'Email',
        'phone' => 'Phone',
        'kebele' => 'Kebele',
        'woreda' => 'Woreda',
        'zone' => 'Zone',
        'edit_profile' => 'Edit Profile'
    ],
    'am' => [
        // Page title and headers
        'page_title' => 'ዜጋ ዳሽቦርድ - ቪ.ኢ.አር.ኤም.ኤስ',
        'dashboard_title' => 'ዜጋ ዳሽቦርድ',
        'welcome_back' => 'እንኳን ደህና መጡ',
        'manage_vital_events' => 'የህይወት ክስተቶች ምዝገባዎችዎን እና የምስክር ወረቀቶችዎን ያስተዳድሩ',
        'citizen' => 'ዜጋ',
        
        // Statistics
        'pending_requests' => 'በመጠባበቅ ላይ ያሉ ጥያቄዎች',
        'certificates_ready' => 'ዝግጁ የሆኑ ምስክር ወረቀቶች',
        'recent_activities' => 'የቅርብ እንቅስቃሴዎች',
        
        // Quick Actions
        'quick_actions' => 'ፈጣን እርምጃዎች',
        'request_event_registration' => 'የክስተት ምዝገባ ይጠይቁ',
        'register_events' => 'ለልደት፣ ጋብቻ፣ ሞት ወይም ፍቺ ክስተቶች ይመዝግቡ',
        'get_started' => 'ጀምር',
        'receive_certificate' => 'ምስክር ወረቀት ይቀበሉ',
        'collect_approved_certificates' => 'የተፈቀዱ ምስክር ወረቀቶችዎን ያሰባስቡ',
        'view_certificates' => 'ምስክር ወረቀቶችን ይመልከቱ',
        'make_payment' => 'ክፍያ ያድርጉ',
        'pay_for_registrations' => 'ለክስተት ምዝገባዎችዎ ይክፈሉ',
        'pay_now' => 'አሁን ይክፈሉ',
        'feedback' => 'ግብረመልስ',
        'share_experience' => 'በእኛ ያለዎትን ልምድ ያካፍሉ',
        'give_feedback' => 'ግብረመልስ ይስጡ',
        
        // Status badges
        'pending' => 'በመጠባበቅ ላይ',
        'approved' => 'ተፈቅዷል',
        'paid' => 'ከፍሏል',
        
        // Event types
        'birth' => 'ልደት',
        'marriage' => 'ጋብቻ',
        'death' => 'ሞት',
        'divorce' => 'ፍቺ',
        
        // Recent events
        'recent_events' => 'የቅርብ ክስተቶች',
        'event' => 'ክስተት',
        'name' => 'ስም',
        'status' => 'ሁኔታ',
        'date' => 'ቀን',
        'no_recent_events' => 'የቅርብ ክስተቶች አልተገኙም',
        
        // Profile info
        'profile_information' => 'የመገለጫ መረጃ',
        'full_name' => 'ሙሉ ስም',
        'username' => 'የተጠቃሚ ስም',
        'email' => 'ኢሜይል',
        'phone' => 'ስልክ',
        'kebele' => 'ቀበሌ',
        'woreda' => 'ወረዳ',
        'zone' => 'ዞን',
        'edit_profile' => 'መገለጫ አርትዕ'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

require_once '../includes/db_connection.php';

// Check if user is citizen
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'citizen') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->prepare("SELECT fullname, username, email, phone, kebele, woreda, zone FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get pending requests count
$pending_query = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE citizen_id = ? AND status = 'Pending'");
$pending_query->bind_param("i", $user_id);
$pending_query->execute();
$pending_result = $pending_query->get_result();
$pending_count = $pending_result->fetch_assoc()['count'];

// Get certificates ready for collection
$certificates_query = $conn->prepare("
    SELECT COUNT(*) as count FROM (
        SELECT id FROM birth_events WHERE user_id = ? AND status = 'Approved'
        UNION ALL
        SELECT id FROM marriage_events WHERE user_id = ? AND status = 'Approved'
        UNION ALL
        SELECT id FROM death_events WHERE user_id = ? AND status = 'Approved'
        UNION ALL
        SELECT id FROM divorce_events WHERE user_id = ? AND status = 'Approved'
    ) as approved_events
");
$certificates_query->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$certificates_query->execute();
$certificates_result = $certificates_query->get_result();
$certificates_count = $certificates_result->fetch_assoc()['count'];

// Get recent events
$recent_events = [];
$events_query = $conn->prepare("
    (SELECT 'Birth' as type, child_name as name, status, created_at FROM birth_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'Marriage' as type, husband_name as name, status, created_at FROM marriage_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'Death' as type, deceased_name as name, status, created_at FROM death_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'Divorce' as type, husband_name as name, status, created_at FROM divorce_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 5
");
$events_query->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$events_query->execute();
$events_result = $events_query->get_result();
while ($event = $events_result->fetch_assoc()) {
    $recent_events[] = $event;
}
?>
<!DOCTYPE html>
<html lang='<?php echo $lang; ?>'>
<head>
    <meta charset='UTF-8'>
    <title><?php echo t('page_title'); ?></title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php if($lang === 'am'): ?>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: justify;
        }
        
        .dashboard-title,
        .dashboard-subtitle,
        .card-header,
        .stat-label,
        .action-title,
        .action-description,
        .btn-action,
        .status-badge,
        .event-badge,
        .h3,
        .h5,
        small,
        .form-label {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        <?php endif; ?>
        
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --danger-color: #e74c3c;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
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
            transition: all 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border: none;
            padding: 18px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 25px;
        }

        .quick-action-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 2px solid transparent;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            border-color: var(--secondary-color);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .action-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .btn-action {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
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

        .status-approved {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success-color);
        }

        .status-paid {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .menu-badge {
            background: var(--warning-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: 5px;
        }

        .user-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
        }

        .event-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
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
            color: var(--danger-color);
        }
        
        /* Language switcher */
        .language-switcher {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 5px;
        }
        
        .lang-btn {
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            background: transparent;
            font-weight: 500;
            transition: all 0.3s ease;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .lang-btn.active {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
        }
        
        .lang-btn:hover:not(.active) {
            background: rgba(44, 62, 80, 0.1);
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .language-switcher {
                top: 70px;
                right: 10px;
            }
            
            .lang-btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<?php include_once '../includes/header.php'; ?>

<!-- Language Switcher -->
<div class="language-switcher">
    <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">
        <i class="fas fa-globe-americas me-1"></i>EN
    </button>
    <button class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>" onclick="changeLanguage('am')">
        <i class="fas fa-globe-africa me-1"></i>አማ
    </button>
</div>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-user me-2"></i><?php echo t('dashboard_title'); ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?php echo t('welcome_back'); ?>, <?= htmlspecialchars($user['fullname']) ?>!
                    <br>
                    <small><?php echo t('manage_vital_events'); ?></small>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="user-info-card" style="display: inline-block; padding: 15px 20px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-id-card fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($user['kebele']) ?></h5>
                            <h3 class="mb-0"><?php echo t('citizen'); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number text-primary"><?= $pending_count ?></div>
                <div class="stat-label"><?php echo t('pending_requests'); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number text-success"><?= $certificates_count ?></div>
                <div class="stat-label"><?php echo t('certificates_ready'); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number text-info"><?= count($recent_events) ?></div>
                <div class="stat-label"><?php echo t('recent_activities'); ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4"><i class="fas fa-bolt me-2"></i><?php echo t('quick_actions'); ?></h3>
        </div>
        <div class="col-md-3 mb-4">
            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="action-title"><?php echo t('request_event_registration'); ?></div>
                <div class="action-description"><?php echo t('register_events'); ?></div>
                <a href="request_event.php" class="btn btn-action"><?php echo t('get_started'); ?></a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="action-title"><?php echo t('receive_certificate'); ?></div>
                <div class="action-description"><?php echo t('collect_approved_certificates'); ?></div>
                <a href="receive_certificate.php" class="btn btn-action">
                    <?php echo t('view_certificates'); ?>
                    <?php if ($certificates_count > 0): ?>
                        <span class="badge bg-warning ms-1"><?= $certificates_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="action-title"><?php echo t('make_payment'); ?></div>
                <div class="action-description"><?php echo t('pay_for_registrations'); ?></div>
                <a href="make_payment.php" class="btn btn-action"><?php echo t('pay_now'); ?></a>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="action-title"><?php echo t('feedback'); ?></div>
                <div class="action-description"><?php echo t('share_experience'); ?></div>
                <a href="feedback.php" class="btn btn-action"><?php echo t('give_feedback'); ?></a>
            </div>
        </div>
    </div>

    <!-- Recent Events and Profile Information -->
    <div class="row">
        <!-- Recent Events -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i><?php echo t('recent_events'); ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_events)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('event'); ?></th>
                                        <th><?php echo t('name'); ?></th>
                                        <th><?php echo t('status'); ?></th>
                                        <th><?php echo t('date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                        <tr>
                                            <td>
                                                <span class="event-badge badge-<?= strtolower($event['type']) ?>">
                                                    <?php 
                                                    if ($event['type'] === 'Birth') echo t('birth');
                                                    elseif ($event['type'] === 'Marriage') echo t('marriage');
                                                    elseif ($event['type'] === 'Death') echo t('death');
                                                    else echo t('divorce');
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($event['name']) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= strtolower($event['status']) ?>">
                                                    <?php 
                                                    if ($event['status'] === 'Pending') echo t('pending');
                                                    elseif ($event['status'] === 'Approved') echo t('approved');
                                                    elseif ($event['status'] === 'Paid') echo t('paid');
                                                    else echo $event['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($event['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?php echo t('no_recent_events'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-circle me-2"></i><?php echo t('profile_information'); ?>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted"><?php echo t('full_name'); ?></label>
                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['fullname']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted"><?php echo t('username'); ?></label>
                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['username']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted"><?php echo t('email'); ?></label>
                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted"><?php echo t('phone'); ?></label>
                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['phone']) ?></p>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label text-muted"><?php echo t('kebele'); ?></label>
                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['kebele']) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted"><?php echo t('woreda'); ?></label>
                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['woreda']) ?></p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label text-muted"><?php echo t('zone'); ?></label>
                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($user['zone']) ?></p>
                    </div>
                    <div class="mt-4">
                        <a href="edit_profile.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-edit me-2"></i><?php echo t('edit_profile'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
function changeLanguage(lang) {
    window.location.href = '?lang=' + lang;
}

// Format dates based on language
document.addEventListener('DOMContentLoaded', function() {
    const lang = '<?php echo $lang; ?>';
    const dateElements = document.querySelectorAll('td:last-child');
    
    dateElements.forEach(element => {
        const dateText = element.textContent.trim();
        if (dateText) {
            const [month, day, year] = dateText.replace(',', '').split(' ');
            if (lang === 'am') {
                const amharicMonths = {
                    'Jan': 'ጃንዩ',
                    'Feb': 'ፌብሩ',
                    'Mar': 'ማርች',
                    'Apr': 'ኤፕሪ',
                    'May': 'ሜይ',
                    'Jun': 'ጁን',
                    'Jul': 'ጁላይ',
                    'Aug': 'ኦገስ',
                    'Sep': 'ሴፕቴ',
                    'Oct': 'ኦክቶ',
                    'Nov': 'ኖቬም',
                    'Dec': 'ዲሴም'
                };
                const amharicMonth = amharicMonths[month] || month;
                element.textContent = `${amharicMonth} ${day}, ${year}`;
            }
        }
    });
});
</script>
</body>
</html>