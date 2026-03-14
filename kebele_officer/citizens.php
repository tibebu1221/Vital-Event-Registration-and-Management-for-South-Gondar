<?php
// List citizens in this kebele
session_start();
require_once '../includes/db_connection.php';

// Check that the user is a kebele officer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kebele') {
    header('Location: ../login.php');
    exit();
}

$officer_id = $_SESSION['user_id'];

// Securely get kebele name for this officer
$stmt = $conn->prepare("SELECT kebele_name FROM kebeles WHERE kebele_officer_id = ?");
$stmt->bind_param("i", $officer_id);
$stmt->execute();
$kebele = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kebele) {
    die('Kebele not found for this officer.');
}

// Use the correct kebele name column
$kebele_name = $kebele['kebele_name'];

// Securely get citizens for this kebele
$stmt = $conn->prepare("SELECT fullname, phone, email, created_at FROM users WHERE role = 'citizen' AND kebele = ? ORDER BY id DESC");
$stmt->bind_param("s", $kebele_name);
$stmt->execute();
$citizens = $stmt->get_result();
$stmt->close();

// Statistics
$total_citizens = $citizens->num_rows;
$today_citizens = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'citizen' AND kebele = '$kebele_name' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$month_citizens = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'citizen' AND kebele = '$kebele_name' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Citizens Management - Kebele Officer | VERMS</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --citizen-color: #2980b9;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--citizen-color) 0%, #3498db 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--card-shadow);
        }

        .stat-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--citizen-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stat-card.total { border-left-color: var(--citizen-color); }
        .stat-card.today { border-left-color: var(--success-color); }
        .stat-card.month { border-left-color: var(--info-color); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }

        .main-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--citizen-color) 0%, #3498db 100%);
            color: white;
            padding: 1.5rem;
            border-bottom: none;
        }

        .table-custom {
            margin-bottom: 0;
        }

        .table-custom thead th {
            background: var(--light-bg);
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: var(--primary-color);
            padding: 1rem;
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f3f4;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-export {
            background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
            color: white;
        }

        .btn-print {
            background: linear-gradient(135deg, var(--info-color) 0%, #3498db 100%);
            color: white;
        }

        .btn-export:hover, .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .filter-section {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 3rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .citizen-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--citizen-color) 0%, #3498db 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .contact-info i {
            width: 16px;
            color: #6c757d;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-section, .print-section * {
                visibility: visible;
            }
            
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .table-print {
                width: 100%;
                border-collapse: collapse;
            }
            
            .table-print th, .table-print td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            
            .table-print th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            
            .print-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #333;
                padding-bottom: 10px;
            }
            
            .print-footer {
                text-align: center;
                margin-top: 20px;
                font-size: 0.8rem;
                color: #666;
            }
        }

        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }
            
            .table-custom {
                font-size: 0.9rem;
            }
            
            .btn-custom {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
            }
            
            .contact-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="fas fa-users me-3"></i>Citizens Management
                </h1>
                <p class="lead mb-0">Manage and monitor citizens in <strong><?= htmlspecialchars($kebele_name) ?></strong> kebele</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card total">
                <div class="stat-icon text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number text-primary"><?= $total_citizens ?></div>
                <div class="stat-label">Total Citizens</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card today">
                <div class="stat-icon text-success">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-number text-success"><?= $today_citizens ?></div>
                <div class="stat-label">Registered Today</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card month">
                <div class="stat-icon text-info">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number text-info"><?= $month_citizens ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filter-section no-print">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search citizens by name, phone, or email..." id="searchInput">
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" id="sortFilter">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name_asc">Name A-Z</option>
                            <option value="name_desc">Name Z-A</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-print btn-custom w-100" onclick="printCitizens()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="main-card">
        <div class="card-header-custom no-print">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-list me-2"></i>Citizens List
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-light text-dark fs-6">Total: <?= $total_citizens ?> citizens</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($citizens->num_rows > 0): ?>
                <!-- Printable Section -->
                <div class="print-section" id="printSection" style="display: none;">
                    <div class="print-header">
                        <h2>Citizens List - <?= htmlspecialchars($kebele_name) ?> Kebele</h2>
                        <p>Generated on: <?= date('F j, Y') ?></p>
                    </div>
                    <table class="table-print">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $citizens->data_seek(0); // Reset pointer to beginning
                            while ($row = $citizens->fetch_assoc()):
                                $registration_date = date('M j, Y', strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= $registration_date ?></td>
                            </tr>
                            <?php $i++; endwhile; ?>
                        </tbody>
                    </table>
                    <div class="print-footer">
                        <p>Total Citizens: <?= $total_citizens ?> | Printed by: <?= $_SESSION['fullname'] ?? 'Kebele Officer' ?></p>
                    </div>
                </div>

                <!-- Regular Display Table -->
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Citizen</th>
                                <th>Contact Information</th>
                                <th>Registration Date</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $citizens->data_seek(0); // Reset pointer to beginning
                            while ($row = $citizens->fetch_assoc()):
                                $initials = getInitials($row['fullname']);
                                $registration_date = date('M j, Y', strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $i ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="citizen-avatar me-3">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($row['fullname']) ?></strong>
                                            <br>
                                            <small class="text-muted">Citizen ID: C<?= str_pad($i, 4, '0', STR_PAD_LEFT) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <i class="fas fa-phone"></i>
                                        <span><?= htmlspecialchars($row['phone']) ?></span>
                                    </div>
                                    <div class="contact-info">
                                        <i class="fas fa-envelope"></i>
                                        <span><?= htmlspecialchars($row['email']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    <?= $registration_date ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('g:i A', strtotime($row['created_at'])) ?>
                                    </small>
                                </td>
                                <td class="no-print">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewCitizenDetails('<?= htmlspecialchars($row['fullname']) ?>', '<?= htmlspecialchars($row['phone']) ?>', '<?= htmlspecialchars($row['email']) ?>')">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php $i++; endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h4 class="text-muted">No Citizens Found</h4>
                    <p class="text-muted">There are no registered citizens in your kebele yet.</p>
                    <button class="btn btn-primary btn-custom mt-3">
                        <i class="fas fa-user-plus me-2"></i>Add First Citizen
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Function to get initials from full name
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Sort functionality
    document.getElementById('sortFilter').addEventListener('change', function(e) {
        // This would typically make an AJAX call to re-sort the data
        // For now, we'll just show a message
        const sortValue = e.target.value;
        alert('Sorting by: ' + e.target.options[e.target.selectedIndex].text);
    });

    // Export functionality
    function exportCitizens() {
        // This would typically generate and download an Excel/PDF file
        alert('Exporting citizens list...');
    }

    // Print functionality
    function printCitizens() {
        // Show the print section
        const printSection = document.getElementById('printSection');
        printSection.style.display = 'block';
        
        // Print the document
        window.print();
        
        // Hide the print section after printing
        setTimeout(() => {
            printSection.style.display = 'none';
        }, 500);
    }

    // View citizen details
    function viewCitizenDetails(name, phone, email) {
        const modalContent = `
            <div class="modal fade" id="citizenModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-user me-2"></i>Citizen Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="citizen-avatar mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    ${getInitials(name)}
                                </div>
                                <h4>${name}</h4>
                                <p class="text-muted">Registered Citizen</p>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <p class="form-control-plaintext">${phone}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <p class="form-control-plaintext">${email}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('citizenModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', modalContent);
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('citizenModal'));
        modal.show();
    }

    // Helper function to get initials
    function getInitials(name) {
        const words = name.split(' ');
        let initials = '';
        words.forEach(word => {
            initials += word.charAt(0).toUpperCase();
        });
        return initials.substring(0, 2);
    }

    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>
</body>
</html>