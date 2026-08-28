<?php
// ============================================================
// Database Connection Configuration (PDO)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'farm_tools_rental');

function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Attempt auto-creation if database doesn't exist yet
            try {
                $pdoServer = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
                $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $ex) {
                die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #f87171; border-radius: 8px; margin: 30px auto; max-width: 600px;'>
                    <h3 style='margin-top: 0;'>Database Connection Error</h3>
                    <p>Unable to connect to MySQL database <strong>" . DB_NAME . "</strong>.</p>
                    <p><strong>Error details:</strong> " . htmlspecialchars($ex->getMessage()) . "</p>
                    <p>Please ensure XAMPP MySQL server is running and import <code>database/farm_tools_rental.sql</code> into phpMyAdmin.</p>
                </div>");
            }
        }
    }
    
    return $pdo;
}

// Global DB Handle
$conn = getDBConnection();
?>
