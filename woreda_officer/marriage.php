<?php
// Woreda Officer Marriage Events Management
session_start();
require_once '../includes/db_connection.php';

// Only woreda officer can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'woreda') {
    header('Location: ../login.php');
    exit();
}

// Get woreda ID for logged-in officer
$user_id = intval($_SESSION['user_id']);
$woreda_row = $conn->query(
    "SELECT id, woreda_name FROM woredas WHERE woreda_officer_id = $user_id"
)->fetch_assoc();
$woreda_id = $woreda_row ? $woreda_row['id'] : 0;
$woreda_name = $woreda_row ? $woreda_row['woreda_name'] : '';

// Fetch all registered marriage events in this woreda
$marriage_events = $conn->query("
    SELECT m.*, u.fullname AS registered_by, k.kebele_name AS kebele_name
    FROM marriage_events m
    JOIN users u ON m.user_id = u.id
    JOIN kebeles k ON m.place_of_marriage = k.id
    WHERE k.woreda_id = $woreda_id
    ORDER BY m.created_at DESC
");

// Fetch details for view more if requested
$view_id = intval($_GET['view_id'] ?? 0);
$detail = null;
if ($view_id) {
    $detail_stmt = $conn->prepare("
        SELECT m.*, u.fullname AS registered_by, k.kebele_name AS kebele_name, w.woreda_name AS woreda_name
        FROM marriage_events m
        JOIN users u ON m.user_id = u.id
        JOIN kebeles k ON m.place_of_marriage = k.id
        JOIN woredas w ON k.woreda_id = w.id
        WHERE m.id = ? LIMIT 1
    ");
    $detail_stmt->bind_param('i', $view_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    $detail = $detail_result->fetch_assoc();
}

// Statistics
$total_marriages = $marriage_events->num_rows;
$approved_marriages = $conn->query("SELECT COUNT(*) as count FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id WHERE k.woreda_id = $woreda_id AND m.status = 'Approved'")->fetch_assoc()['count'];
$pending_marriages = $conn->query("SELECT COUNT(*) as count FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id WHERE k.woreda_id = $woreda_id AND m.status = 'Pending'")->fetch_assoc()['count'];
$today_marriages = $conn->query("SELECT COUNT(*) as count FROM marriage_events m JOIN kebeles k ON m.place_of_marriage = k.id WHERE k.woreda_id = $woreda_id AND DATE(m.created_at) = CURDATE()")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marriage Events Management - Woreda Officer | VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Similar styles as your previous page --- */
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .stat-card { border-radius: 15px; padding: 1.5rem; background:white; box-shadow:0 4px 6px rgba(0,0,0,0.1); transition:0.3s; }
        .stat-card:hover { transform:translateY(-5px); box-shadow:0 8px 15px rgba(0,0,0,0.15); }
        .stat-number { font-size:2.5rem; font-weight:700; }
        .main-card { background:white; border-radius:20px; box-shadow:0 4px 6px rgba(0,0,0,0.1); overflow:hidden; }
        .card-header-custom { background:#e74c3c; color:white; padding:1.5rem; border-bottom:none; }
        .table-custom { margin-bottom:0; }
        .table-custom thead th { background:#f8f9fa; border-bottom:2px solid #dee2e6; }
        .badge-status { padding:0.5rem 1rem; border-radius:20px; font-weight:600; font-size:0.8rem; }
        .btn-custom { border-radius:10px; padding:0.5rem 1.5rem; border:none; font-weight:600; }
        .btn-view { background:#3498db; color:white; }
        .btn-view:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(52,152,219,0.4); }
        .modal-custom .modal-content { border:none; border-radius:20px; box-shadow:0 20px 40px rgba(0,0,0,0.2); }
        .modal-custom .modal-header { background:#e74c3c; color:white; border-radius:20px 20px 0 0; }
        .detail-item { padding:0.75rem 0; border-bottom:1px solid #f1f3f4; }
        .detail-item:last-child { border-bottom:none; }
        .detail-label { font-weight:600; color:#2c3e50; }
        .detail-value { color:#555; font-size:1.1rem; }
        .photo-placeholder { width:120px; height:120px; background:#dfe4ea; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#7f8c8d; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="stat-card text-center"><div class="stat-number"><?= $total_marriages ?></div><div>Total</div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card text-center"><div class="stat-number text-success"><?= $approved_marriages ?></div><div>Approved</div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card text-center"><div class="stat-number text-warning"><?= $pending_marriages ?></div><div>Pending</div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card text-center"><div class="stat-number text-info"><?= $today_marriages ?></div><div>Today</div></div></div>
    </div>

    <!-- Main Table -->
    <div class="main-card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5>Registered Marriage Events</h5>
            <span class="badge bg-light text-dark">Total: <?= $total_marriages ?> records</span>
        </div>
        <div class="card-body p-0">
            <?php if ($marriage_events && $marriage_events->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Couple</th>
                                <th>Marriage Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Registered By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while($row=$marriage_events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['husband_name']) ?></strong> & <strong><?= htmlspecialchars($row['wife_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($row['marriage_date']) ?></td>
                                    <td><?= htmlspecialchars($row['kebele_name']) ?></td>
                                    <td><span class="badge-status bg-<?= $row['status']=='Approved'?'success':($row['status']=='Paid'?'primary':'warning') ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                    <td><?= htmlspecialchars($row['registered_by']) ?></td>
                                    <td><a href="?view_id=<?= $row['id'] ?>" class="btn btn-view btn-sm">View</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-ring fa-3x mb-3"></i>
                    <h5>No Marriage Events Found</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<?php if ($detail): ?>
<div class="modal fade show d-block" id="detailModal" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marriage Event Details</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6 text-center">
                        <div class="detail-label">Groom</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['husband_name']) ?></div>
                        <?php if ($detail['husband_photo']): ?>
                            <img src="../<?= htmlspecialchars($detail['husband_photo']) ?>" class="img-fluid rounded mt-2" style="max-height:120px;">
                        <?php else: ?>
                            <div class="photo-placeholder mx-auto mt-2"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-center">
                        <div class="detail-label">Bride</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['wife_name']) ?></div>
                        <?php if ($detail['wife_photo']): ?>
                            <img src="../<?= htmlspecialchars($detail['wife_photo']) ?>" class="img-fluid rounded mt-2" style="max-height:120px;">
                        <?php else: ?>
                            <div class="photo-placeholder mx-auto mt-2"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Place of Marriage</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['kebele_name']) ?>, <?= htmlspecialchars($detail['woreda_name']) ?></div>
                    </div>
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Witness 1</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['witness_1']) ?></div>
                    </div>
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Witness 2</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['witness_2']) ?></div>
                    </div>
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['status']) ?></div>
                    </div>
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Registered By</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['registered_by']) ?></div>
                    </div>
                    <div class="col-md-6 detail-item">
                        <div class="detail-label">Registered Date</div>
                        <div class="detail-value"><?= htmlspecialchars($detail['registered_date']) ?></div>
                    </div>
                    <?php if ($detail['notes']): ?>
                        <div class="col-12 detail-item">
                            <div class="detail-label">Notes</div>
                            <div class="detail-value"><?= htmlspecialchars($detail['notes']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-custom" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
function closeModal() {
    document.getElementById('detailModal').remove();
    const url = new URL(window.location.href);
    url.searchParams.delete('view_id');
    window.history.replaceState({}, '', url.toString());
    document.body.style.overflow = 'auto';
}
document.body.style.overflow = 'hidden';
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
