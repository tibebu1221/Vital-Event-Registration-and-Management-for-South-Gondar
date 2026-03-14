<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit();
}

$event_type = $_GET['event'] ?? '';
$event_id = intval($_GET['id'] ?? 0);
$event = null;
$certificate_type = '';

$queries = [
    'birth' => [
        'sql' => "SELECT b.*, u.fullname AS citizen_name, k.kebele_name 
                  FROM birth_events b 
                  JOIN users u ON b.user_id = u.id 
                  LEFT JOIN kebeles k ON b.place_of_birth = k.id 
                  WHERE b.id = ? AND b.status = 'Approved'",
        'type' => 'birth'
    ],
    'marriage' => [
        'sql' => "SELECT m.*, u.fullname AS citizen_name 
                  FROM marriage_events m 
                  JOIN users u ON m.user_id = u.id 
                  WHERE m.id = ? AND m.status = 'Approved'",
        'type' => 'marriage'
    ],
    'death' => [
        'sql' => "SELECT d.*, u.fullname AS citizen_name, k.kebele_name 
                  FROM death_events d 
                  JOIN users u ON d.user_id = u.id 
                  LEFT JOIN kebeles k ON d.place_of_death = k.id 
                  WHERE d.id = ? AND d.status = 'Approved'",
        'type' => 'death'
    ],
    'divorce' => [
        'sql' => "SELECT dv.*, u.fullname AS citizen_name, k.kebele_name 
                  FROM divorce_events dv 
                  JOIN users u ON dv.user_id = u.id 
                  LEFT JOIN kebeles k ON dv.place_of_divorce = k.id 
                  WHERE dv.id = ? AND dv.status = 'Approved'",
        'type' => 'divorce'
    ]
];

if (isset($queries[$event_type]) && $event_id) {
    $stmt = $conn->prepare($queries[$event_type]['sql']);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $certificate_type = $queries[$event_type]['type'];
}

// Generate QR code data
$qr_data = "";
if ($event && $certificate_type) {
    $qr_data = "VERMS Certificate\n";
    $qr_data .= "Type: " . ucfirst($certificate_type) . "\n";
    $qr_data .= "Certificate No: " . ($event['form_number'] ?? '') . "\n";
    $qr_data .= "Registration UID: " . ($event['registration_uid'] ?? '') . "\n";
    $qr_data .= "Issued Date: " . ($event['issue_date'] ?? '') . "\n";
    $qr_data .= "Federal Democratic Republic of Ethiopia";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        $titles = [
            'marriage' => 'Marriage Certificate',
            'death' => 'Death Certificate',
            'divorce' => 'Divorce Certificate',
            'birth' => 'Birth Certificate'
        ];
        echo $certificate_type ? $titles[$certificate_type] : 'Certificate';
        ?>
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background: #f4f4f9;
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
        }
        .certificate-container {
            width: 210mm; /* A4 width */
            height: 297mm; /* A4 height */
            margin: 20px auto;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
        }
        .certificate-box {
            border: 10px double #1a237e;
            padding: 30px;
            height: 100%;
            background: #fff;
            position: relative;
            background-image: linear-gradient(to bottom, rgba(26, 35, 126, 0.05), transparent);
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .flag-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .flag {
            height: 50px;
            width: auto;
        }
        .certificate-country {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
        }
        .certificate-agency {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
        }
        .certificate-title {
            font-size: 2rem;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .certificate-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .certificate-top {
            display: flex;
            gap: 20px;
        }
        .photo-section {
            width: 180px;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .certificate-details {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.95rem;
        }
        .certificate-section {
            margin-bottom: 10px;
        }
        .certificate-label {
            font-weight: bold;
            color: #1a237e;
            display: inline-block;
            min-width: 150px;
        }
        .certificate-value {
            color: #333;
        }
        .photo-title {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 10px;
        }
        .certificate-photo {
            max-width: 150px;
            max-height: 200px;
            width: auto;
            height: auto;
            border: 2px solid #1a237e;
            border-radius: 5px;
        }
        .marriage-photos {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .spouse-photo {
            max-width: 80px;
            max-height: 100px;
            width: auto;
            height: auto;
            border: 2px solid #1a237e;
            border-radius: 5px;
        }
        .spouse-name {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #333;
        }
        .no-photo, .marriage-no-photo {
            width: 150px;
            height: 200px;
            background: #e9ecef;
            border: 2px dashed #6c757d;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-style: italic;
            text-align: center;
        }
        .marriage-no-photo {
            width: 80px;
            height: 100px;
            font-size: 0.75rem;
        }
        .certificate-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 2px solid #1a237e;
            margin: 40px 0 5px;
        }
        .signature-name {
            font-weight: bold;
            color: #1a237e;
        }
        .signature-title {
            color: #666;
            font-size: 0.8rem;
        }
        .qr-code {
            text-align: center;
            padding: 10px;
        }
        .qr-code img {
            max-width: 80px;
            height: auto;
        }
        .qr-label {
            font-size: 0.7rem;
            color: #666;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 6rem;
            color: #1a237e;
            opacity: 0.05;
            pointer-events: none;
        }
        .action-buttons {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
        }
        .btn-certificate {
            background: #1a237e;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            margin: 0 5px;
        }
        .btn-certificate:hover {
            background: #283593;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .certificate-container {
                margin: 0;
                box-shadow: none;
                width: 210mm;
                height: 297mm;
            }
            .certificate-box {
                border: 8px double #1a237e;
                padding: 20px;
            }
            .watermark {
                font-size: 4rem;
                opacity: 0.03;
            }
            .print-hide {
                display: none !important;
            }
            .print-only {
                display: block;
            }
            .certificate-photo {
                max-width: 120px;
                max-height: 160px;
            }
            .spouse-photo {
                max-width: 70px;
                max-height: 90px;
            }
        }
    </style>
</head>
<body>
<div class="certificate-container">
    <div class="certificate-box">
        <div class="watermark">OFFICIAL CERTIFICATE</div>
        <div class="certificate-header">
            <div class="flag-container">
                <img src="../images/Flag of Amhara.jpg" alt="Amhara Flag" class="flag">
                <img src="../images/Etiopía - Wikipedia, la enciclopedia libre.jpg" alt="Ethiopia Flag" class="flag">
            </div>
            <div class="certificate-country">South Gondar Zone</div>
            <div class="certificate-agency">Vital Events Registration Agency</div>
            <div class="certificate-title">
                <?php echo $certificate_type ? $titles[$certificate_type] : 'Certificate'; ?>
            </div>
        </div>
        <hr style="border: 1px solid #1a237e; margin: 20px 0;">
        
        <?php if ($event && $certificate_type): ?>
            <div class="certificate-content">
                <div class="certificate-top">
                    <div class="photo-section">
                        <?php if ($certificate_type === 'birth'): ?>
                            <div class="photo-title">Child's Photo</div>
                            <?php if (!empty($event['photo'])): ?>
                                <img src="../<?= htmlspecialchars($event['photo']) ?>" alt="Child Photo" class="certificate-photo">
                            <?php else: ?>
                                <div class="no-photo">No Photo Available</div>
                            <?php endif; ?>
                        <?php elseif ($certificate_type === 'marriage' || $certificate_type === 'divorce'): ?>
                            <div class="photo-title">Couple Photos</div>
                            <div class="marriage-photos">
                                <div>
                                    <?php if (!empty($event['husband_photo'])): ?>
                                        <img src="../<?= htmlspecialchars($event['husband_photo']) ?>" alt="Husband Photo" class="spouse-photo">
                                    <?php else: ?>
                                        <div class="marriage-no-photo">Husband Photo Not Available</div>
                                    <?php endif; ?>
                                    <div class="spouse-name"><?= htmlspecialchars($event['husband_name']) ?></div>
                                </div>
                                <div>
                                    <?php if (!empty($event['wife_photo'])): ?>
                                        <img src="../<?= htmlspecialchars($event['wife_photo']) ?>" alt="Wife Photo" class="spouse-photo">
                                    <?php else: ?>
                                        <div class="marriage-no-photo">Wife Photo Not Available</div>
                                    <?php endif; ?>
                                    <div class="spouse-name"><?= htmlspecialchars($event['wife_name']) ?></div>
                                </div>
                            </div>
                        <?php elseif ($certificate_type === 'death'): ?>
                            <div class="photo-title">Deceased Photo</div>
                            <?php if (!empty($event['photo'])): ?>
                                <img src="../<?= htmlspecialchars($event['photo']) ?>" alt="Deceased Photo" class="certificate-photo">
                            <?php else: ?>
                                <div class="no-photo">No Photo Available</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="certificate-details">
                        <div class="left-column">
                            <?php if ($certificate_type === 'birth'): ?>
                                <div class="certificate-section"><span class="certificate-label">Certificate No:</span> <span class="certificate-value"><?= htmlspecialchars($event['form_number']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registration UID:</span> <span class="certificate-value"><?= htmlspecialchars($event['registration_uid']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Child Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['child_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Father Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['father_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Mother Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['mother_full_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Date of Birth:</span> <span class="certificate-value"><?= htmlspecialchars($event['date_of_birth']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Place of Birth:</span> <span class="certificate-value"><?= htmlspecialchars($event['kebele_name'] ?? $event['place_of_birth']) ?></span></div>
                            <?php elseif ($certificate_type === 'marriage'): ?>
                                <div class="certificate-section"><span class="certificate-label">Certificate No:</span> <span class="certificate-value"><?= htmlspecialchars($event['form_number']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registration UID:</span> <span class="certificate-value"><?= htmlspecialchars($event['registration_uid']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Husband Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['husband_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Wife Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['wife_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Marriage Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['marriage_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Place of Marriage:</span> <span class="certificate-value"><?= htmlspecialchars($event['place_of_marriage']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Witness 1:</span> <span class="certificate-value"><?= htmlspecialchars($event['witness_1']) ?></span></div>
                            <?php elseif ($certificate_type === 'death'): ?>
                                <div class="certificate-section"><span class="certificate-label">Certificate No:</span> <span class="certificate-value"><?= htmlspecialchars($event['form_number']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registration UID:</span> <span class="certificate-value"><?= htmlspecialchars($event['registration_uid']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Deceased Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['deceased_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Sex:</span> <span class="certificate-value"><?= htmlspecialchars($event['sex']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Age:</span> <span class="certificate-value"><?= htmlspecialchars($event['age']) ?> years</span></div>
                                <div class="certificate-section"><span class="certificate-label">Date of Death:</span> <span class="certificate-value"><?= htmlspecialchars($event['date_of_death']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Place of Death:</span> <span class="certificate-value"><?= htmlspecialchars($event['kebele_name'] ?? $event['place_of_death']) ?></span></div>
                            <?php elseif ($certificate_type === 'divorce'): ?>
                                <div class="certificate-section"><span class="certificate-label">Certificate No:</span> <span class="certificate-value"><?= htmlspecialchars($event['form_number']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registration UID:</span> <span class="certificate-value"><?= htmlspecialchars($event['registration_uid']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Husband Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['husband_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Wife Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['wife_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Divorce Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['divorce_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Place of Divorce:</span> <span class="certificate-value"><?= htmlspecialchars($event['kebele_name'] ?? $event['place_of_divorce']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Witness 1:</span> <span class="certificate-value"><?= htmlspecialchars($event['witness_1']) ?></span></div>
                            <?php endif; ?>
                        </div>
                        <div class="right-column">
                            <?php if ($certificate_type === 'birth'): ?>
                                <div class="certificate-section"><span class="certificate-label">Nationality:</span> <span class="certificate-value"><?= htmlspecialchars($event['parents_nationality']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registered Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['registered_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['issue_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued To:</span> <span class="certificate-value"><?= htmlspecialchars($event['citizen_name']) ?></span></div>
                            <?php elseif ($certificate_type === 'marriage'): ?>
                                <div class="certificate-section"><span class="certificate-label">Witness 2:</span> <span class="certificate-value"><?= htmlspecialchars($event['witness_2']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registered Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['registered_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['issue_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued To:</span> <span class="certificate-value"><?= htmlspecialchars($event['citizen_name']) ?></span></div>
                            <?php elseif ($certificate_type === 'death'): ?>
                                <div class="certificate-section"><span class="certificate-label">Cause of Death:</span> <span class="certificate-value"><?= htmlspecialchars($event['cause_of_death']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Reporter Name:</span> <span class="certificate-value"><?= htmlspecialchars($event['reporter_full_name']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Relationship:</span> <span class="certificate-value"><?= htmlspecialchars($event['relationship']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registered Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['registered_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['issue_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued To:</span> <span class="certificate-value"><?= htmlspecialchars($event['citizen_name']) ?></span></div>
                            <?php elseif ($certificate_type === 'divorce'): ?>
                                <div class="certificate-section"><span class="certificate-label">Witness 2:</span> <span class="certificate-value"><?= htmlspecialchars($event['witness_2']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Registered Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['registered_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued Date:</span> <span class="certificate-value"><?= htmlspecialchars($event['issue_date']) ?></span></div>
                                <div class="certificate-section"><span class="certificate-label">Issued To:</span> <span class="certificate-value"><?= htmlspecialchars($event['citizen_name']) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="certificate-footer">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">Kebele Officer</div>
                    <div class="signature-title">Authorized Signatory</div>
                </div>
                <div class="qr-code print-only" id="qrCodeContainer">
                    <div id="qrCode"></div>
                    <div class="qr-label">Scan to Verify</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">Registrar</div>
                    <div class="signature-title">Vital Events Registration Agency</div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">Certificate not found or not approved.</div>
        <?php endif; ?>
    </div>
    <?php if ($event && $certificate_type): ?>
        <div class="action-buttons print-hide">
            <button class="btn btn-certificate" onclick="window.print()">📄 Print Certificate</button>
            <button class="btn btn-certificate" onclick="window.history.back()">← Go Back</button>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($event && $certificate_type): ?>
        const qrData = `<?= addslashes($qr_data) ?>`;
        if (qrData && qrData.trim() !== '') {
            new QRCode(document.getElementById("qrCode"), {
                text: qrData,
                width: 80,
                height: 80,
                colorDark: "#1a237e",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    <?php endif; ?>
});
window.addEventListener('beforeprint', function() {
    document.querySelector('.print-only').style.display = 'block';
});
</script>
</body>
</html>