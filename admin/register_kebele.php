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
    // Clean URL (remove ?lang= parameter)
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation array
$texts = [
    'en' => [
        'page_title'             => 'Kebele Management - VERMS',
        'header_title'           => 'Kebele Management',
        'header_subtitle'        => 'Register and manage kebeles across administrative levels',
        'add_new_kebele'         => 'Add New Kebele',
        'total_kebeles'          => 'Total Kebeles',
        'zones'                  => 'Zones',
        'woredas'                => 'Woredas',
        'kebele_officers'        => 'Kebele Officers',
        'registered_kebeles'     => 'Registered Kebeles',
        'no_kebeles_found'       => 'No Kebeles Found',
        'no_kebeles_message'     => 'There are no registered kebeles in the system yet.',
        'register_first_kebele'  => 'Register First Kebele',
        'register_new_kebele'    => 'Register New Kebele',
        'zone'                   => 'Zone',
        'select_zone'            => 'Select Zone',
        'woreda'                 => 'Woreda',
        'select_woreda'          => 'Select Woreda',
        'kebele_name'            => 'Kebele Name/Number',
        'kebele_placeholder'     => 'e.g. 01, 02, 03',
        'kebele_help'            => 'Enter the kebele number or name',
        'phone'                  => 'Phone Number',
        'phone_placeholder'      => '+251 ...',
        'phone_help'             => 'Primary contact number for the kebele',
        'assign_officer'         => 'Assign Kebele Officer',
        'select_officer'         => 'Select Kebele Officer',
        'officer_help'           => 'Select an officer to manage this kebele',
        'register_kebele'        => 'Register Kebele',
        'active'                 => 'Active',
        'contact'                => 'Contact',
        'location'               => 'Location',
        'status'                 => 'Status',
        'all_fields_required'    => 'All fields are required.',
        'kebele_already_exists'  => 'Kebele already exists in this woreda.',
        'success_message'        => 'Kebele registered successfully!',
        'error_db'               => 'Error registering kebele: ',
    ],
    'am' => [
        'page_title'             => 'ቀበሌ አስተዳደር - VERMS',
        'header_title'           => 'ቀበሌ አስተዳደር',
        'header_subtitle'        => 'ቀበሌዎችን በአስተዳደራዊ ደረጃዎች መመዝገብና መቆጣጠር',
        'add_new_kebele'         => 'አዲስ ቀበሌ መዝግብ',
        'total_kebeles'          => 'ጠቅላላ ቀበሌዎች',
        'zones'                  => 'ዞኖች',
        'woredas'                => 'ወረዳዎች',
        'kebele_officers'        => 'ቀበሌ ኃላፊዎች',
        'registered_kebeles'     => 'የተመዘገቡ ቀበሌዎች',
        'no_kebeles_found'       => 'ምንም ቀበሌ አልተገኘም',
        'no_kebeles_message'     => 'በስርዓቱ ውስጥ ገና ምንም ቀበሌ አልተመዘገበም።',
        'register_first_kebele'  => 'የመጀመሪያውን ቀበሌ መዝግብ',
        'register_new_kebele'    => 'አዲስ ቀበሌ መዝግብ',
        'zone'                   => 'ዞን',
        'select_zone'            => 'ዞን ይምረጡ',
        'woreda'                 => 'ወረዳ',
        'select_woreda'          => 'ወረዳ ይምረጡ',
        'kebele_name'            => 'የቀበሌ ስም / ቁጥር',
        'kebele_placeholder'     => 'ለምሳሌ፡ 01፣ 02፣ 03',
        'kebele_help'            => 'የቀበሌውን ቁጥር ወይም ስም ያስገቡ',
        'phone'                  => 'ስልክ ቁጥር',
        'phone_placeholder'      => '+251 ...',
        'phone_help'             => 'የቀበሌው ዋና የእውቂያ ቁጥር',
        'assign_officer'         => 'የቀበሌ ኃላፊ መመደብ',
        'select_officer'         => 'የቀበሌ ኃላፊ ይምረጡ',
        'officer_help'           => 'ይህን ቀበሌ የሚያስተዳድር ኃላፊ ይምረጡ',
        'register_kebele'        => 'ቀበሌ መዝግብ',
        'active'                 => 'ንቁ',
        'contact'                => 'እውቂያ',
        'location'               => 'ቦታ',
        'status'                 => 'ሁኔታ',
        'all_fields_required'    => 'ሁሉም መስኮች ያስፈልጋሉ።',
        'kebele_already_exists'  => 'ይህ ቀበሌ በዚህ ወረዳ ውስጥ አስቀድሞ ተመዝግቧል።',
        'success_message'        => 'ቀበሌ በተሳካ ሁኔታ ተመዝግቧል!',
        'error_db'               => 'ቀበሌ በመመዝገብ ላይ ስህተት፡ ',
    ]
];

// Fetch zones, woredas, officers
$zones = [];
$zone_result = $conn->query("SELECT id, zone_name FROM zones ORDER BY zone_name ASC");
while ($row = $zone_result->fetch_assoc()) $zones[] = $row;

$woredas = [];
$woreda_result = $conn->query("SELECT id, woreda_name, zone_id FROM woredas ORDER BY woreda_name ASC");
while ($row = $woreda_result->fetch_assoc()) $woredas[] = $row;

$officers = [];
$officer_result = $conn->query("SELECT id, fullname, username FROM users WHERE role = 'kebele' ORDER BY fullname ASC");
while ($row = $officer_result->fetch_assoc()) $officers[] = $row;

// Handle form submission
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_id     = $_POST['zone_id']     ?? '';
    $woreda_id   = $_POST['woreda_id']   ?? '';
    $kebele_name = $_POST['kebele_name'] ?? '';
    $phone       = $_POST['phone']       ?? '';
    $officer_id  = $_POST['officer_id']  ?? '';

    if (!$zone_id || !$woreda_id || !$kebele_name || !$phone || !$officer_id) {
        $error = $texts[$lang]['all_fields_required'];
    } else {
        $stmt = $conn->prepare("SELECT id FROM kebeles WHERE zone_id = ? AND woreda_id = ? AND kebele_name = ?");
        $stmt->bind_param('iis', $zone_id, $woreda_id, $kebele_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = $texts[$lang]['kebele_already_exists'];
        } else {
            $stmt = $conn->prepare("INSERT INTO kebeles (zone_id, woreda_id, kebele_name, phone, kebele_officer_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('iissi', $zone_id, $woreda_id, $kebele_name, $phone, $officer_id);
            
            if ($stmt->execute()) {
                $success = $texts[$lang]['success_message'];
            } else {
                $error = $texts[$lang]['error_db'] . $conn->error;
            }
        }
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
    
    <!-- Better Amharic font support -->
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

        /* Your original styles remain here */
        .page-header { /* ... */ }
        .card { /* ... */ }
        /* ... keep all your original CSS ... */

        .lang-switcher {
            position: fixed;
            top: 1rem;
            right: 1.5rem;
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
                    <i class="fas fa-map-pin me-3"></i><?= $texts[$lang]['header_title'] ?>
                </h1>
                <p class="lead mb-0"><?= $texts[$lang]['header_subtitle'] ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#kebeleModal">
                    <i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['add_new_kebele'] ?>
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
        $total_kebeles = $conn->query("SELECT COUNT(*) as total FROM kebeles")->fetch_assoc()['total'] ?? 0;
        $total_zones   = $conn->query("SELECT COUNT(*) as total FROM zones")->fetch_assoc()['total'] ?? 0;
        $total_woredas = $conn->query("SELECT COUNT(*) as total FROM woredas")->fetch_assoc()['total'] ?? 0;
        $total_officers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'kebele'")->fetch_assoc()['total'] ?? 0;
        ?>
        <div class="col-md-3">
            <div class="stats-card stats-total">
                <i class="fas fa-map-pin"></i>
                <div class="number"><?= $total_kebeles ?></div>
                <div class="label"><?= $texts[$lang]['total_kebeles'] ?></div>
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
            <div class="stats-card stats-woredas">
                <i class="fas fa-map"></i>
                <div class="number"><?= $total_woredas ?></div>
                <div class="label"><?= $texts[$lang]['woredas'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-officers">
                <i class="fas fa-user-tie"></i>
                <div class="number"><?= $total_officers ?></div>
                <div class="label"><?= $texts[$lang]['kebele_officers'] ?></div>
            </div>
        </div>
    </div>

    <!-- Kebele Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= $texts[$lang]['registered_kebeles'] ?></h5>
            <span class="badge bg-light text-dark"><?= $total_kebeles ?> <?= $lang === 'am' ? 'ቀበሌዎች' : 'kebeles' ?></span>
        </div>
        <div class="card-body">
            <?php
            $kebele_query = $conn->query("SELECT k.*, z.zone_name, w.woreda_name, u.fullname AS officer_name 
                FROM kebeles k
                JOIN zones z ON k.zone_id = z.id
                JOIN woredas w ON k.woreda_id = w.id
                JOIN users u ON k.kebele_officer_id = u.id
                ORDER BY k.id DESC");
            ?>
            <?php if ($kebele_query->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $texts[$lang]['kebele_name'] ?></th>
                                <th><?= $texts[$lang]['contact'] ?></th>
                                <th><?= $texts[$lang]['location'] ?></th>
                                <th><?= $texts[$lang]['assign_officer'] ?></th>
                                <th><?= $texts[$lang]['status'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $kebele_query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['kebele_name']) ?></div>
                                        <small class="text-muted">ID: <?= $row['id'] ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <?= htmlspecialchars($row['phone']) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="location-badge">
                                                <i class="fas fa-map-marked-alt me-1"></i>
                                                <?= htmlspecialchars($row['zone_name']) ?>
                                            </span>
                                            <span class="location-badge">
                                                <i class="fas fa-map me-1"></i>
                                                <?= htmlspecialchars($row['woreda_name']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
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
                    <i class="fas fa-map-pin"></i>
                    <h4><?= $texts[$lang]['no_kebeles_found'] ?></h4>
                    <p><?= $texts[$lang]['no_kebeles_message'] ?></p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#kebeleModal">
                        <i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['register_first_kebele'] ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="kebeleModal" tabindex="-1" aria-labelledby="kebeleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['register_new_kebele'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['zone'] ?></label>
                        <select name="zone_id" id="zone_id" class="form-select" required>
                            <option value=""><?= $texts[$lang]['select_zone'] ?></option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['zone_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['woreda'] ?></label>
                        <select name="woreda_id" id="woreda_id" class="form-select" required>
                            <option value=""><?= $texts[$lang]['select_woreda'] ?></option>
                            <?php foreach ($woredas as $woreda): ?>
                                <option value="<?= $woreda['id'] ?>" data-zone="<?= $woreda['zone_id'] ?>">
                                    <?= htmlspecialchars($woreda['woreda_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['kebele_name'] ?></label>
                        <input type="text" name="kebele_name" class="form-control" required 
                               placeholder="<?= $texts[$lang]['kebele_placeholder'] ?>">
                        <div class="form-text"><?= $texts[$lang]['kebele_help'] ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $texts[$lang]['phone'] ?></label>
                        <input type="text" name="phone" class="form-control" required 
                               placeholder="<?= $texts[$lang]['phone_placeholder'] ?>">
                        <div class="form-text"><?= $texts[$lang]['phone_help'] ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= $texts[$lang]['assign_officer'] ?></label>
                        <select name="officer_id" class="form-select" required>
                            <option value=""><?= $texts[$lang]['select_officer'] ?></option>
                            <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>">
                                    <?= htmlspecialchars($officer['fullname']) ?> (<?= htmlspecialchars($officer['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?= $texts[$lang]['officer_help'] ?></div>
                    </div>
                    <div class="col-12 d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i><?= $texts[$lang]['register_kebele'] ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter woredas by selected zone
document.getElementById('zone_id')?.addEventListener('change', function() {
    const zoneId = this.value;
    const woredaSelect = document.getElementById('woreda_id');
    
    if (!woredaSelect) return;

    Array.from(woredaSelect.options).forEach(option => {
        if (!option.value) return;
        option.style.display = (!zoneId || option.getAttribute('data-zone') === zoneId) ? '' : 'none';
    });
    
    woredaSelect.value = '';
});
</script>
</body>
</html>