<?php
// Kebele Officer: Approve & Give Printed Certificate (Now includes Divorce events)
session_start();
require_once '../includes/db_connection.php';

// Only kebele officer can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

// Get kebele ID and name for logged-in officer
$kebele_row = $conn->query(
    "SELECT id, kebele_name FROM kebeles WHERE kebele_officer_id = " . intval($_SESSION['user_id'])
)->fetch_assoc();
$kebele_id = $kebele_row ? $kebele_row['id'] : 0;
$kebele_name = $kebele_row ? $kebele_row['kebele_name'] : '';

// Approve event if requested (birth, marriage, death, divorce)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'], $_POST['event_type'])) {
    $approve_id = intval($_POST['approve_id']);
    $event_type = $_POST['event_type'];
    if ($event_type === 'birth') {
        $stmt = $conn->prepare("UPDATE birth_events SET status = 'Approved' WHERE id = ? AND status = 'Paid'");
        $stmt->bind_param("i", $approve_id);
        $stmt->execute();
    } elseif ($event_type === 'marriage') {
        $stmt = $conn->prepare("UPDATE marriage_events SET status = 'Approved' WHERE id = ? AND status = 'Paid'");
        $stmt->bind_param("i", $approve_id);
        $stmt->execute();
    } elseif ($event_type === 'death') {
        $stmt = $conn->prepare("UPDATE death_events SET status = 'Approved' WHERE id = ? AND status = 'Paid'");
        $stmt->bind_param("i", $approve_id);
        $stmt->execute();
    } elseif ($event_type === 'divorce') {
        $stmt = $conn->prepare("UPDATE divorce_events SET status = 'Approved' WHERE id = ? AND status = 'Paid'");
        $stmt->bind_param("i", $approve_id);
        $stmt->execute();
    }
    header('Location: give_certificate.php');
    exit();
}

// Fetch all PAID events in this kebele and combine them
$all_events = [];

// Birth events
$paid_births = $conn->query("
    SELECT b.*, u.fullname, 'birth' as event_type
    FROM birth_events b
    JOIN users u ON b.user_id = u.id
    WHERE b.status = 'Paid'
        AND b.place_of_birth = $kebele_id
    ORDER BY b.created_at DESC
");
if ($paid_births) {
    while($row = $paid_births->fetch_assoc()) {
        $all_events[] = $row;
    }
}

// Marriage events
$paid_marriages = $conn->query("
    SELECT m.*, u.fullname, 'marriage' as event_type
    FROM marriage_events m
    JOIN users u ON m.user_id = u.id
    WHERE m.status = 'Paid'
        AND m.place_of_marriage = $kebele_id
    ORDER BY m.created_at DESC
");
if ($paid_marriages) {
    while($row = $paid_marriages->fetch_assoc()) {
        $all_events[] = $row;
    }
}

// Death events
$paid_deaths = $conn->query("
    SELECT d.*, u.fullname, 'death' as event_type
    FROM death_events d
    JOIN users u ON d.user_id = u.id
    WHERE d.status = 'Paid'
        AND d.place_of_death = $kebele_id
    ORDER BY d.created_at DESC
");
if ($paid_deaths) {
    while($row = $paid_deaths->fetch_assoc()) {
        $all_events[] = $row;
    }
}

// Divorce events
$paid_divorces = $conn->query("
    SELECT dv.*, u.fullname, 'divorce' as event_type
    FROM divorce_events dv
    JOIN users u ON dv.user_id = u.id
    WHERE dv.status = 'Paid'
        AND dv.place_of_divorce = $kebele_id
    ORDER BY dv.created_at DESC
");
if ($paid_divorces) {
    while($row = $paid_divorces->fetch_assoc()) {
        $all_events[] = $row;
    }
}

// Sort all events by creation date (newest first)
usort($all_events, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Paid Events & Give Certificates</title>
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

        .btn-approve {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-approve:hover {
            background: #219653;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 83, 0.3);
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

        .status-paid {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
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

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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
                    <i class="fas fa-certificate me-2"></i>Certificate Approval
                </h1>
                <p class="dashboard-subtitle">
                    Approve paid events and issue certificates for <?= htmlspecialchars($kebele_name) ?> Kebele
                </p>
            </div>
            <div class="col-md-4 text-md-end">
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php
        $birth_count = array_filter($all_events, function($event) { return $event['event_type'] === 'birth'; });
        $marriage_count = array_filter($all_events, function($event) { return $event['event_type'] === 'marriage'; });
        $death_count = array_filter($all_events, function($event) { return $event['event_type'] === 'death'; });
        $divorce_count = array_filter($all_events, function($event) { return $event['event_type'] === 'divorce'; });
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
                <i class="fas fa-list me-2"></i>All Pending Events
                <span class="badge-count"><?= count($all_events) ?></span>
            </div>
            <span class="status-badge status-paid">All Paid - Ready for Approval</span>
        </div>
        <div class="card-body">
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Events (<?= count($all_events) ?>)</button>
                <button class="filter-btn" data-filter="birth">Birth (<?= count($birth_count) ?>)</button>
                <button class="filter-btn" data-filter="marriage">Marriage (<?= count($marriage_count) ?>)</button>
                <button class="filter-btn" data-filter="death">Death (<?= count($death_count) ?>)</button>
                <button class="filter-btn" data-filter="divorce">Divorce (<?= count($divorce_count) ?>)</button>
            </div>

            <?php if (count($all_events) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="eventsTable">
                        <thead>
                            <tr>
                                <th>Event Type</th>
                                <th>Details</th>
                                <th>Date</th>
                                <th>Registered By</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($all_events as $event): ?>
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
                                    <?php if ($event['event_type'] === 'birth'): ?>
                                        <strong><?= htmlspecialchars($event['child_name']) ?></strong><br>
                                        <small class="text-muted">Father: <?= htmlspecialchars($event['father_full_name']) ?></small>
                                    <?php elseif ($event['event_type'] === 'marriage'): ?>
                                        <strong><?= htmlspecialchars($event['husband_name']) ?></strong> &<br>
                                        <strong><?= htmlspecialchars($event['wife_name']) ?></strong>
                                    <?php elseif ($event['event_type'] === 'death'): ?>
                                        <strong><?= htmlspecialchars($event['deceased_name']) ?></strong>
                                    <?php elseif ($event['event_type'] === 'divorce'): ?>
                                        <strong><?= htmlspecialchars($event['husband_name']) ?></strong> &<br>
                                        <strong><?= htmlspecialchars($event['wife_name']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($event['event_type'] === 'birth'): ?>
                                        <?= htmlspecialchars($event['date_of_birth']) ?>
                                    <?php elseif ($event['event_type'] === 'marriage'): ?>
                                        <?= htmlspecialchars($event['marriage_date']) ?>
                                    <?php elseif ($event['event_type'] === 'death'): ?>
                                        <?= htmlspecialchars($event['date_of_death']) ?>
                                    <?php elseif ($event['event_type'] === 'divorce'): ?>
                                        <?= htmlspecialchars($event['divorce_date']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?= htmlspecialchars($event['fullname']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($event['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="approve_id" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="event_type" value="<?= $event['event_type'] ?>">
                                        <button type="submit" class="btn btn-approve">
                                            <i class="fas fa-check-circle me-1"></i>Approve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h4>No Events Pending Approval</h4>
                    <p>All paid events have been approved or there are no new submissions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Add confirmation for approval actions
document.addEventListener('DOMContentLoaded', function() {
    const approveButtons = document.querySelectorAll('.btn-approve');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to approve this event and issue a certificate? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Filter functionality
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