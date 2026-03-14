<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
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
        'page_title'              => 'My Registered Vital Events & Payments',
        'header_title'            => 'Payment Dashboard',
        'header_subtitle'         => 'Manage payments for your vital event certificates',
        'total_events'            => 'Total Events',
        'my_registered_events'    => 'My Registered Events',
        'events_count'            => 'events',
        'no_events'               => 'No Registered Events',
        'no_events_message'       => 'You haven\'t registered any vital events yet. Register events to make payments.',
        'register_new_event'      => 'Register New Event',
        'type'                    => 'Type',
        'name_details'            => 'Name / Details',
        'form_number'             => 'Form Number',
        'registration_id'         => 'Registration ID',
        'status'                  => 'Status',
        'fee'                     => 'Fee',
        'action'                  => 'Action',
        'pending'                 => 'Pending',
        'paid'                    => 'Paid',
        'approved'                => 'Approved',
        'pay'                     => 'Pay',
        'transaction_code'        => 'Transaction Code',
        'already_paid'            => 'Paid',
        'payment_history'         => 'Payment History',
        'payments_count'          => 'payments',
        'date_time'               => 'Date & Time',
        'service_type'            => 'Service Type',
        'event_id'                => 'Event ID',
        'amount'                  => 'Amount',
        'transaction_code_col'    => 'Transaction Code',
        'completed'               => 'Completed',
        'processing'              => 'Processing...',
        'payment_received'        => 'Payment received successfully for',
        'certificate'             => 'certificate.',
        'already_paid_error'      => 'You have already paid for this event.',
        'invalid_event'           => 'Invalid event type.',
        'enter_transaction'       => 'Please enter a valid transaction code.',
        'error_saving'            => 'Error saving payment:',
        'etb'                     => 'ETB',
        'reg'                     => 'Reg:',
    ],
    'am' => [
        'page_title'              => 'የተመዘገቡ የሕይወት ክስተቶችና ክፍያዎች',
        'header_title'            => 'የክፍያ መቆጣጠሪያ ገፅ',
        'header_subtitle'         => 'የሕይወት ክስተት የምስክር ወረቀቶች ክፍያዎችን ያስተዳድሩ',
        'total_events'            => 'ጠቅላላ ክስተቶች',
        'my_registered_events'    => 'የተመዘገቡ ክስተቶች',
        'events_count'            => 'ክስተቶች',
        'no_events'               => 'ምንም የተመዘገቡ ክስተቶች የሉም',
        'no_events_message'       => 'ገና ምንም የሕይወት ክስተት አላስመዘገቡም። ክፍያ ለመፈጸም ክስተቶችን ይመዝግቡ።',
        'register_new_event'      => 'አዲስ ክስተት መዝግብ',
        'type'                    => 'አይነት',
        'name_details'            => 'ስም / ዝርዝሮች',
        'form_number'             => 'የቅጽ ቁጥር',
        'registration_id'         => 'የመዝገብ መለያ',
        'status'                  => 'ሁኔታ',
        'fee'                     => 'ክፍያ',
        'action'                  => 'እርምጃ',
        'pending'                 => 'በመጠባበቅ ላይ',
        'paid'                    => 'ተከፍሏል',
        'approved'                => 'ተፈቅዷል',
        'pay'                     => 'ክፈል',
        'transaction_code'        => 'የግብይት ኮድ',
        'already_paid'            => 'ተከፍሏል',
        'payment_history'         => 'የክፍያ ታሪክ',
        'payments_count'          => 'ክፍያዎች',
        'date_time'               => 'ቀንና ሰዓት',
        'service_type'            => 'የአገልግሎት አይነት',
        'event_id'                => 'የክስተት መለያ',
        'amount'                  => 'መጠን',
        'transaction_code_col'    => 'የግብይት ኮድ',
        'completed'               => 'ተጠናቋል',
        'processing'              => 'በሂደት ላይ...',
        'payment_received'        => 'ክፍያ በተሳካ ሁኔታ ተቀብሏል ለ',
        'certificate'             => 'የምስክር ወረቀት።',
        'already_paid_error'      => 'ለዚህ ክስተት አስቀድመው ክፍያ አድርገዋል።',
        'invalid_event'           => 'ልክ ያልሆነ የክስተት አይነት።',
        'enter_transaction'       => 'እባክዎ ትክክለኛ የግብይት ኮድ ያስገቡ።',
        'error_saving'            => 'ክፍያ በማስቀመጥ ላይ ስህተት፡',
        'etb'                     => 'ብር',
        'reg'                     => 'ተመዝግቧል፡',
    ]
];

// Payment mapping (event type => fee)
$fee_list = [
    'Birth'    => 50,
    'Marriage' => 100,
    'Death'    => 30,
    'Divorce'  => 80
];

$success = $error = '';

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_type'], $_POST['event_id'])) {
    $event_type = $_POST['event_type'];
    $event_id   = intval($_POST['event_id']);
    $transaction_code = trim($_POST['transaction_code']);

    if (!$transaction_code) {
        $error = $texts[$lang]['enter_transaction'];
    } elseif (!isset($fee_list[$event_type])) {
        $error = $texts[$lang]['invalid_event'];
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM payments WHERE event_type = ? AND event_id = ? AND user_id = ?");
        $check_stmt->bind_param("sii", $event_type, $event_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = $texts[$lang]['already_paid_error'];
        } else {
            $service_fee = $fee_list[$event_type];
            $pay_stmt = $conn->prepare("INSERT INTO payments (user_id, event_type, event_id, service_fee, transaction_code, paid_at) 
                                        VALUES (?, ?, ?, ?, ?, NOW())");
            $pay_stmt->bind_param("isids", $user_id, $event_type, $event_id, $service_fee, $transaction_code);

            if ($pay_stmt->execute()) {
                // Update event status to Paid
                $status_update = $conn->prepare("UPDATE {$event_type}_events SET status = 'Paid' WHERE id = ? AND user_id = ?");
                $status_update->bind_param("ii", $event_id, $user_id);
                $status_update->execute();

                $success = $texts[$lang]['payment_received'] . " {$event_type} " . $texts[$lang]['certificate'];
            } else {
                $error = $texts[$lang]['error_saving'] . " " . $pay_stmt->error;
            }
        }
    }
}

// Fetch all events for the citizen
$events = [];

// Births
$result = $conn->query("SELECT 'Birth' AS event_type, id, child_name AS main_name, form_number, registration_uid, status, registered_date 
                        FROM birth_events WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) $events[] = $row;

// Marriages
$result = $conn->query("SELECT 'Marriage' AS event_type, id, CONCAT(husband_name, ' & ', wife_name) AS main_name, form_number, registration_uid, status, registered_date 
                        FROM marriage_events WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) $events[] = $row;

// Deaths
$result = $conn->query("SELECT 'Death' AS event_type, id, deceased_name AS main_name, form_number, registration_uid, status, registered_date 
                        FROM death_events WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) $events[] = $row;

// Divorces
$result = $conn->query("SELECT 'Divorce' AS event_type, id, CONCAT(husband_name, ' & ', wife_name) AS main_name, form_number, registration_uid, status, registered_date 
                        FROM divorce_events WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) $events[] = $row;

// Fetch previous payments
$payments = $conn->query("SELECT * FROM payments WHERE user_id = $user_id ORDER BY paid_at DESC");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts[$lang]['page_title'] ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Amharic font support -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        [lang="am"] body,
        [lang="am"] h1, [lang="am"] h5, [lang="am"] .btn,
        [lang="am"] .card-header, [lang="am"] .status-badge, [lang="am"] .event-badge {
            font-family: 'Noto Sans Ethiopic', system-ui, sans-serif;
        }

        /* Your original styles continue here... */
        .dashboard-header { /* ... */ }
        .lang-switcher {
            position: fixed;
            top: 1rem;
            right: 1.5rem;
            z-index: 1050;
        }
        /* ... keep all your other CSS ... */
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

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-credit-card me-2"></i><?= $texts[$lang]['header_title'] ?>
                </h1>
                <p class="dashboard-subtitle">
                    <?= $texts[$lang]['header_subtitle'] ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="card bg-light" style="display: inline-block; padding: 15px 20px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-wallet fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark"><?= $texts[$lang]['total_events'] ?></h5>
                            <h3 class="mb-0 text-primary"><?= count($events) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Registered Events Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-2"></i><?= $texts[$lang]['my_registered_events'] ?>
                <span class="badge bg-light text-dark ms-2"><?= count($events) ?> <?= $texts[$lang]['events_count'] ?></span>
            </div>
            <div class="d-flex gap-2">
                <span class="status-badge status-pending"><?= $texts[$lang]['pending'] ?></span>
                <span class="status-badge status-paid"><?= $texts[$lang]['paid'] ?></span>
                <span class="status-badge status-approved"><?= $texts[$lang]['approved'] ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($events) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= $texts[$lang]['type'] ?></th>
                                <th><?= $texts[$lang]['name_details'] ?></th>
                                <th><?= $texts[$lang]['form_number'] ?></th>
                                <th><?= $texts[$lang]['registration_id'] ?></th>
                                <th><?= $texts[$lang]['status'] ?></th>
                                <th><?= $texts[$lang]['fee'] ?></th>
                                <th><?= $texts[$lang]['action'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($events as $e):
                                $fee = $fee_list[$e['event_type']];
                                $already_paid = ($e['status'] === 'Paid' || $e['status'] === 'Approved');
                            ?>
                            <tr>
                                <td>
                                    <?php if ($e['event_type'] === 'Birth'): ?>
                                        <span class="event-badge badge-birth"><i class="fas fa-baby me-1"></i><?= $e['event_type'] ?></span>
                                    <?php elseif ($e['event_type'] === 'Marriage'): ?>
                                        <span class="event-badge badge-marriage"><i class="fas fa-ring me-1"></i><?= $e['event_type'] ?></span>
                                    <?php elseif ($e['event_type'] === 'Death'): ?>
                                        <span class="event-badge badge-death"><i class="fas fa-book-dead me-1"></i><?= $e['event_type'] ?></span>
                                    <?php elseif ($e['event_type'] === 'Divorce'): ?>
                                        <span class="event-badge badge-divorce"><i class="fas fa-file-contract me-1"></i><?= $e['event_type'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($e['main_name']) ?></strong><br>
                                    <small class="text-muted"><?= $texts[$lang]['reg'] ?> <?= date('M j, Y', strtotime($e['registered_date'])) ?></small>
                                </td>
                                <td><code><?= htmlspecialchars($e['form_number']) ?></code></td>
                                <td><code><?= htmlspecialchars($e['registration_uid']) ?></code></td>
                                <td>
                                    <?php if ($e['status'] === 'Pending'): ?>
                                        <span class="status-badge status-pending"><?= $texts[$lang]['pending'] ?></span>
                                    <?php elseif ($e['status'] === 'Paid'): ?>
                                        <span class="status-badge status-paid"><?= $texts[$lang]['paid'] ?></span>
                                    <?php elseif ($e['status'] === 'Approved'): ?>
                                        <span class="status-badge status-approved"><?= $texts[$lang]['approved'] ?></span>
                                    <?php else: ?>
                                        <span class="status-badge"><?= $e['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="service-fee"><?= $texts[$lang]['etb'] ?> <?= number_format($fee, 2) ?></td>
                                <td>
                                    <?php if (!$already_paid): ?>
                                        <form method="post" class="payment-form">
                                            <input type="hidden" name="event_type" value="<?= htmlspecialchars($e['event_type']) ?>">
                                            <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                            <input type="text" name="transaction_code" class="form-control transaction-input" 
                                                   placeholder="<?= $texts[$lang]['transaction_code'] ?>" required maxlength="20">
                                            <button type="submit" class="btn btn-pay">
                                                <i class="fas fa-credit-card me-1"></i><?= $texts[$lang]['pay'] ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i><?= $texts[$lang]['already_paid'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4><?= $texts[$lang]['no_events'] ?></h4>
                    <p><?= $texts[$lang]['no_events_message'] ?></p>
                    <a href="../citizen/request_event.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus-circle me-2"></i><?= $texts[$lang]['register_new_event'] ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History Section -->
    <?php if ($payments && $payments->num_rows > 0): ?>
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-history me-2"></i><?= $texts[$lang]['payment_history'] ?>
                    <span class="badge bg-light text-dark ms-2"><?= $payments->num_rows ?> <?= $texts[$lang]['payments_count'] ?></span>
                </div>
                <i class="fas fa-receipt text-muted"></i>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= $texts[$lang]['date_time'] ?></th>
                                <th><?= $texts[$lang]['service_type'] ?></th>
                                <th><?= $texts[$lang]['event_id'] ?></th>
                                <th><?= $texts[$lang]['amount'] ?></th>
                                <th><?= $texts[$lang]['transaction_code_col'] ?></th>
                                <th><?= $texts[$lang]['status'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $payments->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($row['paid_at'])) ?></strong><br>
                                    <small class="text-muted"><?= date('g:i A', strtotime($row['paid_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($row['event_type'] === 'Birth'): ?>
                                        <span class="event-badge badge-birth"><i class="fas fa-baby me-1"></i><?= $row['event_type'] ?></span>
                                    <?php elseif ($row['event_type'] === 'Marriage'): ?>
                                        <span class="event-badge badge-marriage"><i class="fas fa-ring me-1"></i><?= $row['event_type'] ?></span>
                                    <?php elseif ($row['event_type'] === 'Death'): ?>
                                        <span class="event-badge badge-death"><i class="fas fa-book-dead me-1"></i><?= $row['event_type'] ?></span>
                                    <?php elseif ($row['event_type'] === 'Divorce'): ?>
                                        <span class="event-badge badge-divorce"><i class="fas fa-file-contract me-1"></i><?= $row['event_type'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><code>#<?= $row['event_id'] ?></code></td>
                                <td class="service-fee"><?= $texts[$lang]['etb'] ?> <?= number_format($row['service_fee'], 2) ?></td>
                                <td><code><?= htmlspecialchars($row['transaction_code']) ?></code></td>
                                <td>
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check me-1"></i><?= $texts[$lang]['completed'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form submission UX + transaction code formatting
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.payment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><?= $texts[$lang]['processing'] ?>';
        });
    });

    document.querySelectorAll('.transaction-input').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    });
});
</script>
</body>
</html>