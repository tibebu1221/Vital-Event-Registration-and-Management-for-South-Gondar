<?php
// admin/view_citizens.php
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
        'page_title'              => 'Citizen Management - VERMS',
        'header_title'            => 'Citizen Management',
        'header_subtitle'         => 'View and manage all registered citizens in the system',
        'export_data'             => 'Export Data',
        'total_citizens'          => 'Total Citizens',
        'registered_today'        => 'Registered Today',
        'active_citizens'         => 'Active Citizens',
        'filter_citizens'         => 'Filter Citizens',
        'search_placeholder'      => 'Search citizens...',
        'registered_citizens'     => 'Registered Citizens',
        'no_citizens_found'       => 'No Citizens Found',
        'no_citizens_message'     => 'There are no registered citizens in the system yet.',
        'citizen'                 => 'Citizen',
        'username'                => 'Username',
        'email'                   => 'Email',
        'phone'                   => 'Phone',
        'registered_at'           => 'Registered At',
        'actions'                 => 'Actions',
        'view'                    => 'View',
        'citizen_details'         => 'Citizen Details',
        'registered'              => 'Registered',
        'email_label'             => 'Email:',
        'phone_label'             => 'Phone:',
        'registered_label'        => 'Registered:',
    ],
    'am' => [
        'page_title'              => 'ዜጎች አስተዳደር - VERMS',
        'header_title'            => 'ዜጎች አስተዳደር',
        'header_subtitle'         => 'በስርዓቱ ውስጥ የተመዘገቡ ሁሉንም ዜጎች መመልከትና መቆጣጠር',
        'export_data'             => 'ውሂብ ወደ ውጭ ላክ',
        'total_citizens'          => 'ጠቅላላ ዜጎች',
        'registered_today'        => 'ዛሬ የተመዘገቡ',
        'active_citizens'         => 'ንቁ ዜጎች',
        'filter_citizens'         => 'ዜጎችን አጣራ',
        'search_placeholder'      => 'ዜጎችን ፈልግ...',
        'registered_citizens'     => 'የተመዘገቡ ዜጎች',
        'no_citizens_found'       => 'ምንም ዜጎች አልተገኙም',
        'no_citizens_message'     => 'በስርዓቱ ውስጥ ገና ምንም ዜጎች አልተመዘገቡም።',
        'citizen'                 => 'ዜጋ',
        'username'                => 'የተጠቃሚ ስም',
        'email'                   => 'ኢሜይል',
        'phone'                   => 'ስልክ',
        'registered_at'           => 'የተመዘገበበት ጊዜ',
        'actions'                 => 'እርምጃዎች',
        'view'                    => 'ይመልከቱ',
        'citizen_details'         => 'የዜጋ ዝርዝሮች',
        'registered'              => 'የተመዘገበው',
        'email_label'             => 'ኢሜይል፡',
        'phone_label'             => 'ስልክ፡',
        'registered_label'        => 'የተመዘገበው፡',
    ]
];

// Fetch all citizens
$citizens = [];
$result = $conn->query("SELECT id, fullname, username, email, phone, created_at 
                        FROM users 
                        WHERE role = 'citizen' 
                        ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $citizens[] = $row;
}

// Get statistics
$total_citizens = count($citizens);
$today_registered = 0;
$current_date = date('Y-m-d');
foreach ($citizens as $citizen) {
    if (date('Y-m-d', strtotime($citizen['created_at'])) === $current_date) {
        $today_registered++;
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

        /* Your original styles remain here... */
        .page-header { /* ... */ }
        .lang-switcher {
                 position: fixed;
                 top: 80px; 
                 right: 20px;
                 z-index: 1050;
              }
        /* ... keep all your other CSS ... */
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
                    <i class="fas fa-users me-3"></i><?= $texts[$lang]['header_title'] ?>
                </h1>
                <p class="lead mb-0"><?= $texts[$lang]['header_subtitle'] ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-lg" onclick="exportCitizens()">
                    <i class="fas fa-download me-2"></i><?= $texts[$lang]['export_data'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-card stats-total">
                <i class="fas fa-users"></i>
                <div class="number"><?= $total_citizens ?></div>
                <div class="label"><?= $texts[$lang]['total_citizens'] ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card stats-today">
                <i class="fas fa-user-plus"></i>
                <div class="number"><?= $today_registered ?></div>
                <div class="label"><?= $texts[$lang]['registered_today'] ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card stats-active">
                <i class="fas fa-user-check"></i>
                <div class="number"><?= $total_citizens ?></div>
                <div class="label"><?= $texts[$lang]['active_citizens'] ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-filter me-2"></i><?= $texts[$lang]['filter_citizens'] ?>
                </h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" 
                           placeholder="<?= $texts[$lang]['search_placeholder'] ?>">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Citizens Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= $texts[$lang]['registered_citizens'] ?></h5>
            <span class="badge bg-light text-dark"><?= $total_citizens ?> <?= $lang === 'am' ? 'ዜጎች' : 'citizens' ?></span>
        </div>
        <div class="card-body">
            <?php if (!empty($citizens)): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="citizensTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $texts[$lang]['citizen'] ?></th>
                                <th><?= $texts[$lang]['username'] ?></th>
                                <th><?= $texts[$lang]['email'] ?></th>
                                <th><?= $texts[$lang]['phone'] ?></th>
                                <th><?= $texts[$lang]['registered_at'] ?></th>
                                <th style="width:120px;"><?= $texts[$lang]['actions'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citizens as $i => $citizen): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($citizen['fullname'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($citizen['fullname']) ?></div>
                                                <small class="text-muted">ID: <?= $citizen['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($citizen['username']) ?></td>
                                    <td><?= htmlspecialchars($citizen['email']) ?></td>
                                    <td><?= htmlspecialchars($citizen['phone'] ?? ($lang === 'am' ? 'የለም' : 'N/A')) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($citizen['created_at'])) ?>
                                            <br>
                                            <span class="text-muted"><?= date('g:i A', strtotime($citizen['created_at'])) ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm view-detail-btn"
                                                data-fullname="<?= htmlspecialchars($citizen['fullname']) ?>"
                                                data-username="<?= htmlspecialchars($citizen['username']) ?>"
                                                data-email="<?= htmlspecialchars($citizen['email']) ?>"
                                                data-phone="<?= htmlspecialchars($citizen['phone'] ?? ($lang === 'am' ? 'የለም' : 'N/A')) ?>"
                                                data-registered="<?= date('F j, Y \a\t g:i A', strtotime($citizen['created_at'])) ?>"
                                        >
                                            <i class="fas fa-eye me-1"></i><?= $texts[$lang]['view'] ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h4><?= $texts[$lang]['no_citizens_found'] ?></h4>
                    <p><?= $texts[$lang]['no_citizens_message'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Citizen Detail Modal -->
<div class="modal fade" id="citizenDetailModal" tabindex="-1" aria-labelledby="citizenDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i><?= $texts[$lang]['citizen_details'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="user-avatar mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;" id="modalAvatar">
                        U
                    </div>
                    <h5 id="detailFullname" class="mb-1"></h5>
                    <p class="text-muted mb-0" id="detailUsername"></p>
                </div>
                <div class="list-group">
                    <div class="list-group-item">
                        <strong><i class="fas fa-envelope me-2"></i><?= $texts[$lang]['email_label'] ?></strong>
                        <span id="detailEmail"></span>
                    </div>
                    <div class="list-group-item">
                        <strong><i class="fas fa-phone me-2"></i><?= $texts[$lang]['phone_label'] ?></strong>
                        <span id="detailPhone"></span>
                    </div>
                    <div class="list-group-item">
                        <strong><i class="fas fa-calendar-alt me-2"></i><?= $texts[$lang]['registered_label'] ?></strong>
                        <span id="detailRegistered"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// View Detail button handler
document.querySelectorAll('.view-detail-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('detailFullname').textContent = this.dataset.fullname;
        document.getElementById('detailUsername').textContent = this.dataset.username;
        document.getElementById('detailEmail').textContent = this.dataset.email;
        document.getElementById('detailPhone').textContent = this.dataset.phone;
        document.getElementById('detailRegistered').textContent = this.dataset.registered;
        
        document.getElementById('modalAvatar').textContent = 
            this.dataset.fullname.charAt(0).toUpperCase();
        
        var modal = new bootstrap.Modal(document.getElementById('citizenDetailModal'));
        modal.show();
    });
});

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('#citizensTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Export functionality
function exportCitizens() {
    const citizens = <?= json_encode($citizens) ?>;
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "<?= $lang === 'am' ? 'ሙሉ ስም,የተጠቃሚ ስም,ኢሜይል,ስልክ,የተመዘገበበት ጊዜ' : 'Full Name,Username,Email,Phone,Registered At' ?>\n";
    
    citizens.forEach(citizen => {
        csvContent += `"${citizen.fullname.replace(/"/g,'""')}","${citizen.username}","${citizen.email}","${citizen.phone || 'N/A'}","${citizen.created_at}"\n`;
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "citizens_data_<?= $lang ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Fade-in animation
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#citizensTable tbody tr').forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
        row.classList.add('fade-in');
    });
});

// Fade-in animation CSS
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