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
        'page_title' => 'Zone Management - VERMS',
        'page_header' => 'Zone Management',
        'page_subtitle' => 'Register and manage administrative zones across the region',
        'add_new_zone' => 'Add New Zone',
        'register_first_zone' => 'Register First Zone',
        
        // Statistics
        'total_zones' => 'Total Zones',
        'woredas' => 'Woredas',
        'zone_officers' => 'Zone Officers',
        'kebeles' => 'Kebeles',
        
        // Table
        'registered_zones' => 'Registered Zones',
        'number' => '#',
        'zone_name' => 'Zone Name',
        'contact' => 'Contact',
        'assigned_officer' => 'Assigned Officer',
        'status' => 'Status',
        'active' => 'Active',
        'no_zones_found' => 'No Zones Found',
        'no_zones_message' => 'There are no registered zones in the system yet.',
        
        // Modal
        'register_new_zone' => 'Register New Zone',
        'zone_name_label' => 'Zone Name',
        'phone_number' => 'Phone Number',
        'zone_officer' => 'Zone Officer',
        'select_zone_officer' => 'Select Zone Officer',
        'register_zone' => 'Register Zone',
        
        // Placeholders and help text
        'zone_placeholder' => 'Enter zone name',
        'phone_placeholder' => '+251 ...',
        'zone_help' => 'Enter the official name of the administrative zone',
        'phone_help' => 'Primary contact number for the zone office',
        'officer_help' => 'Assign a zone officer to manage this administrative zone',
        
        // Messages
        'all_fields_required' => 'All fields are required.',
        'zone_exists' => 'Zone name already exists.',
        'zone_registered_success' => 'Zone registered successfully!',
        'zone_registration_failed' => 'Zone registration failed. Please try again.',
        'id' => 'ID:',
        
        // Search
        'search_zones' => 'Search zones...'
    ],
    'am' => [
        // Page title and headers
        'page_title' => 'ዞን አስተዳደር - ቪ.ኢ.አር.ኤም.ኤስ',
        'page_header' => 'ዞን አስተዳደር',
        'page_subtitle' => 'በክልሉ ላይ ያሉ አስተዳዳሪ ዞኖችን ይመዝግቡ እና ያስተዳድሩ',
        'add_new_zone' => 'አዲስ ዞን ጨምር',
        'register_first_zone' => 'የመጀመሪያ ዞን ይመዝግቡ',
        
        // Statistics
        'total_zones' => 'ጠቅላላ ዞኖች',
        'woredas' => 'ወረዳዎች',
        'zone_officers' => 'የዞን አሰልጣኞች',
        'kebeles' => 'ቀበሌዎች',
        
        // Table
        'registered_zones' => 'የተመዘገቡ ዞኖች',
        'number' => '#',
        'zone_name' => 'የዞን ስም',
        'contact' => 'መገናኛ',
        'assigned_officer' => 'የተመደበ አሰልጣኝ',
        'status' => 'ሁኔታ',
        'active' => 'ንቁ',
        'no_zones_found' => 'ዞኖች አልተገኙም',
        'no_zones_message' => 'በስርዓቱ ውስጥ ገና የተመዘገቡ ዞኖች የሉም።',
        
        // Modal
        'register_new_zone' => 'አዲስ ዞን ይመዝግቡ',
        'zone_name_label' => 'የዞን ስም',
        'phone_number' => 'ስልክ ቁጥር',
        'zone_officer' => 'የዞን አሰልጣኝ',
        'select_zone_officer' => 'የዞን አሰልጣኝ ይምረጡ',
        'register_zone' => 'ዞን ይመዝግቡ',
        
        // Placeholders and help text
        'zone_placeholder' => 'የዞን ስም ያስገቡ',
        'phone_placeholder' => '+251 ...',
        'zone_help' => 'የአስተዳዳሪ ዞኑን ኦፊሴላዊ ስም ያስገቡ',
        'phone_help' => 'ለዞን ቢሮ ዋና የስልክ ቁጥር',
        'officer_help' => 'ይህን አስተዳዳሪ ዞን ለማስተዳደር የዞን አሰልጣኝ ይመድቡ',
        
        // Messages
        'all_fields_required' => 'ሁሉም መስኮች አስፈላጊ ናቸው።',
        'zone_exists' => 'የዞን ስም ቀደም ብሎ ይገኛል።',
        'zone_registered_success' => 'ዞን በተሳካ ሁኔታ ተመዝግቧል!',
        'zone_registration_failed' => 'ዞን ምዝገባ አልተሳካም። እባክዎ እንደገና ይሞክሩ።',
        'id' => 'መለያ:',
        
        // Search
        'search_zones' => 'ዞኖችን ይፈልጉ...'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

require_once '../includes/db_connection.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Fetch all zone officers for dropdown
$zone_officers = [];
$result = $conn->query("SELECT id, fullname FROM users WHERE role = 'zone'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $zone_officers[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_name = trim($_POST['zone_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $zone_officer_id = $_POST['zone_officer_id'] ?? '';

    if (!$zone_name || !$phone || !$zone_officer_id) {
        $error = t('all_fields_required');
    } else {
        // Check if zone name already exists
        $stmt = $conn->prepare('SELECT id FROM zones WHERE zone_name = ?');
        $stmt->bind_param('s', $zone_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = t('zone_exists');
        } else {
            // Insert new zone
            $stmt = $conn->prepare('INSERT INTO zones (zone_name, phone, zone_officer_id) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $zone_name, $phone, $zone_officer_id);
            if ($stmt->execute()) {
                $success = t('zone_registered_success');
            } else {
                $error = t('zone_registration_failed');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        <?php if($lang === 'am'): ?>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: justify;
        }
        
        .page-header h1,
        .page-header p,
        .card-header h5,
        .stats-card .number,
        .stats-card .label,
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
        .search-input {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        <?php endif; ?>
        
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--shadow);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.2rem 1.5rem;
            font-weight: 600;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: var(--gradient-secondary);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #229954);
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .stats-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }

        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .stats-card .number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-card .label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .stats-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stats-woredas {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
        }

        .stats-officers {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        .stats-kebeles {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
        }

        .user-avatar-sm {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .zone-badge {
            background: var(--light-color);
            color: var(--dark-color);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
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
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .stats-card {
                padding: 1rem;
            }
            
            .stats-card i {
                font-size: 2rem;
            }
            
            .stats-card .number {
                font-size: 1.5rem;
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
                <h1 class="display-5 fw-bold"><i class="fas fa-map-marked-alt me-3"></i><?php echo t('page_header'); ?></h1>
                <p class="lead mb-0"><?php echo t('page_subtitle'); ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#zoneModal">
                    <i class="fas fa-plus-circle me-2"></i><?php echo t('add_new_zone'); ?>
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
        $total_zones = $conn->query("SELECT COUNT(*) as total FROM zones")->fetch_assoc()['total'];
        $total_woredas = $conn->query("SELECT COUNT(*) as total FROM woredas")->fetch_assoc()['total'];
        $total_officers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'zone'")->fetch_assoc()['total'];
        $total_kebeles = $conn->query("SELECT COUNT(*) as total FROM kebeles")->fetch_assoc()['total'];
        ?>
        <div class="col-md-3">
            <div class="stats-card stats-total">
                <i class="fas fa-map-marked-alt"></i>
                <div class="number"><?= $total_zones ?></div>
                <div class="label"><?php echo t('total_zones'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-woredas">
                <i class="fas fa-map"></i>
                <div class="number"><?= $total_woredas ?></div>
                <div class="label"><?php echo t('woredas'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-officers">
                <i class="fas fa-user-tie"></i>
                <div class="number"><?= $total_officers ?></div>
                <div class="label"><?php echo t('zone_officers'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-kebeles">
                <i class="fas fa-map-pin"></i>
                <div class="number"><?= $total_kebeles ?></div>
                <div class="label"><?php echo t('kebeles'); ?></div>
            </div>
        </div>
    </div>

    <!-- Zone Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?php echo t('registered_zones'); ?></h5>
            <span class="badge bg-light text-dark"><?= $total_zones ?> <?php echo strtolower(t('total_zones')); ?></span>
        </div>
        <div class="card-body">
            <?php
            $zone_query = $conn->query("SELECT z.*, u.fullname AS officer_name FROM zones z 
                JOIN users u ON z.zone_officer_id = u.id 
                ORDER BY z.id DESC");
            ?>
            <?php if ($zone_query->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('number'); ?></th>
                                <th><?php echo t('zone_name'); ?></th>
                                <th><?php echo t('contact'); ?></th>
                                <th><?php echo t('assigned_officer'); ?></th>
                                <th><?php echo t('status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $zone_query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['zone_name']) ?></div>
                                        <small class="text-muted"><?php echo t('id'); ?> <?= $row['id'] ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <?= htmlspecialchars($row['phone']) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-sm">
                                                <?= strtoupper(substr($row['officer_name'], 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($row['officer_name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i><?php echo t('active'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4><?php echo t('no_zones_found'); ?></h4>
                    <p><?php echo t('no_zones_message'); ?></p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#zoneModal">
                        <i class="fas fa-plus-circle me-2"></i><?php echo t('register_first_zone'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for Zone Registration -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i><?php echo t('register_new_zone'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="" method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label"><?php echo t('zone_name_label'); ?></label>
                <input type="text" name="zone_name" class="form-control" required placeholder="<?php echo t('zone_placeholder'); ?>">
                <div class="form-text"><?php echo t('zone_help'); ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?php echo t('phone_number'); ?></label>
                <input type="text" name="phone" class="form-control" required placeholder="<?php echo t('phone_placeholder'); ?>">
                <div class="form-text"><?php echo t('phone_help'); ?></div>
            </div>
            <div class="col-12">
                <label class="form-label"><?php echo t('zone_officer'); ?></label>
                <select name="zone_officer_id" class="form-select" required>
                    <option value=""><?php echo t('select_zone_officer'); ?></option>
                    <?php foreach ($zone_officers as $officer): ?>
                        <option value="<?= $officer['id'] ?>"><?= htmlspecialchars($officer['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?php echo t('officer_help'); ?></div>
            </div>
            <div class="col-12 d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i><?php echo t('register_zone'); ?>
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Language switcher
function changeLanguage(lang) {
    window.location.href = '?lang=' + lang;
}

// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation to table rows
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
        row.classList.add('fade-in');
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Add search functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '<?php echo t("search_zones"); ?>';
    searchInput.className = 'form-control mb-3 search-input';
    searchInput.style.maxWidth = '300px';
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Add search input to card header
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader) {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'd-flex justify-content-end';
        searchContainer.appendChild(searchInput);
        cardHeader.appendChild(searchContainer);
    }
});

// Add CSS for fade-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>