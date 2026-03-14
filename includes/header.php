<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/db_connection.php';

// Get current page for active navigation
$current_page = $_SERVER['REQUEST_URI'];
$is_home = ($current_page == '/VERMS/index.php' || $current_page == '/VERMS/' || $current_page == '/index.php');
$is_about = str_contains($current_page, 'about.php');
$is_service = str_contains($current_page, 'service.php');
$is_notice = str_contains($current_page, 'notice.php');

// Profile Update Logic
$update_message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Get current user data
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            throw new Exception("User not found.");
        }
        
        // Validate password change if provided
        $password_updated = false;
        if (!empty($new_password)) {
            if (empty($current_password)) {
                throw new Exception("Current password is required to set new password.");
            }
            
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect.");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception("New password must be at least 8 characters long.");
            }
            
            if (!preg_match('/[A-Z]/', $new_password)) {
                throw new Exception("Password must contain at least one uppercase letter.");
            }
            
            if (!preg_match('/[a-z]/', $new_password)) {
                throw new Exception("Password must contain at least one lowercase letter.");
            }
            
            if (!preg_match('/[0-9]/', $new_password)) {
                throw new Exception("Password must contain at least one number.");
            }
            
            if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
                throw new Exception("Password must contain at least one special character.");
            }
            
            $password_updated = true;
        }
        
        // Update user data
        if ($password_updated) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullname, $phone, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fullname, $phone, $user_id);
        }
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['fullname'] = $fullname;
            $_SESSION['phone'] = $phone;
            
            $update_message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            throw new Exception("Failed to update profile: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $update_message = $e->getMessage();
        $message_type = "danger";
    }
}

// Dynamic statistics for dashboard
$stats = [
  'births_registered' => 0,
  'marriages_certified' => 0,
  'deaths_recorded' => 0,
  'divorces_certified' => 0,
  'kebeles_served' => 0
];

try {
  // Births Registered (all events)
  $result = $conn->query("SELECT COUNT(*) AS cnt FROM birth_events");
  $stats['births_registered'] = $result ? (int)$result->fetch_assoc()['cnt'] : 0;

  // Marriages Certified (status = 'Approved')
  $result = $conn->query("SELECT COUNT(*) AS cnt FROM marriage_events WHERE status = 'Approved'");
  $stats['marriages_certified'] = $result ? (int)$result->fetch_assoc()['cnt'] : 0;

  // Deaths Recorded (all events)
  $result = $conn->query("SELECT COUNT(*) AS cnt FROM death_events");
  $stats['deaths_recorded'] = $result ? (int)$result->fetch_assoc()['cnt'] : 0;

  // Divorces Certified (status = 'Approved')
  if ($conn->query("SHOW TABLES LIKE 'divorce_events'")->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM divorce_events WHERE status = 'Approved'");
    $stats['divorces_certified'] = $result ? (int)$result->fetch_assoc()['cnt'] : 0;
  }

  // Kebeles Served (all kebeles)
  $result = $conn->query("SELECT COUNT(*) AS cnt FROM kebeles");
  $stats['kebeles_served'] = $result ? (int)$result->fetch_assoc()['cnt'] : 0;
} catch (Exception $e) {
  // Handle DB errors gracefully
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VERMS - Vital Events Registration Management System</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
  <link rel="shortcut icon" href="/VERMS/images/logo.png" type="image/x-icon">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    .navbar {
        background: var(--gradient-primary) !important;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        padding: 0.8rem 0;
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: white !important;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: var(--transition);
    }

    .navbar-brand:hover {
        transform: translateY(-2px);
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        margin: 0 2px;
        transition: var(--transition);
        position: relative;
    }

    .nav-link:hover {
        color: white !important;
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        color: white !important;
        font-weight: 600;
    }

    .nav-link.active::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
    }

    /* Enhanced Dropdown Styles - FIXED POSITIONING */
    .dropdown-menu {
        background: white;
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        padding: 0.5rem 0;
        margin-top: 10px !important;
        min-width: 240px;
        animation: dropdownFade 0.3s ease;
        /* Ensure dropdown stays within viewport */
        max-width: calc(100vw - 20px);
        z-index: 1050; /* Higher than navbar z-index */
    }

    /* Woreda-specific dropdown styling */
    .dropdown-woreda .dropdown-item {
        border-left: 3px solid transparent;
        transition: var(--transition);
    }

    .dropdown-woreda .dropdown-item:hover {
        background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
        color: #0d6efd;
        border-left: 3px solid #0d6efd;
        transform: translateX(5px);
    }

    .dropdown-woreda .dropdown-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        color: white;
        border-bottom: none;
    }

    .dropdown-woreda .dropdown-item i {
        color: #0d6efd;
        width: 20px;
        text-align: center;
    }

    /* Position dropdowns to the left on larger screens to prevent right-side cutoff */
    @media (min-width: 992px) {
        .dropdown-menu-end {
            right: 0;
            left: auto !important;
        }
    }

    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-item {
        padding: 0.8rem 1.5rem;
        color: var(--dark-color);
        font-weight: 500;
        transition: var(--transition);
        border-left: 3px solid transparent;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dropdown-item:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: var(--secondary-color);
        border-left: 3px solid var(--secondary-color);
        transform: translateX(5px);
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
        color: var(--secondary-color);
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        border-color: #e9ecef;
    }

    .dropdown-header {
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        color: var(--primary-color);
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    /* User Avatar */
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        font-size: 0.9rem;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: white;
            margin-top: 1rem;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1040;
        }

        .nav-link {
            color: var(--dark-color) !important;
            padding: 0.8rem 1rem !important;
            margin: 2px 0;
        }

        .nav-link.active {
            background: var(--gradient-primary);
            color: white !important;
        }

        .nav-link.active::before {
            display: none;
        }

        .dropdown-menu {
            box-shadow: none;
            border: 1px solid #e9ecef;
            margin: 0.5rem 0 !important;
            animation: none;
            /* Ensure dropdown doesn't extend beyond screen on mobile */
            max-width: 100%;
            position: static !important;
            transform: none !important;
        }
    }

    /* Profile Modal Enhancement */
    .profile-modal .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .profile-modal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Alert Styles */
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    /* Role-specific dropdown colors */
    .dropdown-citizen .dropdown-item:hover {
        border-left-color: var(--success-color);
    }

    .dropdown-admin .dropdown-item:hover {
        border-left-color: var(--warning-color);
    }

    .dropdown-kebele .dropdown-item:hover {
        border-left-color: var(--secondary-color);
    }

    .dropdown-zone .dropdown-item:hover {
        border-left-color: #6f42c1;
    }

    .dropdown-statistician .dropdown-item:hover {
        border-left-color: #20c997;
    }

    /* Password toggle */
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
    }

    .password-toggle:hover {
        color: var(--secondary-color);
    }

    /* Password strength indicator */
    .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .strength-weak {
        background-color: #e74c3c;
        width: 25%;
    }

    .strength-medium {
        background-color: #f39c12;
        width: 50%;
    }

    .strength-strong {
        background-color: #27ae60;
        width: 100%;
    }

    /* Read-only fields */
    .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
    }

    /* Password requirements */
    .password-requirements {
        margin-top: 8px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .requirement {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
    }

    .requirement i {
        margin-right: 6px;
        font-size: 0.75rem;
        width: 14px;
    }

    .requirement.valid {
        color: var(--success-color);
    }

    .requirement.invalid {
        color: #6c757d;
    }
  </style>
</head>
<body>

<!-- Display Update Message -->
<?php if ($update_message): ?>
<div class="container mt-3">
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($update_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand" href="/VERMS/index.php">
      <i class="fas fa-id-card-alt"></i>
      <span>VERMS</span>
    </a>

    <!-- Mobile toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu items -->
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">

      <!-- Left Nav -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= $is_home ? 'active' : '' ?>" href="/VERMS/index.php">
            <i class="fas fa-home me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $is_about ? 'active' : '' ?>" href="/VERMS/nav-link/about.php">
            <i class="fas fa-info-circle me-1"></i>About
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $is_service ? 'active' : '' ?>" href="/VERMS/nav-link/service.php">
            <i class="fas fa-concierge-bell me-1"></i>Services
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $is_notice ? 'active' : '' ?>" href="/VERMS/nav-link/notice.php">
            <i class="fas fa-bell me-1"></i>Notice
          </a>
        </li>
      </ul>

      <!-- Right Nav -->
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- User Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle position-relative d-flex align-items-center" href="#" id="userDropdown" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
              <div class="user-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="me-2"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end <?= 'dropdown-' . ($_SESSION['role'] ?? 'user') ?>" aria-labelledby="userDropdown">
              <li class="dropdown-header">
                <i class="fas fa-user-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                <small class="d-block text-muted"><?= ucfirst($_SESSION['role'] ?? 'User') ?></small>
              </li>
              <li><hr class="dropdown-divider"></li>
              
              <?php if ($_SESSION['role'] == 'citizen'): ?>
                <li><a class="dropdown-item" href="/VERMS/citizen/dashboard.php">
                  <i class="fas fa-tachometer-alt"></i>Dashboard
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/citizen/request_event.php">
                  <i class="fas fa-file-alt"></i>Request Event Registration
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/citizen/receive_certificate.php">
                  <i class="fas fa-certificate"></i>Receive Certificate
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/citizen/make_payment.php">
                  <i class="fas fa-credit-card"></i>Make Payment
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/citizen/feedback.php">
                  <i class="fas fa-comment"></i>Feedback
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>
                
              <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <li><a class="dropdown-item" href="/VERMS/admin/admin_dashboard.php">
                  <i class="fas fa-tachometer-alt"></i>Admin Dashboard
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/create_officer.php">
                  <i class="fas fa-user-plus"></i>Create Officer Account
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/register_zone.php">
                  <i class="fas fa-map-marker-alt"></i>Register Zone
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/register_woreda.php">
                  <i class="fas fa-map-pin"></i>Register Woreda
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/register_kebele.php">
                  <i class="fas fa-map-signs"></i>Register Kebele
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/account_management.php">
                  <i class="fas fa-users-cog"></i>Account Management
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/admin/view_citizens.php">
                  <i class="fas fa-users"></i>View Citizens
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>
                
              <?php elseif ($_SESSION['role'] == 'kebele'): ?>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/dashboard.php">
                  <i class="fas fa-tachometer-alt"></i>Officer Dashboard
                </a></li>
                 <li><a class="dropdown-item" href="/VERMS/kebele_officer/citizens.php">
                  <i class="fas fa-users"></i>Citizens
                </a></li>
                <li class="dropdown-header">Event Management</li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/birth.php">
                  <i class="fas fa-baby"></i>Birth Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/death.php">
                  <i class="fas fa-book-dead"></i>Death Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/marriage.php">
                  <i class="fas fa-ring"></i>Marriage Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/divorce.php">
                  <i class="fas fa-file-contract"></i>Divorce Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/give_certificate.php">
                  <i class="fas fa-file-contract"></i>Give Certificate
                </a></li>
                  <li><a class="dropdown-item" href="/VERMS/kebele_officer/edit_events.php">
                  <i class="fas fa-edit"></i>Edit Event
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/view_feedback.php">
                  <i class="fas fa-comments"></i>View Feedback
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/VERMS/kebele_officer/view_report.php">
                  <i class="fas fa-chart-bar"></i>Reports & Analytics
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>
                
              <?php elseif ($_SESSION['role'] == 'woreda'): ?>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/dashboard.php">
                  <i class="fas fa-tachometer-alt"></i>Woreda Dashboard
                </a></li>
                <li class="dropdown-header">Event Management</li>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/birth.php">
                  <i class="fas fa-baby"></i>Birth Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/death.php">
                  <i class="fas fa-book-dead"></i>Death Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/marriage.php">
                  <i class="fas fa-ring"></i>Marriage Events
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/divorce.php">
                  <i class="fas fa-file-contract"></i>Divorce Events
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/VERMS/woreda_officer/report.php">
                  <i class="fas fa-chart-bar"></i>Reports & Analytics
                </a></li>
                 <li><a class="dropdown-item" href="/VERMS/woreda_officer/notices.php">
                  <i class="fas fa-bullhorn"></i>Notices
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>

              <?php elseif ($_SESSION['role'] == 'zone'): ?>
                <li><a class="dropdown-item" href="/VERMS\Zone\dashbord.php">
                  <i class="fas fa-tachometer-alt"></i>Zone Dashboard
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/VERMS/zone/regional_reports.php">
                  <i class="fas fa-file-pdf"></i>Regional Reports
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/zone/notices.php">
                  <i class="fas fa-bullhorn"></i> Notices
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>
                      
              <?php elseif ($_SESSION['role'] == 'statistician'): ?>
                <li><a class="dropdown-item" href="/VERMS/statistician/dashboard.php">
                  <i class="fas fa-tachometer-alt"></i>Statistician Dashboard
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">Reports</li>
                <li><a class="dropdown-item" href="/VERMS/statistician/generate_reports.php">
                  <i class="fas fa-chart-bar"></i>Generate Reports
                </a></li>
                <li><a class="dropdown-item" href="/VERMS/statistician/analytics.php">
                  <i class="fas fa-chart-line"></i>Analytics Dashboard
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="fas fa-user-edit"></i>Profile Settings
                </a></li>
              <?php endif; ?>
              
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/VERMS/logout.php">
                <i class="fas fa-sign-out-alt"></i>Logout
              </a></li>
            </ul>
          </li>
        <?php else: ?>
          <!-- Guest Links -->
          <li class="nav-item">
            <a class="nav-link" href="/VERMS/login.php">
              <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/VERMS/register.php">
              <i class="fas fa-user-plus me-1"></i>Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Universal Profile Modal for All User Roles -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade profile-modal" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">
          <i class="fas fa-user-edit me-2"></i>My Profile - <?= ucfirst($_SESSION['role'] ?? 'User') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="" id="profileForm">
          <input type="hidden" name="update_profile" value="1">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($_SESSION['fullname'] ?? '') ?>" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" readonly>
            <small class="form-text text-muted">Username cannot be changed</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly>
            <small class="form-text text-muted">Email cannot be changed</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" required>
          </div>
          
          <!-- Password Change Section -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0">
                <i class="fas fa-key me-1"></i>Change Password
                <small class="text-muted">(Optional)</small>
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3 position-relative">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" id="currentPassword">
                <button type="button" class="password-toggle" data-target="currentPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              
              <div class="mb-3 position-relative">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" id="newPassword">
                <button type="button" class="password-toggle" data-target="newPassword">
                  <i class="fas fa-eye"></i>
                </button>
                <div class="password-strength" id="passwordStrength"></div>
                <div class="password-requirements">
                  <div class="requirement invalid" id="req-length">
                    <i class="fas fa-circle"></i>At least 8 characters
                  </div>
                  <div class="requirement invalid" id="req-uppercase">
                    <i class="fas fa-circle"></i>One uppercase letter
                  </div>
                  <div class="requirement invalid" id="req-lowercase">
                    <i class="fas fa-circle"></i>One lowercase letter
                  </div>
                  <div class="requirement invalid" id="req-number">
                    <i class="fas fa-circle"></i>One number
                  </div>
                  <div class="requirement invalid" id="req-special">
                    <i class="fas fa-circle"></i>One special character
                  </div>
                </div>
              </div>
              
              <div class="mb-3 position-relative">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirmPassword">
                <button type="button" class="password-toggle" data-target="confirmPassword">
                  <i class="fas fa-eye"></i>
                </button>
                <div class="invalid-feedback" id="passwordMatchError">Passwords do not match.</div>
              </div>
            </div>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Enhanced dropdown interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to dropdowns
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', function() {
            const dropdownMenu = this.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.add('show');
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            const dropdownMenu = this.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        });
    });

    // Fix dropdown positioning on mobile
    function handleDropdownPositioning() {
        const dropdownMenus = document.querySelectorAll('.dropdown-menu');
        dropdownMenus.forEach(menu => {
            const rect = menu.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            
            // If dropdown extends beyond right edge of viewport
            if (rect.right > viewportWidth) {
                // Calculate how much to shift left
                const shiftAmount = rect.right - viewportWidth + 10;
                menu.style.left = `-${shiftAmount}px`;
            }
        });
    }
    
    // Run on dropdown show
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('shown.bs.dropdown', handleDropdownPositioning);
    });
    
    // Also run on window resize
    window.addEventListener('resize', handleDropdownPositioning);

    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordField = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password strength indicator and requirement validation
    const newPasswordField = document.getElementById('newPassword');
    const passwordStrength = document.getElementById('passwordStrength');
    
    if (newPasswordField && passwordStrength) {
        newPasswordField.addEventListener('input', function() {
            const password = this.value;
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Update requirement indicators
            updateRequirement('req-length', hasLength);
            updateRequirement('req-uppercase', hasUppercase);
            updateRequirement('req-lowercase', hasLowercase);
            updateRequirement('req-number', hasNumber);
            updateRequirement('req-special', hasSpecial);
            
            // Calculate strength (0-100)
            let strength = 0;
            if (hasLength) strength += 20;
            if (hasUppercase) strength += 20;
            if (hasLowercase) strength += 20;
            if (hasNumber) strength += 20;
            if (hasSpecial) strength += 20;
            
            passwordStrength.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 60) {
                passwordStrength.style.background = '#e74c3c';
            } else if (strength < 100) {
                passwordStrength.style.background = '#f39c12';
            } else {
                passwordStrength.style.background = '#27ae60';
            }
        });
    }

    // Update requirement indicator
    function updateRequirement(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (isValid) {
            element.classList.remove('invalid');
            element.classList.add('valid');
            element.innerHTML = '<i class="fas fa-check-circle"></i>' + element.textContent.substring(element.textContent.indexOf(' ') + 1);
        } else {
            element.classList.remove('valid');
            element.classList.add('invalid');
            element.innerHTML = '<i class="fas fa-circle"></i>' + element.textContent.substring(element.textContent.indexOf(' ') + 1);
        }
    }

    // Password confirmation validation
    const confirmPasswordField = document.getElementById('confirmPassword');
    const passwordMatchError = document.getElementById('passwordMatchError');
    
    if (confirmPasswordField && passwordMatchError) {
        confirmPasswordField.addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.classList.add('is-invalid');
                passwordMatchError.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                passwordMatchError.style.display = 'none';
            }
        });
    }

    // Form validation
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // If password fields are filled, validate
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    document.getElementById('confirmPassword').classList.add('is-invalid');
                    passwordMatchError.style.display = 'block';
                    return false;
                }
                
                // Password strength validation
                const hasLength = newPassword.length >= 8;
                const hasUppercase = /[A-Z]/.test(newPassword);
                const hasLowercase = /[a-z]/.test(newPassword);
                const hasNumber = /[0-9]/.test(newPassword);
                const hasSpecial = /[^A-Za-z0-9]/.test(newPassword);
                
                if (newPassword.length > 0 && (!hasLength || !hasUppercase || !hasLowercase || !hasNumber || !hasSpecial)) {
                    e.preventDefault();
                    alert('Please ensure your password meets all requirements:\n\n• At least 8 characters\n• One uppercase letter\n• One lowercase letter\n• One number\n• One special character');
                    return false;
                }
            }
            
            return true;
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
</body>
</html>