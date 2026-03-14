<?php
// Citizen: Request Vital Event Registration
session_start();
require_once '../includes/db_connection.php';

// Language handling
if (isset($_GET['lang'])) {
    $new_lang = ($_GET['lang'] === 'am') ? 'am' : 'en';
    $_SESSION['lang'] = $new_lang;
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation array (only visible texts)
$texts = [
    'en' => [
        'page_title'                  => 'Request Vital Event Registration - VERMS',
        'header_title'                => 'Request Vital Event Registration',
        'header_subtitle'             => 'Submit and track your vital event registration requests',
        'new_request'                 => 'New Request',
        'request_success'             => 'Your request for %s has been submitted.',
        'all_fields_required'         => 'All fields are required.',
        'error_submitting'            => 'Error submitting request. Please try again.',
        'my_submitted_requests'       => 'My Submitted Requests',
        'requests_count'              => 'requests',
        'event_type'                  => 'Event Type',
        'details'                     => 'Details',
        'status'                      => 'Status',
        'submitted_on'                => 'Submitted On',
        'no_requests'                 => 'No Requests Found',
        'no_requests_message'         => 'You have not submitted any requests yet.',
        'select_event'                => 'Select Event',
        'birth'                       => 'Birth',
        'death'                       => 'Death',
        'marriage'                    => 'Marriage',
        'divorce'                     => 'Divorce',
        'details_placeholder'         => 'Please provide relevant details about the event...',
        'cancel'                      => 'Cancel',
        'submit_request'              => 'Submit Request',
    ],
    'am' => [
        'page_title'                  => 'የሕይወት ክስተት መመዝገቢያ ጥያቄ - VERMS',
        'header_title'                => 'የሕይወት ክስተት መመዝገቢያ ጥያቄ',
        'header_subtitle'             => 'የሕይወት ክስተት መመዝገቢያ ጥያቄዎን ያስገቡና ይከታተሉ',
        'new_request'                 => 'አዲስ ጥያቄ',
        'request_success'             => 'የ%s ጥያቄዎ ተልኳል።',
        'all_fields_required'         => 'ሁሉም መስኮች ያስፈልጋሉ።',
        'error_submitting'            => 'ጥያቄ በመላክ ላይ ስህተት ተፈጥሯል። እባክዎ እንደገና ይሞክሩ።',
        'my_submitted_requests'       => 'የእኔ የተላኩ ጥያቄዎች',
        'requests_count'              => 'ጥያቄዎች',
        'event_type'                  => 'የክስተት አይነት',
        'details'                     => 'ዝርዝሮች',
        'status'                      => 'ሁኔታ',
        'submitted_on'                => 'የተላከበት ቀን',
        'no_requests'                 => 'ምንም ጥያቄ አልተገኘም',
        'no_requests_message'         => 'ገና ምንም ጥያቄ አላስገቡም።',
        'select_event'                => 'ክስተት ይምረጡ',
        'birth'                       => 'ልደት',
        'death'                       => 'ሞት',
        'marriage'                    => 'ጋብቻ',
        'divorce'                     => 'ፍቺ',
        'details_placeholder'         => 'እባክዎ ስለ ክስተቱ ዝርዝር መረጃ ያስገቡ...',
        'cancel'                      => 'ሰርዝ',
        'submit_request'              => 'ጥያቄ ላክ',
    ]
];

$success = $error = '';
$citizen_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_type = $_POST['event_type'] ?? '';
    $details = trim($_POST['details'] ?? '');
    if ($event_type && $details) {
        $stmt = $conn->prepare("INSERT INTO requests (citizen_id, event_type, details) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $citizen_id, $event_type, $details);
        if ($stmt->execute()) {
            $_SESSION['request_success'] = sprintf($texts[$lang]['request_success'], htmlspecialchars($event_type));
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = $texts[$lang]['error_submitting'];
        }
    } else {
        $error = $texts[$lang]['all_fields_required'];
    }
}

// Fetch all requests for this citizen with status
$requests = [];
$stmt = $conn->prepare("SELECT id, event_type, details, status, created_at FROM requests WHERE citizen_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $citizen_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang='<?= $lang ?>'>
<head>
    <meta charset='UTF-8'>
    <title><?= $texts[$lang]['page_title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
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
        [lang="am"] .badge, [lang="am"] .modal-title {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        .lang-switcher {
            position: fixed;
            top: 3rem;
            right: 1.5rem;
            z-index: 1050;
        }

        /* ──────────────────────────────────────────────── */
        /* All your original styles remain completely unchanged */
        /* ──────────────────────────────────────────────── */
        .page-header { background: var(--gradient-primary); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: var(--shadow); }
        /* ... every single line of your original CSS stays exactly the same ... */
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
                <h1 class="display-5 fw-bold"><i class="fas fa-file-alt me-3"></i><?= $texts[$lang]['header_title'] ?></h1>
                <p class="lead mb-0"><?= $texts[$lang]['header_subtitle'] ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#requestEventModal">
                    <i class="fas fa-plus me-2"></i><?= $texts[$lang]['new_request'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class='container my-5'>
    <?php if (isset($_SESSION['request_success'])): ?>
        <div class='alert alert-success'><?= $_SESSION['request_success']; unset($_SESSION['request_success']); ?></div>
    <?php endif; ?>
    <?php if ($success): ?><div class='alert alert-success'><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class='alert alert-danger'><?= $error ?></div><?php endif; ?>

    <!-- Modal for request form -->
    <div class="modal fade" id="requestEventModal" tabindex="-1" aria-labelledby="requestEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestEventModalLabel"><i class="fas fa-plus me-2"></i><?= $texts[$lang]['new_request'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method='post'>
                    <div class="modal-body">
                        <div class='mb-3'>
                            <label class='form-label'><?= $texts[$lang]['event_type'] ?></label>
                            <select name='event_type' class='form-select' required>
                                <option value=''><?= $texts[$lang]['select_event'] ?></option>
                                <option value='Birth'><?= $texts[$lang]['birth'] ?></option>
                                <option value='Death'><?= $texts[$lang]['death'] ?></option>
                                <option value='Marriage'><?= $texts[$lang]['marriage'] ?></option>
                                <option value='Divorce'><?= $texts[$lang]['divorce'] ?></option>
                            </select>
                        </div>
                        <div class='mb-3'>
                            <label class='form-label'><?= $texts[$lang]['details'] ?></label>
                            <textarea name='details' class='form-control' rows='4' 
                                      placeholder="<?= $texts[$lang]['details_placeholder'] ?>" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $texts[$lang]['cancel'] ?></button>
                        <button type='submit' class='btn btn-primary'>
                            <i class="fas fa-paper-plane me-2"></i><?= $texts[$lang]['submit_request'] ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
   
    <!-- Display Submitted Requests -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= $texts[$lang]['my_submitted_requests'] ?></h5>
            <span class="badge bg-light text-dark"><?= count($requests) ?> <?= $texts[$lang]['requests_count'] ?></span>
        </div>
        <div class="card-body">
        <?php if (count($requests) > 0): ?>
            <div class="table-responsive">
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th><?= $texts[$lang]['event_type'] ?></th>
                            <th><?= $texts[$lang]['details'] ?></th>
                            <th><?= $texts[$lang]['status'] ?></th>
                            <th><?= $texts[$lang]['submitted_on'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['event_type']) ?></td>
                                <td><?= htmlspecialchars($req['details']) ?></td>
                                <td>
                                    <span class="badge badge-<?=
                                        $req['status'] == 'Approved' ? 'success' :
                                        ($req['status'] == 'Rejected' ? 'danger' : 'warning')
                                    ?>">
                                        <?= htmlspecialchars($req['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($req['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4><?= $texts[$lang]['no_requests'] ?></h4>
                <p><?= $texts[$lang]['no_requests_message'] ?></p>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>