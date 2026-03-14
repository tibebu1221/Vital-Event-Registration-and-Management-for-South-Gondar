<?php
session_start();
require_once '../includes/db_connection.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

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
        'page_title'              => 'Woreda Management - VERMS',
        'header_title'            => 'Woreda Management',
        'header_subtitle'         => 'Register and manage woredas across administrative zones',
        'add_new_woreda'          => 'Add New Woreda',
        'total_woredas'           => 'Total Woredas',
        'zones'                   => 'Zones',
        'woreda_officers'         => 'Woreda Officers',
        'kebeles'                 => 'Kebeles',
        'registered_woredas'      => 'Registered Woredas',
        'no_woredas_found'        => 'No Woredas Found',
        'no_woredas_message'      => 'There are no registered woredas in the system yet.',
        'register_first_woreda'   => 'Register First Woreda',
        'register_new_woreda'     => 'Register New Woreda',
        'zone'                    => 'Zone',
        'select_zone'             => 'Select Zone',
        'zone_help'               => 'Select the zone where this woreda belongs',
        'woreda_name'             => 'Woreda Name',
        'woreda_placeholder'      => 'Enter woreda name',
        'woreda_help'             => 'Enter the official name of the woreda',
        'phone'                   => 'Phone Number',
        'phone_placeholder'       => '+251 ...',
        'phone_help'              => 'Primary contact number for the woreda office',
        'woreda_officer'          => 'Woreda Officer',
        'select_officer'          => 'Select Woreda Officer',
        'officer_help'            => 'Assign a woreda officer to manage this woreda',
        'register_woreda'         => 'Register Woreda',
        'contact'                 => 'Contact',
        'active'                  => 'Active',
        'all_fields_required'     => 'All fields are required.',
        'woreda_already_exists'   => 'Woreda name already exists in this zone.',
        'success_message'         => 'Woreda registered successfully!',
        'error_message'           => 'Woreda registration failed. Please try again.',
    ],
    'am' => [
        'page_title'              => 'ወረዳ አስተዳደር - VERMS',
        'header_title'            => 'ወረዳ አስተዳደር',
        'header_subtitle'         => 'ወረዳዎችን በአስተዳደራዊ ዞኖች መመዝገብና መቆጣጠር',
        'add_new_woreda'          => 'አዲስ ወረዳ መዝግብ',
        'total_woredas'           => 'ጠቅላላ ወረዳዎች',
        'zones'                   => 'ዞኖች',
        'woreda_officers'         => 'ወረዳ ኃላፊዎች',
        'kebeles'                 => 'ቀበሌዎች',
        'registered_woredas'      => 'የተመዘገቡ ወረዳዎች',
        'no_woredas_found'        => 'ምንም ወረዳ አልተገኘም',
        'no_woredas_message'      => 'በስርዓቱ ውስጥ ገና ምንም ወረዳ አልተመዘገበም።',
        'register_first_woreda'   => 'የመጀመሪያውን ወረዳ መዝግብ',
        'register_new_woreda'     => 'አዲስ ወረዳ መዝግብ',
        'zone'                    => 'ዞን',
        'select_zone'             => 'ዞን ይምረጡ',
        'zone_help'               => 'ይህ ወረዳ የሚገኝበትን ዞን ይምረጡ',
        'woreda_name'             => 'የወረዳ ስም',
        'woreda_placeholder'      => 'የወረዳውን ስም ያስገቡ',
        'woreda_help'             => 'የወረዳውን ኦፊሴላዊ ስም ያስገቡ',
        'phone'                   => 'ስልክ ቁጥር',
        'phone_placeholder'       => '+251 ...',
        'phone_help'              => 'የወረዳ ቢሮ ዋና የእውቂያ ቁጥር',
        'woreda_officer'          => 'ወረዳ ኃላፊ',
        'select_officer'          => 'ወረዳ ኃላፊ ይምረጡ',
        'officer_help'            => 'ይህን ወረዳ የሚያስተዳድር ኃላፊ ይመድቡ',
        'register_woreda'         => 'ወረዳ መዝግብ',
        'contact'                 => 'እውቂያ',
        'active'                  => 'ንቁ',
        'all_fields_required'     => 'ሁሉም መስኮች ያስፈልጋሉ።',
        'woreda_already_exists'   => 'ይህ የወረዳ ስም በዚህ ዞን ውስጥ አስቀድሞ ተመዝግቧል።',
        'success_message'         => 'ወረዳ በተሳካ ሁኔታ ተመዝግቧል!',
        'error_message'           => 'ወረዳ መመዝገብ አልተሳካም። እባክዎ እንደገና ይሞክሩ።',
    ]
];

// Fetch zones
$zones = [];
$result = $conn->query("SELECT id, zone_name FROM zones ORDER BY zone_name ASC");
while ($row = $result->fetch_assoc()) {
    $zones[] = $row;
}

// Fetch woreda officers
$woreda_officers = [];
$result = $conn->query("SELECT id, fullname FROM users WHERE role = 'woreda' ORDER BY fullname ASC");
while ($row = $result->fetch_assoc()) {
    $woreda_officers[] = $row;
}

// Handle form submission
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_id           = $_POST['zone_id']           ?? '';
    $woreda_name       = trim($_POST['woreda_name'] ?? '');
    $phone             = trim($_POST['phone']       ?? '');
    $woreda_officer_id = $_POST['woreda_officer_id'] ?? '';

    if (!$zone_id || !$woreda_name || !$phone || !$woreda_officer_id) {
        $error = $texts[$lang]['all_fields_required'];
    } else {
        $stmt = $conn->prepare('SELECT id FROM woredas WHERE woreda_name = ? AND zone_id = ?');
        $stmt->bind_param('si', $woreda_name, $zone_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = $texts[$lang]['woreda_already_exists'];
        } else {
            $stmt = $conn->prepare('INSERT INTO woredas (zone_id, woreda_name, phone, woreda_officer_id) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('issi', $zone_id, $woreda_name, $phone, $woreda_officer_id);
            
            if ($stmt->execute()) {
                $success = $texts[$lang]['success_message'];
            } else {
                $error = $texts[$lang]['error_message'];
            }
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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        [lang="am"] body,
        [lang="am"] h1, [lang="am"] h5, [lang="am"] .btn,
        [lang="am"] .form-label, [lang="am"] .alert, [lang="am"] .card-header,
        [lang="am"] .stats-card .label, [lang="am"] .empty-state {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        /* Your original styles continue here... */
        .page-header { background: var(--gradient-primary); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: var(--shadow); }
        /* ... keep all your other CSS styles ... */

        .lang-switcher {
                 position: fixed;
                 top: 80px; 
                 right: 20px;
                 z-index: 1050;
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

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold">
                    <i class="fas fa-map me-3"></i><?= $texts[$lang]['header_title'] ?>
                </h1>
                <p class="lead mb-0"><?= $texts[$lang]['header_subtitle'] ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#woredaModal">
                    <i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['add_new_woreda'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $total_woredas  = $conn->query("SELECT COUNT(*) as total FROM woredas")->fetch_assoc()['total'] ?? 0;
        $total_zones    = $conn->query("SELECT COUNT(*) as total FROM zones")->fetch_assoc()['total'] ?? 0;
        $total_officers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'woreda'")->fetch_assoc()['total'] ?? 0;
        $total_kebeles  = $conn->query("SELECT COUNT(*) as total FROM kebeles")->fetch_assoc()['total'] ?? 0;
        ?>
        <div class="col-md-3">
            <div class="stats-card stats-total">
                <i class="fas fa-map"></i>
                <div class="number"><?= $total_woredas ?></div>
                <div class="label"><?= $texts[$lang]['total_woredas'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-zones">
                <i class="fas fa-map-marked-alt"></i>
                <div class="number"><?= $total_zones ?></div>
                <div class="label"><?= $texts[$lang]['zones'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-officers">
                <i class="fas fa-user-tie"></i>
                <div class="number"><?= $total_officers ?></div>
                <div class="label"><?= $texts[$lang]['woreda_officers'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-kebeles">
                <i class="fas fa-map-pin"></i>
                <div class="number"><?= $total_kebeles ?></div>
                <div class="label"><?= $texts[$lang]['kebeles'] ?></div>
            </div>
        </div>
    </div>

    <!-- Woreda Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= $texts[$lang]['registered_woredas'] ?></h5>
            <span class="badge bg-light text-dark"><?= $total_woredas ?> <?= $lang === 'am' ? 'ወረዳዎች' : 'woredas' ?></span>
        </div>
        <div class="card-body">
            <?php
            $woreda_query = $conn->query("SELECT w.*, z.zone_name, u.fullname AS officer_name 
                FROM woredas w
                JOIN zones z ON w.zone_id = z.id
                JOIN users u ON w.woreda_officer_id = u.id
                ORDER BY w.id DESC");
            ?>
            <?php if ($woreda_query->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $texts[$lang]['woreda_name'] ?></th>
                                <th><?= $texts[$lang]['contact'] ?></th>
                                <th><?= $texts[$lang]['zone'] ?></th>
                                <th><?= $texts[$lang]['woreda_officer'] ?></th>
                                <th><?= $lang === 'am' ? 'ሁኔታ' : 'Status' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $woreda_query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['woreda_name']) ?></div>
                                        <small class="text-muted">ID: <?= $row['id'] ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <?= htmlspecialchars($row['phone']) ?>
                                    </td>
                                    <td>
                                        <span class="location-badge">
                                            <i class="fas fa-map-marked-alt me-1"></i>
                                            <?= htmlspecialchars($row['zone_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-sm">
                                                <?= strtoupper(substr($row['officer_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($row['officer_name'] ?? '—') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i><?= $texts[$lang]['active'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-map"></i>
                    <h4><?= $texts[$lang]['no_woredas_found'] ?></h4>
                    <p><?= $texts[$lang]['no_woredas_message'] ?></p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#woredaModal">
                        <i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['register_first_woreda'] ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="woredaModal" tabindex="-1" aria-labelledby="woredaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['register_new_woreda'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['zone'] ?></label>
                        <select name="zone_id" class="form-select" required>
                            <option value=""><?= $texts[$lang]['select_zone'] ?></option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['zone_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?= $texts[$lang]['zone_help'] ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['woreda_name'] ?></label>
                        <input type="text" name="woreda_name" class="form-control" required 
                               placeholder="<?= $texts[$lang]['woreda_placeholder'] ?>">
                        <div class="form-text"><?= $texts[$lang]['woreda_help'] ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['phone'] ?></label>
                        <input type="text" name="phone" class="form-control" required 
                               placeholder="<?= $texts[$lang]['phone_placeholder'] ?>">
                        <div class="form-text"><?= $texts[$lang]['phone_help'] ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['woreda_officer'] ?></label>
                        <select name="woreda_officer_id" class="form-select" required>
                            <option value=""><?= $texts[$lang]['select_officer'] ?></option>
                            <?php foreach ($woreda_officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>"><?= htmlspecialchars($officer['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?= $texts[$lang]['officer_help'] ?></div>
                    </div>
                    <div class="col-12 d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i><?= $texts[$lang]['register_woreda'] ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fade-in animation for table rows
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tbody tr').forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
        row.classList.add('fade-in');
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 5000);
    });
});
</script>
</body>
</html>