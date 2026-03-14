<?php
// admin/account_management.php
session_start();
require_once '../includes/db_connection.php';

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
        'page_title' => 'Account Management - VERMS',
        'page_header' => 'Account Management',
        'page_subtitle' => 'Manage officer accounts and permissions across the system',
        'create_officer' => 'Create New Officer',
        'create_first_officer' => 'Create First Officer',
        
        // Statistics
        'total_officers' => 'Total Officers',
        'zone_officers' => 'Zone Officers',
        'woreda_officers' => 'Woreda Officers',
        'kebele_officers' => 'Kebele Officers',
        'statisticians' => 'Statisticians',
        
        // Filter
        'filter_officers' => 'Filter Officers',
        'all_roles' => 'All Roles',
        'zone_officer' => 'Zone Officer',
        'woreda_officer' => 'Woreda Officer',
        'kebele_officer' => 'Kebele Officer',
        'statistician' => 'Statistician',
        
        // Table headers
        'officer_accounts' => 'Officer Accounts',
        'officer' => 'Officer',
        'username' => 'Username',
        'email' => 'Email',
        'role' => 'Role',
        'status' => 'Status',
        'actions' => 'Actions',
        
        // Status badges
        'active' => 'Active',
        'deactivated' => 'Deactivated',
        
        // Action buttons
        'edit' => 'Edit',
        'reset_pw' => 'Reset PW',
        'deactivate' => 'Deactivate',
        'activate' => 'Activate',
        
        // Empty state
        'no_officers_found' => 'No Officers Found',
        'no_officers_message' => 'There are no officer accounts in the system yet.',
        
        // Modals
        'edit_officer_account' => 'Edit Officer Account',
        'reset_password' => 'Reset Password',
        'cancel' => 'Cancel',
        'save_changes' => 'Save Changes',
        
        // Form labels
        'full_name' => 'Full Name',
        'username' => 'Username',
        'email_address' => 'Email Address',
        'role' => 'Role',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'reset_password_check' => 'Reset Password',
        
        // Form placeholders and help text
        'enter_new_password' => 'Enter new password',
        'confirm_new_password' => 'Confirm new password',
        'password_length' => 'Password must be at least 8 characters long',
        'passwords_match' => '✓ Passwords match',
        'passwords_not_match' => '✗ Passwords do not match',
        
        // Messages
        'deactivate_confirm' => 'Are you sure you want to deactivate this officer?',
        'activate_confirm' => 'Are you sure you want to activate this officer?',
        'password_required' => 'Password must be at least 8 characters long!',
        'passwords_mismatch' => 'Passwords do not match!',
        'reset_password_for' => 'Reset password for',
        'search_officers' => 'Search officers...',
        
        // Success/Error messages
        'deactivate_success' => 'Officer deactivated successfully!',
        'deactivate_error' => 'Error deactivating officer: ',
        'activate_success' => 'Officer activated successfully!',
        'activate_error' => 'Error activating officer: ',
        'update_success' => 'Officer updated successfully!',
        'update_error' => 'Error updating officer: ',
        'update_password_success' => 'Officer updated and password reset successfully!',
        'reset_success' => 'Password reset successfully!',
        'reset_error' => 'Error resetting password: ',
        'password_required_error' => 'Please enter a new password!',
        
        // User roles in Amharic for display
        'role_zone' => 'Zone',
        'role_woreda' => 'Woreda',
        'role_kebele' => 'Kebele',
        'role_statistician' => 'Statistician'
    ],
    'am' => [
        // Page title and headers
        'page_title' => 'መለያ አስተዳደር - ቪ.ኢ.አር.ኤም.ኤስ',
        'page_header' => 'መለያ አስተዳደር',
        'page_subtitle' => 'በስርዓቱ ውስጥ የአሰልጣኞችን መለያዎች እና ፈቃዶችን ያስተዳድሩ',
        'create_officer' => 'አዲስ አሰልጣኝ ይፍጠሩ',
        'create_first_officer' => 'የመጀመሪያ አሰልጣኝ ይፍጠሩ',
        
        // Statistics
        'total_officers' => 'ጠቅላላ አሰልጣኞች',
        'zone_officers' => 'የዞን አሰልጣኞች',
        'woreda_officers' => 'የወረዳ አሰልጣኞች',
        'kebele_officers' => 'የቀበሌ አሰልጣኞች',
        'statisticians' => 'ቁጥር አጥማጆች',
        
        // Filter
        'filter_officers' => 'አሰልጣኞችን አጣር',
        'all_roles' => 'ሁሉም ሚናዎች',
        'zone_officer' => 'የዞን አሰልጣኝ',
        'woreda_officer' => 'የወረዳ አሰልጣኝ',
        'kebele_officer' => 'የቀበሌ አሰልጣኝ',
        'statistician' => 'ቁጥር አጥማጅ',
        
        // Table headers
        'officer_accounts' => 'የአሰልጣኞች መለያዎች',
        'officer' => 'አሰልጣኝ',
        'username' => 'የተጠቃሚ ስም',
        'email' => 'ኢሜይል',
        'role' => 'ሚና',
        'status' => 'ሁኔታ',
        'actions' => 'ድርጊቶች',
        
        // Status badges
        'active' => 'ንቁ',
        'deactivated' => 'የተዘጋ',
        
        // Action buttons
        'edit' => 'አርትዕ',
        'reset_pw' => 'የይለፍ ቃል ዳግም ያዘጋጁ',
        'deactivate' => 'አቦዝን',
        'activate' => 'አግብን',
        
        // Empty state
        'no_officers_found' => 'አሰልጣኞች አልተገኙም',
        'no_officers_message' => 'በስርዓቱ ውስጥ ገና የአሰልጣኝ መለያዎች የሉም።',
        
        // Modals
        'edit_officer_account' => 'የአሰልጣኝ መለያ አርትዕ',
        'reset_password' => 'የይለፍ ቃል ዳግም ያዘጋጁ',
        'cancel' => 'ይቅር',
        'save_changes' => 'ለውጦችን አስቀምጥ',
        
        // Form labels
        'full_name' => 'ሙሉ ስም',
        'username' => 'የተጠቃሚ ስም',
        'email_address' => 'የኢሜይል አድራሻ',
        'role' => 'ሚና',
        'new_password' => 'አዲስ የይለፍ ቃል',
        'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
        'reset_password_check' => 'የይለፍ ቃል ዳግም ያዘጋጁ',
        
        // Form placeholders and help text
        'enter_new_password' => 'አዲስ የይለፍ ቃል ያስገቡ',
        'confirm_new_password' => 'አዲስ የይለፍ ቃል ያረጋግጡ',
        'password_length' => 'የይለፍ ቃሉ ቢያንስ 8 ቁምፊዎች መሆን አለበት',
        'passwords_match' => '✓ የይለፍ ቃላቶቹ ይዛመዳሉ',
        'passwords_not_match' => '✗ የይለፍ ቃላቶቹ አይዛመዱም',
        
        // Messages
        'deactivate_confirm' => 'ይህን አሰልጣኝ ማቦዘን እንደሚፈልጉ እርግጠኛ ነዎት?',
        'activate_confirm' => 'ይህን አሰልጣኝ ማግባት እንደሚፈልጉ እርግጠኛ ነዎት?',
        'password_required' => 'የይለፍ ቃሉ ቢያንስ 8 ቁምፊዎች መሆን አለበት!',
        'passwords_mismatch' => 'የይለፍ ቃላቶቹ አይዛመዱም!',
        'reset_password_for' => 'የይለፍ ቃል ዳግም ያዘጋጁ ለ',
        'search_officers' => 'አሰልጣኞችን ይፈልጉ...',
        
        // Success/Error messages
        'deactivate_success' => 'አሰልጣኙ በተሳካ ሁኔታ ተዘግቷል!',
        'deactivate_error' => 'አሰልጣኝን በማቦዘን ላይ ስህተት: ',
        'activate_success' => 'አሰልጣኙ በተሳካ ሁኔታ ተከፍቷል!',
        'activate_error' => 'አሰልጣኝን በመክፈት ላይ ስህተት: ',
        'update_success' => 'አሰልጣኙ በተሳካ ሁኔታ ተሻሽሏል!',
        'update_error' => 'አሰልጣኝን በማሻሻል ላይ ስህተት: ',
        'update_password_success' => 'አሰልጣኙ ተሻሽሏል እና የይለፍ ቃሉ ዳግም ተዘጋጅቷል!',
        'reset_success' => 'የይለፍ ቃሉ በተሳካ ሁኔታ ዳግም ተዘጋጅቷል!',
        'reset_error' => 'የይለፍ ቃልን በመደገም ማዘጋጀት ላይ ስህተት: ',
        'password_required_error' => 'እባክዎ አዲስ የይለፍ ቃል ያስገቡ!',
        
        // User roles in Amharic for display
        'role_zone' => 'ዞን',
        'role_woreda' => 'ወረዳ',
        'role_kebele' => 'ቀበሌ',
        'role_statistician' => 'ቁጥር አጥማጅ'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle Activate/Deactivate POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deactivate_officer'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE users SET active = 0, status = 'inactive' WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = t('deactivate_success');
        } else {
            $_SESSION['error'] = t('deactivate_error') . $conn->error;
        }
        header('Location: account_management.php');
        exit();
    }
    if (isset($_POST['activate_officer'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE users SET active = 1, status = 'active' WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = t('activate_success');
        } else {
            $_SESSION['error'] = t('activate_error') . $conn->error;
        }
        header('Location: account_management.php');
        exit();
    }
}

// Fetch all officers (including statisticians)
$result = $conn->query("SELECT id, fullname, username, email, role, IFNULL(active,1) as active FROM users WHERE role IN ('zone', 'woreda', 'kebele', 'statistician') ORDER BY fullname ASC");
$officers = [];
while ($row = $result->fetch_assoc()) {
    $officers[] = $row;
}

// Handle edit POST (AJAX or form submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_officer'])) {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    // Check if password reset is requested
    $password_reset = isset($_POST['reset_password']) && $_POST['reset_password'] === '1';
    $new_password = $_POST['new_password'] ?? '';

    if ($password_reset && !empty($new_password)) {
        // Update with new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $fullname, $username, $email, $role, $hashed_password, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = t('update_password_success');
        } else {
            $_SESSION['error'] = t('update_error') . $conn->error;
        }
    } else {
        // Update without password
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $fullname, $username, $email, $role, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = t('update_success');
        } else {
            $_SESSION['error'] = t('update_error') . $conn->error;
        }
    }
    
    header('Location: account_management.php');
    exit();
}

// Handle password reset only POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password_only'])) {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];
    
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hashed_password, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = t('reset_success');
        } else {
            $_SESSION['error'] = t('reset_error') . $conn->error;
        }
    } else {
        $_SESSION['error'] = t('password_required_error');
    }
    
    header('Location: account_management.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo t('page_title'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        <?php if($lang === 'am'): ?>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: justify;
        }
        
        .page-header h1,
        .page-header .lead,
        .card-header h5,
        .stats-card .number,
        .stats-card .label,
        .filter-section h5,
        .modal-title,
        .form-label,
        .btn,
        .table th,
        .table td,
        .alert,
        .empty-state h4,
        .empty-state p,
        .badge,
        .form-control,
        .form-select,
        .form-text,
        .input-group-text {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        <?php endif; ?>
        
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gradient-primary: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --gradient-secondary: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        .deactivated-row {
            opacity: 0.5;
            background: #f8d7da !important;
        }
        .btn-deactivate {
            background: var(--accent-color);
            color: #fff;
            border: none;
        }
        .btn-deactivate:hover {
            background: #c0392b;
        }
        .btn-activate {
            background: var(--success-color);
            color: #fff;
            border: none;
        }
        .btn-activate:hover {
            background: #229954;
        }
        .btn-reset {
            background: var(--warning-color);
            color: #fff;
            border: none;
        }
        .btn-reset:hover {
            background: #e67e22;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .page-header { background: var(--gradient-primary); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: var(--shadow);}
        .card { border: none; border-radius: 15px; box-shadow: var(--shadow); transition: var(--transition); margin-bottom: 1.5rem;}
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);}
        .card-header { background: var(--gradient-primary); color: white; border-radius: 15px 15px 0 0 !important; padding: 1.2rem 1.5rem; font-weight: 600;}
        .table-responsive { border-radius: 12px; overflow: hidden;}
        .table th { background-color: var(--primary-color); color: white; font-weight: 600; border: none; padding: 1rem;}
        .table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #e9ecef;}
        .table tbody tr { transition: var(--transition);}
        .table tbody tr:hover { background-color: rgba(52, 152, 219, 0.05); transform: scale(1.01);}
        .btn-primary { background: var(--gradient-primary); border: none; border-radius: 8px; padding: 0.5rem 1.2rem; font-weight: 500; transition: var(--transition);}
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); background: var(--gradient-secondary);}
        .btn-sm { padding: 0.4rem 1rem; font-size: 0.875rem;}
        .badge { padding: 0.5rem 0.8rem; border-radius: 6px; font-weight: 500; font-size: 0.8rem;}
        .badge-zone { background: linear-gradient(135deg, #3498db, #2980b9); color: white;}
        .badge-woreda { background: linear-gradient(135deg, #27ae60, #229954); color: white;}
        .badge-kebele { background: linear-gradient(135deg, #f39c12, #e67e22); color: white;}
        .badge-statistician { background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;}
        .filter-section { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); margin-bottom: 1.5rem;}
        .form-select { border-radius: 8px; padding: 0.75rem 1rem; border: 2px solid #e9ecef; transition: var(--transition);}
        .form-select:focus { border-color: var(--secondary-color); box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);}
        .stats-card { text-align: center; padding: 1.5rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; box-shadow: var(--shadow);}
        .stats-card i { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.9;}
        .stats-card .number { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;}
        .stats-card .label { font-size: 1rem; opacity: 0.9;}
        .stats-total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);}
        .stats-zone { background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);}
        .stats-woreda { background: linear-gradient(135deg, #27ae60 0%, #229954 100%);}
        .stats-kebele { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);}
        .stats-statistician { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);}
        .modal-content { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);}
        .modal-header { background: var(--gradient-primary); color: white; border-radius: 15px 15px 0 0; padding: 1.5rem;}
        .modal-title { font-weight: 600;}
        .form-label { font-weight: 600; color: var(--dark-color); margin-bottom: 0.5rem;}
        .form-control { border-radius: 8px; padding: 0.75rem 1rem; border: 2px solid #e9ecef; transition: var(--transition);}
        .form-control:focus { border-color: var(--secondary-color); box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);}
        .empty-state { text-align: center; padding: 3rem 1rem; color: #6c757d;}
        .empty-state i { font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;}
        .alert { border-radius: 10px; border: none; padding: 1rem 1.5rem;}
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;}
        .password-section { background: #f8f9fa; border-radius: 8px; padding: 1rem; margin-top: 1rem; border-left: 4px solid var(--warning-color);}
        .password-toggle { cursor: pointer; color: var(--warning-color);}
        
        /* Language switcher */
        .language-switcher {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 25px;
            box-shadow: var(--shadow);
            padding: 5px;
        }
        .lang-btn {
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            background: transparent;
            font-weight: 500;
            transition: var(--transition);
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        .lang-btn.active {
            background: var(--gradient-primary);
            color: white;
        }
        .lang-btn:hover:not(.active) {
            background: rgba(44, 62, 80, 0.1);
        }
        
        @media (max-width: 768px) {
            .table-responsive { font-size: 0.875rem;}
            .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.8rem;}
            .stats-card { padding: 1rem;}
            .stats-card i { font-size: 2rem;}
            .stats-card .number { font-size: 1.5rem;}
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
<?php include '../includes/header.php'; ?>

<!-- Language Switcher -->
<div class="language-switcher">
    <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">
        <i class="fas fa-globe-americas me-1"></i>EN
    </button>
    <button class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>" onclick="changeLanguage('am')">
        <i class="fas fa-globe-africa me-1"></i>አማ
    </button>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold"><i class="fas fa-users-cog me-3"></i><?php echo t('page_header'); ?></h1>
                <p class="lead mb-0"><?php echo t('page_subtitle'); ?></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="create_officer.php" class="btn btn-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i><?php echo t('create_officer'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card stats-total">
                <i class="fas fa-users"></i>
                <div class="number"><?= count($officers) ?></div>
                <div class="label"><?php echo t('total_officers'); ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-zone">
                <i class="fas fa-map-marked-alt"></i>
                <div class="number"><?= count(array_filter($officers, fn($o) => $o['role'] === 'zone')) ?></div>
                <div class="label"><?php echo t('zone_officers'); ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-woreda">
                <i class="fas fa-map"></i>
                <div class="number"><?= count(array_filter($officers, fn($o) => $o['role'] === 'woreda')) ?></div>
                <div class="label"><?php echo t('woreda_officers'); ?></div>
            </div>
</div>
        <div class="col-md-2">
            <div class="stats-card stats-kebele">
                <i class="fas fa-map-pin"></i>
                <div class="number"><?= count(array_filter($officers, fn($o) => $o['role'] === 'kebele')) ?></div>
                <div class="label"><?php echo t('kebele_officers'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-statistician">
                <i class="fas fa-chart-bar"></i>
                <div class="number"><?= count(array_filter($officers, fn($o) => $o['role'] === 'statistician')) ?></div>
                <div class="label"><?php echo t('statisticians'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 text-primary"><i class="fas fa-filter me-2"></i><?php echo t('filter_officers'); ?></h5>
            </div>
            <div class="col-md-6 text-end">
                <select id="roleFilter" class="form-select">
                    <option value="all"><?php echo t('all_roles'); ?></option>
                    <option value="zone"><?php echo t('zone_officer'); ?></option>
                    <option value="woreda"><?php echo t('woreda_officer'); ?></option>
                    <option value="kebele"><?php echo t('kebele_officer'); ?></option>
                    <option value="statistician"><?php echo t('statistician'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Officers Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?php echo t('officer_accounts'); ?></h5>
            <span class="badge bg-light text-dark"><?= count($officers) ?> <?php echo strtolower(t('officer_accounts')); ?></span>
        </div>
        <div class="card-body">
            <?php if (!empty($officers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="officersTable">
                        <thead>
                            <tr>
                                <th><?php echo t('officer'); ?></th>
                                <th><?php echo t('username'); ?></th>
                                <th><?php echo t('email'); ?></th>
                                <th><?php echo t('role'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th style="width:250px;"><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($officers as $officer): ?>
                                <tr data-role="<?= htmlspecialchars($officer['role']); ?>" class="<?= !$officer['active'] ? 'deactivated-row' : '' ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($officer['fullname'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($officer['fullname']); ?></div>
                                                <small class="text-muted">ID: <?= $officer['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($officer['username']); ?></td>
                                    <td><?= htmlspecialchars($officer['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?= $officer['role'] ?>">
                                            <i class="fas fa-<?= 
                                                $officer['role'] === 'zone' ? 'map-marked-alt' : 
                                                ($officer['role'] === 'woreda' ? 'map' : 
                                                ($officer['role'] === 'kebele' ? 'map-pin' : 'chart-bar')) 
                                            ?> me-1"></i>
                                            <?= $lang === 'am' ? t('role_' . $officer['role']) : ucfirst($officer['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $officer['active'] ? 
                                            '<span class="badge bg-success">' . t('active') . '</span>' : 
                                            '<span class="badge bg-danger">' . t('deactivated') . '</span>' 
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm editBtn"
                                                data-id="<?= $officer['id']; ?>"
                                                data-fullname="<?= htmlspecialchars($officer['fullname']); ?>"
                                                data-username="<?= htmlspecialchars($officer['username']); ?>"
                                                data-email="<?= htmlspecialchars($officer['email']); ?>"
                                                data-role="<?= $officer['role']; ?>"
                                                <?= !$officer['active'] ? 'disabled' : '' ?>>
                                            <i class="fas fa-edit me-1"></i><?php echo t('edit'); ?>
                                        </button>
                                        <button class="btn btn-reset btn-sm resetBtn"
                                                data-id="<?= $officer['id']; ?>"
                                                data-fullname="<?= htmlspecialchars($officer['fullname']); ?>"
                                                <?= !$officer['active'] ? 'disabled' : '' ?>>
                                            <i class="fas fa-key me-1"></i><?php echo t('reset_pw'); ?>
                                        </button>
                                        <?php if ($officer['active']): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('<?php echo t('deactivate_confirm'); ?>');">
                                                <input type="hidden" name="id" value="<?= $officer['id']; ?>">
                                                <button type="submit" name="deactivate_officer" class="btn btn-deactivate btn-sm">
                                                    <i class="fas fa-user-slash me-1"></i><?php echo t('deactivate'); ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('<?php echo t('activate_confirm'); ?>');">
                                                <input type="hidden" name="id" value="<?= $officer['id']; ?>">
                                                <button type="submit" name="activate_officer" class="btn btn-activate btn-sm">
                                                    <i class="fas fa-user-check me-1"></i><?php echo t('activate'); ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h4><?php echo t('no_officers_found'); ?></h4>
                    <p><?php echo t('no_officers_message'); ?></p>
                    <a href="create_officer.php" class="btn btn-primary mt-3">
                        <i class="fas fa-user-plus me-2"></i><?php echo t('create_first_officer'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-edit me-2"></i><?php echo t('edit_officer_account'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="reset_password" id="resetPassword" value="0">
        <div class="mb-3">
            <label class="form-label"><?php echo t('full_name'); ?></label>
            <input type="text" name="fullname" id="editFullname" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo t('username'); ?></label>
            <input type="text" name="username" id="editUsername" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo t('email_address'); ?></label>
            <input type="email" name="email" id="editEmail" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo t('role'); ?></label>
            <select name="role" id="editRole" class="form-select" required>
                <option value="zone"><?php echo t('zone_officer'); ?></option>
                <option value="woreda"><?php echo t('woreda_officer'); ?></option>
                <option value="kebele"><?php echo t('kebele_officer'); ?></option>
                <option value="statistician"><?php echo t('statistician'); ?></option>
            </select>
        </div>
        
        <!-- Password Reset Section -->
        <div class="password-section">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="enablePasswordReset">
                <label class="form-check-label fw-bold" for="enablePasswordReset">
                    <i class="fas fa-key me-1"></i><?php echo t('reset_password_check'); ?>
                </label>
            </div>
            <div id="passwordFields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label"><?php echo t('new_password'); ?></label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="editNewPassword" class="form-control" placeholder="<?php echo t('enter_new_password'); ?>">
                        <span class="input-group-text password-toggle" onclick="togglePassword('editNewPassword')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <small class="form-text text-muted"><?php echo t('password_length'); ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo t('confirm_password'); ?></label>
                    <div class="input-group">
                        <input type="password" id="editConfirmPassword" class="form-control" placeholder="<?php echo t('confirm_new_password'); ?>">
                        <span class="input-group-text password-toggle" onclick="togglePassword('editConfirmPassword')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="passwordMatch" class="form-text"></div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
        <button type="submit" name="edit_officer" class="btn btn-primary" id="saveEditBtn">
            <i class="fas fa-save me-2"></i><?php echo t('save_changes'); ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Password Reset Only Modal -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-key me-2"></i><?php echo t('reset_password'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="resetId">
        <p><?php echo t('reset_password_for'); ?> <strong id="resetOfficerName"></strong>?</p>
        <div class="mb-3">
            <label class="form-label"><?php echo t('new_password'); ?></label>
            <div class="input-group">
                <input type="password" name="new_password" id="resetNewPassword" class="form-control" placeholder="<?php echo t('enter_new_password'); ?>" required>
                <span class="input-group-text password-toggle" onclick="togglePassword('resetNewPassword')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <small class="form-text text-muted"><?php echo t('password_length'); ?></small>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo t('confirm_password'); ?></label>
            <div class="input-group">
                <input type="password" id="resetConfirmPassword" class="form-control" placeholder="<?php echo t('confirm_new_password'); ?>" required>
                <span class="input-group-text password-toggle" onclick="togglePassword('resetConfirmPassword')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <div id="resetPasswordMatch" class="form-text"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
        <button type="submit" name="reset_password_only" class="btn btn-warning" id="saveResetBtn">
            <i class="fas fa-key me-2"></i><?php echo t('reset_password'); ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Language switcher
function changeLanguage(lang) {
    window.location.href = '?lang=' + lang;
}

// Role filter
document.getElementById('roleFilter').addEventListener('change', function() {
    let value = this.value;
    document.querySelectorAll('#officersTable tbody tr').forEach(row => {
        if (value === 'all' || row.dataset.role === value) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Edit button -> open modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editFullname').value = this.dataset.fullname;
        document.getElementById('editUsername').value = this.dataset.username;
        document.getElementById('editEmail').value = this.dataset.email;
        document.getElementById('editRole').value = this.dataset.role;
        
        // Reset password fields
        document.getElementById('enablePasswordReset').checked = false;
        document.getElementById('passwordFields').style.display = 'none';
        document.getElementById('resetPassword').value = '0';
        document.getElementById('editNewPassword').value = '';
        document.getElementById('editConfirmPassword').value = '';

        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});

// Reset password button -> open reset modal
document.querySelectorAll('.resetBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('resetId').value = this.dataset.id;
        document.getElementById('resetOfficerName').textContent = this.dataset.fullname;
        document.getElementById('resetNewPassword').value = '';
        document.getElementById('resetConfirmPassword').value = '';
        document.getElementById('resetPasswordMatch').textContent = '';

        new bootstrap.Modal(document.getElementById('resetModal')).show();
    });
});

// Toggle password reset fields in edit modal
document.getElementById('enablePasswordReset').addEventListener('change', function() {
    const passwordFields = document.getElementById('passwordFields');
    if (this.checked) {
        passwordFields.style.display = 'block';
        document.getElementById('resetPassword').value = '1';
    } else {
        passwordFields.style.display = 'none';
        document.getElementById('resetPassword').value = '0';
        document.getElementById('editNewPassword').value = '';
        document.getElementById('editConfirmPassword').value = '';
    }
});

// Password confirmation validation for edit modal
document.getElementById('editConfirmPassword').addEventListener('input', function() {
    const newPassword = document.getElementById('editNewPassword').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('passwordMatch');
    
    if (confirmPassword === '') {
        matchText.textContent = '';
        matchText.className = 'form-text';
    } else if (newPassword === confirmPassword) {
        matchText.textContent = '<?php echo t("passwords_match"); ?>';
        matchText.className = 'form-text text-success';
    } else {
        matchText.textContent = '<?php echo t("passwords_not_match"); ?>';
        matchText.className = 'form-text text-danger';
    }
});

// Password confirmation validation for reset modal
document.getElementById('resetConfirmPassword').addEventListener('input', function() {
    const newPassword = document.getElementById('resetNewPassword').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('resetPasswordMatch');
    
    if (confirmPassword === '') {
        matchText.textContent = '';
        matchText.className = 'form-text';
    } else if (newPassword === confirmPassword) {
        matchText.textContent = '<?php echo t("passwords_match"); ?>';
        matchText.className = 'form-text text-success';
    } else {
        matchText.textContent = '<?php echo t("passwords_not_match"); ?>';
        matchText.className = 'form-text text-danger';
    }
});

// Form validation for reset modal
document.getElementById('resetModal').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('resetNewPassword').value;
    const confirmPassword = document.getElementById('resetConfirmPassword').value;
    
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('<?php echo t("password_required"); ?>');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('<?php echo t("passwords_mismatch"); ?>');
        return false;
    }
});

// Form validation for edit modal with password reset
document.getElementById('editModal').addEventListener('submit', function(e) {
    const resetEnabled = document.getElementById('enablePasswordReset').checked;
    
    if (resetEnabled) {
        const newPassword = document.getElementById('editNewPassword').value;
        const confirmPassword = document.getElementById('editConfirmPassword').value;
        
        if (newPassword.length < 8) {
            e.preventDefault();
            alert('<?php echo t("password_required"); ?>');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('<?php echo t("passwords_mismatch"); ?>');
            return false;
        }
    }
});

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentNode.querySelector('.fa-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Add search functionality
function addSearchFunctionality() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '<?php echo t("search_officers"); ?>';
    searchInput.className = 'form-control mb-3';
    searchInput.style.maxWidth = '300px';
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('#officersTable tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Add search input to filter section
    const filterDiv = document.querySelector('.filter-section .col-md-6');
    if (filterDiv) {
        filterDiv.appendChild(searchInput);
    }
}

// Initialize search functionality
addSearchFunctionality();
</script>
</body>
</html>