<?php
session_start();
require_once 'includes/db_connection.php';

// Initialize variables with default values
$error = '';
$success = '';

// Language handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation arrays
$translations = [
    'en' => [
        'title' => 'Citizen Registration - VERMS',
        'header_title' => 'Citizen Registration',
        'header_subtitle' => 'Join VERMS to manage your vital events registration',
        'personal_info' => 'Personal Information',
        'fullname' => 'Full Name',
        'fullname_placeholder' => 'Enter your full name',
        'phone' => 'Phone Number',
        'phone_placeholder' => 'Enter your phone number',
        'account_details' => 'Account Details',
        'username' => 'Username',
        'username_placeholder' => 'Choose a username',
        'email' => 'Email Address',
        'email_placeholder' => 'Enter your email',
        'password' => 'Password',
        'password_placeholder' => 'Create a password',
        'confirm_password' => 'Confirm Password',
        'confirm_password_placeholder' => 'Confirm your password',
        'security_setup' => 'Security Setup',
        'security_info' => 'This will be used to verify your identity if you forget your password.',
        'security_question' => 'Security Question',
        'select_question' => 'Select a security question',
        'security_answer' => 'Your Answer',
        'security_answer_placeholder' => 'Enter a memorable answer',
        'location_info' => 'Location Information',
        'zone' => 'Zone',
        'zone_placeholder' => 'Select Zone',
        'woreda' => 'Woreda',
        'woreda_placeholder' => 'Select Woreda',
        'kebele' => 'Kebele',
        'kebele_placeholder' => 'Select Kebele',
        'register_button' => 'Complete Registration',
        'login_link' => 'Already have an account?',
        'login_link_text' => 'Sign in here',
        'password_requirements' => 'Password Requirements:',
        'req_length' => 'At least 8 characters',
        'req_uppercase' => 'One uppercase letter',
        'req_lowercase' => 'One lowercase letter',
        'req_number' => 'One number',
        'req_special' => 'One special character',
        'passwords_match' => 'Passwords match',
        'passwords_not_match' => 'Passwords do not match',
        'all_fields_required' => 'All fields are required.',
        'passwords_mismatch' => 'Passwords do not match.',
        'password_length' => 'Password must be at least 8 characters long.',
        'password_uppercase' => 'Password must contain at least one uppercase letter.',
        'password_lowercase' => 'Password must contain at least one lowercase letter.',
        'password_number' => 'Password must contain at least one number.',
        'password_special' => 'Password must contain at least one special character.',
        'security_answer_required' => 'Please enter a security answer (at least 2 characters).',
        'username_exists' => 'Username already exists.',
        'email_exists' => 'Email already exists.',
        'phone_invalid' => 'Invalid phone number. Please enter a valid phone number (10-15 digits, optionally starting with +).',
        'phone_exists' => 'Phone number already exists. Please use a different phone number.',
        'phone_requirements' => 'Phone number must be 10-13 digits. International format allowed (e.g., +251911123456).',
        'registration_failed' => 'Registration failed. Please try again.',
        'registration_success' => 'Registration successful! You can now <a href="login.php">log in</a>.',
        'select_zone_first' => 'Please select a zone first.',
        'select_woreda_first' => 'Please select a woreda first.',
        'complete_location' => 'Please select your complete location information (Zone, Woreda, and Kebele).'
    ],
    'am' => [
        'title' => 'የከተማ ምዝገባ - VERMS',
        'header_title' => 'የከተማ ምዝገባ',
        'header_subtitle' => 'የህይወት ክስተቶች ምዝገባዎን ለማስተዳደር በVERMS ይቀላቀሉ',
        'personal_info' => 'የግል መረጃ',
        'fullname' => 'ሙሉ ስም',
        'fullname_placeholder' => 'ሙሉ ስምዎን ያስገቡ',
        'phone' => 'ስልክ ቁጥር',
        'phone_placeholder' => 'ስልክ ቁጥርዎን ያስገቡ',
        'account_details' => 'የመለያ ዝርዝሮች',
        'username' => 'የተጠቃሚ ስም',
        'username_placeholder' => 'የተጠቃሚ ስም ይምረጡ',
        'email' => 'የኢሜል አድራሻ',
        'email_placeholder' => 'ኢሜልዎን ያስገቡ',
        'password' => 'የይለፍ ቃል',
        'password_placeholder' => 'የይለፍ ቃል ይፍጠሩ',
        'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
        'confirm_password_placeholder' => 'የይለፍ ቃልዎን አረጋግጥ',
        'security_setup' => 'የደህንነት አዋቂያ',
        'security_info' => 'ይህ የይለፍ ቃልዎን ከረሱ ለማረጋገጥ ይጠቅማል።',
        'security_question' => 'የደህንነት ጥያቄ',
        'select_question' => 'የደህንነት ጥያቄ ይምረጡ',
        'security_answer' => 'መልስዎ',
        'security_answer_placeholder' => 'ለማስታወስ የሚችል መልስ ያስገቡ',
        'location_info' => 'የአድራሻ መረጃ',
        'zone' => 'ዞን',
        'zone_placeholder' => 'ዞን ይምረጡ',
        'woreda' => 'ወረዳ',
        'woreda_placeholder' => 'ወረዳ ይምረጡ',
        'kebele' => 'ቀበሌ',
        'kebele_placeholder' => 'ቀበሌ ይምረጡ',
        'register_button' => 'ምዝገባን አጠናቅቅ',
        'login_link' => 'ቀድሞውኑ መለያ አለዎት?',
        'login_link_text' => 'እዚህ ይግቡ',
        'password_requirements' => 'የይለፍ ቃል መስፈርቶች:',
        'req_length' => 'ቢያንስ 8 ቁምፊ',
        'req_uppercase' => 'አንድ አቢይ ፊደል',
        'req_lowercase' => 'አንድ ትንሽ ፊደል',
        'req_number' => 'አንድ ቁጥር',
        'req_special' => 'አንድ ልዩ ቁምፊ',
        'passwords_match' => 'የይለፍ ቃላት ይዛመዳሉ',
        'passwords_not_match' => 'የይለፍ ቃላት አይዛመዱም',
        'all_fields_required' => 'ሁሉም መስኮች ያስፈልጋሉ.',
        'passwords_mismatch' => 'የይለፍ ቃላት አይዛመዱም.',
        'password_length' => 'የይለፍ ቃሉ ቢያንስ 8 ቁምፊ ሊኖረው ይገባል.',
        'password_uppercase' => 'የይለፍ ቃሉ ቢያንስ አንድ አቢይ ፊደል ሊኖረው ይገባል.',
        'password_lowercase' => 'የይለፍ ቃሉ ቢያንስ አንድ ትንሽ ፊደል ሊኖረው ይገባል.',
        'password_number' => 'የይለፍ ቃሉ ቢያንስ አንድ ቁጥር ሊኖረው ይገባል.',
        'password_special' => 'የይለፍ ቃሉ ቢያንስ አንድ ልዩ ቁምፊ ሊኖረው ይገባል.',
        'security_answer_required' => 'እባክዎ የደህንነት መልስ ያስገቡ (ቢያንስ 2 ቁምፊ).',
        'username_exists' => 'የተጠቃሚ ስም አስቀድሞ አለ.',
        'email_exists' => 'ኢሜል አስቀድሞ አለ.',
        'phone_invalid' => 'የማያገለግል ስልክ ቁጥር። እባክዎ ትክክለኛ ስልክ ቁጥር ያስገቡ (10-15 አሃዞች፣ በ+ ሊጀምር ይችላል)።',
        'phone_exists' => 'ስልክ ቁጥሩ አስቀድሞ አለ። እባክዎ ሌላ ስልክ ቁጥር ይጠቀሙ።',
        'phone_requirements' => 'ስልክ ቁጥሩ 10-13 አሃዞች መሆን አለበት። ዓለም አቀፍ ቅርጸት ይፈቀዳል (ለምሳሌ፣ +251911123456)።',
        'registration_failed' => 'ምዝገባ አልተሳካም. እባክዎ እንደገና ይሞክሩ.',
        'registration_success' => 'ምዝገባ ተሳክቷል! አሁን <a href="login.php">መግባት</a> ይችላሉ.',
        'select_zone_first' => 'እባክዎ በመጀመሪያ ዞን ይምረጡ.',
        'select_woreda_first' => 'እባክዎ በመጀመሪያ ወረዳ ይምረጡ.',
        'complete_location' => 'እባክዎ የአድራሻዎን ሙሉ መረጃ (ዞን፣ ወረዳ፣ እና ቀበሌ) ይምረጡ.'
    ]
];

$t = $translations[$lang];

// Registration logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Get location names
    $zone = trim($_POST['zone'] ?? '');
    $woreda = trim($_POST['woreda'] ?? '');
    $kebele = trim($_POST['kebele'] ?? '');
    
    // Get security question and answer
    $security_question = trim($_POST['security_question'] ?? 'What is your memorable security question?');
    $security_answer = trim($_POST['security_answer'] ?? '');
    
    $role = 'citizen';

    // validation

    if (empty($fullname) || empty($username) || empty($email) || empty($password) || 
        empty($confirm_password) || empty($phone) || empty($zone) || 
        empty($woreda) || empty($kebele) || empty($security_answer)) {
        $error = $t['all_fields_required'];
    } elseif ($password !== $confirm_password) {
        $error = $t['passwords_mismatch'];
    } elseif (strlen($password) < 8) {
        $error = $t['password_length'];
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = $t['password_uppercase'];
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = $t['password_lowercase'];
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = $t['password_number'];
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = $t['password_special'];
    } elseif (strlen($security_answer) < 2) {
        $error = $t['security_answer_required'];
    } elseif (!preg_match('/^\+?[0-9]{10,13}$/', $phone)) {
        // Phone number validation
        $error = $t['phone_invalid'];
    } else {
        // Check username
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = $t['username_exists'];
        } else {
            // Check email
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = $t['email_exists'];
            } else {
                // Check phone number
                $stmt = $conn->prepare('SELECT id FROM users WHERE phone = ?');
                $stmt->bind_param('s', $phone);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $error = $t['phone_exists'];
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert with ALL fields including security
                    $stmt = $conn->prepare('INSERT INTO users (fullname, username, email, phone, kebele, woreda, zone, password, role, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('sssssssssss', $fullname, $username, $email, $phone, $kebele, $woreda, $zone, $hashed_password, $role, $security_question, $security_answer);
                    
                    if ($stmt->execute()) {
                        $success = $t['registration_success'];
                        // Clear form data after successful registration
                        $_POST = array();
                    } else {
                        $error = $t['registration_failed'] . ' Error: ' . $stmt->error;
                    }
                }
            }
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
    <title><?php echo $t['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .registration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 80px);
            padding: 20px;
        }

        .registration-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
        }

        .registration-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .registration-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }

        .registration-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .registration-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .registration-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .registration-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .input-with-icon {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: var(--transition);
            height: 50px;
            padding: 12px 15px;
        }

        .input-with-icon:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            height: 50px;
            transition: var(--transition);
        }

        .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .password-strength {
            margin-top: 8px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: var(--transition);
        }

        .password-requirements {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            opacity: 1;
            max-height: 24px;
            overflow: hidden;
        }

        .requirement.hidden {
            opacity: 0;
            max-height: 0;
            margin-bottom: 0;
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

        .btn-register {
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: var(--transition);
            height: 50px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
        }

        .login-link a {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--light-color);
        }

        .is-invalid {
            border-color: var(--accent-color) !important;
        }
        
        .is-valid {
            border-color: var(--success-color) !important;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .phone-help {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Password input group with show/hide button */
        .password-input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
        }

        .toggle-password:hover {
            color: var(--secondary-color);
        }

        /* Language switcher */
        .language-switcher {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
        }

        .language-btn {
            background: white;
            border: 2px solid var(--secondary-color);
            border-radius: 25px;
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            min-width: auto;
            width: auto;
        }

        .language-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .language-btn.active {
            background: var(--secondary-color);
            color: white;
        }

        .language-btn i {
            font-size: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .registration-body {
                padding: 25px;
            }
            
            .registration-header {
                padding: 20px;
            }
            
            .registration-header h1 {
                font-size: 1.8rem;
            }
            
            .language-switcher {
                top: 80px;
                right: 10px;
            }
            
            .language-btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .language-btn span {
                display: none;
            }
            
            .language-btn {
                padding: 8px;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .language-btn i {
                font-size: 1.1rem;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Language Switcher -->
<div class="language-switcher">
    <a href="?lang=en" class="language-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
        <i class="fas fa-globe-americas"></i>
        <span>English</span>
    </a>
    <a href="?lang=am" class="language-btn <?php echo $lang === 'am' ? 'active' : ''; ?>" style="margin-left: 5px;">
        <i class="fas fa-language"></i>
        <span>አማርኛ</span>
    </a>
</div>

<div class="registration-container">
    <div class="registration-card">
        <div class="registration-header">
            <div class="registration-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="display-6 fw-bold"><?php echo $t['header_title']; ?></h1>
            <p class="mb-0"><?php echo $t['header_subtitle']; ?></p>
        </div>

        <div class="registration-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="registrationForm">
                <div class="section-title">
                    <i class="fas fa-user me-2"></i><?php echo $t['personal_info']; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['fullname']; ?></label>
                        <input type="text" name="fullname" class="form-control input-with-icon" placeholder="<?php echo $t['fullname_placeholder']; ?>" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['phone']; ?></label>
                        <input type="tel" name="phone" class="form-control input-with-icon" 
                               placeholder="<?php echo $t['phone_placeholder']; ?>" 
                               required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        <div class="phone-help"><?php echo $t['phone_requirements']; ?></div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-key me-2"></i><?php echo $t['account_details']; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['username']; ?></label>
                        <input type="text" name="username" class="form-control input-with-icon" placeholder="<?php echo $t['username_placeholder']; ?>" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['email']; ?></label>
                        <input type="email" name="email" class="form-control input-with-icon" placeholder="<?php echo $t['email_placeholder']; ?>" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['password']; ?></label>
                        <div class="password-input-group">
                            <input type="password" name="password" id="password" class="form-control input-with-icon" placeholder="<?php echo $t['password_placeholder']; ?>" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrength"></div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement invalid" id="req-length">
                                <i class="fas fa-circle"></i><?php echo $t['req_length']; ?>
                            </div>
                            <div class="requirement invalid" id="req-uppercase">
                                <i class="fas fa-circle"></i><?php echo $t['req_uppercase']; ?>
                            </div>
                            <div class="requirement invalid" id="req-lowercase">
                                <i class="fas fa-circle"></i><?php echo $t['req_lowercase']; ?>
                            </div>
                            <div class="requirement invalid" id="req-number">
                                <i class="fas fa-circle"></i><?php echo $t['req_number']; ?>
                            </div>
                            <div class="requirement invalid" id="req-special">
                                <i class="fas fa-circle"></i><?php echo $t['req_special']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['confirm_password']; ?></label>
                        <div class="password-input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control input-with-icon" placeholder="<?php echo $t['confirm_password_placeholder']; ?>" required>
                            <button type="button" class="toggle-password" id="toggleConfirmPassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match mt-2" id="passwordMatch"></div>
                    </div>
                </div>

                <!-- NEW: Security Questions Section -->
                <div class="section-title">
                    <i class="fas fa-shield-alt me-2"></i><?php echo $t['security_setup']; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo $t['security_info']; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['security_question']; ?></label>
                        <select name="security_question" class="form-select" required>
                            <option value="" disabled <?= !isset($_POST['security_question']) ? 'selected' : '' ?>><?php echo $t['select_question']; ?></option>
                            <option value="What is your memorable security question?" <?= (isset($_POST['security_question']) && $_POST['security_question'] == 'What is your memorable security question?') ? 'selected' : 'selected' ?>>
                                What is your memorable security question?
                            </option>
                            <option value="What city were you born in?" <?= (isset($_POST['security_question']) && $_POST['security_question'] == 'What city were you born in?') ? 'selected' : '' ?>>
                                What city were you born in?
                            </option>
                            <option value="What is your mother's maiden name?" <?= (isset($_POST['security_question']) && $_POST['security_question'] == 'What is your mother\'s maiden name?') ? 'selected' : '' ?>>
                                What is your mother's maiden name?
                            </option>
                            <option value="What was your first pet's name?" <?= (isset($_POST['security_question']) && $_POST['security_question'] == 'What was your first pet\'s name?') ? 'selected' : '' ?>>
                                What was your first pet's name?
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $t['security_answer']; ?></label>
                        <input type="text" name="security_answer" class="form-control input-with-icon" 
                               placeholder="<?php echo $t['security_answer_placeholder']; ?>" required
                               value="<?= htmlspecialchars($_POST['security_answer'] ?? '') ?>">
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $t['location_info']; ?>
                </div>
                <div class="row g-3">
                    <?php
                    // Fetch zones for dropdown
                    $zone_options = [];
                    $zone_result = $conn->query("SELECT DISTINCT zone_name FROM zones ORDER BY zone_name ASC");
                    if ($zone_result) {
                        while ($row = $zone_result->fetch_assoc()) {
                            $zone_options[] = $row['zone_name'];
                        }
                    }
                    
                    // Fetch woredas for dropdown
                    $woreda_options = [];
                    $woreda_result = $conn->query("SELECT DISTINCT woreda_name FROM woredas ORDER BY woreda_name ASC");
                    if ($woreda_result) {
                        while ($row = $woreda_result->fetch_assoc()) {
                            $woreda_options[] = $row['woreda_name'];
                        }
                    }
                    
                    // Fetch kebeles for dropdown
                    $kebele_options = [];
                    $kebele_result = $conn->query("SELECT DISTINCT kebele_name FROM kebeles ORDER BY kebele_name ASC");
                    if ($kebele_result) {
                        while ($row = $kebele_result->fetch_assoc()) {
                            $kebele_options[] = $row['kebele_name'];
                        }
                    }
                    ?>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $t['zone']; ?></label>
                        <select name="zone" id="zone" class="form-select" required>
                            <option value="" disabled selected><?php echo $t['zone_placeholder']; ?></option>
                            <?php foreach ($zone_options as $zone_name): ?>
                                <option value="<?= htmlspecialchars($zone_name) ?>" <?= (isset($_POST['zone']) && $_POST['zone'] == $zone_name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($zone_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $t['woreda']; ?></label>
                        <select name="woreda" id="woreda" class="form-select" required>
                            <option value="" disabled selected><?php echo $t['woreda_placeholder']; ?></option>
                            <?php foreach ($woreda_options as $woreda_name): ?>
                                <option value="<?= htmlspecialchars($woreda_name) ?>" <?= (isset($_POST['woreda']) && $_POST['woreda'] == $woreda_name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($woreda_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $t['kebele']; ?></label>
                        <select name="kebele" id="kebele" class="form-select" required>
                            <option value="" disabled selected><?php echo $t['kebele_placeholder']; ?></option>
                            <?php foreach ($kebele_options as $kebele_name): ?>
                                <option value="<?= htmlspecialchars($kebele_name) ?>" <?= (isset($_POST['kebele']) && $_POST['kebele'] == $kebele_name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kebele_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-register mt-4"><i class="fas fa-user-plus me-2"></i><?php echo $t['register_button']; ?></button>
            </form>

            <div class="login-link">
                <?php echo $t['login_link']; ?> <a href="login.php"><?php echo $t['login_link_text']; ?></a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password strength indicator and requirement validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    
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
    
    strengthBar.style.width = strength + '%';
    
    // Update color based on strength
    if (strength < 60) {
        strengthBar.style.background = '#e74c3c';
    } else if (strength < 100) {
        strengthBar.style.background = '#f39c12';
    } else {
        strengthBar.style.background = '#27ae60';
    }
    
    // Show/hide requirements based on password input
    const requirementsContainer = document.querySelector('.password-requirements');
    const requirements = requirementsContainer.querySelectorAll('.requirement');
    
    if (password.length > 0) {
        // Show all requirements
        requirements.forEach(req => {
            req.classList.remove('hidden');
        });
    } else {
        // Hide all requirements when password is empty
        requirements.forEach(req => {
            req.classList.add('hidden');
        });
    }
});

// Update requirement indicator with show/hide functionality
function updateRequirement(elementId, isValid) {
    const element = document.getElementById(elementId);
    if (isValid) {
        element.classList.remove('invalid');
        element.classList.add('valid');
        // Change icon to check and text
        const icon = element.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-check-circle';
            icon.style.color = '#27ae60';
        }
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
        // Change icon back to circle
        const icon = element.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-circle';
            icon.style.color = '#adb5bd';
        }
    }
}

// Password match indicator
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchIndicator = document.getElementById('passwordMatch');
    
    if (confirmPassword === '') {
        matchIndicator.innerHTML = '';
        return;
    }
    
    if (password === confirmPassword) {
        matchIndicator.innerHTML = '<span style="color: #27ae60;"><i class="fas fa-check-circle me-1"></i><?php echo $t["passwords_match"]; ?></span>';
    } else {
        matchIndicator.innerHTML = '<span style="color: #e74c3c;"><i class="fas fa-times-circle me-1"></i><?php echo $t["passwords_not_match"]; ?></span>';
    }
});

// Phone number validation
document.querySelector('input[name="phone"]').addEventListener('input', function() {
    const phoneInput = this;
    const phone = phoneInput.value;
    
    // Remove any non-digit characters except plus sign at the beginning
    let cleanedPhone = phone.replace(/[^\d+]/g, '');
    
    // Keep plus sign only if it's at the beginning
    if (cleanedPhone.startsWith('+')) {
        cleanedPhone = '+' + cleanedPhone.substring(1).replace(/\D/g, '');
    } else {
        cleanedPhone = cleanedPhone.replace(/\D/g, '');
    }
    
    // Update the input value with cleaned version
    if (phone !== cleanedPhone) {
        phoneInput.value = cleanedPhone;
    }
    
    // Validate phone format
    const phoneRegex = /^\+?[0-9]{10,13}$/;
    const isValid = phoneRegex.test(cleanedPhone);
    
    // Add visual feedback
    if (cleanedPhone === '') {
        phoneInput.classList.remove('is-invalid');
        phoneInput.classList.remove('is-valid');
    } else if (isValid) {
        phoneInput.classList.remove('is-invalid');
        phoneInput.classList.add('is-valid');
    } else {
        phoneInput.classList.remove('is-valid');
        phoneInput.classList.add('is-invalid');
    }
});

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Toggle confirm password visibility
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const confirmInput = document.getElementById('confirm_password');
    const icon = this.querySelector('i');
    
    if (confirmInput.type === 'password') {
        confirmInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        confirmInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Form validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const securityAnswer = document.querySelector('input[name="security_answer"]').value;
    
    // Check password requirements
    const hasLength = password.length >= 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);
    
    if (!hasLength || !hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
        e.preventDefault();
        alert('<?php echo $lang === "am" ? "እባክዎ የይለፍ ቃልዎ ሁሉንም መስፈርቶች እንደሚያሟሉ ያረጋግጡ:\n\n• ቢያንስ 8 ቁምፊ\n• አንድ አቢይ ፊደል\n• አንድ ትንሽ ፊደል\n• አንድ ቁጥር\n• አንድ ልዩ ቁምፊ" : "Please ensure your password meets all requirements:\n\n• At least 8 characters\n• One uppercase letter\n• One lowercase letter\n• One number\n• One special character"; ?>');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('<?php echo $t["passwords_mismatch"]; ?>');
        return false;
    }
    
    // Check security answer
    if (securityAnswer.length < 2) {
        e.preventDefault();
        alert('<?php echo $t["security_answer_required"]; ?>');
        return false;
    }
    
    // Check phone number
    const phone = document.querySelector('input[name="phone"]').value;
    const phoneRegex = /^\+?[0-9]{10,13}$/;
    
    if (!phoneRegex.test(phone)) {
        e.preventDefault();
        alert('<?php echo $t["phone_invalid"]; ?>');
        document.querySelector('input[name="phone"]').focus();
        return false;
    }
    
    // Check if all location fields are selected
    const zone = document.getElementById('zone').value;
    const woreda = document.getElementById('woreda').value;
    const kebele = document.getElementById('kebele').value;
    
    if (!zone || !woreda || !kebele) {
        e.preventDefault();
        alert('<?php echo $t["complete_location"]; ?>');
        return false;
    }
    
    return true;
});
</script>
</body>
</html>