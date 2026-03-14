<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'woreda') {
    header('Location: ../login.php');
    exit();
}

$officer_id = $_SESSION['user_id'];
$woreda = $conn->query("SELECT id, woreda_name FROM woredas WHERE woreda_officer_id = $officer_id")->fetch_assoc();
if (!$woreda) die("Woreda not found.");

$woreda_id = $woreda['id'];
$woreda_name = $woreda['woreda_name'];

$success = $error = "";

// Handle adding a new notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file_name = "";

    // File upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/notices/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($file_tmp, $upload_dir . $file_name);
    }

    if (empty($title)) $error = "Title is required.";

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO notices (woreda_id, title, description, file_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $woreda_id, $title, $description, $file_name);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Notice added successfully!";
            header("Location: woreda_notices.php");
            exit();
        } else {
            $error = "Error adding notice: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle editing a notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $notice_id = intval($_POST['notice_id']);
    $title = trim($_POST['edit_title']);
    $description = trim($_POST['edit_description']);

    // Get current file
    $notice = $conn->query("SELECT file_name FROM notices WHERE id = $notice_id AND woreda_id = $woreda_id")->fetch_assoc();
    $file_name = $notice['file_name'] ?? "";

    // Handle new file upload
    if (isset($_FILES['edit_file']) && $_FILES['edit_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/notices/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_tmp = $_FILES['edit_file']['tmp_name'];
        $new_file_name = time() . '_' . basename($_FILES['edit_file']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            // Remove old file if exists
            if (!empty($file_name) && file_exists($upload_dir . $file_name)) {
                unlink($upload_dir . $file_name);
            }
            $file_name = $new_file_name;
        }
    }

    if (empty($title)) $error = "Title is required.";

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE notices SET title=?, description=?, file_name=? WHERE id=? AND woreda_id=?");
        $stmt->bind_param("sssii", $title, $description, $file_name, $notice_id, $woreda_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Notice updated successfully!";
            header("Location: woreda_notices.php");
            exit();
        } else {
            $error = "Error updating notice: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle deleting a notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $notice_id = intval($_POST['notice_id']);

    // Get file name to delete
    $notice = $conn->query("SELECT file_name FROM notices WHERE id = $notice_id AND woreda_id = $woreda_id")->fetch_assoc();
    $file_name = $notice['file_name'] ?? "";
    $upload_dir = '../images/notices/';
    if (!empty($file_name) && file_exists($upload_dir . $file_name)) {
        unlink($upload_dir . $file_name);
    }

    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ? AND woreda_id = ?");
    $stmt->bind_param("ii", $notice_id, $woreda_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Notice deleted successfully!";
        header("Location: woreda_notices.php");
        exit();
    } else {
        $error = "Error deleting notice: " . $conn->error;
    }
    $stmt->close();
}

// Success message
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch notices
$notices = $conn->query("SELECT * FROM notices WHERE woreda_id = $woreda_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Woreda Notices - <?= htmlspecialchars($woreda_name) ?></title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --success: #4cc9f0;
        --info: #4895ef;
        --warning: #f8961e;
        --danger: #e63946;
        --light: #f8f9fa;
        --dark: #212529;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #eef1f5 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        min-height: 100vh;
    }
    
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        padding: 2rem 0;
        border-radius: 0 0 30px 30px;
        box-shadow: 0 6px 24px rgba(44,62,80,0.15);
        margin-bottom: 2rem;
    }
    
    .card-custom {
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        border: none;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .card-custom:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }
    
    .table-container:hover {
        box-shadow: var(--hover-shadow);
    }
    
    .table thead th {
        background: linear-gradient(to right, var(--primary), var(--secondary));
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
    }
    
    .table td {
        vertical-align: middle;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .btn-primary-custom {
        background: linear-gradient(to right, var(--primary), var(--secondary));
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        color: white;
    }
    
    .btn-success-custom {
        background: linear-gradient(to right, #4cc9f0, #4895ef);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-success-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 201, 240, 0.3);
        color: white;
    }
    
    .btn-warning-custom {
        background: linear-gradient(to right, var(--warning), #f3722c);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-warning-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(248, 150, 30, 0.3);
        color: white;
    }
    
    .btn-danger-custom {
        background: linear-gradient(to right, var(--danger), #d00000);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-danger-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
        color: white;
    }
    
    .btn-outline-info-custom {
        border: 1px solid var(--info);
        color: var(--info);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-outline-info-custom:hover {
        background-color: var(--info);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(72, 149, 239, 0.3);
    }
    
    .modal-header-custom {
        background: linear-gradient(to right, var(--primary), var(--secondary));
        color: white;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header-warning {
        background: linear-gradient(to right, var(--warning), #f3722c);
        color: white;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header-danger {
        background: linear-gradient(to right, var(--danger), #d00000);
        color: white;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: var(--hover-shadow);
    }
    
    .alert-custom {
        border-radius: 12px;
        border: none;
        box-shadow: var(--card-shadow);
    }
    
    .section-title {
        position: relative;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        color: var(--primary);
        font-weight: 600;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(to right, var(--primary), var(--info));
        border-radius: 3px;
    }
    
    .notice-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary);
    }
    
    .notice-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--hover-shadow);
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem 0;
            border-radius: 0 0 20px 20px;
        }
        
        .table-container {
            padding: 1rem;
        }
        
        .table thead th, .table tbody td {
            padding: 0.75rem;
        }
        
        .btn-primary-custom, .btn-success-custom {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
    }
</style>
</head>
<body>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class='page-header'>
    <div class='container'>
        <div class='row align-items-center'>
            <div class='col-md-8'>
                <h1><i class='fas fa-bullhorn me-2'></i>Woreda Notices</h1>
                <h4 class='mb-0'><?= htmlspecialchars($woreda_name) ?></h4>
            </div>
            <div class='col-md-4 text-md-end'>
                <button class='btn btn-success-custom' data-bs-toggle='modal' data-bs-target='#addNoticeModal'>
                    <i class="fas fa-plus-circle me-2"></i> Add New Notice
                </button>
            </div>
        </div>
    </div>
</div>

<div class='container my-4'>
    <!-- Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Notices Display -->
    <?php if ($notices && $notices->num_rows > 0): ?>
    <div class="table-container">
        <h5 class="section-title"><i class="fas fa-list me-2"></i> All Notices</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Title</th>
                        <th class="text-center">Date Posted</th>
                        <th class="text-center">File</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while($row = $notices->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $count++; ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['title']); ?></div>
                            <?php if (!empty($row['description'])): ?>
                                <small class="text-muted"><?= htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : '') ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="fw-medium"><?= date('M d, Y', strtotime($row['created_at'])); ?></div>
                            <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($row['file_name'])): ?>
                                <a href="../images/notices/<?= urlencode($row['file_name']) ?>" target="_blank" class="btn btn-outline-info-custom btn-sm">
                                    <i class="fas fa-file-download me-1"></i> Download
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-warning-custom btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editNoticeModal<?= $row['id'] ?>">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button class="btn btn-danger-custom btn-sm" data-bs-toggle="modal" data-bs-target="#deleteNoticeModal<?= $row['id'] ?>">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editNoticeModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editNoticeModalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="notice_id" value="<?= $row['id'] ?>">
                            <div class="modal-header modal-header-warning">
                              <h5 class="modal-title" id="editNoticeModalLabel<?= $row['id'] ?>"><i class="fas fa-edit me-2"></i> Edit Notice</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="edit_title" class="form-control" value="<?= htmlspecialchars($row['title']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="edit_description" rows="4" class="form-control"><?= htmlspecialchars($row['description']) ?></textarea>
                              </div>
                              <div class="mb-3">
                                <label class="form-label fw-semibold">Attach New File (optional, replaces old)</label>
                                <input type="file" name="edit_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                                <?php if (!empty($row['file_name'])): ?>
                                    <div class="mt-2">
                                      <small class="text-muted">Current file: <a href="../images/notices/<?= urlencode($row['file_name']) ?>" target="_blank"><?= htmlspecialchars($row['file_name']) ?></a></small>
                                    </div>
                                <?php endif; ?>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-success-custom">Save Changes</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <!-- End Edit Modal -->

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteNoticeModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteNoticeModalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="notice_id" value="<?= $row['id'] ?>">
                            <div class="modal-header modal-header-danger">
                              <h5 class="modal-title" id="deleteNoticeModalLabel<?= $row['id'] ?>"><i class="fas fa-trash me-2"></i> Delete Notice</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p>Are you sure you want to <strong class="text-danger">delete</strong> the notice:</p>
                              <div class="alert alert-light border">
                                <h6 class="mb-1"><?= htmlspecialchars($row['title']) ?></h6>
                                <small class="text-muted">Posted: <?= date('M d, Y H:i', strtotime($row['created_at'])) ?></small>
                              </div>
                              <?php if (!empty($row['file_name'])): ?>
                                <p class="mb-0"><small class="text-muted">File <a href="../images/notices/<?= urlencode($row['file_name']) ?>" target="_blank"><?= htmlspecialchars($row['file_name']) ?></a> will also be removed.</small></p>
                              <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-danger-custom">Delete Notice</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <!-- End Delete Modal -->

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-info alert-custom text-center py-4">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h5>No Notices Available</h5>
            <p class="mb-0">You haven't posted any notices yet. Click the "Add New Notice" button to create your first notice.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Notice Modal -->
<div class="modal fade" id="addNoticeModal" tabindex="-1" aria-labelledby="addNoticeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-header modal-header-custom">
          <h5 class="modal-title" id="addNoticeModalLabel"><i class="fas fa-plus-circle me-2"></i> Add New Notice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="Enter notice title" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="4" class="form-control" placeholder="Enter notice description (optional)"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Attach File (optional)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
            <div class="form-text">Supported formats: PDF, DOC, DOCX, JPG, PNG, JPEG</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success-custom">Post Notice</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>