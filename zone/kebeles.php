<?php
// Manage Kebeles - Zone Officer
session_start();
require_once '../../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'zone') {
    header('Location: ../../login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);
$user_row = $conn->query("SELECT zone FROM users WHERE id = $user_id")->fetch_assoc();
$zone_name = $user_row ? $user_row['zone'] : '';

// Get zone ID
$zone_row = $conn->query("SELECT id FROM zones WHERE zone_name = '" . $conn->real_escape_string($zone_name) . "'")->fetch_assoc();
$zone_id = $zone_row ? $zone_row['id'] : 0;

// Fetch all kebeles in this zone
$kebeles = $conn->query("
    SELECT k.*, w.woreda_name, u.fullname as officer_name
    FROM kebeles k
    JOIN woredas w ON k.woreda_id = w.id
    LEFT JOIN users u ON k.kebele_officer_id = u.id
    WHERE k.zone_id = $zone_id
    ORDER BY w.woreda_name, k.kebele_name
");

// Get statistics
$total_kebeles = $kebeles->num_rows;
$kebeles_with_officers = $conn->query("SELECT COUNT(*) as count FROM kebeles WHERE zone_id = $zone_id AND kebele_officer_id IS NOT NULL")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Kebeles - <?= htmlspecialchars($zone_name) ?> Zone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #3498db 0%, #6c5ce7 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
            text-align: center;
        }
        
        .main-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <header class="dashboard-header">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-map-pin me-2"></i>Manage Kebeles
            </h1>
            <p class="lead mb-0">Manage kebele administration in <?= htmlspecialchars($zone_name) ?> Zone</p>
        </div>
    </header>

    <div class="container">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="fas fa-map-pin fa-2x text-primary mb-2"></i>
                    <h3><?= $total_kebeles ?></h3>
                    <p class="text-muted mb-0">Total Kebeles</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h3><?= $kebeles_with_officers ?></h3>
                    <p class="text-muted mb-0">With Officers</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="fas fa-user-times fa-2x text-warning mb-2"></i>
                    <h3><?= $total_kebeles - $kebeles_with_officers ?></h3>
                    <p class="text-muted mb-0">Without Officers</p>
                </div>
            </div>
        </div>

        <!-- Kebeles Table -->
        <div class="main-card">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Kebeles List</h5>
            </div>
            <div class="card-body">
                <?php if ($kebeles->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kebele Name</th>
                                    <th>Woreda</th>
                                    <th>Phone</th>
                                    <th>Officer</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; while($kebele = $kebeles->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($kebele['kebele_name']) ?></td>
                                    <td><?= htmlspecialchars($kebele['woreda_name']) ?></td>
                                    <td><?= htmlspecialchars($kebele['phone']) ?></td>
                                    <td>
                                        <?php if ($kebele['officer_name']): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($kebele['officer_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">No Officer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $kebele['kebele_officer_id'] ? 'success' : 'warning' ?>">
                                            <?= $kebele['kebele_officer_id'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-map-pin fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Kebeles Found</h4>
                        <p class="text-muted">There are no kebeles registered in your zone yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>