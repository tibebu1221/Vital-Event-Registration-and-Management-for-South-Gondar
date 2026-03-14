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
        'page_title' => 'Officer Registration - VERMS',
        'registration_header' => 'Officer Registration',
        'registration_subtitle' => 'Create an account to manage vital records in your assigned area.',
        
        // Form labels
        'full_name' => 'Full Name',
        'username' => 'Username',
        'email' => 'Email',
        'role' => 'Role',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'select_role' => 'Select Role',
        'kebele_officer' => 'Kebele Officer',
        'woreda_officer' => 'Woreda Officer',
        'zone_officer' => 'Zone Officer',
        'statistician' => 'Statistician',
        
        // Password requirements
        'password_requirements' => 'Password Requirements',
        'min_length' => 'At least 8 characters',
        'uppercase' => 'One uppercase letter',
        'lowercase' => 'One lowercase letter',
        'number' => 'One number',
        'special_char' => 'One special character',
        
        // Buttons and links
        'create_account' => 'Create Account',
        'already_registered' => 'Already registered?',
        'sign_in_here' => 'Sign in here',
        'login' => 'Log in',
        
        // Messages
        'all_fields_required' => 'All fields are required.',
        'passwords_not_match' => 'Passwords do not match.',
        'password_min_length' => 'Password must be at least 8 characters long.',
        'password_uppercase' => 'Password must contain at least one uppercase letter.',
        'password_lowercase' => 'Password must contain at least one lowercase letter.',
        'password_number' => 'Password must contain at least one number.',
        'password_special_char' => 'Password must contain at least one special character.',
        'username_email_exists' => 'Username or email already exists.',
        'account_created_success' => 'Officer account created successfully!',
        'try_again' => 'Please try again.',
        'error_creating_account' => 'Error creating account.',
        
        // Validation messages
        'passwords_match' => 'Passwords match',
        'passwords_not_match_js' => 'Passwords do not match'
    ],
    'am' => [
        // Page title and headers
        'page_title' => 'የአሰልጣኝ ምዝገባ - ቪ.ኢ.አር.ኤም.ኤስ',
        'registration_header' => 'የአሰልጣኝ ምዝገባ',
        'registration_subtitle' => 'በተመደቡልዎት አካባቢ የህይወት መዝገቦችን ለማስተዳደር መለያ ይፍጠሩ።',
        
        // Form labels
        'full_name' => 'ሙሉ ስም',
        'username' => 'የተጠቃሚ ስም',
        'email' => 'ኢሜይል',
        'role' => 'ሚና',
        'password' => 'የይለፍ ቃል',
        'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
        'select_role' => 'ሚና ይምረጡ',
        'kebele_officer' => 'የቀበሌ አሰልጣኝ',
        'woreda_officer' => 'የወረዳ አሰልጣኝ',
        'zone_officer' => 'የዞን አሰልጣኝ',
        'statistician' => 'ቁጥር አጥማጅ',
        
        // Password requirements
        'password_requirements' => 'የይለፍ ቃል መስፈርቶች',
        'min_length' => 'ቢያንስ 8 ቁምፊዎች',
        'uppercase' => 'አንድ አቢይ ፊደል',
        'lowercase' => 'አንድ ትንሽ ፊደል',
        'number' => 'አንድ ቁጥር',
        'special_char' => 'አንድ ልዩ ቁምፊ',
        
        // Buttons and links
        'create_account' => 'መለያ ይፍጠሩ',
        'already_registered' => 'ቀደም ብለው የተመዘገቡ?',
        'sign_in_here' => 'እዚህ ይግቡ',
        'login' => 'ግባ',
        
        // Messages
        'all_fields_required' => 'ሁሉም መስኮች አስፈላጊ ናቸው።',
        'passwords_not_match' => 'የይለፍ ቃላቶቹ አይዛመዱም።',
        'password_min_length' => 'የይለፍ ቃሉ ቢያንስ 8 ቁምፊዎች መሆን አለበት።',
        'password_uppercase' => 'የይለፍ ቃሉ ቢያንስ አንድ አቢይ ፊደል መያዝ አለበት።',
        'password_lowercase' => 'የይለፍ ቃሉ ቢያንስ አንድ ትንሽ ፊደል መያዝ አለበት።',
        'password_number' => 'የይለፍ ቃሉ ቢያንስ አንድ ቁጥር መያዝ አለበት።',
        'password_special_char' => 'የይለፍ ቃሉ ቢያንስ አንድ ልዩ ቁምፊ መያዝ አለበት።',
        'username_email_exists' => 'የተጠቃሚ ስም ወይም ኢሜይል ቀደም ብሎ ይገኛል።',
        'account_created_success' => 'የአሰልጣኝ መለያ በተሳካ ሁኔታ ተፈጥሯል!',
        'try_again' => 'እባክዎ እንደገና ይሞክሩ።',
        'error_creating_account' => 'መለያ ለመፍጠር ስህተት።',
        
        // Validation messages
        'passwords_match' => 'የይለፍ ቃላቶቹ ይዛመዳሉ',
        'passwords_not_match_js' => 'የይለፍ ቃላቶቹ አይዛመዱም'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

require_once '../includes/db_connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    if (!$fullname || !$username || !$email || !$password || !$confirm_password || !$role) {
        $error = t('all_fields_required');
    } elseif ($password !== $confirm_password) {
        $error = t('passwords_not_match');
    } elseif (strlen($password) < 8) {
        $error = t('password_min_length');
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = t('password_uppercase');
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = t('password_lowercase');
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = t('password_number');
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = t('password_special_char');
    } else {
        // Check for existing username or email
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = t('username_email_exists');
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (fullname, username, email, password, role, phone, kebele, woreda, zone, status, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active", 1)');
            $default_phone = "";
            $default_kebele = "";
            $default_woreda = "";
            $default_zone = "";
            $stmt->bind_param('sssssssss', $fullname, $username, $email, $hashed_password, $role, $default_phone, $default_kebele, $default_woreda, $default_zone);

            if ($stmt->execute()) {
                $success = t('account_created_success') . ' <a href="login.php">' . t('login') . '</a>.';
                $_POST = [];
            } else {
                $error = t('error_creating_account') . ' ' . t('try_again');
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo t('page_title'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
<?php if($lang === 'am'): ?>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');

body {
    font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    text-align: justify;
}

.registration-header h1,
.registration-header p,
.form-label,
.btn-register,
.alert,
.login-link,
.password-requirements,
.requirement {
    font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
<?php endif; ?>

:root {
  --primary-color: #2c3e50;
  --secondary-color: #3498db;
  --accent-color: #e74c3c;
  --success-color: #27ae60;
  --warning-color: #f39c12;
  --gradient-primary: linear-gradient(135deg,#2c3e50 0%,#3498db 100%);
  --transition: all 0.3s ease;
}
body {
  background: linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);
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
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 900px;
  overflow: hidden;
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
  top: 0; left: 0; right: 0;
  height: 4px;
  background: var(--accent-color);
}
.registration-icon {
  font-size: 3rem;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.2);
  width: 80px; height: 80px;
  border-radius: 50%;
  margin: 0 auto 15px;
}
.registration-body { padding: 40px; }
.password-strength {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
  margin-top: 8px;
}
.password-strength-bar {
  height: 100%;
  width: 0%;
  border-radius: 3px;
  transition: all 0.3s ease;
}
.password-requirements {
  margin-top: 8px;
  font-size: 0.85rem;
}
.requirement {
  display: flex;
  align-items: center;
  margin-bottom: 4px;
  transition: all 0.3s ease;
}
.requirement.valid {
  color: var(--success-color);
}
.requirement.invalid {
  color: #6c757d;
}
.requirement i {
  margin-right: 8px;
  transition: all 0.3s ease;
}
.requirement.valid i {
  color: var(--success-color);
}
.requirement.invalid i {
  color: #adb5bd;
}
.btn-register {
  background: var(--gradient-primary);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px 30px;
  font-weight: 600;
  width: 100%;
  transition: var(--transition);
}
.btn-register:hover { transform: translateY(-2px); }
.alert {
  border-radius: 8px;
}
.alert-danger {
  background: rgba(231,76,60,0.1);
  color: var(--accent-color);
}
.alert-success {
  background: rgba(39,174,96,0.1);
  color: var(--success-color);
}

/* Language switcher - Fixed position to prevent overlap */
.language-switcher {
    position: fixed;
    top: 100px;
    right: 20px;
    z-index: 1000;
    display: flex;
    gap: 8px;
}

.lang-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 2px solid var(--primary-color);
    border-radius: 25px;
    padding: 8px 16px;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--primary-color);
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    min-width: 70px;
}

.lang-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

.lang-btn.active {
    background: var(--gradient-primary);
    color: white;
    border-color: transparent;
}

.lang-btn i {
    margin-right: 5px;
    font-size: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .language-switcher {
        position: fixed;
        top: 80px; 
        right: 20px;
        z-index: 1050;
    }
    
    .lang-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
        min-width: 60px;
    }
    
    .lang-btn i {
        margin-right: 3px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .language-switcher {
        top: 70px;
        right: 10px;
        flex-direction: column;
        gap: 5px;
    }
    
    .lang-btn {
        padding: 5px 10px;
        font-size: 0.75rem;
        min-width: 55px;
    }
    
    .lang-btn span {
        display: none;
    }
    
    .lang-btn i {
        margin-right: 0;
        font-size: 1rem;
    }
}
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- Language Switcher - Fixed position to prevent overlap -->
<div class="language-switcher">
    <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
        <i class="fas fa-globe-americas"></i>
        <span>EN</span>
    </a>
    <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">
        <i class="fas fa-globe-africa"></i>
        <span>አማ</span>
    </a>
</div>

<div class="registration-container">
  <div class="registration-card">
    <div class="registration-header">
      <div class="registration-icon"><i class="fas fa-user-shield"></i></div>
      <h1 class="display-6 fw-bold"><?php echo t('registration_header'); ?></h1>
      <p><?php echo t('registration_subtitle'); ?></p>
    </div>

    <div class="registration-body">
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
      <?php endif; ?>

      <form method="POST" id="officerForm">
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label"><?php echo t('full_name'); ?></label>
            <input type="text" name="fullname" class="form-control" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo t('username'); ?></label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo t('email'); ?></label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo t('role'); ?></label>
            <select name="role" class="form-select" required>
              <option value=""><?php echo t('select_role'); ?></option>
              <option value="kebele" <?= ($_POST['role'] ?? '') === 'kebele' ? 'selected' : '' ?>><?php echo t('kebele_officer'); ?></option>
              <option value="woreda" <?= ($_POST['role'] ?? '') === 'woreda' ? 'selected' : '' ?>><?php echo t('woreda_officer'); ?></option>
              <option value="zone" <?= ($_POST['role'] ?? '') === 'zone' ? 'selected' : '' ?>><?php echo t('zone_officer'); ?></option>
              <option value="statistician" <?= ($_POST['role'] ?? '') === 'statistician' ? 'selected' : '' ?>><?php echo t('statistician'); ?></option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo t('password'); ?></label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo t('confirm_password'); ?></label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            <div id="passwordMatch" class="mt-2"></div>
          </div>

          <div class="col-12">
            <small class="text-muted"><?php echo t('password_requirements'); ?>:</small>
            <div class="password-strength"><div id="passwordStrength" class="password-strength-bar"></div></div>
            <div class="password-requirements">
              <div class="requirement invalid" id="req-length"><i class="fas fa-circle me-1"></i><?php echo t('min_length'); ?></div>
              <div class="requirement invalid" id="req-uppercase"><i class="fas fa-circle me-1"></i><?php echo t('uppercase'); ?></div>
              <div class="requirement invalid" id="req-lowercase"><i class="fas fa-circle me-1"></i><?php echo t('lowercase'); ?></div>
              <div class="requirement invalid" id="req-number"><i class="fas fa-circle me-1"></i><?php echo t('number'); ?></div>
              <div class="requirement invalid" id="req-special"><i class="fas fa-circle me-1"></i><?php echo t('special_char'); ?></div>
            </div>
          </div>

        </div>

        <button type="submit" class="btn btn-register mt-4">
          <i class="fas fa-user-plus me-2"></i><?php echo t('create_account'); ?>
        </button>
      </form>

      <div class="login-link mt-3 text-center">
        <?php echo t('already_registered'); ?> <a href="login.php"><?php echo t('sign_in_here'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Password validation
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const strengthBar = document.getElementById('passwordStrength');
const matchIndicator = document.getElementById('passwordMatch');

passwordInput.addEventListener('input', () => {
  const p = passwordInput.value;
  
  // Check each requirement
  const checks = {
    length: p.length >= 8,
    upper: /[A-Z]/.test(p),
    lower: /[a-z]/.test(p),
    number: /[0-9]/.test(p),
    special: /[^A-Za-z0-9]/.test(p)
  };
  
  // Update each requirement display
  Object.entries(checks).forEach(([key, valid]) => {
    const requirementElement = document.getElementById(`req-${key}`);
    if (requirementElement) {
      if (valid) {
        requirementElement.classList.remove('invalid');
        requirementElement.classList.add('valid');
        const icon = requirementElement.querySelector('i');
        if (icon) {
          icon.className = 'fas fa-check-circle me-1';
          icon.style.color = '#27ae60';
        }
      } else {
        requirementElement.classList.remove('valid');
        requirementElement.classList.add('invalid');
        const icon = requirementElement.querySelector('i');
        if (icon) {
          icon.className = 'fas fa-circle me-1';
          icon.style.color = '#adb5bd';
        }
      }
    }
  });
  
  // Update strength bar
  const score = Object.values(checks).filter(Boolean).length * 20;
  strengthBar.style.width = score + '%';
  
  // Color code the strength bar
  if (score < 20) {
    strengthBar.style.backgroundColor = '#e74c3c'; // Red for very weak
  } else if (score < 60) {
    strengthBar.style.backgroundColor = '#f39c12'; // Orange/Yellow for weak
  } else if (score < 100) {
    strengthBar.style.backgroundColor = '#3498db'; // Blue for medium
  } else {
    strengthBar.style.backgroundColor = '#27ae60'; // Green for strong
  }
});

function updateRequirementDisplay(id, isValid) {
  const element = document.getElementById(id);
  if (!element) return;
  
  if (isValid) {
    element.classList.remove('invalid');
    element.classList.add('valid');
  } else {
    element.classList.remove('valid');
    element.classList.add('invalid');
  }
}

confirmInput.addEventListener('input', () => {
  const lang = '<?php echo $lang; ?>';
  const match = passwordInput.value === confirmInput.value;
  const matchText = lang === 'am' ? '<?php echo t("passwords_match"); ?>' : 'Passwords match';
  const notMatchText = lang === 'am' ? '<?php echo t("passwords_not_match_js"); ?>' : 'Passwords do not match';
  
  matchIndicator.innerHTML = confirmInput.value
    ? match
      ? '<span style="color:#27ae60;"><i class="fas fa-check-circle me-1"></i>' + matchText + '</span>'
      : '<span style="color:#e74c3c;"><i class="fas fa-times-circle me-1"></i>' + notMatchText + '</span>'
    : '';
});

// Form validation
document.getElementById('officerForm').addEventListener('submit', function(e) {
    // Check if there's already a success message
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        // Account already created, prevent form submission
        e.preventDefault();
        return false;
    }
    
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    const role = document.querySelector('select[name="role"]').value;
    
    if (!role) {
        e.preventDefault();
        alert('<?php echo addslashes(t("select_role")); ?>');
        return false;
    }
    
    if (password !== confirm) {
        e.preventDefault();
        alert('<?php echo addslashes(t("passwords_not_match")); ?>');
        return false;
    }
    
    const checks = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    if (!Object.values(checks).every(Boolean)) {
        e.preventDefault();
        alert('<?php echo addslashes(t("password_requirements")); ?>');
        return false;
    }
});
</script>
</body>
</html>