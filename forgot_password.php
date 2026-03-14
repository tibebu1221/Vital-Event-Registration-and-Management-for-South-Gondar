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

// Translations
$translations = [
    'en' => [
        'title' => 'Forgot Password',
        'forgot_password' => 'Forgot Password',
        'enter_username' => 'Enter your username',
        'username_placeholder' => 'Enter your username',
        'next_button' => 'Next',
        'back_to_login' => 'Back to Login',
        'username_required' => 'Username is required.',
        'username_not_found' => 'Username not found.',
        'security_question' => 'Security Question',
        'answer_placeholder' => 'Enter your answer',
        'answer_required' => 'Answer is required.',
        'wrong_answer' => 'Incorrect answer.',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'password_placeholder' => 'Enter new password',
        'confirm_placeholder' => 'Re-enter new password',
        'reset_button' => 'Reset Password',
        'password_too_short' => 'Password must be at least 8 characters.',
        'password_mismatch' => 'Passwords do not match.',
        'password_updated' => 'Password updated successfully! You can now login.',
        'setup_security_first' => 'You need to setup security questions first. Please contact administrator.'
    ],
    'am' => [
        'title' => 'የይለፍ ቃል ረስቻለሁ',
        'forgot_password' => 'የይለፍ ቃል ረስቻለሁ',
        'enter_username' => 'የተጠቃሚ ስምዎን ያስገቡ',
        'username_placeholder' => 'የተጠቃሚ ስምዎን ያስገቡ',
        'next_button' => 'ቀጣይ',
        'back_to_login' => 'ወደ መግቢያ ተመለስ',
        'username_required' => 'የተጠቃሚ ስም ያስፈልጋል.',
        'username_not_found' => 'የተጠቃሚ ስም አልተገኘም.',
        'security_question' => 'የደህንነት ጥያቄ',
        'answer_placeholder' => 'መልስዎን ያስገቡ',
        'answer_required' => 'መልስ ያስፈልጋል.',
        'wrong_answer' => 'ልክ ያልሆነ መልስ.',
        'new_password' => 'አዲስ የይለፍ ቃል',
        'confirm_password' => 'የይለፍ ቃል ያረጋግጡ',
        'password_placeholder' => 'አዲስ የይለፍ ቃል ያስገቡ',
        'confirm_placeholder' => 'አዲስ የይለፍ ቃል እንደገና ያስገቡ',
        'reset_button' => 'የይለፍ ቃል ዳግም ያስጀምሩ',
        'password_too_short' => 'የይለፍ ቃል ቢያንስ 8 ቁምፊ መሆን አለበት.',
        'password_mismatch' => 'የይለፍ ቃሎች አይዛመዱም.',
        'password_updated' => 'የይለፍ ቃል በተሳካ ሁኔታ ተቀይሯል! አሁን መግባት ይችላሉ.',
        'setup_security_first' => 'መጀመሪያ የደህንነት ጥያቄዎችን ማዋቀር ያስፈልግዎታል። እባክዎ አስተዳዳሪን ያነጋግሩ።'
    ]
];

function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$message_type = '';
$username = '';

// Step 1: Username verification
if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    if (!$username) {
        $message = t('username_required');
        $message_type = 'error';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id, security_question FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if user has security question set
            if (empty($user['security_question'])) {
                $message = t('setup_security_first');
                $message_type = 'error';
            } else {
                // Store in session for next steps
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_username'] = $username;
                
                // Go to step 2
                header('Location: forgot_password.php?step=2');
                exit();
            }
        } else {
            $message = t('username_not_found');
            $message_type = 'error';
        }
    }
}

// Step 2: Security question verification
if ($step === 2) {
    if (!isset($_SESSION['reset_user_id'])) {
        header('Location: forgot_password.php');
        exit();
    }
    
    // Get user's security question
    $stmt = $conn->prepare("SELECT security_question, security_answer FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['reset_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $security_question = $user['security_question'];
    
    // Handle answer submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answer = trim($_POST['answer'] ?? '');
        
        if (!$answer) {
            $message = t('answer_required');
            $message_type = 'error';
        } else {
            // Verify answer (assuming answers are stored as plain text for simplicity)
            // For better security, you should hash the answers like passwords
            $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND security_answer = ?");
            $stmt->bind_param("is", $_SESSION['reset_user_id'], $answer);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Answer is correct, go to step 3
                $_SESSION['reset_verified'] = true;
                header('Location: forgot_password.php?step=3');
                exit();
            } else {
                $message = t('wrong_answer');
                $message_type = 'error';
            }
        }
    }
}

// Step 3: Set new password
if ($step === 3) {
    if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_verified'])) {
        header('Location: forgot_password.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (strlen($new_password) < 8) {
            $message = t('password_too_short');
            $message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = t('password_mismatch');
            $message_type = 'error';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['reset_user_id']);
            
            if ($update_stmt->execute()) {
                // Clear session data
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_username']);
                unset($_SESSION['reset_verified']);
                
                $message = t('password_updated');
                $message_type = 'success';
                
                // Don't redirect immediately, show success message
                $step = 4; // Success step
            }
        }
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
    <style>
        body {
            background-color: #f2f7fb;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .logo-text {
            color: #0d6efd;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .step {
            text-align: center;
            flex: 1;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step.active .step-number {
            background: #0d6efd;
            color: white;
        }
        .step.completed .step-number {
            background: #198754;
            color: white;
        }
        .step-label {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .step.active .step-label {
            color: #0d6efd;
            font-weight: bold;
        }
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
        .security-question-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .security-question-box p {
            margin: 0;
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-label">Username</div>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-label">Verify</div>
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">New Password</div>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <span class="logo-text">VERMS</span>
            <h4 class="mt-2"><?php echo t('forgot_password'); ?></h4>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($step === 1): ?>
            <!-- Step 1: Enter Username -->
            <form method="post" action="forgot_password.php?step=1">
                <div class="mb-3">
                    <label class="form-label"><?php echo t('enter_username'); ?></label>
                    <input type="text" name="username" class="form-control" 
                           placeholder="<?php echo t('username_placeholder'); ?>" 
                           value="<?php echo htmlspecialchars($username); ?>" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary mb-3">
                    <?php echo t('next_button'); ?> <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left"></i> <?php echo t('back_to_login'); ?>
                    </a>
                </div>
            </form>
            
        <?php elseif($step === 2): ?>
            <!-- Step 2: Security Question -->
            <form method="post" action="forgot_password.php?step=2">
                <div class="security-question-box">
                    <p><?php echo t('security_question'); ?>:</p>
                    <p class="mt-2">"<?php echo htmlspecialchars($security_question); ?>"</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><?php echo t('answer_placeholder'); ?></label>
                    <input type="text" name="answer" class="form-control" 
                           placeholder="<?php echo t('answer_placeholder'); ?>" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo t('next_button'); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                    <a href="forgot_password.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
            
        <?php elseif($step === 3): ?>
            <!-- Step 3: Set New Password -->
            <form method="post" action="forgot_password.php?step=3">
                <div class="mb-3 password-input-group">
                    <label class="form-label"><?php echo t('new_password'); ?></label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="<?php echo t('password_placeholder'); ?>" 
                           required minlength="8">
                    <button type="button" class="password-toggle" id="passwordToggle1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <div class="mb-3 password-input-group">
                    <label class="form-label"><?php echo t('confirm_password'); ?></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                           placeholder="<?php echo t('confirm_placeholder'); ?>" 
                           required minlength="8">
                    <button type="button" class="password-toggle" id="passwordToggle2">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> <?php echo t('reset_button'); ?>
                    </button>
                    <a href="forgot_password.php?step=2" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
            
        <?php elseif($step === 4): ?>
            <!-- Success Step -->
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #198754;"></i>
                </div>
                <h5 class="mb-3"><?php echo t('password_updated'); ?></h5>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('back_to_login'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const toggles = ['passwordToggle1', 'passwordToggle2'];
            
            toggles.forEach((toggleId, index) => {
                const toggle = document.getElementById(toggleId);
                if (toggle) {
                    const passwordInput = document.getElementById(index === 0 ? 'password' : 'confirm_password');
                    const eyeIcon = toggle.querySelector('i');
                    
                    toggle.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        
                        if (type === 'text') {
                            eyeIcon.classList.remove('fa-eye');
                            eyeIcon.classList.add('fa-eye-slash');
                        } else {
                            eyeIcon.classList.remove('fa-eye-slash');
                            eyeIcon.classList.add('fa-eye');
                        }
                    });
                }
            });
            
            // Auto-focus first input
            const firstInput = document.querySelector('form input');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>