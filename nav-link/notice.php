<?php
session_start();
require_once '../includes/db_connection.php';

// Language handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation arrays
$translations = [
    'en' => [
        'title' => 'ODA - Notices',
        'page_title' => 'Latest Notices',
        'stay_updated' => 'Stay updated with our latest announcements',
        'read_more' => 'Read More',
        'posted_on' => 'Posted on',
        'no_notices' => 'No notices available',
        'check_back' => 'Check back later for updates and announcements.',
        'image_not_found' => 'Image not found',
        'failed_load_image' => 'Failed to load image',
        'download_attachment' => 'Download Attachment',
        'file_not_found' => 'File Not Found',
        'download' => 'Download',
        'close' => 'Close',
        'view_full_size' => 'Click image to view full size',
        'attachment' => 'Attachment',
        'download_image' => 'Download Image',
        'failed_to_load' => 'Failed to load image',
        'download_file' => 'Download File',
        'image_loaded' => 'Image loaded successfully',
        'date_format' => 'F j, Y \\a\\t g:i A',
        'debug_info' => 'Debug Info:',
        'images_dir' => 'Images directory:',
        'notices_count' => 'Notices count:',
        'notice_id' => 'Notice ID:',
        'file' => 'File:',
        'path' => 'Path:',
        'exists' => 'Exists:',
        'yes' => 'Yes',
        'no' => 'No'
    ],
    'am' => [
        'title' => 'ODA - ማስታወቂያዎች',
        'page_title' => 'የቅርብ ማስታወቂያዎች',
        'stay_updated' => 'በእኛ የቅርብ ማስታወቂያዎች የተዘምኑ ይሁኑ',
        'read_more' => 'ተጨማሪ ያንብቡ',
        'posted_on' => 'በዚህ ቀን ታትሟል',
        'no_notices' => 'ምንም ማስታወቂያዎች የሉም',
        'check_back' => 'ለዝመና እና ለማስታወቂያዎች ቆይተው ይመልከቱ።',
        'image_not_found' => 'ምስል አልተገኘም',
        'failed_load_image' => 'ምስልን ማምጣት አልተሳካም',
        'download_attachment' => 'አባሪ ያውርዱ',
        'file_not_found' => 'ፋይል አልተገኘም',
        'download' => 'አውርድ',
        'close' => 'ዝጋ',
        'view_full_size' => 'ሙሉ መጠን ለማየት ምስሉን ይጫኑ',
        'attachment' => 'አባሪ',
        'download_image' => 'ምስል አውርድ',
        'failed_to_load' => 'ምስልን ማምጣት አልተሳካም',
        'download_file' => 'ፋይል አውርድ',
        'image_loaded' => 'ምስል በተሳካ ሁኔታ ተጭኗል',
        'date_format' => 'F j, Y በ g:i A',
        'debug_info' => 'የማስተካከያ መረጃ:',
        'images_dir' => 'የምስሎች አደባባይ:',
        'notices_count' => 'የማስታወቂያዎች ብዛት:',
        'notice_id' => 'የማስታወቂያ መታወቂያ:',
        'file' => 'ፋይል:',
        'path' => 'መንገድ:',
        'exists' => 'አለ:',
        'yes' => 'አዎ',
        'no' => 'አይ'
    ]
];

$t = $translations[$lang];

// Fetch notices
$notices = [];
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
}

// Function to check if file exists and get correct path
function getNoticeFilePath($filename) {
    if (empty($filename)) return null;
    
    // Correct path to your images directory
    $upload_dir = '../images/notices/';
    $file_path = $upload_dir . $filename;
    
    // Check if file exists
    if (file_exists($file_path)) {
        return $file_path;
    }
    
    return null;
}

// Function to get web-accessible URL for images
function getNoticeImageUrl($filename) {
    if (empty($filename)) return null;
    
    // Web-accessible path
    return '../images/notices/' . $filename;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $t['title']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Language Switcher */
    .language-switcher {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
    }

    .language-btn {
        background: white;
        border: 2px solid #3498db;
        border-radius: 25px;
        padding: 8px 15px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
        min-width: auto;
        width: auto;
    }

    .language-btn:hover {
        background: #3498db;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }

    .language-btn.active {
        background: #3498db;
        color: white;
    }

    .language-btn i {
        font-size: 1rem;
    }

    /* Existing Styles */
    .card-description {
        max-height: 80px;
        overflow: hidden;
        position: relative;
    }
    .card-description.fade::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 100%;
        height: 30px;
        background: linear-gradient(transparent, white);
    }
    .notice-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .notice-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .notice-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
        cursor: pointer;
    }
    .image-placeholder {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
    }
    .image-container {
        position: relative;
        overflow: hidden;
    }
    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .image-container:hover .image-overlay {
        opacity: 1;
    }
    .zoom-icon {
        color: white;
        font-size: 2rem;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    .image-container:hover .zoom-icon {
        transform: scale(1);
    }
    .img-error {
        border: 2px dashed #dc3545;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .language-switcher {
            top: 80px;
            right: 10px;
        }
        
        .language-btn {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .language-btn span {
            display: none;
        }
        
        .language-btn {
            padding: 8px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .language-btn i {
            font-size: 1.1rem;
            margin: 0;
        }
    }
</style>
</head>
<body>

<?php include_once __DIR__ . '/../includes/header.php'; ?>

<!-- Language Switcher -->
<div class="language-switcher">
    <a href="?lang=en" class="language-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
        <i class="fas fa-globe-americas"></i>
        <span>English</span>
    </a>
    <a href="?lang=am" class="language-btn <?php echo $lang === 'am' ? 'active' : ''; ?>" style="margin-left: 5px;">
        <i class="fas fa-language"></i>
        <span>አማርኛ</span>
    </a>
</div>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-bullhorn me-2 text-primary"></i><?php echo $t['page_title']; ?></h1>
        <div class="text-muted">
            <i class="fas fa-info-circle me-1"></i> <?php echo $t['stay_updated']; ?>
        </div>
    </div>

    <!-- Debug info (remove in production) -->
    <?php if (isset($_GET['debug'])): ?>
    <div class="alert alert-info">
        <strong><?php echo $t['debug_info']; ?></strong><br>
        <?php echo $t['images_dir']; ?> <?= realpath('../images/notices/') ?: $t['no'] ?><br>
        <?php echo $t['notices_count']; ?> <?= count($notices) ?><br>
        <?php foreach ($notices as $notice): ?>
            <?php echo $t['notice_id']; ?> <?= $notice['id'] ?>, <?php echo $t['file']; ?> <?= $notice['file_name'] ?>, 
            <?php echo $t['path']; ?> <?= getNoticeFilePath($notice['file_name']) ?>, 
            <?php echo $t['exists']; ?> <?= getNoticeFilePath($notice['file_name']) ? $t['yes'] : $t['no'] ?><br>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Notices Cards -->
    <div class="row g-4">
        <?php if (count($notices) > 0): ?>
            <?php foreach ($notices as $notice): ?>
                <?php
                $file_extension = !empty($notice['file_name']) ? strtolower(pathinfo($notice['file_name'], PATHINFO_EXTENSION)) : '';
                $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $image_path = !empty($notice['file_name']) ? getNoticeFilePath($notice['file_name']) : null;
                $image_url = !empty($notice['file_name']) ? getNoticeImageUrl($notice['file_name']) : null;
                $image_exists = $image_path && file_exists($image_path);
                ?>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm notice-card">
                        <!-- Image Display -->
                        <?php if (!empty($notice['file_name'])): ?>
                            <?php if ($is_image && $image_exists): ?>
                                <div class="image-container" data-bs-toggle="modal" data-bs-target="#imageModal<?= $notice['id'] ?>">
                                    <img src="<?= $image_url ?>" 
                                         class="card-img-top notice-image" 
                                         alt="<?= htmlspecialchars($notice['title']) ?>"
                                         onerror="handleImageError(this, <?= $notice['id'] ?>)">
                                    <div class="image-overlay">
                                        <i class="fas fa-search-plus zoom-icon"></i>
                                    </div>
                                </div>
                            <?php elseif ($is_image && !$image_exists): ?>
                                <div class="image-placeholder img-error" data-bs-toggle="modal" data-bs-target="#viewMoreModal<?= $notice['id'] ?>">
                                    <div class="text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <small class="d-block"><?php echo $t['image_not_found']; ?></small>
                                        <small class="d-block"><?= $notice['file_name'] ?></small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="image-placeholder" data-bs-toggle="modal" data-bs-target="#viewMoreModal<?= $notice['id'] ?>">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                    <small class="ms-2"><?= strtoupper($file_extension) ?> <?php echo $t['file']; ?></small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="image-placeholder" data-bs-toggle="modal" data-bs-target="#viewMoreModal<?= $notice['id'] ?>">
                                <i class="fas fa-bullhorn fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($notice['title']) ?></h5>
                            <p class="card-text card-description <?= strlen($notice['description']) > 100 ? 'fade' : '' ?>">
                                <?= nl2br(htmlspecialchars($notice['description'])) ?>
                            </p>
                            <?php if (strlen($notice['description']) > 100): ?>
                                <button class="btn btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#viewMoreModal<?= $notice['id'] ?>">
                                    <small><?php echo $t['read_more']; ?> <i class="fas fa-chevron-right ms-1"></i></small>
                                </button>
                            <?php endif; ?>
                            <small class="text-muted mt-auto pt-2 border-top">
                                <i class="far fa-clock me-1"></i> <?= date($t['date_format'], strtotime($notice['created_at'])) ?>
                            </small>
                        </div>
                        
                        <!-- File Download for non-image files -->
                        <?php if (!empty($notice['file_name']) && !$is_image): ?>
                            <div class="card-footer text-center bg-light">
                                <a href="<?= $image_url ?: '#' ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm <?= !$image_exists ? 'disabled' : '' ?>">
                                    <i class="fas fa-file-download me-1"></i> 
                                    <?= $image_exists ? $t['download_attachment'] : $t['file_not_found'] ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Image Modal for Full Size View -->
                <?php if (!empty($notice['file_name']) && $is_image && $image_exists): ?>
                <div class="modal fade" id="imageModal<?= $notice['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-dark text-white">
                                <h6 class="modal-title"><?= htmlspecialchars($notice['title']) ?></h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0 text-center bg-dark">
                                <img src="<?= $image_url ?>" 
                                     class="img-fluid" 
                                     alt="<?= htmlspecialchars($notice['title']) ?>"
                                     style="max-height: 80vh; object-fit: contain;"
                                     onerror="handleModalImageError(this, <?= $notice['id'] ?>)">
                            </div>
                            <div class="modal-footer bg-dark text-white justify-content-center">
                                <a href="<?= $image_url ?>" 
                                   download 
                                   class="btn btn-outline-light btn-sm me-2">
                                    <i class="fas fa-download me-1"></i> <?php echo $t['download']; ?>
                                </a>
                                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> <?php echo $t['close']; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- View More Modal -->
                <div class="modal fade" id="viewMoreModal<?= $notice['id'] ?>" tabindex="-1" aria-labelledby="viewMoreModalLabel<?= $notice['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="viewMoreModalLabel<?= $notice['id'] ?>">
                                    <i class="fas fa-bullhorn me-2"></i><?= htmlspecialchars($notice['title']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Image Display in Modal -->
                                <?php if (!empty($notice['file_name']) && $is_image): ?>
                                    <?php if ($image_exists): ?>
                                        <div class="text-center mb-4">
                                            <img src="<?= $image_url ?>" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 alt="<?= htmlspecialchars($notice['title']) ?>"
                                                 style="max-height: 300px; object-fit: contain; cursor: pointer;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#imageModal<?= $notice['id'] ?>">
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-mouse-pointer me-1"></i><?php echo $t['view_full_size']; ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <?php echo $t['image_not_found']; ?>: <?= $notice['file_name'] ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <p class="lead"><?= nl2br(htmlspecialchars($notice['description'])) ?></p>
                                </div>
                                
                                <!-- File Download in Modal -->
                                <?php if (!empty($notice['file_name'])): ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i><?php echo $t['attachment']; ?></h6>
                                        <?php if ($image_exists): ?>
                                            <a href="<?= $image_url ?>" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="fas fa-file-download me-1"></i> 
                                                <?php echo $t['download']; ?> <?= !$is_image ? strtoupper($file_extension) . ' ' . $t['file'] : $t['download_image'] ?>
                                            </a>
                                            <small class="text-muted ms-2">
                                                <?= $notice['file_name'] ?>
                                            </small>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <?php echo $t['file_not_found']; ?>: <?= $notice['file_name'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <small class="text-muted me-auto">
                                    <i class="far fa-clock me-1"></i> <?php echo $t['posted_on']; ?> <?= date($t['date_format'], strtotime($notice['created_at'])) ?>
                                </small>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['close']; ?></button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h4><?php echo $t['no_notices']; ?></h4>
                    <p><?php echo $t['check_back']; ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function handleImageError(img, noticeId) {
    console.error('<?php echo $t['failed_load_image']; ?>:', img.src);
    img.style.display = 'none';
    
    // Find and show the placeholder
    const card = img.closest('.card');
    const placeholder = card.querySelector('.image-placeholder');
    if (placeholder) {
        placeholder.style.display = 'flex';
        placeholder.classList.add('img-error');
        placeholder.innerHTML = `
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <small class="d-block"><?php echo $t['failed_load_image']; ?></small>
            </div>
        `;
    }
}

function handleModalImageError(img, noticeId) {
    console.error('<?php echo $t['failed_to_load']; ?>:', img.src);
    img.style.display = 'none';
    
    const modalBody = img.closest('.modal-body');
    modalBody.innerHTML = `
        <div class="alert alert-danger text-center m-3">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
            <h5><?php echo $t['failed_to_load']; ?></h5>
            <p><?php echo $t['failed_to_load']; ?></p>
            <a href="${img.src}" download class="btn btn-primary">
                <i class="fas fa-download me-1"></i> <?php echo $t['download_file']; ?>
            </a>
        </div>
    `;
}

// Add click event to all image placeholders
document.addEventListener('DOMContentLoaded', function() {
    const imagePlaceholders = document.querySelectorAll('.image-placeholder');
    imagePlaceholders.forEach(placeholder => {
        placeholder.addEventListener('click', function() {
            const targetModal = this.getAttribute('data-bs-target');
            if (targetModal) {
                const modal = new bootstrap.Modal(document.querySelector(targetModal));
                modal.show();
            }
        });
    });
    
    // Debug: Log all image sources
    const images = document.querySelectorAll('img[src*="images"]');
    images.forEach(img => {
        console.log('<?php echo $t['image_loaded']; ?>:', img.src, 'Loaded:', img.complete && img.naturalHeight !== 0);
    });
});
</script>
</body>
</html>