<?php
session_start();
// Language handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'en';

// Translation arrays
$translations = [
    'en' => [
        'title' => 'About South Gondar Zone Vital Events',
        'hero_title' => 'About South Gondar Zone Vital Events',
        'hero_subtitle' => 'Transforming Vital Events Registration in South Gondar Zone',
        'what_is_verms' => 'What is VERMS?',
        'verms_desc1' => 'The <strong class="text-primary">South Gondar Zone Vital Events Registration Management System</strong> is a comprehensive digital platform designed to revolutionize how vital events are registered and managed across South Gondar Zone.',
        'verms_desc2' => 'Our system streamlines the registration of <span class="text-primary fw-bold">births, marriages, divorces, and deaths</span> in South Gondar Zone, ensuring transparency, efficiency, and accessibility for citizens and government offices alike. By digitizing these critical processes, we eliminate bureaucratic hurdles and bring vital services closer to the people of South Gondar.',
        'digital_transformation' => 'Digital Transformation',
        'digital_transformation_desc' => 'Bringing traditional registration processes into the digital age',
        'our_mission' => 'Our Mission',
        'mission_desc' => 'Provide a reliable, efficient, and citizen-friendly platform that transforms how vital records are registered, stored, and accessed across Ethiopia, ensuring every citizen\'s life events are properly documented and secured.',
        'our_vision' => 'Our Vision',
        'vision_desc' => 'To become the leading digital solution in Africa for managing vital events, ensuring every citizen\'s record is accurately documented, easily accessible, and securely maintained for future generations.',
        'core_objectives' => 'Our Core Objectives',
        'digitize_records' => 'Digitize Records',
        'digitize_desc' => 'Transform paper-based records into secure digital formats',
        'data_security' => 'Data Security',
        'security_desc' => 'Ensure complete security and transparency of all records',
        'multi_level_support' => 'Multi-level Support',
        'support_desc' => 'Support Kebele, Woreda & Zone administration levels',
        'citizen_access' => 'Citizen Access',
        'access_desc' => 'Provide easy access to services for all citizens',
        'why_choose_verms' => 'Why Choose VERMS?',
        'efficiency_speed' => 'Efficiency & Speed',
        'efficiency_list1' => 'Fast and reliable registration process',
        'efficiency_list2' => 'Real-time status updates & notifications',
        'efficiency_list3' => 'Reduced processing time from weeks to days',
        'security_reliability' => 'Security & Reliability',
        'security_list1' => 'Safe & secure record storage',
        'security_list2' => 'Reduces errors & duplication',
        'security_list3' => 'Backup and disaster recovery systems',
        'user_experience' => 'User Experience',
        'ux_list1' => 'User-friendly design for citizens & officers',
        'ux_list2' => 'Multi-language support',
        'ux_list3' => 'Accessible on multiple devices',
        'government_efficiency' => 'Government Efficiency',
        'gov_list1' => 'Boosts government operational efficiency',
        'gov_list2' => 'Automated reporting and analytics',
        'gov_list3' => 'Cost-effective solution',
        'working_together' => 'Working Together for a Better Future',
        'impact_desc' => 'With VERMS, we aim to build trust, improve service delivery, and ensure that every citizen\'s vital life events are documented securely for generations to come. Our platform represents a commitment to technological advancement and citizen-centric governance.',
        'faster_processing' => 'Faster Processing',
        'accuracy_rate' => 'Accuracy Rate',
        'service_availability' => 'Service Availability',
        'ready_experience' => 'Ready to Experience the Future of Vital Events Registration?',
        'join_thousands' => 'Join thousands of Ethiopians who have simplified their vital event registrations with VERMS',
        'create_account' => 'Create Account',
        'login_system' => 'Login to System'
    ],
    'am' => [
        'title' => 'ስለ ደቡብ ጎንደር ዞን ህይወት ክስተቶች',
        'hero_title' => 'ስለ ደቡብ ጎንደር ዞን ህይወት ክስተቶች',
        'hero_subtitle' => 'ደቡብ ጎንደር ዞን ውስጥ የህይወት ክስተቶች ምዝገባን መለወጥ',
        'what_is_verms' => 'VERMS ምንድን ነው?',
        'verms_desc1' => '<strong class="text-primary">የደቡብ ጎንደር ዞን ህይወት ክስተቶች ምዝገባ አስተዳደር ስርዓት</strong> በደቡብ ጎንደር ዞን ውስጥ የህይወት ክስተቶች እንዴት እንደሚመዘገቡ እና እንደሚዳደሩ ለማለወጥ የተነደፈ የተሟላ ዲጂታል መድረክ ነው።',
        'verms_desc2' => 'ስርዓታችን የ<span class="text-primary fw-bold">መወለድ፣ የጋብቻ፣ የፍች እና የሞት</span> ምዝገባን በደቡብ ጎንደር ዞን ውስጥ ለማቃለል ያገለግላል፣ ግልጽነት፣ ውጤታማነት እና ተደራሽነትን ለከተማዎች እና ለመንግስት ቢሮዎች እኩል ያረጋግጣል። እነዚህን ወሳኝ ሂደቶች በዲጂታል በመለወጥ፣ ባርክራሲያዊ እክሎችን እናስወግዳለን እና አስፈላጊ አገልግሎቶችን ለደቡብ ጎንደር ህዝብ ቅርብ እናደርጋለን።',
        'digital_transformation' => 'ዲጂታል ለውጥ',
        'digital_transformation_desc' => 'ባህላዊ የምዝገባ ሂደቶችን ወደ ዲጂታል ዘመን መቀየር',
        'our_mission' => 'ተልእኳችን',
        'mission_desc' => 'በኢትዮጵያ ውስጥ የህይወት ምዝገባዎች እንዴት እንደሚመዘገቡ፣ እንደሚቀመጡ እና እንደሚደረሰባቸው የሚቀይር አስተማማኝ፣ ውጤታማ እና የከተማ ሰላማዊ መድረክ መስጠት፣ የእያንዳንዱ የከተማ የህይወት ክስተቶች በትክክል እንዲመዘገቡ እና እንዲጠበቁ ማረጋገጥ።',
        'our_vision' => 'ራዕይ እኛ',
        'vision_desc' => 'በአፍሪቃ ውስጥ የህይወት ክስተቶችን ለማስተዳደር መሪ ዲጂታል መፍትሔ መሆን፣ የእያንዳንዱ የከተማ ምዝገባ በትክክል እንዲመዘገብ፣ በቀላሉ ሊደርስበት የሚችል እና ለወደፊት ትውልድ በደህንነት እንዲጠበቅ ማረጋገጥ።',
        'core_objectives' => 'ዋና ዓላማዎቻችን',
        'digitize_records' => 'ምዝገባዎችን በዲጂታል ማድረግ',
        'digitize_desc' => 'በወረቀት ላይ የተመሰረቱ ምዝገባዎችን ወደ ደህንነታቸው የተጠበቁ ዲጂታል ቅርጾች መለወጥ',
        'data_security' => 'የውሂብ ደህንነት',
        'security_desc' => 'ሁሉም ምዝገባዎች ሙሉ ደህንነት እና ግልጽነት እንዲኖራቸው ማረጋገጥ',
        'multi_level_support' => 'ባለብዙ ደረጃ ድጋፍ',
        'support_desc' => 'የቀበሌ፣ ወረዳ እና ዞን አስተዳደር ደረጃዎችን ድጋፍ ማድረግ',
        'citizen_access' => 'የከተማ መድረሻ',
        'access_desc' => 'ለሁሉም ዜጎች ለአገልግሎቶች ቀላል መድረሻ መስጠት',
        'why_choose_verms' => 'ለምን VERMS ይመርጡ?',
        'efficiency_speed' => 'ውጤታማነት እና ፍጥነት',
        'efficiency_list1' => 'ፈጣን እና አስተማማኝ የምዝገባ ሂደት',
        'efficiency_list2' => 'በእውነተኛ ጊዜ ሁኔታ ዝመናዎች እና ማሳወቂያዎች',
        'efficiency_list3' => 'የሂደቱን ጊዜ ከሳምንታት ወደ ቀናት መቀነስ',
        'security_reliability' => 'ደህንነት እና አስተማማኝነት',
        'security_list1' => 'ደህንነታቸው የተጠበቀ የምዝገባ ማከማቻ',
        'security_list2' => 'ስህተቶችን እና ድጋሚ ምዝገባን መቀነስ',
        'security_list3' => 'የተጠባበቀ እና የአደጋ መመለስ ስርዓቶች',
        'user_experience' => 'የተጠቃሚ ተሞክሮ',
        'ux_list1' => 'ለዜጎች እና ለባለስልጣናት ለተጠቃሚ ምቹ ዲዛይን',
        'ux_list2' => 'በብዙ ቋንቋዎች ድጋፍ',
        'ux_list3' => 'በብዙ መሳሪያዎች ላይ መድረስ የሚቻል',
        'government_efficiency' => 'የመንግስት ውጤታማነት',
        'gov_list1' => 'የመንግስት ኦፕሬሽናል ውጤታማነትን ያሳድጋል',
        'gov_list2' => 'በራስ-ሰር የሪፖርት ማድረግ እና ትንታኔ',
        'gov_list3' => 'ወጪ ቆጣቢ መፍትሔ',
        'working_together' => 'ለተሻለ ወደፊት አንድ ላይ መስራት',
        'impact_desc' => 'በVERMS፣ እምነትን ለመገንባት፣ የአገልግሎት አቅርቦትን ለማሻሻል እና የእያንዳንዱ ዜጋ አስፈላጊ የህይወት ክስተቶች ለሚመጡ ትውልዶች በደህንነት እንዲመዘገቡ ለማረጋገጥ እንቸገራለን። መድረካችን ለቴክኖሎጂ እድገት እና ለከተማ-ማዕከላዊ አስተዳደር ቁርጠኝነትን ይወክላል።',
        'faster_processing' => 'ፈጣን ሂደት',
        'accuracy_rate' => 'ትክክለኛነት መጠን',
        'service_availability' => 'አገልግሎት መገኘት',
        'ready_experience' => 'የህይወት ክስተቶች ምዝገባ ወደፊት ለማወቅ ዝግጁ ነዎት?',
        'join_thousands' => 'ሺዎች የሚቆጠሩ ኢትዮጵያውያን የህይወት ክስተቶች ምዝገባቸውን በVERMS እንዳቃለሉት ይቀላቀሉ',
        'create_account' => 'መለያ ይፍጠሩ',
        'login_system' => 'ወደ ስርዓቱ ይግቡ'
    ]
];

$t = $translations[$lang];
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

    /* Language Switcher */
    .language-switcher {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
    }

    .language-btn {
        background: white;
        border: 2px solid var(--secondary-color);
        border-radius: 25px;
        padding: 8px 15px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
        min-width: auto;
        width: auto;
    }

    .language-btn:hover {
        background: var(--secondary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }

    .language-btn.active {
        background: var(--secondary-color);
        color: white;
    }

    .language-btn i {
        font-size: 1rem;
    }

    /* About Page Styles */
    .about-hero-section {
        position: relative;
        overflow: hidden;
    }

    .about-hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }

    .about-hero-section .container {
        position: relative;
        z-index: 2;
    }

    .mission-vision-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border-top: 4px solid transparent;
    }

    .mission-vision-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .mission-vision-card:first-child {
        border-top-color: var(--primary-color);
    }

    .mission-vision-card:last-child {
        border-top-color: var(--warning-color);
    }

    .icon-container {
        display: inline-block;
        padding: 20px;
        border-radius: 50%;
        background: rgba(52, 152, 219, 0.1);
    }

    .objective-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
    }

    .objective-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .objective-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(39, 174, 96, 0.1);
        margin: 0 auto;
    }

    .benefit-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
        border-left: 4px solid var(--primary-color);
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .about-visual {
        text-align: center;
    }

    .visual-circle {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .impact-item {
        text-align: center;
        padding: 20px;
    }

    .impact-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .impact-label {
        font-size: 1rem;
        opacity: 0.9;
    }

    .cta-buttons .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition);
    }

    .cta-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Animation for stats */
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .impact-item {
        animation: countUp 0.8s ease-out;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .impact-number {
            font-size: 2rem;
        }
        
        .mission-vision-card {
            padding: 2rem 1.5rem;
        }
        
        .about-hero-section {
            padding: 4rem 0;
        }
        
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

<?php include '../includes/header.php'; ?>

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

<!-- Hero Section -->
<section class="about-hero-section py-5" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4"><?php echo $t['hero_title']; ?></h1>
                <p class="lead mb-4"><?php echo $t['hero_subtitle']; ?></p>
            </div>
        </div>
    </div>
</section>

<!-- What is VERMS Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="text-primary fw-bold mb-4"><?php echo $t['what_is_verms']; ?></h2>
                <p class="lead text-muted mb-4">
                    <?php echo $t['verms_desc1']; ?>
                </p>
                <p class="text-muted">
                    <?php echo $t['verms_desc2']; ?>
                </p>
            </div>
            <div class="col-lg-6">
                <div class="about-visual p-4 text-center">
                    <div class="visual-circle mx-auto mb-4">
                        <i class="fas fa-database fa-3x text-white"></i>
                    </div>
                    <h4 class="fw-bold text-primary"><?php echo $t['digital_transformation']; ?></h4>
                    <p class="text-muted"><?php echo $t['digital_transformation_desc']; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-md-6">
                <div class="mission-vision-card h-100 text-center p-5">
                    <div class="icon-container mb-4">
                        <i class="fa-solid fa-bullseye fa-3x text-primary"></i>
                    </div>
                    <h3 class="fw-bold text-secondary mb-4"><?php echo $t['our_mission']; ?></h3>
                    <p class="text-muted fs-5">
                        <?php echo $t['mission_desc']; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mission-vision-card h-100 text-center p-5">
                    <div class="icon-container mb-4">
                        <i class="fa-solid fa-lightbulb fa-3x text-warning"></i>
                    </div>
                    <h3 class="fw-bold text-secondary mb-4"><?php echo $t['our_vision']; ?></h3>
                    <p class="text-muted fs-5">
                        <?php echo $t['vision_desc']; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Objectives Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-5"><?php echo $t['core_objectives']; ?></h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="objective-card text-center p-4">
                    <div class="objective-icon mb-3">
                        <i class="fa-solid fa-database fa-2x text-success"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['digitize_records']; ?></h5>
                    <p class="text-muted small"><?php echo $t['digitize_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="objective-card text-center p-4">
                    <div class="objective-icon mb-3">
                        <i class="fa-solid fa-user-shield fa-2x text-danger"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['data_security']; ?></h5>
                    <p class="text-muted small"><?php echo $t['security_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="objective-card text-center p-4">
                    <div class="objective-icon mb-3">
                        <i class="fa-solid fa-users fa-2x text-info"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['multi_level_support']; ?></h5>
                    <p class="text-muted small"><?php echo $t['support_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="objective-card text-center p-4">
                    <div class="objective-icon mb-3">
                        <i class="fa-solid fa-globe fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['citizen_access']; ?></h5>
                    <p class="text-muted small"><?php echo $t['access_desc']; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose VERMS Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-5"><?php echo $t['why_choose_verms']; ?></h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="benefit-card p-4">
                    <div class="benefit-header d-flex align-items-center mb-3">
                        <i class="fa-solid fa-bolt text-warning fa-2x me-3"></i>
                        <h5 class="fw-bold mb-0"><?php echo $t['efficiency_speed']; ?></h5>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['efficiency_list1']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['efficiency_list2']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['efficiency_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-card p-4">
                    <div class="benefit-header d-flex align-items-center mb-3">
                        <i class="fa-solid fa-shield-alt text-primary fa-2x me-3"></i>
                        <h5 class="fw-bold mb-0"><?php echo $t['security_reliability']; ?></h5>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['security_list1']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['security_list2']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['security_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-card p-4">
                    <div class="benefit-header d-flex align-items-center mb-3">
                        <i class="fa-solid fa-users text-info fa-2x me-3"></i>
                        <h5 class="fw-bold mb-0"><?php echo $t['user_experience']; ?></h5>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['ux_list1']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['ux_list2']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['ux_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-card p-4">
                    <div class="benefit-header d-flex align-items-center mb-3">
                        <i class="fa-solid fa-chart-line text-success fa-2x me-3"></i>
                        <h5 class="fw-bold mb-0"><?php echo $t['government_efficiency']; ?></h5>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['gov_list1']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['gov_list2']; ?></li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> <?php echo $t['gov_list3']; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Impact Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <i class="fa-solid fa-handshake-angle fa-3x mb-4"></i>
                <h2 class="fw-bold mb-4"><?php echo $t['working_together']; ?></h2>
                <p class="lead mb-4">
                    <?php echo $t['impact_desc']; ?>
                </p>
                <div class="impact-stats row mt-5">
                    <div class="col-md-4">
                        <div class="impact-item">
                            <div class="impact-number">75%</div>
                            <div class="impact-label"><?php echo $t['faster_processing']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="impact-item">
                            <div class="impact-number">95%</div>
                            <div class="impact-label"><?php echo $t['accuracy_rate']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="impact-item">
                            <div class="impact-number">24/7</div>
                            <div class="impact-label"><?php echo $t['service_availability']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h3 class="fw-bold text-primary mb-4"><?php echo $t['ready_experience']; ?></h3>
                <p class="text-muted mb-4"><?php echo $t['join_thousands']; ?></p>
                <div class="cta-buttons">
                    <a href="../register.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i><?php echo $t['create_account']; ?>
                    </a>
                    <a href="../login.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i><?php echo $t['login_system']; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Simple counter animation for stats
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-count'));
        if (target) {
            const duration = 2000; // 2 seconds
            const step = target / (duration / 16); // 60fps
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(current) + (target > 99 ? '+' : '%');
            }, 16);
        }
    });
});
</script>
</body>
</html>