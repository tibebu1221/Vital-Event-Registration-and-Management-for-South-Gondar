<?php
// Kebele Officer: View Feedback in Table with Popup View
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

// Initialize variables with default values
$feedbacks = null;
$feedback_count = 0;

// Get all feedback entries with user info
try {
    $result = $conn->query("
        SELECT f.*, u.fullname, u.email, u.phone
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.id
        ORDER BY f.submitted_at DESC
    ");
    
    if ($result) {
        $feedbacks = $result;
        $feedback_count = $feedbacks->num_rows;
    } else {
        // Handle query error
        $feedbacks = null;
        $feedback_count = 0;
    }
} catch (Exception $e) {
    // Handle any exceptions
    $feedbacks = null;
    $feedback_count = 0;
    error_log("Database error: " . $e->getMessage());
}

// For popup detail
$view_id = intval($_GET['view_id'] ?? 0);
$detail = null;
if ($view_id) {
    try {
        $detail_stmt = $conn->prepare("
            SELECT f.*, u.fullname, u.email, u.phone
            FROM feedback f
            LEFT JOIN users u ON f.user_id = u.id
            WHERE f.id = ?
            LIMIT 1
        ");
        if ($detail_stmt) {
            $detail_stmt->bind_param('i', $view_id);
            $detail_stmt->execute();
            $detail_result = $detail_stmt->get_result();
            $detail = $detail_result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Detail query error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>View Feedback - Kebele Management System</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #1abc9c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 500;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .btn-view {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.25rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .feedback-message {
            background-color: #f8f9fa;
            border-left: 4px solid var(--accent-color);
            padding: 1rem;
            border-radius: 0 6px 6px 0;
            margin-top: 0.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .badge-new {
            background-color: var(--accent-color);
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
            }
            
            .stats-card {
                text-align: center;
            }
            
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .d-flex.justify-content-between.align-items-center > div:last-child {
                margin-top: 1rem;
                width: 100%;
            }
            
            .input-group {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class='container my-5'>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class='page-title'>Citizen Feedback</h1>
            <p class="text-muted">Review and manage feedback submitted by citizens</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="input-group me-3" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" placeholder="Search feedback..." id="searchInput">
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="#">All Feedback</a></li>
                    <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Unread</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Feedback Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>All Feedback</h5>
            <span class="badge bg-primary rounded-pill"><?= $feedback_count ?> entries</span>
        </div>
        <div class="card-body p-0">
            <?php if ($feedbacks && $feedback_count > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="feedbackTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Citizen</th>
                                <th width="20%">Contact</th>
                                <th width="15%">Date</th>
                                <th width="10%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i=1; while($row = $feedbacks->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?= substr(htmlspecialchars($row['fullname'] ?? 'U'), 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($row['fullname'] ?? 'Unknown') ?></div>
                                            <small class="text-muted">ID: <?= $row['user_id'] ?? 'N/A' ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($row['email']) ?></div>
                                    <div><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= date('M d, Y', strtotime($row['submitted_at'])) ?></div>
                                    <small class="text-muted"><?= date('H:i A', strtotime($row['submitted_at'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                </td>
                                <td>
                                    <a href="?view_id=<?= $row['id'] ?>" class="btn btn-view btn-sm">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <h4>No Feedback Yet</h4>
                    <p class="text-muted">No citizens have submitted feedback at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feedback Detail Modal -->
<?php if ($detail): ?>
<div class="modal fade show" tabindex="-1" role="dialog" style="display:block; background: rgba(0,0,0,0.6);">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <!-- Header -->
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-comment-dots me-2"></i> Feedback Details
        </h5>
        <a href="view_feedback.php" class="btn-close btn-close-white"></a>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body">
            <!-- Citizen Info -->
            <div class="d-flex align-items-center mb-4">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                   style="width: 70px; height: 70px; font-size: 1.8rem;">
                <?= substr(htmlspecialchars($detail['fullname'] ?? 'U'), 0, 1) ?>
              </div>
              <div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($detail['fullname'] ?? 'Unknown') ?></h4>
                <small class="text-muted d-block">User ID: <?= htmlspecialchars($detail['user_id'] ?? 'N/A') ?></small>
                <small class="text-muted d-block">
                  <i class="fas fa-envelope me-2 text-primary"></i><?= htmlspecialchars($detail['email']) ?>
                </small>
                <small class="text-muted d-block">
                  <i class="fas fa-phone me-2 text-primary"></i><?= htmlspecialchars($detail['phone']) ?>
                </small>
              </div>
            </div>

            <hr>

            <!-- Feedback Message -->
            <div class="mb-3">
              <h6 class="fw-semibold text-muted mb-2">
                <i class="fas fa-message me-2"></i> Feedback Message
              </h6>
              <div class="bg-white border rounded-3 p-3 text-secondary" style="min-height: 120px;">
                <?= nl2br(htmlspecialchars($detail['message'])) ?>
              </div>
            </div>

            <!-- Submitted Date -->
            <div class="text-end">
              <small class="text-muted">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                Submitted on <?= date('M d, Y', strtotime($detail['submitted_at'])) ?> at <?= date('h:i A', strtotime($detail['submitted_at'])) ?>
              </small>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  // Disable background scrolling while modal is open
  document.body.style.overflow = 'hidden';
</script>
<?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
    // Simple search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#feedbackTable tbody tr');
        
        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>