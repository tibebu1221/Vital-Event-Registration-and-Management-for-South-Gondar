<?php
// Woreda Officer Divorce Events Management
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

// Fetch all registered divorce events in this woreda
$divorce_events = $conn->query("
    SELECT dv.*, u.fullname AS registered_by, k.kebele_name AS kebele_name
    FROM divorce_events dv
    JOIN users u ON dv.user_id = u.id
    JOIN kebeles k ON dv.place_of_divorce = k.id
    WHERE k.woreda_id = $woreda_id
    ORDER BY dv.created_at DESC
");

// Fetch details for view more if requested
$view_id = intval($_GET['view_id'] ?? 0);
$detail = null;
if ($view_id) {
    $detail_stmt = $conn->prepare("
        SELECT dv.*, u.fullname AS registered_by, k.kebele_name AS kebele_name, w.woreda_name AS woreda_name
        FROM divorce_events dv
        JOIN users u ON dv.user_id = u.id
        JOIN kebeles k ON dv.place_of_divorce = k.id
        JOIN woredas w ON k.woreda_id = w.id
        WHERE dv.id = ?
        LIMIT 1
    ");
    $detail_stmt->bind_param('i', $view_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    $detail = $detail_result->fetch_assoc();
}

// Statistics
$total_divorces = $divorce_events->num_rows;
$approved_divorces = $conn->query("SELECT COUNT(*) as count FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id WHERE k.woreda_id = $woreda_id AND dv.status = 'Approved'")->fetch_assoc()['count'];
$pending_divorces = $conn->query("SELECT COUNT(*) as count FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id WHERE k.woreda_id = $woreda_id AND dv.status = 'Pending'")->fetch_assoc()['count'];
$today_divorces = $conn->query("SELECT COUNT(*) as count FROM divorce_events dv JOIN kebeles k ON dv.place_of_divorce = k.id WHERE k.woreda_id = $woreda_id AND DATE(dv.created_at) = CURDATE()")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Divorce Events Management - Woreda Officer | VERMS</title>
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
            --divorce-color: #8e44ad;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--divorce-color) 0%, #9b59b6 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .stat-card { background: white; border: none; border-radius: 15px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 4px solid var(--divorce-color); margin-bottom: 1rem; }
        .stat-card.total { border-left-color: var(--divorce-color); }
        .stat-card.approved { border-left-color: var(--success-color); }
        .stat-card.pending { border-left-color: var(--warning-color); }
        .stat-card.today { border-left-color: var(--info-color); }
        .stat-number { font-size: 2.5rem; font-weight: 700; }
        .main-card { background: white; border-radius: 20px; box-shadow: var(--card-shadow); overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, var(--divorce-color) 0%, #9b59b6 100%); color: white; padding: 1.5rem; }
        .table-custom thead th { background: var(--light-bg); color: var(--primary-color); font-weight: 600; }
        .badge-status { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.8rem; }
        .btn-custom { border-radius: 10px; font-weight: 600; border: none; }
        .btn-view { background: linear-gradient(135deg, var(--info-color) 0%, #2dc4e6 100%); color: white; }
        .modal-custom .modal-content { border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .modal-custom .modal-header { background: linear-gradient(135deg, var(--divorce-color) 0%, #9b59b6 100%); color: white; border-radius: 20px 20px 0 0; }
        .detail-item { padding: 0.75rem 0; border-bottom: 1px solid #f1f3f4; }
        .detail-label { font-weight: 600; color: var(--primary-color); }
        .detail-value { color: #555; font-size: 1.1rem; }
        .photo-placeholder { width: 150px; height: 150px; background: linear-gradient(135deg, #f1f3f4 0%, #dfe4ea 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #7f8c8d; font-size: 3rem; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header">
    <h1 class="fw-bold"><i class="fas fa-file-contract me-2"></i>Divorce Events Management</h1>
    <p class="lead">Manage and monitor divorce registrations in <strong><?= htmlspecialchars($woreda_name) ?></strong> Woreda</p>
</div>

<div class="container">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card total text-center"><div class="stat-number"><?= $total_divorces ?></div><h6>Total Divorces</h6></div></div>
        <div class="col-md-3"><div class="stat-card approved text-center"><div class="stat-number"><?= $approved_divorces ?></div><h6>Approved</h6></div></div>
        <div class="col-md-3"><div class="stat-card pending text-center"><div class="stat-number"><?= $pending_divorces ?></div><h6>Pending</h6></div></div>
        <div class="col-md-3"><div class="stat-card today text-center"><div class="stat-number"><?= $today_divorces ?></div><h6>Today's Registrations</h6></div></div>
    </div>

    <div class="main-card">
        <div class="card-header-custom">
            <div class="row">
                <div class="col-md-6"><h4><i class="fas fa-list me-2"></i>Registered Divorce Events</h4></div>
                <div class="col-md-6 text-end"><span class="badge bg-light text-dark fs-6">Total: <?= $total_divorces ?> records</span></div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($divorce_events && $divorce_events->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Couple</th>
                                <th>Date of Divorce</th>
                                <th>Place</th>
                                <th>Status</th>
                                <th>Registered By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while($row=$divorce_events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['husband_name']) ?></strong> & <strong><?= htmlspecialchars($row['wife_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['divorce_date']) ?></td>
                                    <td><?= htmlspecialchars($row['kebele_name']) ?></td>
                                    <td><span class="badge-status bg-<?= $row['status']=='Approved'?'success':'warning' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                    <td><?= htmlspecialchars($row['registered_by']) ?></td>
                                    <td><a href="?view_id=<?= $row['id'] ?>" class="btn btn-view btn-sm btn-custom"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-file-contract fa-3x mb-3"></i><h5>No Divorce Events Found</h5></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($detail): ?>
<div class="modal fade show d-block" id="detailModal" tabindex="-1" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Divorce Event Details</h5>
                <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item"><div class="detail-label">Husband</div><div class="detail-value"><?= htmlspecialchars($detail['husband_name']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Wife</div><div class="detail-value"><?= htmlspecialchars($detail['wife_name']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Divorce Date</div><div class="detail-value"><?= htmlspecialchars($detail['divorce_date']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Place of Divorce</div><div class="detail-value"><?= htmlspecialchars($detail['kebele_name']) ?>, <?= htmlspecialchars($detail['woreda_name']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Witness 1</div><div class="detail-value"><?= htmlspecialchars($detail['witness_1']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Witness 2</div><div class="detail-value"><?= htmlspecialchars($detail['witness_2']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><?= htmlspecialchars($detail['status']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Registered By</div><div class="detail-value"><?= htmlspecialchars($detail['registered_by']) ?></div></div>
                        <div class="detail-item"><div class="detail-label">Registration Date</div><div class="detail-value"><?= date('M j, Y', strtotime($detail['created_at'])) ?></div></div>
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
    modal.remove();
    const url = new URL(window.location.href);
    url.searchParams.delete('view_id');
    window.history.replaceState({}, '', url.toString());
    document.body.style.overflow = 'auto';
}
document.body.style.overflow = 'hidden';
document.getElementById('detailModal').addEventListener('click', function(e) {
    if(e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') closeModal();
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
