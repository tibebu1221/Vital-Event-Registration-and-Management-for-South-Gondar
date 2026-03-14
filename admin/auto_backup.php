<?php
/**
 * Automatic Database Backup System for VERMS
 * Run this script via cron job for scheduled backups
 * Also provides web interface for manual backup management
 */

// Start session for web interface
if (session_status() == PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
    session_start();
}

// Language management for web interface
if (php_sapi_name() !== 'cli' && !isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default language
}

if (php_sapi_name() !== 'cli' && isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Database configuration from your setup
$host = "localhost";
$username = "root";
$password = "";
$database = "VERMS";

class AutoBackup {
    private $config;
    private $conn;
    private $lang = 'en';
    
    public function __construct($config, $lang = 'en') {
        $this->config = $config;
        $this->lang = $lang;
        
        // Create database connection
        $this->conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
        
        if ($this->conn->connect_error) {
            throw new Exception($this->translate("Database connection failed: ") . $this->conn->connect_error);
        }
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->config['backup_dir'])) {
            mkdir($this->config['backup_dir'], 0755, true);
        }
        
        // Create manual backup directory if it doesn't exist
        if (!is_dir($this->config['manual_backup_dir'])) {
            mkdir($this->config['manual_backup_dir'], 0755, true);
        }
    }
    
    private function translate($key) {
        $translations = [
            'en' => [
                'Database connection failed: ' => 'Database connection failed: ',
                'Backup created successfully: ' => 'Backup created successfully: ',
                'Backup failed: ' => 'Backup failed: ',
                'Removed old backup: ' => 'Removed old backup: ',
                'Starting automatic backup for VERMS database...' => 'Starting automatic backup for VERMS database...',
                'Backup created successfully: ' => 'Backup created successfully: ',
                'Backup failed: ' => 'Backup failed: ',
                'Old backups cleaned up.' => 'Old backups cleaned up.',
                'Testing VERMS database connection...' => 'Testing VERMS database connection...',
                'Connected successfully to: ' => 'Connected successfully to: ',
                'Tables found: ' => 'Tables found: ',
                'Starting manual backup...' => 'Starting manual backup...',
                'Manual backup created successfully: ' => 'Manual backup created successfully: ',
                'Manual backup failed: ' => 'Manual backup failed: ',
                'Backup downloaded: ' => 'Backup downloaded: ',
                'Backup deleted: ' => 'Backup deleted: ',
                'File not found: ' => 'File not found: ',
                'Backup restored successfully.' => 'Backup restored successfully.',
                'Restore failed: ' => 'Restore failed: '
            ],
            'am' => [
                'Database connection failed: ' => 'የዳታቤዝ ግንኙነት አልተሳካም: ',
                'Backup created successfully: ' => 'ባክአፕ በተሳካ ሁኔታ ተፈጥሯል: ',
                'Backup failed: ' => 'ባክአፕ አልተሳካም: ',
                'Removed old backup: ' => 'የድሮ ባክአፕ ተወግዷል: ',
                'Starting automatic backup for VERMS database...' => 'ለቪ.ኢ.አር.ኤም.ኤስ ዳታቤዝ አውቶማቲክ ባክአፕ እየጀመረ ነው...',
                'Backup created successfully: ' => 'ባክአፕ በተሳካ ሁኔታ ተፈጥሯል: ',
                'Backup failed: ' => 'ባክአፕ አልተሳካም: ',
                'Old backups cleaned up.' => 'የድሮ ባክአፖች ተሰልተዋል።',
                'Testing VERMS database connection...' => 'የቪ.ኢ.አር.ኤም.ኤስ ዳታቤዝ ግንኙነት እየተሞከረ ነው...',
                'Connected successfully to: ' => 'በተሳካ ሁኔታ ተገናኝቷል: ',
                'Tables found: ' => 'ሰንጠረዦች ተገኝተዋል: ',
                'Starting manual backup...' => 'እጅ ባክአፕ እየጀመረ ነው...',
                'Manual backup created successfully: ' => 'እጅ ባክአፕ በተሳካ ሁኔታ ተፈጥሯል: ',
                'Manual backup failed: ' => 'እጅ ባክአፕ አልተሳካም: ',
                'Backup downloaded: ' => 'ባክአፕ ተወርድሯል: ',
                'Backup deleted: ' => 'ባክአፕ ተሰርዟል: ',
                'File not found: ' => 'ፋይል አልተገኘም: ',
                'Backup restored successfully.' => 'ባክአፕ በተሳካ ሁኔታ ተመልሷል።',
                'Restore failed: ' => 'መመለስ አልተሳካም: '
            ]
        ];
        
        return isset($translations[$this->lang][$key]) ? $translations[$this->lang][$key] : $key;
    }
    
    public function createBackup($type = 'auto') {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $prefix = $type === 'manual' ? 'verms_backup_manual_' : 'verms_backup_auto_';
            $backup_dir = $type === 'manual' ? $this->config['manual_backup_dir'] : $this->config['backup_dir'];
            $backup_file = $backup_dir . $prefix . $timestamp . ".sql.gz";
            
            // Create compressed backup using mysqldump
            $command = sprintf(
                "mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s | gzip > %s",
                escapeshellarg($this->config['host']),
                escapeshellarg($this->config['username']),
                escapeshellarg($this->config['password']),
                escapeshellarg($this->config['database']),
                escapeshellarg($backup_file)
            );
            
            // Execute command
            $output = null;
            $return_var = null;
            exec($command . ' 2>&1', $output, $return_var);
            
            if ($return_var === 0 && file_exists($backup_file)) {
                $result = [
                    'success' => true,
                    'type' => $type,
                    'file' => basename($backup_file),
                    'size' => filesize($backup_file),
                    'timestamp' => $timestamp,
                    'database' => $this->config['database']
                ];
                
                if ($type === 'auto') {
                    // Clean up old automatic backups
                    $this->cleanOldBackups('auto');
                }
                
                // Log success
                $this->log($this->translate('Backup created successfully: ') . $result['file']);
                
                return $result;
            } else {
                throw new Exception($this->translate("Backup command failed. Output: ") . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            $this->log($this->translate('Backup failed: ') . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function createManualBackup() {
        return $this->createBackup('manual');
    }
    
    private function cleanOldBackups($type = 'auto') {
        $backup_dir = $type === 'manual' ? $this->config['manual_backup_dir'] : $this->config['backup_dir'];
        $prefix = $type === 'manual' ? 'verms_backup_manual_' : 'verms_backup_auto_';
        
        $backups = [];
        $files = scandir($backup_dir);
        
        foreach ($files as $file) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '.*\.sql\.gz$/', $file)) {
                $file_path = $backup_dir . $file;
                $backups[] = [
                    'file' => $file,
                    'path' => $file_path,
                    'time' => filemtime($file_path)
                ];
            }
        }
        
        // Sort by modification time (newest first)
        usort($backups, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        // Remove backups exceeding the limit
        $max_backups = $type === 'manual' ? $this->config['max_manual_backups'] : $this->config['max_backups'];
        if (count($backups) > $max_backups) {
            $old_backups = array_slice($backups, $max_backups);
            
            foreach ($old_backups as $old_backup) {
                if (unlink($old_backup['path'])) {
                    $this->log($this->translate('Removed old backup: ') . $old_backup['file']);
                }
            }
        }
    }
    
    public function getBackupInfo($type = 'all') {
        $info = [
            'total_backups' => 0,
            'total_size' => 0,
            'latest_backup' => null,
            'backups' => []
        ];
        
        $directories = [];
        if ($type === 'all' || $type === 'auto') {
            $directories[] = $this->config['backup_dir'];
        }
        if ($type === 'all' || $type === 'manual') {
            $directories[] = $this->config['manual_backup_dir'];
        }
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;
            
            $files = scandir($dir);
            
            foreach ($files as $file) {
                if (preg_match('/^verms_backup_(auto|manual)_.*\.sql\.gz$/', $file, $matches)) {
                    $file_path = $dir . $file;
                    $size = filesize($file_path);
                    $modified = filemtime($file_path);
                    $backup_type = $matches[1];
                    
                    $info['backups'][] = [
                        'type' => $backup_type,
                        'file' => $file,
                        'size' => $size,
                        'modified' => $modified,
                        'date' => date('Y-m-d H:i:s', $modified),
                        'path' => $file_path
                    ];
                    
                    $info['total_size'] += $size;
                    $info['total_backups']++;
                    
                    // Find latest backup
                    if (!$info['latest_backup'] || $modified > $info['latest_backup']['modified']) {
                        $info['latest_backup'] = [
                            'type' => $backup_type,
                            'file' => $file,
                            'size' => $size,
                            'modified' => $modified,
                            'date' => date('Y-m-d H:i:s', $modified)
                        ];
                    }
                }
            }
        }
        
        // Sort backups by date (newest first)
        usort($info['backups'], function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $info;
    }
    
    public function getDatabaseInfo() {
        $tables = [];
        $result = $this->conn->query("SHOW TABLES");
        
        while ($row = $result->fetch_array()) {
            $table_name = $row[0];
            $table_result = $this->conn->query("SELECT COUNT(*) as count FROM `$table_name`");
            $table_count = $table_result->fetch_assoc()['count'];
            
            $tables[] = [
                'name' => $table_name,
                'records' => $table_count
            ];
        }
        
        // Get database size
        $size_result = $this->conn->query("
            SELECT SUM(data_length + index_length) as size 
            FROM information_schema.tables 
            WHERE table_schema = '" . $this->config['database'] . "'
        ");
        $db_size = $size_result->fetch_assoc()['size'];
        
        return [
            'name' => $this->config['database'],
            'tables' => $tables,
            'total_tables' => count($tables),
            'size' => $db_size
        ];
    }
    
    private function log($message, $level = 'INFO') {
        $log_file = $this->config['backup_dir'] . 'backup.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message\n";
        
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        
        // Also output to console if running manually
        if (php_sapi_name() === 'cli') {
            echo $log_message;
        }
    }
    
    public function sendNotification($backup_result) {
        if (!$this->config['notify_email']) {
            return;
        }
        
        $subject = "VERMS Database Backup " . ($backup_result['success'] ? 'Successful' : 'Failed');
        
        $message = "VERMS Database Backup Report\n";
        $message .= "============================\n\n";
        
        if ($backup_result['success']) {
            $message .= "Status: SUCCESS\n";
            $message .= "Database: " . $backup_result['database'] . "\n";
            $message .= "File: " . $backup_result['file'] . "\n";
            $message .= "Size: " . $this->formatSize($backup_result['size']) . "\n";
            $message .= "Time: " . $backup_result['timestamp'] . "\n";
        } else {
            $message .= "Status: FAILED\n";
            $message .= "Database: " . $this->config['database'] . "\n";
            $message .= "Error: " . $backup_result['error'] . "\n";
        }
        
        $message .= "\nSystem: " . gethostname() . "\n";
        $message .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        
        mail($this->config['notify_email'], $subject, $message);
    }
    
    public function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    public function downloadBackup($filename, $type = 'auto') {
        $backup_dir = $type === 'manual' ? $this->config['manual_backup_dir'] : $this->config['backup_dir'];
        $file_path = $backup_dir . $filename;
        
        if (!file_exists($file_path)) {
            throw new Exception($this->translate('File not found: ') . $filename);
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
    
    public function deleteBackup($filename, $type = 'auto') {
        $backup_dir = $type === 'manual' ? $this->config['manual_backup_dir'] : $this->config['backup_dir'];
        $file_path = $backup_dir . $filename;
        
        if (!file_exists($file_path)) {
            throw new Exception($this->translate('File not found: ') . $filename);
        }
        
        if (unlink($file_path)) {
            $this->log($this->translate('Backup deleted: ') . $filename);
            return true;
        }
        
        return false;
    }
    
    public function restoreBackup($filename, $type = 'auto') {
        $backup_dir = $type === 'manual' ? $this->config['manual_backup_dir'] : $this->config['backup_dir'];
        $file_path = $backup_dir . $filename;
        
        if (!file_exists($file_path)) {
            throw new Exception($this->translate('File not found: ') . $filename);
        }
        
        // Create temporary SQL file
        $temp_sql = tempnam(sys_get_temp_dir(), 'verms_restore_');
        
        // Decompress the backup
        $command = sprintf(
            "gunzip -c %s > %s",
            escapeshellarg($file_path),
            escapeshellarg($temp_sql)
        );
        
        exec($command . ' 2>&1', $output, $return_var);
        
        if ($return_var !== 0) {
            unlink($temp_sql);
            throw new Exception($this->translate('Restore failed: ') . implode("\n", $output));
        }
        
        // Read SQL file
        $sql_content = file_get_contents($temp_sql);
        
        // Execute SQL queries
        $this->conn->multi_query($sql_content);
        
        // Wait for all queries to complete
        while ($this->conn->more_results()) {
            $this->conn->next_result();
        }
        
        // Clean up
        unlink($temp_sql);
        
        return true;
    }
    
    public function getSystemInfo() {
        return [
            'php_version' => phpversion(),
            'mysql_version' => $this->conn->server_info,
            'system' => php_uname(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'backup_dir' => $this->config['backup_dir'],
            'manual_backup_dir' => $this->config['manual_backup_dir'],
            'max_backups' => $this->config['max_backups'],
            'max_manual_backups' => $this->config['max_manual_backups']
        ];
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Configuration
$config = [
    'host' => $host,
    'username' => $username,
    'password' => $password,
    'database' => $database,
    'backup_dir' => __DIR__ . '/../backups/auto/',
    'manual_backup_dir' => __DIR__ . '/../backups/manual/',
    'max_backups' => 30, // Keep last 30 automatic backups
    'max_manual_backups' => 50, // Keep last 50 manual backups
    'backup_time' => '02:00', // Default backup time (2 AM)
    'notify_email' => 'admin@verms.com' // Email for notifications
];

// Handle command line execution
if (php_sapi_name() === 'cli') {
    try {
        $backup = new AutoBackup($config);
        
        $option = $argv[1] ?? 'create';
        
        switch ($option) {
            case 'create':
                echo $backup->translate('Starting automatic backup for VERMS database...') . "\n";
                $result = $backup->createBackup();
                
                if ($result['success']) {
                    echo $backup->translate('Backup created successfully: ') . $result['file'] . "\n";
                    $backup->sendNotification($result);
                    exit(0);
                } else {
                    echo $backup->translate('Backup failed: ') . $result['error'] . "\n";
                    $backup->sendNotification($result);
                    exit(1);
                }
                break;
                
            case 'info':
                $backup = new AutoBackup($config);
                $info = $backup->getBackupInfo();
                $db_info = $backup->getDatabaseInfo();
                
                echo "VERMS Backup Information:\n";
                echo "Database: " . $db_info['name'] . "\n";
                echo "Total Tables: " . $db_info['total_tables'] . "\n";
                echo "Total Backups: " . $info['total_backups'] . "\n";
                echo "Total Size: " . $backup->formatSize($info['total_size']) . "\n";
                if ($info['latest_backup']) {
                    echo "Latest Backup: " . $info['latest_backup']['file'] . " (" . $info['latest_backup']['date'] . ")\n";
                }
                break;
                
            case 'clean':
                $backup = new AutoBackup($config);
                $backup->cleanOldBackups('auto');
                echo $backup->translate('Old backups cleaned up.') . "\n";
                break;
                
            case 'test':
                echo $backup->translate('Testing VERMS database connection...') . "\n";
                $backup = new AutoBackup($config);
                $db_info = $backup->getDatabaseInfo();
                echo $backup->translate('Connected successfully to: ') . $db_info['name'] . "\n";
                echo $backup->translate('Tables found: ') . $db_info['total_tables'] . "\n";
                foreach ($db_info['tables'] as $table) {
                    echo "  - " . $table['name'] . " (" . $table['records'] . " records)\n";
                }
                break;
                
            default:
                echo "Usage: php auto_backup.php [create|info|clean|test]\n";
                echo "  create - Create a new backup\n";
                echo "  info   - Show backup information\n";
                echo "  clean  - Clean old backups\n";
                echo "  test   - Test database connection\n";
                break;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Web Interface
if (php_sapi_name() !== 'cli') {
    // Check if user is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }
    
    $lang = $_SESSION['lang'];
    $backup = new AutoBackup($config, $lang);
    
    // Translations for web interface
    $translations = [
        'en' => [
            'title' => 'VERMS Database Backup Manager',
            'dashboard' => 'Backup Dashboard',
            'automatic_backups' => 'Automatic Backups',
            'manual_backups' => 'Manual Backups',
            'system_info' => 'System Information',
            'database_info' => 'Database Information',
            'create_manual_backup' => 'Create Manual Backup',
            'backup_type' => 'Backup Type',
            'backup_file' => 'Backup File',
            'file_size' => 'File Size',
            'created_date' => 'Created Date',
            'actions' => 'Actions',
            'download' => 'Download',
            'delete' => 'Delete',
            'restore' => 'Restore',
            'no_backups_found' => 'No backups found',
            'database_name' => 'Database Name',
            'total_tables' => 'Total Tables',
            'database_size' => 'Database Size',
            'total_records' => 'Total Records',
            'table_name' => 'Table Name',
            'records' => 'Records',
            'php_version' => 'PHP Version',
            'mysql_version' => 'MySQL Version',
            'system' => 'System',
            'memory_limit' => 'Memory Limit',
            'max_execution_time' => 'Max Execution Time',
            'backup_directory' => 'Backup Directory',
            'manual_backup_directory' => 'Manual Backup Directory',
            'max_auto_backups' => 'Max Automatic Backups',
            'max_manual_backups' => 'Max Manual Backups',
            'success' => 'Success',
            'error' => 'Error',
            'backup_created_success' => 'Backup created successfully!',
            'backup_created_error' => 'Failed to create backup: ',
            'backup_deleted_success' => 'Backup deleted successfully!',
            'backup_deleted_error' => 'Failed to delete backup: ',
            'backup_restored_success' => 'Backup restored successfully!',
            'backup_restored_error' => 'Failed to restore backup: ',
            'confirm_delete' => 'Are you sure you want to delete this backup?',
            'confirm_restore' => 'Are you sure you want to restore this backup? This will overwrite current data.',
            'last_backup' => 'Last Backup',
            'total_backups' => 'Total Backups',
            'total_backup_size' => 'Total Backup Size',
            'create_backup_now' => 'Create Backup Now',
            'refresh' => 'Refresh',
            'cron_instructions' => 'Cron Job Instructions',
            'cron_example' => 'Example (run daily at 2 AM):',
            'cron_command' => '0 2 * * * php /path/to/auto_backup.php create',
            'back_to_dashboard' => 'Back to Dashboard'
        ],
        'am' => [
            'title' => 'ቪ.ኢ.አር.ኤም.ኤስ የዳታቤዝ ባክአፕ አስተዳዳሪ',
            'dashboard' => 'ባክአፕ ዳሽቦርድ',
            'automatic_backups' => 'አውቶማቲክ ባክአፖች',
            'manual_backups' => 'እጅ ባክአፖች',
            'system_info' => 'የስርዓት መረጃ',
            'database_info' => 'የዳታቤዝ መረጃ',
            'create_manual_backup' => 'እጅ ባክአፕ ፍጠር',
            'backup_type' => 'የባክአፕ አይነት',
            'backup_file' => 'ባክአፕ ፋይል',
            'file_size' => 'የፋይል መጠን',
            'created_date' => 'የተፈጠረበት ቀን',
            'actions' => 'ድርጊቶች',
            'download' => 'ያውርዱ',
            'delete' => 'ሰርዝ',
            'restore' => 'ዳግም አስቀምጥ',
            'no_backups_found' => 'ባክአፖች አልተገኙም',
            'database_name' => 'የዳታቤዝ ስም',
            'total_tables' => 'ጠቅላላ ሰንጠረዦች',
            'database_size' => 'የዳታቤዝ መጠን',
            'total_records' => 'ጠቅላላ መዝገቦች',
            'table_name' => 'የሰንጠረዥ ስም',
            'records' => 'መዝገቦች',
            'php_version' => 'ፒ.ኤች.ፒ ስሪት',
            'mysql_version' => 'ማይ.ኤስ.ኪው.ኤል ስሪት',
            'system' => 'ስርዓት',
            'memory_limit' => 'የማህደረ ትውስታ ገደብ',
            'max_execution_time' => 'ከፍተኛ የማስኬድ ጊዜ',
            'backup_directory' => 'የባክአፕ ዳይሬክቶሪ',
            'manual_backup_directory' => 'የእጅ ባክአፕ ዳይሬክቶሪ',
            'max_auto_backups' => 'ከፍተኛ አውቶማቲክ ባክአፖች',
            'max_manual_backups' => 'ከፍተኛ እጅ ባክአፖች',
            'success' => 'ተሳክቷል',
            'error' => 'ስህተት',
            'backup_created_success' => 'ባክአፕ በተሳካ ሁኔታ ተፈጥሯል!',
            'backup_created_error' => 'ባክአፕ ማፍጠር አልተሳካም: ',
            'backup_deleted_success' => 'ባክአፕ በተሳካ ሁኔታ ተሰርዟል!',
            'backup_deleted_error' => 'ባክአፕ መሰረዝ አልተሳካም: ',
            'backup_restored_success' => 'ባክአፕ በተሳካ ሁኔታ ተመልሷል!',
            'backup_restored_error' => 'ባክአፕ መመለስ አልተሳካም: ',
            'confirm_delete' => 'ይህን ባክአፕ መሰረዝ እንደሚፈልጉ እርግጠኛ ነዎት?',
            'confirm_restore' => 'ይህን ባክአፕ መመለስ እንደሚፈልጉ እርግጠኛ ነዎት? ይህ የአሁኑን ውሂብ ይተካል።',
            'last_backup' => 'የመጨረሻ ባክአፕ',
            'total_backups' => 'ጠቅላላ ባክአፖች',
            'total_backup_size' => 'ጠቅላላ የባክአፕ መጠን',
            'create_backup_now' => 'አሁን ባክአፕ ፍጠር',
            'refresh' => 'አድስ',
            'cron_instructions' => 'የክሮን ስራ መመሪያዎች',
            'cron_example' => 'ምሳሌ (በየቀኑ ከጠዋት 2 ሰዓት ማስኬድ):',
            'cron_command' => '0 2 * * * php /path/to/auto_backup.php create',
            'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ'
        ]
    ];
    
    // Handle actions
    $action = $_GET['action'] ?? '';
    $filename = $_GET['file'] ?? '';
    $type = $_GET['type'] ?? 'auto';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_manual_backup'])) {
        $result = $backup->createManualBackup();
        if ($result['success']) {
            $_SESSION['success'] = $translations[$lang]['backup_created_success'];
        } else {
            $_SESSION['error'] = $translations[$lang]['backup_created_error'] . $result['error'];
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if ($action === 'download' && $filename) {
        try {
            $backup->downloadBackup($filename, $type);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    if ($action === 'delete' && $filename) {
        try {
            if ($backup->deleteBackup($filename, $type)) {
                $_SESSION['success'] = $translations[$lang]['backup_deleted_success'];
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $translations[$lang]['backup_deleted_error'] . $e->getMessage();
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if ($action === 'restore' && $filename) {
        try {
            if ($backup->restoreBackup($filename, $type)) {
                $_SESSION['success'] = $translations[$lang]['backup_restored_success'];
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $translations[$lang]['backup_restored_error'] . $e->getMessage();
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Get data for display
    $backup_info = $backup->getBackupInfo('all');
    $db_info = $backup->getDatabaseInfo();
    $system_info = $backup->getSystemInfo();
    
    function t($key) {
        global $translations, $lang;
        return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
    }
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo t('title'); ?> - VERMS</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            <?php if($lang === 'am'): ?>
            @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap');
            
            body {
                font-family: 'Noto Sans Ethiopic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                text-align: justify;
            }
            <?php endif; ?>
            
            :root {
                --primary: #4361ee;
                --secondary: #7209b7;
                --success: #4bb543;
                --warning: #ff9e00;
                --danger: #dc3545;
                --info: #17a2b8;
            }
            
            body {
                background: #f5f7fb;
            }
            
            .dashboard-header {
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                color: white;
                padding: 2rem 0;
                margin-bottom: 2rem;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            
            .stat-card {
                background: white;
                border-radius: 15px;
                padding: 1.5rem;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 1.5rem;
            }
            
            .stat-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                margin: 0 auto 1rem;
            }
            
            .stat-number {
                font-size: 2rem;
                font-weight: 700;
                text-align: center;
            }
            
            .stat-label {
                text-align: center;
                color: #6c757d;
                margin-top: 0.5rem;
            }
            
            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 1.5rem;
            }
            
            .card-header {
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                color: white;
                border-radius: 15px 15px 0 0 !important;
                border: none;
                padding: 1.25rem 1.5rem;
                font-weight: 600;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                border: none;
                padding: 0.75rem 1.5rem;
                font-weight: 500;
            }
            
            .btn-primary:hover {
                background: linear-gradient(135deg, #3a56d4, #6108a0);
            }
            
            .table th {
                background: #f8f9fa;
                font-weight: 600;
            }
            
            .backup-type-auto {
                background: #e7f3ff;
                color: var(--primary);
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.875rem;
                font-weight: 500;
            }
            
            .backup-type-manual {
                background: #fff3cd;
                color: var(--warning);
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.875rem;
                font-weight: 500;
            }
            
            .language-switcher {
                position: absolute;
                top: 20px;
                right: 20px;
                z-index: 1000;
                background: white;
                border-radius: 25px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                padding: 5px;
            }
            
            .lang-btn {
                border: none;
                padding: 8px 15px;
                border-radius: 20px;
                background: transparent;
                font-weight: 500;
                transition: all 0.3s;
                color: var(--primary);
            }
            
            .lang-btn.active {
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                color: white;
            }
            
            @media (max-width: 768px) {
                .language-switcher {
                    top: 10px;
                    right: 10px;
                    padding: 4px;
                }
                
                .lang-btn {
                    padding: 6px 12px;
                    font-size: 0.875rem;
                }
            }
        </style>
    </head>
    <body>
        <?php include '../includes/header.php'; ?>
        
        <!-- Language Switcher -->
        <div class="language-switcher">
            <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">
                <i class="fas fa-globe-americas me-1"></i>EN
            </button>
            <button class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>" onclick="changeLanguage('am')">
                <i class="fas fa-globe-africa me-1"></i>አማ
            </button>
        </div>
        
        <div class="dashboard-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold"><i class="fas fa-database me-3"></i><?php echo t('title'); ?></h1>
                        <p class="lead mb-0"><?php echo t('dashboard'); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <form method="post" class="d-inline">
                            <button type="submit" name="create_manual_backup" class="btn btn-light btn-lg">
                                <i class="fas fa-plus-circle me-2"></i><?php echo t('create_manual_backup'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container my-5">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(67, 97, 238, 0.1); color: var(--primary);">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-number" style="color: var(--primary);"><?= $db_info['total_tables'] ?></div>
                        <div class="stat-label"><?php echo t('total_tables'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(75, 181, 67, 0.1); color: var(--success);">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="stat-number" style="color: var(--success);"><?= $backup->formatSize($db_info['size']) ?></div>
                        <div class="stat-label"><?php echo t('database_size'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(255, 158, 0, 0.1); color: var(--warning);">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="stat-number" style="color: var(--warning);"><?= $backup_info['total_backups'] ?></div>
                        <div class="stat-label"><?php echo t('total_backups'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(23, 162, 184, 0.1); color: var(--info);">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="stat-number" style="color: var(--info);"><?= $backup->formatSize($backup_info['total_size']) ?></div>
                        <div class="stat-label"><?php echo t('total_backup_size'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Database Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-2"></i><?php echo t('database_info'); ?>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong><?php echo t('database_name'); ?>:</strong> <?= $db_info['name'] ?>
                            </div>
                            <div class="mb-3">
                                <strong><?php echo t('total_tables'); ?>:</strong> <?= $db_info['total_tables'] ?>
                            </div>
                            <div class="mb-3">
                                <strong><?php echo t('database_size'); ?>:</strong> <?= $backup->formatSize($db_info['size']) ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th><?php echo t('table_name'); ?></th>
                                            <th><?php echo t('records'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($db_info['tables'] as $table): ?>
                                            <tr>
                                                <td><?= $table['name'] ?></td>
                                                <td><span class="badge bg-secondary"><?= $table['records'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-cogs me-2"></i><?php echo t('system_info'); ?>
                        </div>
                        <div class="card-body">
                            <div class="mb-2"><strong><?php echo t('php_version'); ?>:</strong> <?= $system_info['php_version'] ?></div>
                            <div class="mb-2"><strong><?php echo t('mysql_version'); ?>:</strong> <?= $system_info['mysql_version'] ?></div>
                            <div class="mb-2"><strong><?php echo t('system'); ?>:</strong> <?= $system_info['system'] ?></div>
                            <div class="mb-2"><strong><?php echo t('memory_limit'); ?>:</strong> <?= $system_info['memory_limit'] ?></div>
                            <div class="mb-2"><strong><?php echo t('max_execution_time'); ?>:</strong> <?= $system_info['max_execution_time'] ?> seconds</div>
                            <div class="mb-2"><strong><?php echo t('backup_directory'); ?>:</strong> <?= $system_info['backup_dir'] ?></div>
                            <div class="mb-2"><strong><?php echo t('manual_backup_directory'); ?>:</strong> <?= $system_info['manual_backup_dir'] ?></div>
                            <div class="mb-2"><strong><?php echo t('max_auto_backups'); ?>:</strong> <?= $system_info['max_backups'] ?></div>
                            <div class="mb-2"><strong><?php echo t('max_manual_backups'); ?>:</strong> <?= $system_info['max_manual_backups'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Automatic Backups -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-robot me-2"></i><?php echo t('automatic_backups'); ?>
                    <span class="badge bg-light text-dark ms-2"><?= count(array_filter($backup_info['backups'], fn($b) => $b['type'] === 'auto')) ?></span>
                </div>
                <div class="card-body">
                    <?php $auto_backups = array_filter($backup_info['backups'], fn($b) => $b['type'] === 'auto'); ?>
                    <?php if (!empty($auto_backups)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('backup_file'); ?></th>
                                        <th><?php echo t('file_size'); ?></th>
                                        <th><?php echo t('created_date'); ?></th>
                                        <th><?php echo t('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auto_backups as $backup_item): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-archive me-2 text-primary"></i>
                                                <?= $backup_item['file'] ?>
                                            </td>
                                            <td><?= $backup->formatSize($backup_item['size']) ?></td>
                                            <td><?= $backup_item['date'] ?></td>
                                            <td>
                                                <a href="?action=download&file=<?= urlencode($backup_item['file']) ?>&type=auto" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i><?php echo t('download'); ?>
                                                </a>
                                                <button onclick="confirmDelete('<?= $backup_item['file'] ?>', 'auto')" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash me-1"></i><?php echo t('delete'); ?>
                                                </button>
                                                <button onclick="confirmRestore('<?= $backup_item['file'] ?>', 'auto')" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-undo me-1"></i><?php echo t('restore'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?php echo t('no_backups_found'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Manual Backups -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hands me-2"></i><?php echo t('manual_backups'); ?>
                    <span class="badge bg-light text-dark ms-2"><?= count(array_filter($backup_info['backups'], fn($b) => $b['type'] === 'manual')) ?></span>
                </div>
                <div class="card-body">
                    <?php $manual_backups = array_filter($backup_info['backups'], fn($b) => $b['type'] === 'manual'); ?>
                    <?php if (!empty($manual_backups)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('backup_file'); ?></th>
                                        <th><?php echo t('file_size'); ?></th>
                                        <th><?php echo t('created_date'); ?></th>
                                        <th><?php echo t('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($manual_backups as $backup_item): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-archive me-2 text-warning"></i>
                                                <?= $backup_item['file'] ?>
                                            </td>
                                            <td><?= $backup->formatSize($backup_item['size']) ?></td>
                                            <td><?= $backup_item['date'] ?></td>
                                            <td>
                                                <a href="?action=download&file=<?= urlencode($backup_item['file']) ?>&type=manual" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i><?php echo t('download'); ?>
                                                </a>
                                                <button onclick="confirmDelete('<?= $backup_item['file'] ?>', 'manual')" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash me-1"></i><?php echo t('delete'); ?>
                                                </button>
                                                <button onclick="confirmRestore('<?= $backup_item['file'] ?>', 'manual')" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-undo me-1"></i><?php echo t('restore'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?php echo t('no_backups_found'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Cron Job Instructions -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i><?php echo t('cron_instructions'); ?>
                </div>
                <div class="card-body">
                    <p class="mb-2"><?php echo t('cron_example'); ?></p>
                    <div class="alert alert-secondary">
                        <code><?php echo t('cron_command'); ?></code>
                    </div>
                    <p class="mb-0"><?php echo t('create_backup_now'); ?>:</p>
                    <code>php <?= basename(__FILE__) ?> create</code>
                </div>
            </div>
            
            <!-- Back to Dashboard -->
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i><?php echo t('back_to_dashboard'); ?>
                </a>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function changeLanguage(lang) {
                window.location.href = '?lang=' + lang;
            }
            
            function confirmDelete(filename, type) {
                if (confirm('<?php echo t('confirm_delete'); ?>')) {
                    window.location.href = '?action=delete&file=' + encodeURIComponent(filename) + '&type=' + type;
                }
            }
            
            function confirmRestore(filename, type) {
                if (confirm('<?php echo t('confirm_restore'); ?>')) {
                    window.location.href = '?action=restore&file=' + encodeURIComponent(filename) + '&type=' + type;
                }
            }
        </script>
    </body>
    </html>
    <?php
}
?>