<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Language management
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default language
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Translations array
$translations = [
    'en' => [
        'title' => 'VERMS - Vital Events Registration Management System',
        'hero_title' => 'South Gondar Zone Vital Events',
        'hero_subtitle' => 'Vital Events Registration Management System - Serving South Gondar Zone citizens',
        'get_started' => 'Get Started',
        'go_dashboard' => 'Go to Dashboard',
        'zones' => 'Zones',
        'woredas' => 'Woredas',
        'kebeles' => 'Kebeles',
        'total_population' => 'Total Population',
        'our_services' => 'Our Services',
        'birth_registration' => 'Birth Registration',
        'birth_desc' => 'Register and manage birth records efficiently with our streamlined digital system. Issue official birth certificates with ease.',
        'approved_registrations' => 'Approved Registrations',
        'marriage_registration' => 'Marriage Registration',
        'marriage_desc' => 'Record marriage events and issue official certificates. Simplify the marriage registration process for citizens.',
        'death_registration' => 'Death Registration',
        'death_desc' => 'Ensure accurate documentation of death events with our comprehensive registration system.',
        'divorce_registration' => 'Divorce Registration',
        'divorce_desc' => 'Manage divorce proceedings and maintain proper records for legal documentation.',
        'certificate_issuance' => 'Certificate Issuance',
        'certificate_desc' => 'Generate and print official certificates for all registered vital events with security features.',
        'analytics_reports' => 'Analytics & Reports',
        'analytics_desc' => 'Access comprehensive reports and analytics for better decision making and planning.',
        'professional_documentation' => 'Professional Documentation',
        'real_time_statistics' => 'Real-time Statistics',
        'access_system' => 'Access System',
        'our_vital_services' => 'Our Vital Events Services',
        'birth_slide_title' => 'Birth Registration',
        'birth_slide_desc' => 'Welcome new life with proper documentation. Our birth registration service ensures every child is officially recognized and documented from day one.',
        'marriage_slide_title' => 'Marriage Registration',
        'marriage_slide_desc' => 'Celebrate your union with official recognition. Register your marriage and receive certified documentation for your lifelong commitment.',
        'death_slide_title' => 'Death Registration',
        'death_slide_desc' => 'Handle sensitive moments with care and professionalism. Our death registration service provides respectful and accurate documentation.',
        'divorce_slide_title' => 'Divorce Registration',
        'divorce_slide_desc' => 'Manage legal separations with proper documentation. Ensure all legal requirements are met with our comprehensive divorce registration service.',
        'what_people_say' => 'What People Say',
        'testimonial1' => '"Registering my marriage through VERMS was very easy. I completed everything online without needing to visit the office multiple times!"',
        'testimonial2' => '"The birth registration process for my newborn was fast and clear. The staff were supportive, and I received the certificate on time."',
        'testimonial3' => '"I am grateful for how smoothly the system handled my late father\'s death registration. Everything was respectful and efficient."',
        'testimonial4' => '"This online system saved me time when updating our household data. I didn\'t have to queue at the office anymore."',
        'testimonial5' => '"I appreciated how transparent and quick the verification process was. The officers were professional and kind."',
        'testimonial6' => '"The new VERMS platform is very user-friendly. I easily registered my child\'s birth from home using my phone."',
        'ready_get_started' => 'Ready to Get Started?',
        'cta_text' => 'Join our community in simplifying vital event registrations for South Gondar Zone',
        'create_account' => 'Create Account',
        'login_system' => 'Login to System',
        'logout' => 'Logout',
        'from' => 'from',
        'previous' => 'Previous',
        'next' => 'Next',
        'view_details' => 'View Details',
        'dashboard' => 'Dashboard'
    ],
    'am' => [
        'title' => 'ቪ.ኢ.አር.ኤም.ኤስ - የህይወት ክስተቶች ምዝገባ አስተዳደር ስርዓት',
        'hero_title' => 'የደቡብ ጎንደር ዞን ህይወት ክስተቶች',
        'hero_subtitle' => 'የህይወት ክስተቶች ምዝገባ አስተዳደር ስርዓት - ለደቡብ ጎንደር ዞን ዜጎች አገልግሎት የሚሰጥ',
        'get_started' => 'ጀምር',
        'go_dashboard' => 'ወደ ዳሽቦርድ ይሂዱ',
        'zones' => 'ዞኖች',
        'woredas' => 'ወረዳዎች',
        'kebeles' => 'ቀበሌዎች',
        'total_population' => 'አጠቃላይ የህዝብ ብዛት',
        'our_services' => 'አገልግሎቶቻችን',
        'birth_registration' => 'የልደት ምዝገባ',
        'birth_desc' => 'የልደት መዛግብቶችን በውጤታማነት ይመዝግቡ እና ያስተዳድሩ። ኦፊሴላዊ የልደት የምስክር ወረቀቶችን በቀላሉ ይስጡ።',
        'approved_registrations' => 'የተፈቀዱ ምዝገባዎች',
        'marriage_registration' => 'የጋብቻ ምዝገባ',
        'marriage_desc' => 'የጋብቻ ክስተቶችን ይመዝግቡ እና ኦፊሴላዊ የምስክር ወረቀቶችን ይስጡ። ለዜጎች የጋብቻ ምዝገባ ሂደቱን ያቃልሉ።',
        'death_registration' => 'የሞት ምዝገባ',
        'death_desc' => 'የሞት ክስተቶችን ትክክለኛ ሰነድ እንዲኖራቸው በአጠቃላይ ምዝገባ ስርዓታችን ያረጋግጡ።',
        'divorce_registration' => 'የፍቺ ምዝገባ',
        'divorce_desc' => 'የፍቺ ሂደቶችን ያስተዳድሩ እና ህጋዊ ሰነድ ትክክለኛ መዛግብቶችን ይጠብቁ።',
        'certificate_issuance' => 'የምስክር ወረቀት ማሰራጨት',
        'certificate_desc' => 'ለሁሉም የተመዘገቡ የህይወት ክስተቶች ኦፊሴላዊ የምስክር ወረቀቶችን ከደህንነት ባህሪያት ጋር ይፍጠሩ እና ይተክሉ።',
        'analytics_reports' => 'ትንታኔ እና ዘገባዎች',
        'analytics_desc' => 'ለተሻለ ውሳኔ እና ዕቅድ የተዋቀሩ ዘገባዎችን እና ትንታኔዎችን ይድረሱ።',
        'professional_documentation' => 'ሙያዊ ሰነድ አሰራር',
        'real_time_statistics' => 'በቀጥታ ስታቲስቲክስ',
        'access_system' => 'ወደ ስርዓቱ ይግቡ',
        'our_vital_services' => 'የህይወት ክስተቶች አገልግሎቶቻችን',
        'birth_slide_title' => 'የልደት ምዝገባ',
        'birth_slide_desc' => 'አዲስ ህይወትን በትክክለኛ ሰነድ ይቀበሉ። የልደት ምዝገባ አገልግሎታችን እያንዳንዱ ህፃን ከመጀመሪያው ቀን ጀምሮ በይፋ እንዲታወቅ እና እንዲመዘገብ ያረጋግጣል።',
        'marriage_slide_title' => 'የጋብቻ ምዝገባ',
        'marriage_slide_desc' => 'ትህትናዎን በይፋ እውቅና አሳድሮ ይዘውት። ጋብቻዎን ይመዝግቡ እና ለዘላለማዊ ቁርጠኝነትዎ የተመሰከረለት የምስክር ወረቀት ያግኙ።',
        'death_slide_title' => 'የሞት ምዝገባ',
        'death_slide_desc' => 'ስሜታዊ ጊዜዎችን በማንነት እና በሙያዊነት ያስተናግዱ። የሞት ምዝገባ አገልግሎታችን አክብሮት እና ትክክለኛ ሰነድ ያቀርባል።',
        'divorce_slide_title' => 'የፍቺ ምዝገባ',
        'divorce_slide_desc' => 'ህጋዊ መለያየቶችን በትክክለኛ ሰነድ ያስተዳድሩ። ሁሉም ህጋዊ መስፈርቶች በአጠቃላይ የፍቺ ምዝገባ አገልግሎታችን እንደተሟሉ ያረጋግጡ።',
        'what_people_say' => 'ሰዎች ምን ይላሉ',
        'testimonial1' => '"ቪ.ኢ.አር.ኤም.ኤስ በኩል ጋብቻዬን ማመዝገብ በጣም ቀላል ነበር። ሁሉንም ነገር በመስመር ላይ ሳለሁ አጠናቀርኩት፣ በብዙ ጊዜ ቢሮ ለመጎብኘት አልፈለግኩም!"',
        'testimonial2' => '"ለአዲስ የተወለደ ህፃኔ የልደት ምዝገባ ሂደቱ ፈጣን እና ግልጽ ነበር። ሰራተኞቹ ድጋፍ አደረጉ እናም የምስክር ወረቀቱን በጊዜው አገኘሁ።"',
        'testimonial3' => '"ስለ የአባቴ ሞት ምዝገባ ስርዓቱ እንዴት በቀላሉ እንደተነጋገረ እመሰግናለሁ። ሁሉም ነገር አክብሮት እና ውጤታማ ነበር።"',
        'testimonial4' => '"ይህ የመስመር ላይ ስርዓት የቤተሰብ መረጃ ስናዘምን ጊዜ አስቀረልኝ። በቢሮ ላይ መስለብ አልፈለግኩም።"',
        'testimonial5' => '"የማረጋገጫ ሂደቱ እንዴት ግልጽ እና ፈጣን እንደነበር አደነቅሁ። ባለሙያዎቹ ሙያዊ እና ደግ ነበሩ።"',
        'testimonial6' => '"አዲሱ የቪ.ኢ.አር.ኤም.ኤስ መድረክ በጣም ለተጠቃሚ የሚመች ነው። የህፃኔን ልደት ከቤት በሞባይል ስልኬ በቀላሉ አመዘገብኩት።"',
        'ready_get_started' => 'ለመጀመር ዝግጁ ነዎት?',
        'cta_text' => 'ለደቡብ ጎንደር ዞን የህይወት ክስተቶችን ምዝገባ ለማቃለል ማህበረሰባችንን ይቀላቀሉ',
        'create_account' => 'መለያ ይፍጠሩ',
        'login_system' => 'ወደ ስርዓቱ ይግቡ',
        'logout' => 'ውጣ',
        'from' => 'ከ',
        'previous' => 'ቀዳሚ',
        'next' => 'ቀጣይ',
        'view_details' => 'ዝርዝሮችን ይመልከቱ',
        'dashboard' => 'ዳሽቦርድ'
    ]
];

// Translation helper function
function t($key) {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('title'); ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
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
            background-color: #f8f9fa;
            font-family: <?php echo $lang === 'am' ? "'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" : "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"; ?>;
            line-height: 1.6;
            <?php if($lang === 'am'): ?>
            text-align: justify;
            <?php endif; ?>
        }

        /* Fixed Language Switcher - Moved to Header */
        .language-switcher-header {
            position: absolute;
            top: 15px;
            right: 250px;
            z-index: 1030;
        }

        .lang-btn-header {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            color: var(--primary-color);
            text-decoration: none;
            display: inline-block;
            margin-left: 5px;
        }

        .lang-btn-header.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .lang-btn-header:hover:not(.active) {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Alternative: Floating language switcher for logged out state */
        .language-switcher-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 25px;
            box-shadow: var(--shadow);
            padding: 5px;
            display: flex;
            gap: 5px;
        }

        .lang-btn-floating {
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            background: transparent;
            font-weight: 500;
            transition: var(--transition);
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .lang-btn-floating.active {
            background: var(--gradient-primary);
            color: white;
        }

        .lang-btn-floating:hover:not(.active) {
            background: rgba(44, 62, 80, 0.1);
        }

        /* Adjust header for language switcher */
        .navbar {
            position: relative;
            min-height: 70px; /* Ensure enough space for language switcher */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .language-switcher-header {
                top: 10px;
                right: 10px;
            }
            
            .lang-btn-header {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
            
            .navbar {
                min-height: 80px; /* More space on mobile */
            }
            
            /* Stack language buttons on small screens if they don't fit */
            @media (max-width: 100px) {
                .language-switcher-header {
                    display: flex;
                    flex-direction: column;
                    gap:0px;
                }
            }
        }

        @media (max-width: 576px) {
            .language-switcher-header {
                position: static;
                margin-top: 100px;
                text-align: center;
            }
            
            .navbar {
                min-height: auto;
                padding-bottom: 1px;
            }
        }

        /* Ensure header buttons don't overlap */
        .navbar-nav {
            margin-right: 10px; /* Space for language switcher */
        }

        /* Hero Section with Debre_Tabor1.jpg background */
        .hero-section {
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.85) 0%, rgba(52, 152, 219, 0.85) 100%), 
                        url('images/slider/Debre_Tabor1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            text-align: center;
            padding: 180px 0;
            position: relative;
            overflow: hidden;
            min-height: 700px;
            display: flex;
            align-items: center;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 3.8rem;
            margin-bottom: 25px;
            font-weight: 800;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
        }

        .hero-section p {
            font-size: 1.6rem;
            margin-bottom: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            background: rgba(0, 0, 0, 0.4);
            padding: 20px 30px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }

        .btn-hero {
            background: rgba(255, 255, 255, 0.25);
            border: 3px solid white;
            color: white;
            padding: 15px 40px;
            font-size: 1.3rem;
            border-radius: 50px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .btn-hero:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .features-section {
            padding: 100px 0;
            background-color: #fff;
            text-align: center;
        }

        .section-title {
            font-size: 2.8rem;
            margin-bottom: 60px;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            margin: 15px auto;
            border-radius: 2px;
        }

        .feature {
            padding: 40px 20px;
            border-radius: 15px;
            transition: var(--transition);
            height: 100%;
            background: white;
            box-shadow: var(--shadow);
            border-top: 4px solid transparent;
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            border-top: 4px solid var(--secondary-color);
        }

        .feature i {
            font-size: 3.5rem;
            margin-bottom: 25px;
            display: inline-block;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .feature p {
            color: #666;
            font-size: 1.1rem;
        }

        .calendar-clock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 40px 0;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .calendar-clock .calendar,
        .calendar-clock .clock {
            background: var(--gradient-primary);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: var(--shadow);
        }

        .carousel-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .icon-slide {
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            color: white;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .icon-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .icon-slide-content {
            position: relative;
            z-index: 2;
        }

        .slide-icon {
            font-size: 8rem;
            margin-bottom: 30px;
            opacity: 0.9;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
        }

        .slide-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        }

        .slide-description {
            font-size: 1.3rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
            opacity: 0.95;
        }

        .carousel-indicators {
            bottom: -60px;
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
            background-color: var(--primary-color);
            opacity: 0.5;
        }

        .carousel-indicators button.active {
            opacity: 1;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        .testimonial-section {
            padding: 100px 0;
            background-color: #fff;
            text-align: center;
        }

        .testimonial-card {
            padding: 30px;
            border-radius: 15px;
            transition: var(--transition);
            height: 100%;
            background: white;
            box-shadow: var(--shadow);
            border-left: 4px solid transparent;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--secondary-color);
        }

        .testimonial-card i {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .testimonial-card p {
            font-style: italic;
            margin-bottom: 20px;
            color: #555;
        }

        .testimonial-card strong {
            color: var(--primary-color);
        }

        .stats-section {
            padding: 80px 0;
            background: var(--gradient-primary);
            color: white;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .cta-section {
            padding: 100px 0;
            background: var(--gradient-secondary);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            transition: var(--transition);
            font-weight: 600;
            border: none;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        <?php if($lang === 'am'): ?>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
        
        .amharic-text {
            direction: ltr;
            text-align: left;
        }
        
        .hero-section h1,
        .section-title,
        .feature h3,
        .slide-title,
        .cta-section h2 {
            font-weight: 700;
        }
        
        .hero-section p,
        .feature p,
        .slide-description,
        .testimonial-card p {
            font-weight: 400;
        }
        <?php endif; ?>

        @media (max-width: 768px) {
            .hero-section {
                padding: 120px 0;
                min-height: 500px;
                background-attachment: scroll;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section p {
                font-size: 1.2rem;
                padding: 15px 20px;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .calendar-clock {
                flex-direction: column;
                gap: 20px;
            }
            
            .calendar-clock .calendar,
            .calendar-clock .clock {
                width: 100%;
                text-align: center;
            }
            
            .slide-icon {
                font-size: 5rem;
            }
            
            .slide-title {
                font-size: 2rem;
            }
            
            .slide-description {
                font-size: 1.1rem;
            }
            
            .icon-slide {
                padding: 60px 20px;
                min-height: 300px;
            }
            
            .navbar-nav {
                margin-right: 0; /* Reset on mobile */
            }
            
            .btn-hero {
                padding: 12px 30px;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 100px 0;
                min-height: 400px;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
                padding: 10px 15px;
            }
            
            .language-switcher-header {
                position: static;
                margin-top: 100px;
                text-align: center;
            }
            
            .navbar {
                min-height: auto;
                padding-bottom: 1px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php 
    // Try different possible paths for the header
    $header_paths = ['includes/header.php', '../includes/header.php', './includes/header.php'];
    $header_found = false;
    
    foreach ($header_paths as $path) {
        if (file_exists($path)) {
            // Include the header and add language switcher to it
            ob_start();
            include $path;
            $header_content = ob_get_clean();
            
            // Check if the header has navbar
            if (strpos($header_content, 'navbar') !== false) {
                // Insert language switcher into the navbar
                $language_switcher = '
                <div class="language-switcher-header">
                    <a href="?lang=en" class="lang-btn-header ' . ($lang === 'en' ? 'active' : '') . '">
                        <i class="fas fa-globe-americas me-1"></i>EN
                    </a>
                    <a href="?lang=am" class="lang-btn-header ' . ($lang === 'am' ? 'active' : '') . '">
                        <i class="fas fa-globe-africa me-1"></i>አማ
                    </a>
                </div>';
                
                // Insert language switcher at the end of navbar
                $header_content = str_replace('</nav>', $language_switcher . '</nav>', $header_content);
            }
            
            echo $header_content;
            $header_found = true;
            break;
        }
    }
    
    if (!$header_found) {
        // Fallback simple header with integrated language switcher
        echo '
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-certificate me-2"></i>VERMS
                </a>
                <div class="navbar-nav ms-auto">';
        
        if (isset($_SESSION['user_id'])) {
            echo '<a class="nav-link" href="logout.php">' . t('logout') . '</a>';
        } else {
            echo '
            <a class="nav-link" href="login.php">' . t('login_system') . '</a>
            <a class="nav-link" href="register.php">' . t('create_account') . '</a>';
        }
        
        echo '
                </div>
                <!-- Language Switcher integrated in header -->
                <div class="language-switcher-header">
                    <a href="?lang=en" class="lang-btn-header ' . ($lang === 'en' ? 'active' : '') . '">
                        <i class="fas fa-globe-americas me-1"></i>EN
                    </a>
                    <a href="?lang=am" class="lang-btn-header ' . ($lang === 'am' ? 'active' : '') . '">
                        <i class="fas fa-globe-africa me-1"></i>አማ
                    </a>
                </div>
            </div>
        </nav>';
    }
    ?>

    <!-- Hero Section with Debre_Tabor1.jpg background -->
    <section class="hero-section">
        <div class="container">
            <h1><?php echo t('hero_title'); ?></h1>
            <p><?php echo t('hero_subtitle'); ?></p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-hero btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i><?php echo t('get_started'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo $_SESSION['role'] ?? 'citizen'; ?>/dashboard.php" class="btn btn-hero btn-lg">
                    <i class="fas fa-tachometer-alt me-2"></i><?php echo t('go_dashboard'); ?>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <?php
                // Database connection with error handling
                $db_connected = false;
                $zone_count = $woreda_count = $kebele_count = $total_population = 0;
                $birth_count = $marriage_count = $death_count = $divorce_count = 0;
                
                // Try different possible paths for database connection
                $db_paths = ['includes/db_connection.php', '../includes/db_connection.php', './includes/db_connection.php'];
                
                foreach ($db_paths as $db_path) {
                    if (file_exists($db_path)) {
                        require_once $db_path;
                        $db_connected = true;
                        
                        // Get statistics if database is connected
                        try {
                            // Count zones
                            $zone_result = $conn->query("SELECT COUNT(*) as count FROM zones");
                            if ($zone_result) {
                                $zone_count = $zone_result->fetch_assoc()['count'];
                            }
                            
                            // Count woredas
                            $woreda_result = $conn->query("SELECT COUNT(*) as count FROM woredas");
                            if ($woreda_result) {
                                $woreda_count = $woreda_result->fetch_assoc()['count'];
                            }
                            
                            // Count kebeles
                            $kebele_result = $conn->query("SELECT COUNT(*) as count FROM kebeles");
                            if ($kebele_result) {
                                $kebele_count = $kebele_result->fetch_assoc()['count'];
                            }
                            
                            // Count birth registrations
                            $birth_result = $conn->query("SELECT COUNT(*) as count FROM birth_events WHERE status = 'Approved'");
                            if ($birth_result) {
                                $birth_count = $birth_result->fetch_assoc()['count'];
                            }
                            
                            // Count death registrations
                            $death_result = $conn->query("SELECT COUNT(*) as count FROM death_events WHERE status = 'Approved'");
                            if ($death_result) {
                                $death_count = $death_result->fetch_assoc()['count'];
                            }
                            
                            // Calculate total population as (births - deaths)
                            $total_population = $birth_count - $death_count;
                            if ($total_population < 0) {
                                $total_population = 0;
                            }
                            
                            // Count marriage registrations (for features section)
                            $marriage_result = $conn->query("SELECT COUNT(*) as count FROM marriage_events WHERE status = 'Approved'");
                            if ($marriage_result) {
                                $marriage_count = $marriage_result->fetch_assoc()['count'];
                            }
                            
                            // Count divorce registrations (for features section)
                            $divorce_result = $conn->query("SELECT COUNT(*) as count FROM divorce_events WHERE status = 'Approved'");
                            if ($divorce_result) {
                                $divorce_count = $divorce_result->fetch_assoc()['count'];
                            }
                            
                        } catch (Exception $e) {
                            // If there's an error with the queries, use default values
                            error_log("Database query error: " . $e->getMessage());
                        }
                        
                        break;
                    }
                }
                
                if (!$db_connected) {
                    // Use default demo data if database is not connected
                    $zone_count = 1;
                    $woreda_count = 24;
                    $kebele_count = 589;
                    $birth_count = 12500;
                    $death_count = 6200;
                    $total_population = $birth_count - $death_count;
                    $marriage_count = 8500;
                    $divorce_count = 1800;
                }
                ?>
                
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $zone_count; ?></div>
                        <div class="stat-label"><?php echo t('zones'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $woreda_count; ?></div>
                        <div class="stat-label"><?php echo t('woredas'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $kebele_count; ?></div>
                        <div class="stat-label"><?php echo t('kebeles'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php echo number_format($total_population); ?>
                        </div>
                        <div class="stat-label"><?php echo t('total_population'); ?></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title"><?php echo t('our_services'); ?></h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-baby"></i>
                        <h3><?php echo t('birth_registration'); ?></h3>
                        <p><?php echo t('birth_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo number_format($birth_count); ?> <?php echo t('approved_registrations'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-ring"></i>
                        <h3><?php echo t('marriage_registration'); ?></h3>
                        <p><?php echo t('marriage_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo number_format($marriage_count); ?> <?php echo t('approved_registrations'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-book-dead"></i>
                        <h3><?php echo t('death_registration'); ?></h3>
                        <p><?php echo t('death_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo number_format($death_count); ?> <?php echo t('approved_registrations'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-file-contract"></i>
                        <h3><?php echo t('divorce_registration'); ?></h3>
                        <p><?php echo t('divorce_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo number_format($divorce_count); ?> <?php echo t('approved_registrations'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-certificate"></i>
                        <h3><?php echo t('certificate_issuance'); ?></h3>
                        <p><?php echo t('certificate_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo t('professional_documentation'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature">
                        <i class="fas fa-chart-line"></i>
                        <h3><?php echo t('analytics_reports'); ?></h3>
                        <p><?php echo t('analytics_desc'); ?></p>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?php echo t('real_time_statistics'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calendar & Clock -->
    <div class="container">
        <div class="calendar-clock">
            <div class="calendar">
                <i class="fas fa-calendar-alt me-2"></i>
                <span id="date"></span>
            </div>
            <div>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i><?php echo t('access_system'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $_SESSION['role'] ?? 'citizen'; ?>/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i><?php echo t('go_dashboard'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="clock">
                <i class="fas fa-clock me-2"></i>
                <span id="time"></span>
            </div>
        </div>
    </div>

    <!-- Icon Carousel Section -->
    <section class="carousel-section">
        <div class="container">
            <h2 class="section-title text-center mb-5"><?php echo t('our_vital_services'); ?></h2>
            <div id="iconCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <!-- Birth Registration Slide -->
                    <div class="carousel-item active">
                        <div class="icon-slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="icon-slide-content">
                                <i class="fas fa-baby slide-icon"></i>
                                <h2 class="slide-title"><?php echo t('birth_slide_title'); ?></h2>
                                <p class="slide-description">
                                    <?php echo t('birth_slide_desc'); ?>
                                </p>
                                <div class="mt-4">
                                    <h4><?php echo number_format($birth_count); ?> <?php echo t('approved_registrations'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Marriage Registration Slide -->
                    <div class="carousel-item">
                        <div class="icon-slide" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="icon-slide-content">
                                <i class="fas fa-ring slide-icon"></i>
                                <h2 class="slide-title"><?php echo t('marriage_slide_title'); ?></h2>
                                <p class="slide-description">
                                    <?php echo t('marriage_slide_desc'); ?>
                                </p>
                                <div class="mt-4">
                                    <h4><?php echo number_format($marriage_count); ?> <?php echo t('approved_registrations'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Death Registration Slide -->
                    <div class="carousel-item">
                        <div class="icon-slide" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="icon-slide-content">
                                <i class="fas fa-book-dead slide-icon"></i>
                                <h2 class="slide-title"><?php echo t('death_slide_title'); ?></h2>
                                <p class="slide-description">
                                    <?php echo t('death_slide_desc'); ?>
                                </p>
                                <div class="mt-4">
                                    <h4><?php echo number_format($death_count); ?> <?php echo t('approved_registrations'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Divorce Registration Slide -->
                    <div class="carousel-item">
                        <div class="icon-slide" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="icon-slide-content">
                                <i class="fas fa-file-contract slide-icon"></i>
                                <h2 class="slide-title"><?php echo t('divorce_slide_title'); ?></h2>
                                <p class="slide-description">
                                    <?php echo t('divorce_slide_desc'); ?>
                                </p>
                                <div class="mt-4">
                                    <h4><?php echo number_format($divorce_count); ?> <?php echo t('approved_registrations'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#iconCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo t('previous'); ?></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#iconCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo t('next'); ?></span>
                </button>

                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <li data-bs-target="#iconCarousel" data-bs-slide-to="0" class="active"></li>
                    <li data-bs-target="#iconCarousel" data-bs-slide-to="1"></li>
                    <li data-bs-target="#iconCarousel" data-bs-slide-to="2"></li>
                    <li data-bs-target="#iconCarousel" data-bs-slide-to="3"></li>
                </ol>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonial-section">
        <div class="container">
            <h2 class="section-title"><?php echo t('what_people_say'); ?></h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial1'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'ዳዊት መንገሻ' : 'Dawit Mengesha'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'ደብረ ታቦር ወረዳ, ቀበሌ 01' : 'Debre Tabor Woreda, Kebele 01'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial2'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'ሀብታሙ ዓለሙ' : 'Habtamu Alemu'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'እብናት ወረዳ, ቀበሌ 06' : 'Ebnat Woreda, Kebele 06'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial3'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'ውቢት አስናቀ' : 'Wubit Asnake'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'ላይ ጋይንት ወረዳ, ቀበሌ 04' : 'Lay Gayint Woreda, Kebele 04'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial4'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'ጌታቸው ሙሉ' : 'Getachew Mulu'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'ስማዳ ወረዳ, ቀበሌ 02' : 'Simada Woreda, Kebele 02'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial5'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'አልማዝ በቀለ' : 'Almaz Bekele'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'ደራ ወረዳ, ቀበሌ 05' : 'Dera Woreda, Kebele 05'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-user"></i>
                        <p><?php echo t('testimonial6'); ?></p>
                        <strong>- <?php echo $lang === 'am' ? 'ብንያም ተስፋዬ' : 'Biniyam Tesfaye'; ?></strong>
                        <br><small class="text-muted"><?php echo t('from'); ?> <?php echo $lang === 'am' ? 'እስቴ ወረዳ, ቀበሌ 01' : 'Este Woreda, Kebele 01'; ?>, <?php echo $lang === 'am' ? 'ደቡብ ጎንደር ዞን' : 'South Gondar Zone'; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2><?php echo t('ready_get_started'); ?></h2>
            <p class="mb-4"><?php echo t('cta_text'); ?></p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-cta me-3"><?php echo t('create_account'); ?></a>
                <a href="login.php" class="btn btn-outline-light"><?php echo t('login_system'); ?></a>
            <?php else: ?>
                <a href="<?php echo $_SESSION['role'] ?? 'citizen'; ?>/dashboard.php" class="btn btn-cta me-3"><?php echo t('go_dashboard'); ?></a>
                <a href="logout.php" class="btn btn-outline-light"><?php echo t('logout'); ?></a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php 
    // Try different possible paths for the footer
    $footer_paths = ['includes/footer.php', '../includes/footer.php', './includes/footer.php'];
    $footer_found = false;
    
    foreach ($footer_paths as $path) {
        if (file_exists($path)) {
            include $path;
            $footer_found = true;
            break;
        }
    }
    
    if (!$footer_found) {
        // Fallback simple footer
        echo '
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container text-center">
                <p>&copy; 2024 VERMS - ' . ($lang === 'am' ? 'የህይወት ክስተቶች ምዝገባ አስተዳደር ስርዓት' : 'Vital Events Registration Management System') . '. ' . ($lang === 'am' ? 'ሁሉም መብቶች የተጠበቁ ናቸው' : 'All rights reserved') . '.</p>
            </div>
        </footer>';
    }
    ?>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDate() {
            const dateElement = document.getElementById("date");
            const lang = '<?php echo $lang; ?>';
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            
            if (lang === 'am') {
                // Ethiopian date in Amharic
                const ethiopianMonths = ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ', 'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'];
                const ethiopianDays = ['እሑድ', 'ሰኞ', 'ማክሰኞ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ'];
                
                const now = new Date();
                const day = now.getDay();
                const date = now.getDate();
                const month = now.getMonth();
                const year = now.getFullYear();
                
                const ethDate = `${ethiopianDays[day]}, ${date} ${ethiopianMonths[month]} ${year}`;
                dateElement.textContent = ethDate;
            } else {
                dateElement.textContent = new Date().toLocaleDateString('en-US', options);
            }
        }

        function updateTime() {
            const timeElement = document.getElementById("time");
            const lang = '<?php echo $lang; ?>';
            const now = new Date();
            
            if (lang === 'am') {
                // Ethiopian time (12-hour format)
                let hours = now.getHours();
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'ከሰዓት' : 'ጥዋት';
                hours = hours % 12 || 12;
                
                const ethTime = `${hours}:${minutes} ${ampm}`;
                timeElement.textContent = ethTime;
            } else {
                timeElement.textContent = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }
        }

        // Initialize date and time
        updateDate();
        updateTime();
        
        // Update time every second
        setInterval(updateTime, 1000);
        
        // Carousel auto-advance
        $(document).ready(function(){
            $('#iconCarousel').carousel({
                interval: 5000,
                pause: "hover"
            });
        });

        // Animate statistics counting
        $(document).ready(function() {
            $('.stat-number').each(function() {
                const $this = $(this);
                let countText = $this.text().replace(/,/g, '');
                const countTo = parseInt(countText);
                
                if (!isNaN(countTo)) {
                    $({ countNum: 0 }).animate({ countNum: countTo }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.floor(this.countNum).toLocaleString());
                        },
                        complete: function() {
                            $this.text(countTo.toLocaleString());
                        }
                    });
                }
            });
        });

        // Save language preference in localStorage
        $(document).ready(function() {
            const lang = '<?php echo $lang; ?>';
            localStorage.setItem('preferred_lang', lang);
        });
    </script>
</body>
</html>