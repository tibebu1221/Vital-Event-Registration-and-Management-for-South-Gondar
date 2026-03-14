<?php
// Citizen Profile
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'citizen') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Language handling
if (isset($_GET['lang'])) {
    $new_lang = ($_GET['lang'] === 'am') ? 'am' : 'en';
    $_SESSION['lang'] = $new_lang;
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation array
$texts = [
    'en' => [
        'page_title'              => 'My Profile - VERMS',
        'header_title'            => 'Your Profile',
        'full_name'               => 'Full Name',
        'email'                   => 'Email',
        'phone'                   => 'Phone Number',
        'username'                => 'Username',
        'kebele'                  => 'Kebele',
        'woreda'                  => 'Woreda',
        'zone'                    => 'Zone',
        'save_changes'            => 'Save Changes',
        'profile_updated'         => 'Profile updated successfully!',
        'update_failed'           => 'Failed to update profile.',
        'username_disabled'       => 'Username (cannot be changed)',
        'kebele_disabled'         => 'Kebele (assigned by admin)',
        'woreda_disabled'         => 'Woreda (assigned by admin)',
        'zone_disabled'           => 'Zone (assigned by admin)',
    ],
    'am' => [
        'page_title'              => 'የእኔ መገለጫ - VERMS',
        'header_title'            => 'የእርስዎ መገለጫ',
        'full_name'               => 'ሙሉ ስም',
        'email'                   => 'ኢሜይል',
        'phone'                   => 'ስልክ ቁጥር',
        'username'                => 'የተጠቃሚ ስም',
        'kebele'                  => 'ቀበሌ',
        'woreda'                  => 'ወረዳ',
        'zone'                    => 'ዞን',
        'save_changes'            => 'ለውጦችን ቀይር',
        'profile_updated'         => 'መገለጫዎ በተሳካ ሁኔታ ተዘምኗል!',
        'update_failed'           => 'መገለጫን ማዘመን አልተሳካም።',
        'username_disabled'       => 'የተጠቃሚ ስም (ሊቀየር አይችልም)',
        'kebele_disabled'         => 'ቀበሌ (በአስተዳዳሪ የተመደበ)',
        'woreda_disabled'         => 'ወረዳ (በአስተዳዳሪ የተመደበ)',
        'zone_disabled'           => 'ዞን (በአስተዳዳሪ የተመደበ)',
    ]
];

// Fetch user data
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Handle profile update
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if ($fullname && $email) {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param('sssi', $fullname, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $success = $texts[$lang]['profile_updated'];
            // Refresh user data after update
            $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        } else {
            $error = $texts[$lang]['update_failed'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts[$lang]['page_title'] ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Amharic font support -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

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
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        [lang="am"] body,
        [lang="am"] h1, [lang="am"] h5, [lang="am"] .btn,
        [lang="am"] .form-label, [lang="am"] .modal-title,
        [lang="am"] .alert, [lang="am"] .card-header {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        .lang-switcher {
            position: fixed;
            top: 1rem;
            right: 1.5rem;
            z-index: 1050;
        }

        .profile-card {
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            font-size: 3rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: var(--shadow);
        }

        .form-label {
            font-weight: 600;
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            opacity: 0.9;
        }
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

<div class="container my-5">
    <div class="profile-card card shadow-lg">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0"><?= $texts[$lang]['header_title'] ?></h4>
        </div>
        <div class="card-body text-center">
            <!-- Success / Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Avatar -->
            <div class="profile-avatar">
                <?= strtoupper(substr($user['fullname'] ?? 'U', 0, 1)) ?>
            </div>

            <!-- Profile Form -->
            <form method="post">
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['full_name'] ?></label>
                    <input type="text" name="fullname" class="form-control" 
                           value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['email'] ?></label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['phone'] ?></label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['username'] ?></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>
                    <small class="form-text text-muted"><?= $texts[$lang]['username_disabled'] ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['kebele'] ?></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['kebele'] ?? 'N/A') ?>" disabled>
                    <small class="form-text text-muted"><?= $texts[$lang]['kebele_disabled'] ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['woreda'] ?></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['woreda'] ?? 'N/A') ?>" disabled>
                    <small class="form-text text-muted"><?= $texts[$lang]['woreda_disabled'] ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $texts[$lang]['zone'] ?></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['zone'] ?? 'N/A') ?>" disabled>
                    <small class="form-text text-muted"><?= $texts[$lang]['zone_disabled'] ?></small>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" name="save_profile" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i><?= $texts[$lang]['save_changes'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>