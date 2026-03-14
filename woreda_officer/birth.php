<?php
// Woreda Officer Birth Events Management
session_start();
require_once '../includes/db_connection.php';

// Only woreda officer can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'woreda') {
    header('Location: ../login.php');
    exit();
}

// Get woreda ID for logged-in officer
$user_id = intval($_SESSION['user_id']);
$woreda_row = $conn->query("SELECT id, woreda_name FROM woredas WHERE woreda_officer_id = $user_id")->fetch_assoc();
$woreda_id = $woreda_row ? $woreda_row['id'] : 0;
$woreda_name = $woreda_row ? $woreda_row['woreda_name'] : '';

// Fetch all registered birth events in this woreda
$birth_events = $conn->query("
    SELECT b.*, u.fullname AS registered_by, k.kebele_name AS kebele_name
    FROM birth_events b
    JOIN users u ON b.user_id = u.id
    JOIN kebeles k ON b.place_of_birth = k.id
    WHERE k.woreda_id = $woreda_id
    ORDER BY b.created_at DESC
");

// Fetch details for view more if requested
$view_id = intval($_GET['view_id'] ?? 0);
$detail = null;
if ($view_id) {
    $detail_stmt = $conn->prepare("
        SELECT b.*, u.fullname AS registered_by, k.kebele_name AS kebele_name, w.woreda_name AS woreda_name
        FROM birth_events b
        JOIN users u ON b.user_id = u.id
        JOIN kebeles k ON b.place_of_birth = k.id
        JOIN woredas w ON k.woreda_id = w.id
        WHERE b.id = ?
        LIMIT 1
    ");
    $detail_stmt->bind_param('i', $view_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    $detail = $detail_result->fetch_assoc();
}

// Statistics
$total_births = $birth_events->num_rows;
$approved_births = $conn->query("SELECT COUNT(*) as count FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id WHERE k.woreda_id = $woreda_id AND b.status = 'Approved'")->fetch_assoc()['count'];
$pending_births = $conn->query("SELECT COUNT(*) as count FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id WHERE k.woreda_id = $woreda_id AND b.status = 'Pending'")->fetch_assoc()['count'];
$today_births = $conn->query("SELECT COUNT(*) as count FROM birth_events b JOIN kebeles k ON b.place_of_birth = k.id WHERE k.woreda_id = $woreda_id AND DATE(b.created_at) = CURDATE()")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Birth Events Management - Woreda Officer | VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--card-shadow);
        }

        .stat-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.total { border-left-color: var(--primary-color); }
        .stat-card.approved { border-left-color: var(--success-color); }
        .stat-card.pending { border-left-color: var(--warning-color); }
        .stat-card.today { border-left-color: var(--info-color); }

        .stat-number { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }

        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
        }

        .table-custom thead th {
            background: var(--light-bg);
            color: var(--primary-color);
            font-weight: 600;
        }

        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .btn-custom {
            border-radius: 10px;
            font-weight: 600;
            border: none;
        }

        .btn-view {
            background: linear-gradient(135deg, var(--info-color) 0%, #2dc4e6 100%);
            color: white;
        }

        .modal-custom .modal-content {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .modal-custom .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }

        .detail-item { padding: 0.75rem 0; border-bottom: 1px solid #f1f3f4; }
        .detail-label { font-weight: 600; color: var(--primary-color); }
        .detail-value { color: #555; font-size: 1.1rem; }

        .photo-placeholder {
            width: 150px; height: 150px;
            background: linear-gradient(135deg, #f1f3f4 0%, #dfe4ea 100%);
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            color: #7f8c8d; font-size: 3rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header text-center">
    <h1 class="fw-bold"><i class="fas fa-baby me-2"></i>Birth Events Management</h1>
    <p class="lead">Manage and monitor birth registrations in <strong><?= htmlspecialchars($woreda_name) ?></strong> Woreda</p>
</div>

<div class="container">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card total text-center"><div class="stat-number text-primary"><?= $total_births ?></div><h6>Total Births</h6></div></div>
        <div class="col-md-3"><div class="stat-card approved text-center"><div class="stat-number text-success"><?= $approved_births ?></div><h6>Approved</h6></div></div>
        <div class="col-md-3"><div class="stat-card pending text-center"><div class="stat-number text-warning"><?= $pending_births ?></div><h6>Pending</h6></div></div>
        <div class="col-md-3"><div class="stat-card today text-center"><div class="stat-number text-info"><?= $today_births ?></div><h6>Today's Registrations</h6></div></div>
    </div>

    <div class="main-card">
        <div class="card-header-custom">
            <div class="row">
                <div class="col-md-6"><h4><i class="fas fa-list me-2"></i>Registered Birth Events</h4></div>
                <div class="col-md-6 text-end"><span class="badge bg-light text-dark fs-6">Total: <?= $total_births ?> records</span></div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($birth_events && $birth_events->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Child Info</th>
                                <th>Date of Birth</th>
                                <th>Location</th>
                                <th>Parents</th>
                                <th>Status</th>
                                <th>Registered By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while($row=$birth_events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['child_name']) ?></strong><br><small><?= htmlspecialchars($row['sex']) ?></small></td>
                                    <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
                                    <td><?= htmlspecialchars($row['kebele_name']) ?></td>
                                    <td><small>F: <?= htmlspecialchars($row['father_full_name']) ?><br>M: <?= htmlspecialchars($row['mother_full_name']) ?></small></td>
                                    <td>
                                        <span class="badge-status bg-<?= 
                                            $row['status']=='Approved'?'success':($row['status']=='Paid'?'primary':'warning') ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($row['registered_by']) ?></small></td>
                                    <td><a href="?view_id=<?= $row['id'] ?>" class="btn btn-view btn-sm btn-custom"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-baby-carriage fa-3x mb-3"></i><h5>No Birth Events Found</h5></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($detail): ?>
<div class="modal fade show d-block" id="detailModal" tabindex="-1" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-baby me-2"></i>Birth Event Details</h5>
                <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 detail-item"><div class="detail-label">Child Name</div><div class="detail-value"><?= htmlspecialchars($detail['child_name']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Sex</div><div class="detail-value"><?= htmlspecialchars($detail['sex']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Father's Name</div><div class="detail-value"><?= htmlspecialchars($detail['father_full_name']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Mother's Name</div><div class="detail-value"><?= htmlspecialchars($detail['mother_full_name']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Grandfather's Name</div><div class="detail-value"><?= htmlspecialchars($detail['grandfather_name']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Date of Birth</div><div class="detail-value"><?= htmlspecialchars($detail['date_of_birth']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Place of Birth</div><div class="detail-value"><?= htmlspecialchars($detail['kebele_name']) ?>, <?= htmlspecialchars($detail['woreda_name']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Status</div><div class="detail-value"><?= htmlspecialchars($detail['status']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Registered Date</div><div class="detail-value"><?= htmlspecialchars($detail['registered_date']) ?></div></div>
                            <div class="col-md-6 detail-item"><div class="detail-label">Registered By</div><div class="detail-value"><?= htmlspecialchars($detail['registered_by']) ?></div></div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <?php if ($detail['photo']): ?>
                            <img src="../<?= htmlspecialchars($detail['photo']) ?>" alt="Child Photo" class="img-fluid rounded shadow" style="max-height:200px;">
                        <?php else: ?>
                            <div class="photo-placeholder"><i class="fas fa-baby"></i></div>
                            <small class="text-muted d-block mt-2">No photo available</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-custom" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.remove('show');
    modal.style.opacity = '1';
    const url = new URL(window.location.href);
    url.searchParams.delete('view_id');
    setTimeout(() => {
        modal.remove();
        window.history.replaceState({}, '', url.toString());
        document.body.style.overflow = 'auto';
    }, 200);
}
document.body.style.overflow = 'hidden';
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
