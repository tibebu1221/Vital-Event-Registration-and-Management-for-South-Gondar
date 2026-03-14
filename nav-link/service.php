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
        'title' => 'South Gondar Zone Vital Events Services',
        'hero_title' => 'South Gondar Zone Vital Events Services',
        'hero_subtitle' => 'Comprehensive digital solutions for vital events management in South Gondar Zone',
        'core_services' => 'Core Services',
        'accessibility' => 'Accessibility',
        'digital' => 'Digital',
        'what_we_offer' => 'What We Offer in South Gondar Zone',
        'streamlining' => 'Streamlining vital events registration for South Gondar Zone with secure, efficient digital solutions',
        'birth_registration' => 'Birth Registration',
        'birth_desc' => 'Register and manage birth records efficiently with secure digital storage and instant certificate generation.',
        'marriage_registration' => 'Marriage Registration',
        'marriage_desc' => 'Easily record marriage events and issue official certificates with witness documentation and secure verification.',
        'death_registration' => 'Death Registration',
        'death_desc' => 'Ensure accurate documentation of death events with official records and proper legal procedures.',
        'divorce_registration' => 'Divorce Registration',
        'divorce_desc' => 'Record divorce cases and update official records seamlessly with proper legal documentation.',
        'government_admin' => 'Government Administration Support',
        'government_desc' => 'Help Kebele, Woreda, and Zone officers manage vital records efficiently with comprehensive administrative tools.',
        'citizen_online' => 'Citizen Online Access',
        'citizen_desc' => 'Allow citizens to request records, track applications, and access profiles through our secure online portal.',
        'how_it_works' => 'How It Works',
        'register_account' => 'Register Account',
        'register_desc' => 'Create your citizen or officer account with verified credentials',
        'submit_request' => 'Submit Request',
        'submit_desc' => 'Fill out the required forms for your vital event registration',
        'verification' => 'Verification',
        'verification_desc' => 'Officers verify and process your application securely',
        'receive_certificate' => 'Receive Certificate',
        'receive_desc' => 'Get your official digital or printed certificate',
        'why_choose_verms' => 'Why Choose VERMS?',
        'efficiency_speed' => 'Efficiency & Speed',
        'efficiency_list1' => 'Fast and reliable registration process',
        'efficiency_list2' => 'User-friendly for citizens and officers',
        'efficiency_list3' => 'Real-time notifications and status updates',
        'security_reliability' => 'Security & Reliability',
        'security_list1' => 'Secure digital record storage',
        'security_list2' => 'Increases efficiency for government administration',
        'security_list3' => 'Reduces duplication and errors',
        'advanced_features' => 'Advanced Features',
        'advanced_list1' => 'Automated reporting and analytics',
        'advanced_list2' => 'Multi-level administrative access',
        'advanced_list3' => 'Backup and recovery systems',
        'support_accessibility' => 'Support & Accessibility',
        'support_list1' => '24/7 online access',
        'support_list2' => 'Multi-language support',
        'support_list3' => 'Comprehensive help documentation',
        'ready_get_started' => 'Ready to Get Started?',
        'join_thousands' => 'Join thousands of Ethiopians using VERMS for efficient vital events management',
        'login_system' => 'Login to System',
        'create_account' => 'Create Account',
        'need_help' => 'Need help? Contact our support team for assistance',
        'digital_certificates' => 'Digital Certificates',
        'secure_storage' => 'Secure Storage',
        'quick_processing' => 'Quick Processing',
        'witness_records' => 'Witness Records',
        'legal_documentation' => 'Legal Documentation',
        'secure_verification' => 'Secure Verification',
        'legal_compliance' => 'Legal Compliance',
        'family_support' => 'Family Support',
        'record_updates' => 'Record Updates',
        'multi_level_support' => 'Multi-level Support',
        'admin_tools' => 'Administrative Tools',
        'reporting_systems' => 'Reporting Systems',
        'online_portal' => 'Online Portal',
        'application_tracking' => 'Application Tracking',
        'secure_access' => 'Secure Access'
    ],
    'am' => [
        'title' => 'የደቡብ ጎንደር ዞን ህይወት ክስተቶች አገልግሎቶች',
        'hero_title' => 'የደቡብ ጎንደር ዞን ህይወት ክስተቶች አገልግሎቶች',
        'hero_subtitle' => 'በደቡብ ጎንደር ዞን ውስጥ ለህይወት ክስተቶች አስተዳደር የተሟላ ዲጂታል መፍትሄዎች',
        'core_services' => 'ዋና አገልግሎቶች',
        'accessibility' => 'ተደራሽነት',
        'digital' => 'ዲጂታል',
        'what_we_offer' => 'በደቡብ ጎንደር ዞን የምንሰጠው',
        'streamlining' => 'በደቡብ ጎንደር ዞን ውስጥ የህይወት ክስተቶች ምዝገባን በደህንነት እና በውጤታማ ዲጂታል መፍትሄዎች በመሳሪያ ማቃለል',
        'birth_registration' => 'የመወለድ ምዝገባ',
        'birth_desc' => 'የመወለድ ምዝገባዎችን በውጤታማነት ይመዝግቡ እና ያስተዳድሩ በደህንነት የተጠበቀ ዲጂታል ማከማቻ እና ባለ ቅጽበት ማረጋገጫ ማመንጨት።',
        'marriage_registration' => 'የጋብቻ ምዝገባ',
        'marriage_desc' => 'የጋብቻ ክስተቶችን በቀላሉ ይመዝግቡ እና በምስክር ሰነድ እና በደህንነት የተጠበቀ ማረጋገጫ ሳሎን ማረጋገጫ ይስጡ።',
        'death_registration' => 'የሞት ምዝገባ',
        'death_desc' => 'የሞት ክስተቶችን ትክክለኛ ሰነድ በይፋ ምዝገባዎች እና በተገቢ የሕግ ሂደቶች ማረጋገጥ።',
        'divorce_registration' => 'የፍች ምዝገባ',
        'divorce_desc' => 'የፍች ጉዳዮችን ይመዝግቡ እና በተገቢ የሕግ ሰነድ መሠረት ይፋዊ ምዝገባዎችን ያዘምኑ።',
        'government_admin' => 'የመንግስት አስተዳደር ድጋፍ',
        'government_desc' => 'የቀበሌ፣ ወረዳ እና ዞን ባለስልጣናት የህይወት ምዝገባዎችን በውጤታማነት እንዲያስተዳድሩ በሙሉ የአስተዳደር መሳሪያዎች ይርዳቸው።',
        'citizen_online' => 'የከተማ በመስመር ላይ መድረሻ',
        'citizen_desc' => 'ዜጎች ምዝገባዎችን እንዲጠይቁ፣ ማመልከቻዎችን እንዲከታተሉ እና መገለጫዎችን በደህንነታችን በመስመር ላይ መድረክ እንዲደርሱ ያስችሉ።',
        'how_it_works' => 'እንዴት እንደሚሰራ',
        'register_account' => 'መለያ ይመዝግቡ',
        'register_desc' => 'የተረጋገጠ የማንነት ማስረጃ ያለው የከተማ ወይም የባለስልጣን መለያዎን ይፍጠሩ',
        'submit_request' => 'የእገዛ ጥያቄ አስገባ',
        'submit_desc' => 'ለህይወት ክስተት ምዝገባዎ የሚያስፈልጉትን ቅጾች ይሙሉ',
        'verification' => 'ማረጋገጫ',
        'verification_desc' => 'ባለስልጣናት ማመልከቻዎን በደህንነት ያረጋግጣሉ እና ያካሂዳሉ',
        'receive_certificate' => 'ማረጋገጫ ይቀበሉ',
        'receive_desc' => 'የእርስዎን ይፋዊ ዲጂታል ወይም የተተረጎመ ማረጋገጫ ያግኙ',
        'why_choose_verms' => 'ለምን VERMS ይመርጡ?',
        'efficiency_speed' => 'ውጤታማነት እና ፍጥነት',
        'efficiency_list1' => 'ፈጣን እና አስተማማኝ የምዝገባ ሂደት',
        'efficiency_list2' => 'ለዜጎች እና ለባለስልጣናት ለተጠቃሚ ምቹ',
        'efficiency_list3' => 'በእውነተኛ ጊዜ ማሳወቂያዎች እና የሁኔታ ዝመናዎች',
        'security_reliability' => 'ደህንነት እና አስተማማኝነት',
        'security_list1' => 'ደህንነታቸው የተጠበቀ ዲጂታል የምዝገባ ማከማቻ',
        'security_list2' => 'ለመንግስት አስተዳደር ውጤታማነትን ይጨምራል',
        'security_list3' => 'ድጋሚ ምዝገባ እና ስህተቶችን ይቀንሳል',
        'advanced_features' => 'የላቀ ባህሪያት',
        'advanced_list1' => 'በራስ-ሰር የሪፖርት ማድረግ እና ትንታኔ',
        'advanced_list2' => 'ባለብዙ ደረጃ የአስተዳደር መድረሻ',
        'advanced_list3' => 'የተጠባበቀ እና የመመለስ ስርዓቶች',
        'support_accessibility' => 'ድጋፍ እና ተደራሽነት',
        'support_list1' => '24/7 በመስመር ላይ መድረሻ',
        'support_list2' => 'በብዙ ቋንቋዎች ድጋፍ',
        'support_list3' => 'ሙሉ የእገዛ ሰነድ',
        'ready_get_started' => 'ለመጀመር ዝግጁ ነዎት?',
        'join_thousands' => 'ሺዎች የሚቆጠሩ ኢትዮጵያውያን ለውጤታማ የህይወት ክስተቶች አስተዳደር VERMS ን በመጠቀም ይቀላቀሉ',
        'login_system' => 'ወደ ስርዓቱ ይግቡ',
        'create_account' => 'መለያ ይፍጠሩ',
        'need_help' => 'እገዛ ይፈልጋሉ? ለእገዛ የእኛን የድጋፍ ቡድን ያነጋግሩ',
        'digital_certificates' => 'ዲጂታል ማረጋገጫዎች',
        'secure_storage' => 'የተጠበቀ ማከማቻ',
        'quick_processing' => 'ፈጣን ሂደት',
        'witness_records' => 'የምስክር ምዝገባዎች',
        'legal_documentation' => 'የሕግ ሰነድ',
        'secure_verification' => 'የተጠበቀ ማረጋገጫ',
        'legal_compliance' => 'የሕግ አገዛዝ',
        'family_support' => 'የቤተሰብ ድጋፍ',
        'record_updates' => 'ምዝገባ ማዘመን',
        'multi_level_support' => 'ባለብዙ ደረጃ ድጋፍ',
        'admin_tools' => 'የአስተዳደር መሳሪያዎች',
        'reporting_systems' => 'የሪፖርት ስርዓቶች',
        'online_portal' => 'በመስመር ላይ መድረክ',
        'application_tracking' => 'የማመልከቻ መከታተያ',
        'secure_access' => 'የተጠበቀ መድረሻ'
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

    /* Services Page Styles */
    .services-hero {
        position: relative;
        overflow: hidden;
    }

    .services-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }

    .services-hero .container {
        position: relative;
        z-index: 2;
    }

    .stat-item {
        text-align: center;
        padding: 20px 10px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .service-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border-top: 4px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .service-card:hover::before {
        transform: scaleX(1);
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .service-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(52, 152, 219, 0.1);
        margin: 0 auto;
        transition: var(--transition);
    }

    .service-card:hover .service-icon {
        transform: scale(1.1);
        background: rgba(52, 152, 219, 0.2);
    }

    .service-features {
        margin-top: auto;
    }

    .badge {
        margin-right: 5px;
        font-weight: 500;
    }

    .process-step {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
    }

    .process-step:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--gradient-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 auto;
    }

    .benefit-item {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
        border-left: 4px solid var(--primary-color);
    }

    .benefit-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .benefit-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(52, 152, 219, 0.1);
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
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .service-card, .process-step, .benefit-item {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Stagger animation for service cards */
    .service-card:nth-child(1) { animation-delay: 0.1s; }
    .service-card:nth-child(2) { animation-delay: 0.2s; }
    .service-card:nth-child(3) { animation-delay: 0.3s; }
    .service-card:nth-child(4) { animation-delay: 0.4s; }
    .service-card:nth-child(5) { animation-delay: 0.5s; }
    .service-card:nth-child(6) { animation-delay: 0.6s; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 2rem;
        }
        
        .services-hero {
            padding: 3rem 0;
        }
        
        .service-card, .benefit-item {
            margin-bottom: 1.5rem;
        }
        
        .cta-buttons .btn {
            display: block;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .cta-buttons .btn:last-child {
            margin-bottom: 0;
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
<section class="services-hero py-5" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4"><?php echo $t['hero_title']; ?></h1>
                <p class="lead mb-4"><?php echo $t['hero_subtitle']; ?></p>
                <div class="service-stats row mt-5">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">4</div>
                            <div class="stat-label"><?php echo $t['core_services']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label"><?php echo $t['accessibility']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">95%</div>
                            <div class="stat-label"><?php echo $t['digital']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Services Section -->
<section class="services-section py-5">
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-5"><?php echo $t['what_we_offer']; ?></h2>
        <p class="text-center text-muted lead mb-5"><?php echo $t['streamlining']; ?></p>

        <div class="row g-4">
            <!-- Birth Registration -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-baby fa-3x text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['birth_registration']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['birth_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['digital_certificates']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['secure_storage']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['quick_processing']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Marriage Registration -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-ring fa-3x text-success"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['marriage_registration']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['marriage_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['witness_records']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['legal_documentation']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['secure_verification']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Death Registration -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-book-dead fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['death_registration']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['death_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['legal_compliance']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['secure_storage']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['family_support']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Services -->
        <div class="row g-4 mt-4">
            <!-- Divorce Registration -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-file-signature fa-3x text-info"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['divorce_registration']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['divorce_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['legal_documentation']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['record_updates']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['quick_processing']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Government Administration -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-users fa-3x text-warning"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['government_admin']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['government_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['multi_level_support']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['admin_tools']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['reporting_systems']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Citizen Online Access -->
            <div class="col-md-4">
                <div class="service-card h-100 text-center p-4">
                    <div class="service-icon mb-4">
                        <i class="fas fa-globe fa-3x text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-secondary mb-3"><?php echo $t['citizen_online']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $t['citizen_desc']; ?></p>
                    <div class="service-features">
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['online_portal']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['application_tracking']; ?></span>
                        <span class="badge bg-light text-dark mb-1"><?php echo $t['secure_access']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Process Section -->
<section class="process-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-5"><?php echo $t['how_it_works']; ?></h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="process-step text-center p-4">
                    <div class="step-number">1</div>
                    <h5 class="fw-bold mt-3"><?php echo $t['register_account']; ?></h5>
                    <p class="text-muted small"><?php echo $t['register_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step text-center p-4">
                    <div class="step-number">2</div>
                    <h5 class="fw-bold mt-3"><?php echo $t['submit_request']; ?></h5>
                    <p class="text-muted small"><?php echo $t['submit_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step text-center p-4">
                    <div class="step-number">3</div>
                    <h5 class="fw-bold mt-3"><?php echo $t['verification']; ?></h5>
                    <p class="text-muted small"><?php echo $t['verification_desc']; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step text-center p-4">
                    <div class="step-number">4</div>
                    <h5 class="fw-bold mt-3"><?php echo $t['receive_certificate']; ?></h5>
                    <p class="text-muted small"><?php echo $t['receive_desc']; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose VERMS -->
<section class="features-section py-5">
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-5"><?php echo $t['why_choose_verms']; ?></h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="benefit-item p-4">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-bolt fa-2x text-warning"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['efficiency_speed']; ?></h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['efficiency_list1']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['efficiency_list2']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['efficiency_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-item p-4">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['security_reliability']; ?></h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['security_list1']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['security_list2']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['security_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-item p-4">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-chart-line fa-2x text-success"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['advanced_features']; ?></h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['advanced_list1']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['advanced_list2']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['advanced_list3']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefit-item p-4">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-headset fa-2x text-info"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo $t['support_accessibility']; ?></h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['support_list1']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['support_list2']; ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $t['support_list3']; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 text-center" style="background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%); color: white;">
    <div class="container">
        <h3 class="fw-bold mb-3"><?php echo $t['ready_get_started']; ?></h3>
        <p class="lead mb-4"><?php echo $t['join_thousands']; ?></p>
        <div class="cta-buttons">
            <a href="../login.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-sign-in-alt me-2"></i><?php echo $t['login_system']; ?>
            </a>
            <a href="../register.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-user-plus me-2"></i><?php echo $t['create_account']; ?>
            </a>
        </div>
        <div class="mt-4">
            <small class="text-light opacity-75"><?php echo $t['need_help']; ?></small>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Simple animation for elements when they come into view
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.service-card, .process-step, .benefit-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});
</script>
</body>
</html>