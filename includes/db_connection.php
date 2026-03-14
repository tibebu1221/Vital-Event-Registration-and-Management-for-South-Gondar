<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "VERMS";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$create_db_query = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($create_db_query) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($database);

// Array of table creation queries
$table_creation_queries = [

    // Users table with 'statistician' role and status & active columns added
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(30) NOT NULL,
        kebele VARCHAR(100) NOT NULL,
        woreda VARCHAR(100) NOT NULL,
        zone VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('kebele', 'woreda', 'zone', 'admin', 'citizen', 'statistician') NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // Requests table
    "CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        citizen_id INT NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        details TEXT NOT NULL,
        status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (citizen_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // Birth Events (full certificate fields)
    "CREATE TABLE IF NOT EXISTS birth_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        form_number VARCHAR(50) UNIQUE,
        registration_uid VARCHAR(50) UNIQUE,
        child_name VARCHAR(100),
        father_name VARCHAR(100),
        grandfather_name VARCHAR(100),
        sex ENUM('Male','Female'),
        date_of_birth DATE,
        place_of_birth INT,
        mother_full_name VARCHAR(100),
        father_full_name VARCHAR(100),
        parents_nationality VARCHAR(100),
        registered_date DATE,
        issue_date DATE,
        photo VARCHAR(255),
        notes TEXT,
        status ENUM('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (place_of_birth) REFERENCES kebeles(id)
    ) ENGINE=InnoDB",

    // Marriage Events
    "CREATE TABLE IF NOT EXISTS marriage_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        form_number VARCHAR(50) UNIQUE,
        registration_uid VARCHAR(50) UNIQUE,
        husband_name VARCHAR(100),
        wife_name VARCHAR(100),
        marriage_date DATE,
        place_of_marriage INT,
        witness_1 VARCHAR(100),
        witness_2 VARCHAR(100),
        registered_date DATE,
        issue_date DATE,
        husband_photo VARCHAR(255),
        wife_photo VARCHAR(255),
        notes TEXT,
        status ENUM('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (place_of_marriage) REFERENCES kebeles(id)
    ) ENGINE=InnoDB",

    // Death Events
    "CREATE TABLE IF NOT EXISTS death_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        form_number VARCHAR(50) UNIQUE,
        registration_uid VARCHAR(50) UNIQUE,
        deceased_name VARCHAR(100),
        sex ENUM('Male','Female'),
        date_of_death DATE,
        place_of_death INT,
        age INT,
        cause_of_death VARCHAR(200),
        reporter_full_name VARCHAR(100),
        relationship VARCHAR(100),
        registered_date DATE,
        issue_date DATE,
        photo VARCHAR(255),
        notes TEXT,
        status ENUM('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (place_of_death) REFERENCES kebeles(id)
    ) ENGINE=InnoDB",

    // Divorce Events (detailed, similar to marriage_events)
    "CREATE TABLE IF NOT EXISTS divorce_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        form_number VARCHAR(50) UNIQUE,
        registration_uid VARCHAR(50) UNIQUE,
        husband_name VARCHAR(100),
        wife_name VARCHAR(100),
        divorce_date DATE,
        place_of_divorce INT,
        witness_1 VARCHAR(100),
        witness_2 VARCHAR(100),
        registered_date DATE,
        issue_date DATE,
        husband_photo VARCHAR(255),
        wife_photo VARCHAR(255),
        notes TEXT,
        status ENUM('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (place_of_divorce) REFERENCES kebeles(id)
    ) ENGINE=InnoDB",

    // Feedback table
    "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        message TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    // Zones table
    "CREATE TABLE IF NOT EXISTS zones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_name VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        zone_officer_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (zone_officer_id) REFERENCES users(id)
    ) ENGINE=InnoDB",

    // Woredas table
    "CREATE TABLE IF NOT EXISTS woredas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_id INT NOT NULL,
        woreda_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        woreda_officer_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (zone_id) REFERENCES zones(id),
        FOREIGN KEY (woreda_officer_id) REFERENCES users(id),
        UNIQUE (zone_id, woreda_name)
    ) ENGINE=InnoDB",

    // Kebeles table
    "CREATE TABLE IF NOT EXISTS kebeles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_id INT NOT NULL,
        woreda_id INT NOT NULL,
        kebele_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        kebele_officer_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (zone_id) REFERENCES zones(id),
        FOREIGN KEY (woreda_id) REFERENCES woredas(id),
        FOREIGN KEY (kebele_officer_id) REFERENCES users(id),
        UNIQUE (woreda_id, kebele_name)
    ) ENGINE=InnoDB"
];

// Execute each query
foreach ($table_creation_queries as $query) {
    if ($conn->query($query) !== TRUE) {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Optional: Add default admin user
$default_admin_username = "admin";
$check_admin_query = $conn->prepare("SELECT * FROM users WHERE username = ?");
$check_admin_query->bind_param("s", $default_admin_username);
$check_admin_query->execute();
$admin_result = $check_admin_query->get_result();

if ($admin_result->num_rows == 0) {
    $default_admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $default_admin_email = "admin@verms.com";
    $default_admin_fullname = "System Administrator";
    $default_admin_role = "admin";
    $default_admin_phone = "";
    $default_admin_kebele = "";
    $default_admin_woreda = "";
    $default_admin_zone = "";

    $add_admin_query = $conn->prepare("INSERT INTO users (fullname, username, email, phone, kebele, woreda, zone, password, role, status, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)");
    $add_admin_query->bind_param("sssssssss", $default_admin_fullname, $default_admin_username, $default_admin_email, $default_admin_phone, $default_admin_kebele, $default_admin_woreda, $default_admin_zone, $default_admin_password, $default_admin_role);
    $add_admin_query->execute();
}

// If the database already exists and the status or active columns are missing, add them
$check_status_column = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($check_status_column->num_rows == 0) {
    $add_status_column_query = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER role";
    $conn->query($add_status_column_query);
}
$check_active_column = $conn->query("SHOW COLUMNS FROM users LIKE 'active'");
if ($check_active_column->num_rows == 0) {
    $add_active_column_query = "ALTER TABLE users ADD COLUMN active TINYINT(1) DEFAULT 1 AFTER status";
    $conn->query($add_active_column_query);
}
?>