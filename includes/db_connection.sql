-- Users table (add status and active for deactivation/reactivation)
CREATE TABLE users (
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
    status ENUM('active','inactive') DEFAULT 'active',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Zones table
CREATE TABLE zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    zone_officer_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_officer_id) REFERENCES users(id) ON DELETE SET NULL,
    aaaa  int
) ENGINE=InnoDB;

-- Woredas table
CREATE TABLE woredas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    woreda_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    woreda_officer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    FOREIGN KEY (woreda_officer_id) REFERENCES users(id),
    UNIQUE (zone_id, woreda_name)
) ENGINE=InnoDB;

-- Kebeles table
CREATE TABLE kebeles (
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
) ENGINE=InnoDB;

-- Requests table (after users is created!)
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    details TEXT NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Birth Events
CREATE TABLE birth_events (
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_of_birth) REFERENCES kebeles(id)
) ENGINE=InnoDB;

-- Marriage Events
CREATE TABLE marriage_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    form_number VARCHAR(50) UNIQUE,
    registration_uid VARCHAR(50) UNIQUE,
    husband_name VARCHAR(100),
    wife_name VARCHAR(100),
    marriage_date DATE,
    place_of_marriage VARCHAR(100),
    witness_1 VARCHAR(100),
    witness_2 VARCHAR(100),
    registered_date DATE,
    issue_date DATE,
    husband_photo VARCHAR(255),
    wife_photo VARCHAR(255),
    notes TEXT,
    status ENUM('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Death Events
CREATE TABLE death_events (
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_of_death) REFERENCES kebeles(id)
) ENGINE=InnoDB;

-- Divorce Events
CREATE TABLE divorce_events (
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_of_divorce) REFERENCES kebeles(id)
    
) ENGINE=InnoDB;

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_id INT NOT NULL,
    service_fee DECIMAL(10,2) NOT NULL,
    transaction_code VARCHAR(100) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Notices table
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    woreda_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (woreda_id) REFERENCES woredas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);