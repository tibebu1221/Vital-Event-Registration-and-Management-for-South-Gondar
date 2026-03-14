<?php
// Kebele Officer: Edit Registered Events (All types, except form_number, registration_uid and status)
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

// Get kebele ID for logged-in officer
$user_id = intval($_SESSION['user_id']);
$kebele_row = $conn->query(
    "SELECT id, kebele_name FROM kebeles WHERE kebele_officer_id = $user_id"
)->fetch_assoc();
$kebele_id = $kebele_row ? $kebele_row['id'] : 0;
$kebele_name = $kebele_row ? $kebele_row['kebele_name'] : '';

// Fetch all registered events in this kebele (not just paid)
$events = [];

// Births
$births = $conn->query("
    SELECT 'birth' AS event_type, b.id, b.child_name AS main_name, b.date_of_birth AS event_date, b.status, b.notes, b.created_at
    FROM birth_events b
    WHERE b.place_of_birth = $kebele_id
    ORDER BY b.created_at DESC
");
while ($row = $births->fetch_assoc()) $events[] = $row;

// Marriages
$marriages = $conn->query("
    SELECT 'marriage' AS event_type, m.id, CONCAT(m.husband_name, ' & ', m.wife_name) AS main_name, m.marriage_date AS event_date, m.status, m.notes, m.created_at
    FROM marriage_events m
    WHERE m.place_of_marriage = $kebele_id
    ORDER BY m.created_at DESC
");
while ($row = $marriages->fetch_assoc()) $events[] = $row;

// Deaths
$deaths = $conn->query("
    SELECT 'death' AS event_type, d.id, d.deceased_name AS main_name, d.date_of_death AS event_date, d.status, d.notes, d.created_at
    FROM death_events d
    WHERE d.place_of_death = $kebele_id
    ORDER BY d.created_at DESC
");
while ($row = $deaths->fetch_assoc()) $events[] = $row;

// Divorces
$divorces = $conn->query("
    SELECT 'divorce' AS event_type, dv.id, CONCAT(dv.husband_name, ' & ', dv.wife_name) AS main_name, dv.divorce_date AS event_date, dv.status, dv.notes, dv.created_at
    FROM divorce_events dv
    WHERE dv.place_of_divorce = $kebele_id
    ORDER BY dv.created_at DESC
");
while ($row = $divorces->fetch_assoc()) $events[] = $row;

// Handle Edit
$edit_event_type = $_GET['edit_type'] ?? null;
$edit_id = intval($_GET['edit_id'] ?? 0);
$edit_error = '';
$edit_data = null;

if ($edit_event_type && $edit_id) {
    $table = "";
    switch ($edit_event_type) {
        case 'birth': $table = 'birth_events'; break;
        case 'marriage': $table = 'marriage_events'; break;
        case 'death': $table = 'death_events'; break;
        case 'divorce': $table = 'divorce_events'; break;
    }
    if ($table) {
        $res = $conn->query("SELECT * FROM $table WHERE id = $edit_id");
        $edit_data = $res->fetch_assoc();
    }
    // Handle update (except form_number, registration_uid and status)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
        $fields = [];
        if ($edit_event_type === 'birth') {
            $fields = [
                'child_name' => $_POST['child_name'],
                'father_name' => $_POST['father_name'],
                'mother_full_name' => $_POST['mother_full_name'],
                'grandfather_name' => $_POST['grandfather_name'],
                'sex' => $_POST['sex'],
                'date_of_birth' => $_POST['date_of_birth'],
                'place_of_birth' => $_POST['place_of_birth'],
                'father_full_name' => $_POST['father_full_name'],
                'parents_nationality' => $_POST['parents_nationality'],
                'registered_date' => $_POST['registered_date'],
                'issue_date' => $_POST['issue_date'],
                'notes' => $_POST['notes']
            ];
        } elseif ($edit_event_type === 'marriage') {
            $fields = [
                'husband_name' => $_POST['husband_name'],
                'wife_name' => $_POST['wife_name'],
                'marriage_date' => $_POST['marriage_date'],
                'place_of_marriage' => $_POST['place_of_marriage'],
                'witness_1' => $_POST['witness_1'],
                'witness_2' => $_POST['witness_2'],
                'registered_date' => $_POST['registered_date'],
                'issue_date' => $_POST['issue_date'],
                'notes' => $_POST['notes']
            ];
        } elseif ($edit_event_type === 'death') {
            $fields = [
                'deceased_name' => $_POST['deceased_name'],
                'sex' => $_POST['sex'],
                'date_of_death' => $_POST['date_of_death'],
                'place_of_death' => $_POST['place_of_death'],
                'age' => $_POST['age'],
                'cause_of_death' => $_POST['cause_of_death'],
                'reporter_full_name' => $_POST['reporter_full_name'],
                'relationship' => $_POST['relationship'],
                'registered_date' => $_POST['registered_date'],
                'issue_date' => $_POST['issue_date'],
                'notes' => $_POST['notes']
            ];
        } elseif ($edit_event_type === 'divorce') {
            $fields = [
                'husband_name' => $_POST['husband_name'],
                'wife_name' => $_POST['wife_name'],
                'divorce_date' => $_POST['divorce_date'],
                'place_of_divorce' => $_POST['place_of_divorce'],
                'witness_1' => $_POST['witness_1'],
                'witness_2' => $_POST['witness_2'],
                'registered_date' => $_POST['registered_date'],
                'issue_date' => $_POST['issue_date'],
                'notes' => $_POST['notes']
            ];
        }
        // Build query
        $sets = [];
        $params = [];
        $types = '';
        foreach ($fields as $k=>$v) {
            $sets[] = "$k=?";
            $params[] = $v;
            $types .= 's';
        }
        $params[] = $edit_id;
        $types .= 'i';
        $query = "UPDATE $table SET ".implode(',', $sets)." WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header("Location: edit_events.php");
            exit();
        } else {
            $edit_error = "Failed to update event: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Registered Events - VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

        .dashboard-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: var(--gradient-secondary);
            color: white;
            border: none;
            padding: 18px 25px;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 25px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 15px 12px;
        }

        .table td {
            padding: 15px 12px;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .btn-edit {
            background: var(--warning-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-edit:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.3);
        }

        .badge-count {
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-left: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            font-weight: 600;
        }

        .event-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-birth {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .badge-marriage {
            background: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }

        .badge-death {
            background: rgba(149, 165, 166, 0.2);
            color: #95a5a6;
        }

        .badge-divorce {
            background: rgba(231, 76, 60, 0.2);
            color: var(--accent-color);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .status-paid {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .status-approved {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success-color);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .filter-btn:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 25px;
            border: none;
        }

        .modal-body {
            padding: 25px;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .modal-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-edit me-2"></i>Event Management
                </h1>
                <p class="dashboard-subtitle">
                    Manage and edit all registered events for <?= htmlspecialchars($kebele_name) ?> Kebele
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="card bg-light" style="display: inline-block; padding: 15px 20px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark">Total Events</h5>
                            <h3 class="mb-0 text-primary"><?= count($events) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php
        $birth_count = array_filter($events, function($event) { return $event['event_type'] === 'birth'; });
        $marriage_count = array_filter($events, function($event) { return $event['event_type'] === 'marriage'; });
        $death_count = array_filter($events, function($event) { return $event['event_type'] === 'death'; });
        $divorce_count = array_filter($events, function($event) { return $event['event_type'] === 'divorce'; });
        ?>
        <div class="stat-item">
            <div class="stat-number text-primary"><?= count($birth_count) ?></div>
            <div class="stat-label">Birth Events</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color: #9b59b6;"><?= count($marriage_count) ?></div>
            <div class="stat-label">Marriage Events</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color: #95a5a6;"><?= count($death_count) ?></div>
            <div class="stat-label">Death Events</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-danger"><?= count($divorce_count) ?></div>
            <div class="stat-label">Divorce Events</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-2"></i>All Registered Events
                <span class="badge-count"><?= count($events) ?></span>
            </div>
            <div class="d-flex gap-2">
                <span class="status-badge status-pending">Pending</span>
                <span class="status-badge status-paid">Paid</span>
                <span class="status-badge status-approved">Approved</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Events (<?= count($events) ?>)</button>
                <button class="filter-btn" data-filter="birth">Birth (<?= count($birth_count) ?>)</button>
                <button class="filter-btn" data-filter="marriage">Marriage (<?= count($marriage_count) ?>)</button>
                <button class="filter-btn" data-filter="death">Death (<?= count($death_count) ?>)</button>
                <button class="filter-btn" data-filter="divorce">Divorce (<?= count($divorce_count) ?>)</button>
            </div>

            <?php if (count($events) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="eventsTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Event Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($events as $event): ?>
                            <tr class="event-row" data-type="<?= $event['event_type'] ?>">
                                <td>
                                    <?php if ($event['event_type'] === 'birth'): ?>
                                        <span class="event-badge badge-birth">
                                            <i class="fas fa-baby me-1"></i>Birth
                                        </span>
                                    <?php elseif ($event['event_type'] === 'marriage'): ?>
                                        <span class="event-badge badge-marriage">
                                            <i class="fas fa-ring me-1"></i>Marriage
                                        </span>
                                    <?php elseif ($event['event_type'] === 'death'): ?>
                                        <span class="event-badge badge-death">
                                            <i class="fas fa-book-dead me-1"></i>Death
                                        </span>
                                    <?php elseif ($event['event_type'] === 'divorce'): ?>
                                        <span class="event-badge badge-divorce">
                                            <i class="fas fa-file-contract me-1"></i>Divorce
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($event['main_name']) ?></strong>
                                </td>
                                <td>
                                    <?= date('M j, Y', strtotime($event['event_date'])) ?>
                                </td>
                                <td>
                                    <?php if ($event['status'] === 'Pending'): ?>
                                        <span class="status-badge status-pending"><?= $event['status'] ?></span>
                                    <?php elseif ($event['status'] === 'Paid'): ?>
                                        <span class="status-badge status-paid"><?= $event['status'] ?></span>
                                    <?php elseif ($event['status'] === 'Approved'): ?>
                                        <span class="status-badge status-approved"><?= $event['status'] ?></span>
                                    <?php else: ?>
                                        <span class="status-badge"><?= $event['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $event['notes'] ? htmlspecialchars(substr($event['notes'], 0, 50)) . (strlen($event['notes']) > 50 ? '...' : '') : 'No notes' ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($event['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="?edit_type=<?= $event['event_type'] ?>&edit_id=<?= $event['id'] ?>" class="btn btn-edit">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4>No Events Found</h4>
                    <p>There are no registered events in your kebele at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($edit_data): ?>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">
                    <i class="fas fa-edit me-2"></i>Edit <?= ucfirst($edit_event_type) ?> Event
                </h5>
                <a href="edit_events.php" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <?php if ($edit_error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($edit_error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <?php if ($edit_event_type === 'birth'): ?>
                        <div class="form-section">
                            <h6 class="form-section-title">Child Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Child Name</label>
                                    <input type="text" name="child_name" class="form-control" value="<?= htmlspecialchars($edit_data['child_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sex</label>
                                    <select name="sex" class="form-select">
                                        <option value="Male" <?= $edit_data['sex']=='Male'?'selected':'' ?>>Male</option>
                                        <option value="Female" <?= $edit_data['sex']=='Female'?'selected':'' ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($edit_data['date_of_birth']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Place of Birth (Kebele ID)</label>
                                    <input type="number" name="place_of_birth" class="form-control" value="<?= htmlspecialchars($edit_data['place_of_birth']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title">Parent Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Father Name</label>
                                    <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($edit_data['father_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother Name</label>
                                    <input type="text" name="mother_full_name" class="form-control" value="<?= htmlspecialchars($edit_data['mother_full_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Grandfather Name</label>
                                    <input type="text" name="grandfather_name" class="form-control" value="<?= htmlspecialchars($edit_data['grandfather_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Parents Nationality</label>
                                    <input type="text" name="parents_nationality" class="form-control" value="<?= htmlspecialchars($edit_data['parents_nationality']) ?>">
                                </div>
                            </div>
                        </div>

                    <?php elseif ($edit_event_type === 'marriage'): ?>
                        <div class="form-section">
                            <h6 class="form-section-title">Couple Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Husband Name</label>
                                    <input type="text" name="husband_name" class="form-control" value="<?= htmlspecialchars($edit_data['husband_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Wife Name</label>
                                    <input type="text" name="wife_name" class="form-control" value="<?= htmlspecialchars($edit_data['wife_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marriage Date</label>
                                    <input type="date" name="marriage_date" class="form-control" value="<?= htmlspecialchars($edit_data['marriage_date']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Place of Marriage (Kebele ID)</label>
                                    <input type="number" name="place_of_marriage" class="form-control" value="<?= htmlspecialchars($edit_data['place_of_marriage']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title">Witness Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Witness 1</label>
                                    <input type="text" name="witness_1" class="form-control" value="<?= htmlspecialchars($edit_data['witness_1']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Witness 2</label>
                                    <input type="text" name="witness_2" class="form-control" value="<?= htmlspecialchars($edit_data['witness_2']) ?>">
                                </div>
                            </div>
                        </div>

                    <?php elseif ($edit_event_type === 'death'): ?>
                        <div class="form-section">
                            <h6 class="form-section-title">Deceased Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Deceased Name</label>
                                    <input type="text" name="deceased_name" class="form-control" value="<?= htmlspecialchars($edit_data['deceased_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sex</label>
                                    <select name="sex" class="form-select">
                                        <option value="Male" <?= $edit_data['sex']=='Male'?'selected':'' ?>>Male</option>
                                        <option value="Female" <?= $edit_data['sex']=='Female'?'selected':'' ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Death</label>
                                    <input type="date" name="date_of_death" class="form-control" value="<?= htmlspecialchars($edit_data['date_of_death']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Place of Death (Kebele ID)</label>
                                    <input type="number" name="place_of_death" class="form-control" value="<?= htmlspecialchars($edit_data['place_of_death']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Age</label>
                                    <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($edit_data['age']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cause of Death</label>
                                    <input type="text" name="cause_of_death" class="form-control" value="<?= htmlspecialchars($edit_data['cause_of_death']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title">Reporter Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Reporter Name</label>
                                    <input type="text" name="reporter_full_name" class="form-control" value="<?= htmlspecialchars($edit_data['reporter_full_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" name="relationship" class="form-control" value="<?= htmlspecialchars($edit_data['relationship']) ?>">
                                </div>
                            </div>
                        </div>

                    <?php elseif ($edit_event_type === 'divorce'): ?>
                        <div class="form-section">
                            <h6 class="form-section-title">Couple Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Husband Name</label>
                                    <input type="text" name="husband_name" class="form-control" value="<?= htmlspecialchars($edit_data['husband_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Wife Name</label>
                                    <input type="text" name="wife_name" class="form-control" value="<?= htmlspecialchars($edit_data['wife_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Divorce Date</label>
                                    <input type="date" name="divorce_date" class="form-control" value="<?= htmlspecialchars($edit_data['divorce_date']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Place of Divorce (Kebele ID)</label>
                                    <input type="number" name="place_of_divorce" class="form-control" value="<?= htmlspecialchars($edit_data['place_of_divorce']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title">Witness Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Witness 1</label>
                                    <input type="text" name="witness_1" class="form-control" value="<?= htmlspecialchars($edit_data['witness_1']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Witness 2</label>
                                    <input type="text" name="witness_2" class="form-control" value="<?= htmlspecialchars($edit_data['witness_2']) ?>">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Common Fields -->
                    <div class="form-section">
                        <h6 class="form-section-title">Registration Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Registered Date</label>
                                <input type="date" name="registered_date" class="form-control" value="<?= htmlspecialchars($edit_data['registered_date']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Issue Date</label>
                                <input type="date" name="issue_date" class="form-control" value="<?= htmlspecialchars($edit_data['issue_date']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($edit_data['notes']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="submit" name="update_event" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Event
                        </button>
                        <a href="edit_events.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const eventRows = document.querySelectorAll('.event-row');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter rows
            eventRows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-type') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>
</body>
</html>