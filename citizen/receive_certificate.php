<?php
// Citizen: Receive Certificate (complete, now includes Divorce)
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'citizen') {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Approved certificates for this citizen
$certs = $conn->query("SELECT * FROM birth_events WHERE user_id = $user_id AND status = 'Approved' ORDER BY issue_date DESC, id DESC");
$marriage_certs = $conn->query("SELECT * FROM marriage_events WHERE user_id = $user_id AND status = 'Approved' ORDER BY issue_date DESC, id DESC");
$death_certs = $conn->query("SELECT * FROM death_events WHERE user_id = $user_id AND status = 'Approved' ORDER BY issue_date DESC, id DESC");
$divorce_certs = $conn->query("SELECT * FROM divorce_events WHERE user_id = $user_id AND status = 'Approved' ORDER BY issue_date DESC, id DESC");

// Count total certificates
$total_certificates = $certs->num_rows + $marriage_certs->num_rows + $death_certs->num_rows + $divorce_certs->num_rows;
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>My Certificates - VERMS</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
        }
        
        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        
        .certificate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .certificate-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .certificate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }
        
        .certificate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .certificate-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .certificate-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .certificate-details {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .certificate-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        
        .certificate-badge {
            background: var(--light);
            color: var(--dark);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .btn-modern {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-modern:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-modern-outline {
            background: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-modern-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-1px);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        
        .filter-tabs {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        
        .nav-modern {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .nav-modern .nav-link {
            background: var(--light);
            color: var(--secondary);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-modern .nav-link.active {
            background: var(--primary);
            color: white;
        }
        
        .nav-modern .nav-link:hover:not(.active) {
            background: #e2e8f0;
            color: var(--dark);
        }
        
        .badge-count {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        
        .table-modern {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        
        .table-modern thead {
            background: var(--light);
        }
        
        .table-modern th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-modern td {
            padding: 1rem;
            border-color: var(--border);
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-approved {
            background: #dcfce7;
            color: #166534;
        }
        
        @media (max-width: 768px) {
            .certificate-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 2rem 0;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .nav-modern {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">My Certificates</h1>
                <p class="lead mb-0 opacity-90">Manage and access all your vital event certificates in one place</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_certificates ?></div>
                    <div class="text-muted">Total Certificates</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Quick Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?= $certs->num_rows ?></div>
                <div class="text-muted">Birth Certificates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?= $marriage_certs->num_rows ?></div>
                <div class="text-muted">Marriage Certificates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-secondary"><?= $death_certs->num_rows ?></div>
                <div class="text-muted">Death Certificates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger"><?= $divorce_certs->num_rows ?></div>
                <div class="text-muted">Divorce Certificates</div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <ul class="nav nav-modern" id="certificateTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    <i class="fas fa-layer-group me-2"></i>All Certificates
                    <span class="badge-count"><?= $total_certificates ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="birth-tab" data-bs-toggle="tab" data-bs-target="#birth" type="button">
                    <i class="fas fa-baby me-2"></i>Birth
                    <span class="badge-count"><?= $certs->num_rows ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="marriage-tab" data-bs-toggle="tab" data-bs-target="#marriage" type="button">
                    <i class="fas fa-ring me-2"></i>Marriage
                    <span class="badge-count"><?= $marriage_certs->num_rows ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="death-tab" data-bs-toggle="tab" data-bs-target="#death" type="button">
                    <i class="fas fa-cross me-2"></i>Death
                    <span class="badge-count"><?= $death_certs->num_rows ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="divorce-tab" data-bs-toggle="tab" data-bs-target="#divorce" type="button">
                    <i class="fas fa-file-contract me-2"></i>Divorce
                    <span class="badge-count"><?= $divorce_certs->num_rows ?></span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- All Certificates -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <?php if ($total_certificates > 0): ?>
                <div class="certificate-grid">
                    <!-- Birth Certificates -->
                    <?php while($row = $certs->fetch_assoc()): ?>
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fas fa-baby"></i>
                            </div>
                            <div class="certificate-title">Birth Certificate</div>
                            <div class="certificate-details">
                                <strong><?= htmlspecialchars($row['child_name']) ?></strong><br>
                                Born: <?= htmlspecialchars($row['date_of_birth']) ?>
                            </div>
                            <div class="certificate-meta">
                                <span class="certificate-badge"><?= htmlspecialchars($row['form_number']) ?></span>
                                <a href="../kebele_officer/print_certificate.php?event=birth&id=<?= $row['id'] ?>" 
                                   class="btn-modern-outline btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Marriage Certificates -->
                    <?php while($row = $marriage_certs->fetch_assoc()): ?>
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fas fa-ring"></i>
                            </div>
                            <div class="certificate-title">Marriage Certificate</div>
                            <div class="certificate-details">
                                <strong><?= htmlspecialchars($row['husband_name']) ?></strong> &<br>
                                <?= htmlspecialchars($row['wife_name']) ?>
                            </div>
                            <div class="certificate-meta">
                                <span class="certificate-badge"><?= htmlspecialchars($row['form_number']) ?></span>
                                <a href="../kebele_officer/print_certificate.php?event=marriage&id=<?= $row['id'] ?>" 
                                   class="btn-modern-outline btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Death Certificates -->
                    <?php while($row = $death_certs->fetch_assoc()): ?>
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fas fa-cross"></i>
                            </div>
                            <div class="certificate-title">Death Certificate</div>
                            <div class="certificate-details">
                                <strong><?= htmlspecialchars($row['deceased_name']) ?></strong><br>
                                Died: <?= htmlspecialchars($row['date_of_death']) ?>
                            </div>
                            <div class="certificate-meta">
                                <span class="certificate-badge"><?= htmlspecialchars($row['form_number']) ?></span>
                                <a href="../kebele_officer/print_certificate.php?event=death&id=<?= $row['id'] ?>" 
                                   class="btn-modern-outline btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Divorce Certificates -->
                    <?php while($row = $divorce_certs->fetch_assoc()): ?>
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div class="certificate-title">Divorce Certificate</div>
                            <div class="certificate-details">
                                <strong><?= htmlspecialchars($row['husband_name']) ?></strong> &<br>
                                <?= htmlspecialchars($row['wife_name']) ?>
                            </div>
                            <div class="certificate-meta">
                                <span class="certificate-badge"><?= htmlspecialchars($row['form_number']) ?></span>
                                <a href="../kebele_officer/print_certificate.php?event=divorce&id=<?= $row['id'] ?>" 
                                   class="btn-modern-outline btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No Certificates Yet</h4>
                    <p>You don't have any approved certificates. Once your applications are approved, they will appear here.</p>
                    <a href="../citizen/apply_certificate.php" class="btn-modern mt-3">
                        <i class="fas fa-plus me-2"></i>Apply for Certificate
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Individual Certificate Type Views -->
        <?php 
        $cert_types = [
            'birth' => ['icon' => 'fa-baby', 'data' => $certs, 'name_field' => 'child_name', 'date_field' => 'date_of_birth'],
            'marriage' => ['icon' => 'fa-ring', 'data' => $marriage_certs, 'name_field' => 'husband_name', 'secondary_name' => 'wife_name'],
            'death' => ['icon' => 'fa-cross', 'data' => $death_certs, 'name_field' => 'deceased_name', 'date_field' => 'date_of_death'],
            'divorce' => ['icon' => 'fa-file-contract', 'data' => $divorce_certs, 'name_field' => 'husband_name', 'secondary_name' => 'wife_name', 'date_field' => 'divorce_date']
        ];
        
        foreach ($cert_types as $type => $config): 
            $data = $config['data'];
            // Reset pointer for each data set since we already iterated through them
            if ($type == 'birth') $data->data_seek(0);
            if ($type == 'marriage') $data->data_seek(0);
            if ($type == 'death') $data->data_seek(0);
            if ($type == 'divorce') $data->data_seek(0);
        ?>
            <div class="tab-pane fade" id="<?= $type ?>" role="tabpanel">
                <?php if ($data->num_rows > 0): ?>
                    <div class="certificate-grid">
                        <?php while($row = $data->fetch_assoc()): ?>
                            <div class="certificate-card">
                                <div class="certificate-icon">
                                    <i class="fas <?= $config['icon'] ?>"></i>
                                </div>
                                <div class="certificate-title">
                                    <?= ucfirst($type) ?> Certificate
                                </div>
                                <div class="certificate-details">
                                    <strong><?= htmlspecialchars($row[$config['name_field']]) ?></strong>
                                    <?php if (isset($config['secondary_name'])): ?>
                                        <br>& <?= htmlspecialchars($row[$config['secondary_name']]) ?>
                                    <?php endif; ?>
                                    <?php if (isset($config['date_field'])): ?>
                                        <br><?= ucfirst(str_replace('_', ' ', $config['date_field'])) ?>: <?= htmlspecialchars($row[$config['date_field']]) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="certificate-meta">
                                    <span class="certificate-badge"><?= htmlspecialchars($row['form_number']) ?></span>
                                    <span class="status-badge status-approved">Approved</span>
                                    <a href="../kebele_officer/print_certificate.php?event=<?= $type ?>&id=<?= $row['id'] ?>" 
                                       class="btn-modern-outline btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas <?= $config['icon'] ?>"></i>
                        <h4>No <?= ucfirst($type) ?> Certificates</h4>
                        <p>You don't have any approved <?= $type ?> certificates yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script>
    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        // Animate cards on load
        const cards = document.querySelectorAll('.certificate-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Add loading state for tabs
        const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = document.querySelector(this.getAttribute('data-bs-target'));
                const cards = target.querySelectorAll('.certificate-card');
                
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            });
        });
    });
</script>
</body>
</html>