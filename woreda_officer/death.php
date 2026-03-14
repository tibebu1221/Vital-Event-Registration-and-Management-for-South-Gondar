<?php
// Woreda Officer Death Events Management
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

// Fetch all registered death events in this woreda
$death_events = $conn->query("
    SELECT d.*, u.fullname AS registered_by, k.kebele_name AS kebele_name
    FROM death_events d
    JOIN users u ON d.user_id = u.id
    JOIN kebeles k ON d.place_of_death = k.id
    WHERE k.woreda_id = $woreda_id
    ORDER BY d.created_at DESC
");

// Fetch details for view more if requested
$view_id = intval($_GET['view_id'] ?? 0);
$detail = null;
if ($view_id) {
    $detail_stmt = $conn->prepare("
        SELECT d.*, u.fullname AS registered_by, k.kebele_name AS kebele_name, w.woreda_name AS woreda_name
        FROM death_events d
        JOIN users u ON d.user_id = u.id
        JOIN kebeles k ON d.place_of_death = k.id
        JOIN woredas w ON k.woreda_id = w.id
        WHERE d.id = ?
        LIMIT 1
    ");
    $detail_stmt->bind_param('i', $view_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    $detail = $detail_result->fetch_assoc();
}

// Statistics
$total_deaths = $death_events->num_rows;
$approved_deaths = $conn->query("SELECT COUNT(*) as count FROM death_events d JOIN kebeles k ON d.place_of_death = k.id WHERE k.woreda_id = $woreda_id AND d.status = 'Approved'")->fetch_assoc()['count'];
$pending_deaths = $conn->query("SELECT COUNT(*) as count FROM death_events d JOIN kebeles k ON d.place_of_death = k.id WHERE k.woreda_id = $woreda_id AND d.status = 'Pending'")->fetch_assoc()['count'];
$today_deaths = $conn->query("SELECT COUNT(*) as count FROM death_events d JOIN kebeles k ON d.place_of_death = k.id WHERE k.woreda_id = $woreda_id AND DATE(d.created_at) = CURDATE()")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Death Events Management - Woreda Officer | VERMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f6f7fb;
            font-family: 'Segoe UI', sans-serif;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #1e272e, #485460);
            color: #fff;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-custom th {
            background: #f1f3f5;
            color: #2c3e50;
        }
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        /* Modal styling */
        .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, #2f3640, #353b48);
            color: white;
        }
        .modal-body {
            padding: 2rem;
        }
        .detail-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .photo-placeholder {
            width: 120px;
            height: 120px;
            background: #f1f2f6;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #95a5a6;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="fas fa-book-dead me-2"></i>Death Events Management</h2>
        <p>Manage and review death registrations in <strong><?= htmlspecialchars($woreda_name) ?></strong> woreda</p>
    </div>
</div>

<div class="container">
    <div class="row mb-4 text-center">
        <div class="col-md-3"><div class="stat-card"><h3><?= $total_deaths ?></h3><p>Total Deaths</p></div></div>
        <div class="col-md-3"><div class="stat-card"><h3 class="text-success"><?= $approved_deaths ?></h3><p>Approved</p></div></div>
        <div class="col-md-3"><div class="stat-card"><h3 class="text-warning"><?= $pending_deaths ?></h3><p>Pending</p></div></div>
        <div class="col-md-3"><div class="stat-card"><h3 class="text-danger"><?= $today_deaths ?></h3><p>Today's</p></div></div>
    </div>

    <div class="main-card p-3">
        <?php if ($death_events && $death_events->num_rows > 0): ?>
        <table class="table table-hover table-custom align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Deceased</th>
                    <th>Date of Death</th>
                    <th>Kebele</th>
                    <th>Age & Sex</th>
                    <th>Status</th>
                    <th>Registered By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($row = $death_events->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['deceased_name']) ?></td>
                    <td><?= htmlspecialchars($row['date_of_death']) ?></td>
                    <td><?= htmlspecialchars($row['kebele_name']) ?></td>
                    <td><?= htmlspecialchars($row['age']) ?> yrs / <?= htmlspecialchars($row['sex']) ?></td>
                    <td>
                        <span class="badge-status bg-<?= 
                            $row['status']=='Approved'?'success':($row['status']=='Paid'?'primary':'warning') ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['registered_by']) ?></td>
                    <td>
                        <a href="?view_id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="text-center py-4 text-muted">
            <i class="fas fa-book-dead fa-3x mb-3"></i>
            <p>No death events found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<?php if ($detail): ?>
<div class="modal fade show d-block" id="detailModal" style="background: rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fas fa-book-dead me-2"></i>Death Event Details</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                <div class="detail-card">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="detail-item"><span class="detail-label">Deceased Name:</span> <?= htmlspecialchars($detail['deceased_name']) ?></div>
                            <div class="detail-item"><span class="detail-label">Sex:</span> <?= htmlspecialchars($detail['sex']) ?></div>
                            <div class="detail-item"><span class="detail-label">Age:</span> <?= htmlspecialchars($detail['age']) ?> years</div>
                            <div class="detail-item"><span class="detail-label">Date of Death:</span> <?= htmlspecialchars($detail['date_of_death']) ?></div>
                            <div class="detail-item"><span class="detail-label">Place of Death:</span> <?= htmlspecialchars($detail['kebele_name']) ?>, <?= htmlspecialchars($detail['woreda_name']) ?></div>
                            <div class="detail-item"><span class="detail-label">Cause of Death:</span> <?= htmlspecialchars($detail['cause_of_death']) ?></div>
                            <div class="detail-item"><span class="detail-label">Reporter:</span> <?= htmlspecialchars($detail['reporter_full_name']) ?></div>
                            <div class="detail-item"><span class="detail-label">Relationship:</span> <?= htmlspecialchars($detail['relationship']) ?></div>
                            <div class="detail-item"><span class="detail-label">Status:</span> <?= htmlspecialchars($detail['status']) ?></div>
                            <div class="detail-item"><span class="detail-label">Registered Date:</span> <?= htmlspecialchars($detail['registered_date']) ?></div>
                            <div class="detail-item"><span class="detail-label">Registered By:</span> <?= htmlspecialchars($detail['registered_by']) ?></div>
                            <?php if ($detail['notes']): ?>
                                <div class="detail-item"><span class="detail-label">Notes:</span> <?= htmlspecialchars($detail['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php if ($detail['photo']): ?>
                                <img src="../<?= htmlspecialchars($detail['photo']) ?>" alt="Deceased" class="img-fluid rounded shadow">
                            <?php else: ?>
                                <div class="photo-placeholder"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.body.style.overflow = 'hidden';
    function closeModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
            const url = new URL(window.location.href);
            url.searchParams.delete('view_id');
            window.history.replaceState({}, '', url.toString());
            document.body.style.overflow = 'auto';
        }, 200);
    }
    document.getElementById('detailModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
