<?php
// Citizen: Feedback
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'citizen') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Language handling
if (isset($_GET['lang'])) {
    $new_lang = ($_GET['lang'] === 'am') ? 'am' : 'en';
    $_SESSION['lang'] = $new_lang;
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation array
$texts = [
    'en' => [
        'page_title'              => 'Citizen Feedback | VERMS',
        'header_title'            => 'Citizen Feedback',
        'header_subtitle'         => 'Share your thoughts to improve our services',
        'my_feedback'             => 'My Feedback',
        'submit_feedback'         => 'Submit Feedback',
        'message'                 => 'Message',
        'submitted_on'            => 'Submitted On',
        'no_feedback'             => 'No Feedback Submitted Yet',
        'no_feedback_message'     => 'Click the button above to share your first feedback.',
        'submit_new'              => 'Submit Feedback',
        'type_feedback'           => 'Type your feedback here...',
        'submit'                  => 'Submit',
        'success_message'         => 'Feedback submitted successfully!',
        'error_empty'             => 'Message cannot be empty.',
        'error_submit'            => 'Error submitting feedback. Please try again.',
    ],
    'am' => [
        'page_title'              => 'የዜግነት አስተያየት | VERMS',
        'header_title'            => 'የዜግነት አስተያየት',
        'header_subtitle'         => 'አገልግሎታችንን ለማሻሻል ሃሳብዎን ያካፍሉ',
        'my_feedback'             => 'የእኔ አስተያየቶች',
        'submit_feedback'         => 'አስተያየት ላክ',
        'message'                 => 'መልእክት',
        'submitted_on'            => 'የተላከበት ቀን',
        'no_feedback'             => 'ገና አስተያየት አልተላከም',
        'no_feedback_message'     => 'ከላይ ያለውን አዝራር ጠቅ በማድረግ የመጀመሪያ አስተያየትዎን ያካፍሉ።',
        'submit_new'              => 'አስተያየት ላክ',
        'type_feedback'           => 'እዚህ አስተያየትዎን ይፃፉ...',
        'submit'                  => 'ላክ',
        'success_message'         => 'አስተያየትዎ በተሳካ ሁኔታ ተልኳል!',
        'error_empty'             => 'መልእክት ባዶ መሆን አይችልም።',
        'error_submit'            => 'አስተያየት በመላክ ላይ ስህተት ተፈጥሯል። እባክዎ እንደገና ይሞክሩ።',
    ]
];

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    
    if ($message) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $message);
        
        if ($stmt->execute()) {
            $_SESSION['feedback_success'] = $texts[$lang]['success_message'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = $texts[$lang]['error_submit'];
        }
        $stmt->close();
    } else {
        $error = $texts[$lang]['error_empty'];
    }
}

// Clear success message after display
if (isset($_SESSION['feedback_success'])) {
    $success = $_SESSION['feedback_success'];
    unset($_SESSION['feedback_success']);
}

// Fetch all feedback by this user
$feedbacks = [];
$stmt = $conn->prepare("SELECT message, submitted_at FROM feedback WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts[$lang]['page_title'] ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Amharic font support -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        [lang="am"] body,
        [lang="am"] h1, [lang="am"] h4, [lang="am"] h5,
        [lang="am"] .btn, [lang="am"] .alert, [lang="am"] .modal-title,
        [lang="am"] .card-header, [lang="am"] .table th {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        .lang-switcher {
            position: fixed;
            top: 4rem;
            right: 1.5rem;
            z-index: 1050;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
        }

        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .card-header {
            background: #fff;
            font-weight: 600;
            border-bottom: 1px solid #eaeaea;
        }

        .table thead th {
            background: #f1f3f9;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .date-badge {
            background: #e9ecef;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- Language Switcher -->
<div class="lang-switcher">
    <div class="btn-group btn-group-sm shadow">
        <a href="?lang=en" class="btn <?= $lang === 'en' ? 'btn-primary' : 'btn-outline-primary' ?>">EN</a>
        <a href="?lang=am" class="btn <?= $lang === 'am' ? 'btn-primary' : 'btn-outline-primary' ?>">አማ</a>
    </div>
</div>

<div class="page-header text-center">
    <h1 class="fw-bold">
        <i class="fas fa-comment-dots me-2"></i><?= $texts[$lang]['header_title'] ?>
    </h1>
    <p class="mb-0"><?= $texts[$lang]['header_subtitle'] ?></p>
</div>

<div class="container mb-5">
    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><?= $texts[$lang]['my_feedback'] ?></h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
            <i class="fas fa-plus-circle me-1"></i><?= $texts[$lang]['submit_feedback'] ?>
        </button>
    </div>

    <!-- Feedback Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (count($feedbacks) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?= $texts[$lang]['message'] ?></th>
                                <th><?= $texts[$lang]['submitted_on'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $fb): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fb['message']) ?></td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="far fa-clock me-1"></i>
                                            <?= date('M j, Y g:i A', strtotime($fb['submitted_at'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-comment-dots"></i>
                    <h5><?= $texts[$lang]['no_feedback'] ?></h5>
                    <p class="mb-0"><?= $texts[$lang]['no_feedback_message'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane me-2"></i><?= $texts[$lang]['submit_new'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <textarea name="message" class="form-control" rows="5" 
                              placeholder="<?= $texts[$lang]['type_feedback'] ?>" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= $texts[$lang]['submit'] ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>