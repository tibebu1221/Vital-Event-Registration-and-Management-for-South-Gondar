<?php
session_start();
require_once 'includes/db_connection.php';

// Language handling
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Translations array
$translations = [
    'en' => [
        'title' => 'VERMS Login',
        'vernms' => 'VERMS',
        'system_name' => 'Vital Events Registration Management System',
        'username' => 'Username',
        'username_placeholder' => 'Enter Username',
        'password' => 'Password',
        'password_placeholder' => 'Enter Password',
        'role' => 'Role',
        'select_role' => '-- Select Role --',
        'kebele_officer' => 'Kebele Officer',
        'woreda_officer' => 'Woreda Officer',
        'zone_officer' => 'Zone Officer',
        'citizen' => 'Citizen',
        'admin' => 'Admin',
        'statistician' => 'Statistician',
        'login_button' => 'Login',
        'forgot_password' => 'Forgot password?',
        'no_account' => 'Don\'t have an account?',
        'register_here' => 'Register here',
        'all_fields_required' => 'All fields are required.',
        'account_deactivated' => 'Your account has been deactivated. Please contact admin.',
        'invalid_password' => 'Invalid password.',
        'invalid_credentials' => 'Invalid username or role.',
        'login_image_alt' => 'VERMS Login'
    ],
    'am' => [
        'title' => 'ቪ.ኢ.አር.ኤም.ኤስ መግቢያ',
        'vernms' => 'ቪ.ኢ.አር.ኤም.ኤስ',
        'system_name' => 'የህይወት ክስተቶች ምዝገባ አስተዳደር ስርዓት',
        'username' => 'የተጠቃሚ ስም',
        'username_placeholder' => 'የተጠቃሚ ስም ያስገቡ',
        'password' => 'የይለፍ ቃል',
        'password_placeholder' => 'የይለፍ ቃል ያስገቡ',
        'role' => 'የድርጅቱ ሚና',
        'select_role' => '-- ሚና ይምረጡ --',
        'kebele_officer' => 'የቀበሌ ባለስልጣን',
        'woreda_officer' => 'የወረዳ ባለስልጣን',
        'zone_officer' => 'የዞን ባለስልጣን',
        'citizen' => 'ዜጋ',
        'admin' => 'አስተዳዳሪ',
        'statistician' => 'የስታቲስቲክስ ባለሙያ',
        'login_button' => 'ግባ',
        'forgot_password' => 'የይለፍ ቃል ረሳሁ?',
        'no_account' => 'መለያ የሎትም?',
        'register_here' => 'እዚህ ይመዝገቡ',
        'all_fields_required' => 'ሁሉም መስኮች ያስፈልጋሉ.',
        'account_deactivated' => 'መለያዎ እንቅስቃሴ ተወግዷል። እባክዎ አስተዳዳሪን ያነጋግሩ።',
        'invalid_password' => 'የይለፍ ቃል የተሳሳተ ነው.',
        'invalid_credentials' => 'የተጠቃሚ ስም ወይም ሚና የተሳሳተ ነው.',
        'login_image_alt' => 'ቪ.ኢ.አር.ኤም.ኤስ መግቢያ'
    ]
];

function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/admin_dashboard.php');
            break;
        case 'kebele':
            header('Location: kebele_officer/dashboard.php');
            break;
        case 'woreda':
            header('Location: woreda_officer/dashboard.php');
            break;
        case 'zone':
            header('Location: zone/dashboard.php');
            break;
        case 'citizen':
            header('Location: citizen/dashboard.php');
            break;
        case 'statistician':
            header('Location: statistician/dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit();
}

$error = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$username || !$password || !$role) {
        $error = t('all_fields_required');
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role, status, active FROM users WHERE username = ? AND role = ?");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['status'] !== 'active' || !$user['active']) {
                $error = t('account_deactivated');
            } elseif (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/admin_dashboard.php');
                        break;
                    case 'kebele':
                        header('Location: kebele_officer/dashboard.php');
                        break;
                    case 'woreda':
                        header('Location: woreda_officer/dashboard.php');
                        break;
                    case 'zone':
                        header('Location: Zone/dashbord.php');
                        break;
                    case 'citizen':
                        header('Location: citizen/dashboard.php');
                        break;
                    case 'statistician':
                        header('Location: statistician/dashboard.php');
                        break;
                    default:
                        header('Location: index.php');
                }
                exit();
            } else {
                $error = t('invalid_password');
            }
        } else {
            $error = t('invalid_credentials');
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" href="assets/images/photo_2025-10-14_13-37-19.jpg" type="image/x-icon">
    <style>
        body { background-color: #f2f7fb; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .login-left img { border-radius: 1rem 0 0 1rem; object-fit: cover; height: 100%; width: 100%; }
        .btn-login { background-color: #0d6efd; border: none; transition: all 0.3s ease; }
        .btn-login:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
        .logo-text { color: #0d6efd; }
        .login-right { padding: 2rem; }
        
        /* Language switcher - positioned at top right corner */
        .language-switcher-corner {
            position: fixed;
            top: 80px; /* Positioned below header */
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 5px;
            display: fixed;
            gap: 5px;
        }
        
        .lang-btn-corner {
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
        
        .lang-btn-corner.active {
            background: #0d6efd;
            color: white;
        }
        
        .lang-btn-corner:hover:not(.active) {
            background: rgba(44, 62, 80, 0.1);
        }
        
        /* Password input group styling */
        .password-input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #0d6efd;
        }
        
        .password-toggle:focus {
            outline: none;
            box-shadow: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) { 
            .login-left { display: none; } 
            .language-switcher-corner {
                top: 70px;
                right: 10px;
                padding: 4px;
            }
            .lang-btn-corner {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .language-switcher-corner {
                top: 60px;
                right: 5px;
                flex-direction: column;
                gap: 3px;
            }
            .lang-btn-corner {
                padding: 3px 6px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Language switcher in top right corner -->
<div class="language-switcher-corner">
    <a href="?lang=en" class="lang-btn-corner <?php echo $lang === 'en' ? 'active' : ''; ?>">
        <i class="fas fa-globe-americas"></i> EN
    </a>
    <a href="?lang=am" class="lang-btn-corner <?php echo $lang === 'am' ? 'active' : ''; ?>">
        <i class="fas fa-globe-africa"></i> አማ
    </a>
</div>

<section class="vh-100">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col col-xl-10">
                <div class="card">
                    <div class="row g-0">
                        <!-- Left Image -->
                        <div class="col-md-6 login-left d-none d-md-block">
                            <img src="images/photo_2025-10-14_13-37-19.jpg" alt="<?php echo t('login_image_alt'); ?>" class="img-fluid" />
                        </div>
                        <!-- Right Form -->
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="login-right card-body text-black">
                                <div class="text-center mb-4">
                                    <span class="h3 fw-bold mb-0 logo-text"><?php echo t('vernms'); ?></span>
                                    <p class="mt-2 text-muted"><?php echo t('system_name'); ?></p>
                                </div>

                                <?php if(!empty($error)): ?>
                                    <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <form action="login.php" method="post">
                                    <div class="form-outline mb-3">
                                        <input type="text" name="username" class="form-control form-control-lg" 
                                               placeholder="<?php echo t('username_placeholder'); ?>" required />
                                        <label class="form-label"><?php echo t('username'); ?></label>
                                    </div>
                                    
                                    <div class="form-outline mb-3 password-input-group">
                                        <input type="password" name="password" id="password" class="form-control form-control-lg" 
                                               placeholder="<?php echo t('password_placeholder'); ?>" required />
                                        <label class="form-label"><?php echo t('password'); ?></label>
                                        <button type="button" class="password-toggle" id="passwordToggle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="form-outline mb-4">
                                        <select name="role" class="form-select form-select-lg" required>
                                            <option value=""><?php echo t('select_role'); ?></option>
                                            <option value="kebele"><?php echo t('kebele_officer'); ?></option>
                                            <option value="woreda"><?php echo t('woreda_officer'); ?></option>
                                            <option value="zone"><?php echo t('zone_officer'); ?></option>
                                            <option value="citizen"><?php echo t('citizen'); ?></option>
                                            <option value="admin"><?php echo t('admin'); ?></option>
                                            <option value="statistician"><?php echo t('statistician'); ?></option>
                                        </select>
                                    </div>
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-login btn-lg">
                                            <i class="fas fa-sign-in-alt"></i> <?php echo t('login_button'); ?>
                                        </button>
                                    </div>
                                    <div class="text-center mb-3">
                                        <a class="small text-muted" href="forgot_password.php"><?php echo t('forgot_password'); ?></a>
                                    </div>
                                    <p class="mb-2 text-center text-muted">
                                        <?php echo t('no_account'); ?> 
                                        <a href="register.php" class="text-primary fw-bold"><?php echo t('register_here'); ?></a>
                                    </p>
                                </form>
                            </div>
                        </div>
                        <!-- End Right Form -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Save language preference
    document.addEventListener('DOMContentLoaded', function() {
        const lang = '<?php echo $lang; ?>';
        localStorage.setItem('preferred_lang', lang);
        
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const eyeIcon = passwordToggle.querySelector('i');
        
        passwordToggle.addEventListener('click', function() {
            // Toggle password visibility
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
        
        // Focus management for better UX
        passwordInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        passwordInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
</script>
</body>
</html>