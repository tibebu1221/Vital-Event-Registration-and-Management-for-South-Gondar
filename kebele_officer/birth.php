<?php
// Kebele Officer: Register/View Birth Events
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

$officer_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT id, kebele_name FROM kebeles WHERE kebele_officer_id = ?");
$stmt->bind_param("i", $officer_id);
$stmt->execute();
$kebele_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kebele_row) {
    die('<div class="alert alert-danger m-4">No kebele assigned to this officer. Please contact admin.</div>');
}
$kebele_id = $kebele_row['id'];
$kebele_name = $kebele_row['kebele_name'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all modal form fields
    $form_number = trim($_POST['form_number'] ?? '');
    $registration_uid = trim($_POST['registration_uid'] ?? '');
    $child_name = trim($_POST['child_name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $grandfather_name = trim($_POST['grandfather_name'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $mother_full_name = trim($_POST['mother_full_name'] ?? '');
    $father_full_name = trim($_POST['father_full_name'] ?? '');
    $parents_nationality = trim($_POST['parents_nationality'] ?? '');
    $registered_date = $_POST['registered_date'] ?? '';
    $issue_date = $_POST['issue_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $photo_path = '';
    
    // Debug: Check the date values
    error_log("Date of Birth: " . $date_of_birth);
    error_log("Registered Date: " . $registered_date);
    error_log("Issue Date: " . $issue_date);
    
    // Get the request ID and applicant ID
    $request_id = $_POST['request_id'] ?? null;
    $applicant_id = null;
    
    if ($request_id) {
        // Get citizen_id from the request
        $req_stmt = $conn->prepare("SELECT citizen_id FROM requests WHERE id = ?");
        $req_stmt->bind_param("i", $request_id);
        $req_stmt->execute();
        $req_result = $req_stmt->get_result();
        if ($req_row = $req_result->fetch_assoc()) {
            $applicant_id = $req_row['citizen_id'];
            // Update request status to Approved
            $update_req = $conn->prepare("UPDATE requests SET status = 'Approved' WHERE id = ?");
            $update_req->bind_param("i", $request_id);
            $update_req->execute();
            $update_req->close();
        }
        $req_stmt->close();
    } elseif (isset($_POST['citizen_id'])) {
        $applicant_id = intval($_POST['citizen_id']);
    }
    
    // Handle file upload (photo)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/birth_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_filename = uniqid('birth_', true) . '.' . $ext;
        $photo_path_full = $upload_dir . $photo_filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path_full)) {
            $photo_path = 'images/birth_photos/' . $photo_filename;
        } else {
            $error = 'Error uploading photo. Please try again.';
        }
    }
    
    // Validate required fields
    if ($form_number && $registration_uid && $child_name && $father_name && $grandfather_name && $sex && $date_of_birth && $mother_full_name && $father_full_name && $parents_nationality && $registered_date && $issue_date && $photo_path && $applicant_id) {
        
        // Debug: Check all values before insertion
        error_log("Applicant ID: " . $applicant_id);
        error_log("Form Number: " . $form_number);
        error_log("Registration UID: " . $registration_uid);
        error_log("Child Name: " . $child_name);
        error_log("Father Name: " . $father_name);
        error_log("Grandfather Name: " . $grandfather_name);
        error_log("Sex: " . $sex);
        error_log("Date of Birth: " . $date_of_birth);
        error_log("Place of Birth: " . $kebele_id);
        error_log("Mother Name: " . $mother_full_name);
        error_log("Father Full Name: " . $father_full_name);
        error_log("Parents Nationality: " . $parents_nationality);
        error_log("Registered Date: " . $registered_date);
        error_log("Issue Date: " . $issue_date);
        error_log("Photo Path: " . $photo_path);
        error_log("Notes: " . $notes);
        
        // FIXED: Correct parameter count - 15 parameters for 15 placeholders
        $stmt = $conn->prepare("INSERT INTO birth_events (user_id, form_number, registration_uid, child_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, mother_full_name, father_full_name, parents_nationality, registered_date, issue_date, photo, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        
        if ($stmt) {
            // FIXED: Correct bind_param - 16 parameters (15 + status)
            $stmt->bind_param('isssssssssssssss', 
                $applicant_id, 
                $form_number, 
                $registration_uid, 
                $child_name, 
                $father_name, 
                $grandfather_name, 
                $sex, 
                $date_of_birth, 
                $kebele_id, 
                $mother_full_name, 
                $father_full_name, 
                $parents_nationality, 
                $registered_date, 
                $issue_date, 
                $photo_path, 
                $notes
            );
            
            if ($stmt->execute()) {
                $_SESSION['payment_user_id'] = $applicant_id;
                $_SESSION['payment_event_type'] = 'Birth';
                $_SESSION['registration_success'] = "Birth certificate registered successfully for " . htmlspecialchars($child_name);
                $stmt->close();
                header('Location: ../citizen/make_payment.php');
                exit();
            } else {
                $error = 'Error registering birth event: ' . $stmt->error;
                // Debug: Log the SQL error
                error_log("SQL Error: " . $stmt->error);
                $stmt->close();
            }
        } else {
            $error = 'Error preparing statement: ' . $conn->error;
            error_log("Prepare Error: " . $conn->error);
        }
    } else {
        $error = 'All fields are required and a citizen must be selected.';
        // Debug: Check which fields are missing
        $missing_fields = [];
        if (!$form_number) $missing_fields[] = 'form_number';
        if (!$registration_uid) $missing_fields[] = 'registration_uid';
        if (!$child_name) $missing_fields[] = 'child_name';
        if (!$father_name) $missing_fields[] = 'father_name';
        if (!$grandfather_name) $missing_fields[] = 'grandfather_name';
        if (!$sex) $missing_fields[] = 'sex';
        if (!$date_of_birth) $missing_fields[] = 'date_of_birth';
        if (!$mother_full_name) $missing_fields[] = 'mother_full_name';
        if (!$father_full_name) $missing_fields[] = 'father_full_name';
        if (!$parents_nationality) $missing_fields[] = 'parents_nationality';
        if (!$registered_date) $missing_fields[] = 'registered_date';
        if (!$issue_date) $missing_fields[] = 'issue_date';
        if (!$photo_path) $missing_fields[] = 'photo';
        if (!$applicant_id) $missing_fields[] = 'citizen_id';
        
        error_log("Missing fields: " . implode(', ', $missing_fields));
    }
}

// Get birth requests sent by citizens in this kebele
$birth_requests_stmt = $conn->prepare("
    SELECT r.id, r.citizen_id, u.fullname, r.details, r.created_at
    FROM requests r
    JOIN users u ON r.citizen_id = u.id
    WHERE r.event_type = 'Birth' AND u.kebele = ? AND r.status = 'Pending'
    ORDER BY r.created_at DESC
");
$birth_requests_stmt->bind_param("i", $kebele_id);
$birth_requests_stmt->execute();
$birth_requests = $birth_requests_stmt->get_result();

// Get registered births
$births_stmt = $conn->prepare("SELECT * FROM birth_events WHERE place_of_birth = ? ORDER BY id DESC");
$births_stmt->bind_param("i", $kebele_id);
$births_stmt->execute();
$births = $births_stmt->get_result();

// Get pending births count
$pending_births_stmt = $conn->prepare("SELECT COUNT(*) as count FROM birth_events WHERE place_of_birth = ? AND status = 'Pending'");
$pending_births_stmt->bind_param("i", $kebele_id);
$pending_births_stmt->execute();
$pending_births = $pending_births_stmt->get_result()->fetch_assoc();

// Get approved births count
$approved_births_stmt = $conn->prepare("SELECT COUNT(*) as count FROM birth_events WHERE place_of_birth = ? AND status = 'Approved'");
$approved_births_stmt->bind_param("i", $kebele_id);
$approved_births_stmt->execute();
$approved_births = $approved_births_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Birth Events Management - VERMS</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
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

        .btn-primary {
            background: var(--secondary-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
        }

        .btn-success {
            background: var(--success-color);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-success:hover {
            background: #219653;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 83, 0.3);
        }

        .btn-warning {
            background: var(--warning-color);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-warning:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.3);
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .status-approved {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success-color);
        }

        .status-paid {
            background: rgba(52, 152, 219, 0.2);
            color: var(--secondary-color);
        }

        .avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 16px 16px 0 0;
            border: none;
            padding: 20px 25px;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                    <i class="fas fa-baby me-2"></i>Birth Events Management
                </h1>
                <p class="dashboard-subtitle">
                    Manage birth registrations and certificates for <?= htmlspecialchars($kebele_name) ?> Kebele
                </p>
            </div>
         
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['registration_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['registration_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['registration_success']); ?>
    <?php endif; ?>
    <!-- Birth Requests Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-inbox me-2"></i>Birth Registration Requests
                <span class="badge bg-light text-dark ms-2"><?= $birth_requests ? $birth_requests->num_rows : 0 ?> requests</span>
            </div>
            <div class="d-flex gap-2">
                <span class="status-badge status-pending">Pending Review</span>
            </div>
        </div>
        <div class="card-body">
            <?php if ($birth_requests && $birth_requests->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requester</th>
                                <th>Details</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while($row = $birth_requests->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div><?= htmlspecialchars($row['fullname']) ?></div>
                                    </div>
                                </td>
                                <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm register-from-request" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#registerBirthModal"
                                            data-request-id="<?= $row['id'] ?>"
                                            data-citizen-id="<?= $row['citizen_id'] ?>"
                                            data-requester="<?= htmlspecialchars($row['fullname']) ?>"
                                            data-details="<?= htmlspecialchars($row['details']) ?>">
                                        <i class="fas fa-edit me-1"></i>Register
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No Birth Requests</h4>
                    <p>There are no pending birth registration requests from citizens in your kebele.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Birth Certificate Registration Modal -->
<div class="modal fade" id="registerBirthModal" tabindex="-1" aria-labelledby="registerBirthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-baby me-2"></i>Register Birth Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data" id="birthRegistrationForm">
                <input type="hidden" name="request_id" id="request_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Request Info (shown when registering from request) -->
                        <div class="col-12" id="requestInfoSection" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Registering from Request</h6>
                                <p class="mb-1"><strong>Requester:</strong> <span id="requesterName"></span></p>
                                <p class="mb-0"><strong>Request Details:</strong> <span id="requestDetails"></span></p>
                            </div>
                        </div>
                        
                        <!-- Citizen Selection -->
                        <div class="col-md-12">
                            <label class="form-label">Select Citizen (Applicant)</label>
                            <select name="citizen_id" class="form-select" required id="citizenSelect">
                                <option value="">-- Select Citizen --</option>
                                <?php 
                                $citizen_stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'citizen' AND kebele = ? ORDER BY fullname");
                                $citizen_stmt->bind_param("i", $kebele_id);
                                $citizen_stmt->execute();
                                $citizens_res = $citizen_stmt->get_result();
                                while($c = $citizens_res->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['fullname']) ?></option>
                                <?php endwhile; $citizen_stmt->close(); ?>
                            </select>
                        </div>
                        
                        <!-- Form Number & Unique ID -->
                        <div class="col-md-6">
                            <label class="form-label">Birth Registration Form Number</label>
                            <input type="text" name="form_number" id="modal_form_number" class="form-control" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unique Birth Registration ID</label>
                            <input type="text" name="registration_uid" id="modal_registration_uid" class="form-control" required readonly>
                        </div>
                        
                        <!-- Child Information -->
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-baby me-2"></i>Child Information</h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Child Full Name</label>
                            <input type="text" name="child_name" id="modal_child_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Father Name</label>
                            <input type="text" name="father_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grandfather Name</label>
                            <input type="text" name="grandfather_name" class="form-control" required>
                        </div>
                        
                        <!-- Birth Details -->
                        <div class="col-md-3">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="modal_date_of_birth" class="form-control" required>
                        </div>
                        <!-- Place of Birth: hidden, always kebele_id -->
                        <input type="hidden" name="place_of_birth" value="<?= htmlspecialchars($kebele_id) ?>">
                        <!-- Parents Information -->
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-users me-2"></i>Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother Full Name</label>
                            <input type="text" name="mother_full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father Full Name</label>
                            <input type="text" name="father_full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parents' Nationality</label>
                            <input type="text" name="parents_nationality" class="form-control" required value="Ethiopian">
                        </div>
                        <!-- Registration Dates -->
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-calendar-alt me-2"></i>Registration Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Birth Registered Date</label>
                            <input type="date" name="registered_date" id="modal_registered_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Certificate Issue Date</label>
                            <input type="date" name="issue_date" id="modal_issue_date" class="form-control" required>
                        </div>
                        <!-- Photo Upload -->
                        <div class="col-md-6">
                            <label class="form-label">Child Photo</label>
                            <input type="file" name="photo" accept="image/*" class="form-control" required>
                            <div class="form-text">Upload a clear photo of the child (JPG, PNG, max 5MB)</div>
                        </div>
                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" id="modal_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Register Birth
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
// Generate unique IDs
function generateUniqueId(prefix) {
    return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).substr(2, 5).toUpperCase();
}

// Handle modal opening for both new registration and from request
document.addEventListener('DOMContentLoaded', function() {
    var registerModal = document.getElementById('registerBirthModal');
    var requestInfoSection = document.getElementById('requestInfoSection');
    var citizenSelect = document.getElementById('citizenSelect');
    var requestIdInput = document.getElementById('request_id');
    
    // Handle registration from request
    var requestButtons = document.querySelectorAll('.register-from-request');
    requestButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var requestId = this.getAttribute('data-request-id');
            var citizenId = this.getAttribute('data-citizen-id');
            var requester = this.getAttribute('data-requester');
            var details = this.getAttribute('data-details');
            
            requestInfoSection.style.display = 'block';
            document.getElementById('requesterName').textContent = requester;
            document.getElementById('requestDetails').textContent = details;
            requestIdInput.value = requestId;
            citizenSelect.value = citizenId;
            citizenSelect.disabled = true;
        });
    });
    
    // Handle new registration (not from request)
    registerModal.addEventListener('show.bs.modal', function(event) {
        if (!event.relatedTarget || !event.relatedTarget.classList.contains('register-from-request')) {
            requestInfoSection.style.display = 'none';
            requestIdInput.value = '';
            citizenSelect.disabled = false;
            citizenSelect.value = '';
        }
        
        // Generate unique IDs
        document.getElementById('modal_form_number').value = generateUniqueId('FORM');
        document.getElementById('modal_registration_uid').value = generateUniqueId('BIRTH');
        
        // Set default dates
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('modal_date_of_birth').value = today;
        document.getElementById('modal_registered_date').value = today;
        document.getElementById('modal_issue_date').value = today;
    });
    
    registerModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('birthRegistrationForm').reset();
        citizenSelect.disabled = false;
    });
});

// Form validation
document.getElementById('birthRegistrationForm').addEventListener('submit', function(e) {
    var citizenSelect = document.getElementById('citizenSelect');
    if (citizenSelect.value === '') {
        e.preventDefault();
        alert('Please select a citizen for this birth registration.');
        citizenSelect.focus();
        return false;
    }
    
    // Validate dates
    var dateOfBirth = document.getElementById('modal_date_of_birth').value;
    var registeredDate = document.getElementById('modal_registered_date').value;
    var issueDate = document.getElementById('modal_issue_date').value;
    
    if (!dateOfBirth || !registeredDate || !issueDate) {
        e.preventDefault();
        alert('Please fill all date fields.');
        return false;
    }
    
    return true;
});
</script>
</body>   
</html>