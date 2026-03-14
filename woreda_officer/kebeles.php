<?php
// woreda_officer/kebeles.php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'woreda') {
    header('Location: ../login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Get woreda info
$woreda = $conn->query("SELECT * FROM woredas WHERE woreda_officer_id = $user_id")->fetch_assoc();
$woreda_id = $woreda['id'];
$woreda_name = $woreda['woreda_name'];

// Get kebeles in this woreda
$kebeles = $conn->query("
    SELECT k.*, u.fullname as officer_name, u.username, u.phone as officer_phone,
           COUNT(DISTINCT be.id) as birth_count,
           COUNT(DISTINCT de.id) as death_count,
           COUNT(DISTINCT me.id) as marriage_count,
           COUNT(DISTINCT dve.id) as divorce_count
    FROM kebeles k
    LEFT JOIN users u ON k.kebele_officer_id = u.id
    LEFT JOIN birth_events be ON k.id = be.place_of_birth
    LEFT JOIN death_events de ON k.id = de.place_of_death
    LEFT JOIN marriage_events me ON k.id = me.place_of_marriage
    LEFT JOIN divorce_events dve ON k.id = dve.place_of_divorce
    WHERE k.woreda_id = $woreda_id
    GROUP BY k.id, k.kebele_name, u.fullname, u.username, u.phone
    ORDER BY k.kebele_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Kebeles - <?= htmlspecialchars($woreda_name) ?> Woreda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-map-marker-alt"></i> Kebeles in <?= htmlspecialchars($woreda_name) ?> Woreda</h4>
            </div>
            <div class="card-body">
                <?php if ($kebeles->num_rows > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kebele Name</th>
                                <th>Phone</th>
                                <th>Officer</th>
                                <th>Total Events</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while($k = $kebeles->fetch_assoc()): ?>
                            <?php 
                                $total_events = $k['birth_count'] + $k['death_count'] + $k['marriage_count'] + $k['divorce_count'];
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($k['kebele_name']) ?></td>
                                <td><?= htmlspecialchars($k['phone']) ?></td>
                                <td>
                                    <?php if ($k['officer_name']): ?>
                                        <?= htmlspecialchars($k['officer_name']) ?> (<?= htmlspecialchars($k['username']) ?>)<br>
                                        <small><?= htmlspecialchars($k['officer_phone'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-danger">No Officer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        B:<?= $k['birth_count'] ?> 
                                        D:<?= $k['death_count'] ?> 
                                        M:<?= $k['marriage_count'] ?> 
                                        DV:<?= $k['divorce_count'] ?>
                                    </span>
                                    <br>
                                    <small>Total: <?= $total_events ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $k['kebele_officer_id'] ? 'success' : 'warning' ?>">
                                        <?= $k['kebele_officer_id'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        No kebeles found in this woreda. Contact zone officer to add kebeles.
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>