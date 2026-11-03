<?php
/**
 * Database Connection using PDO
 * Optimized for MySQL 8.x and MariaDB on Hostinger
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static ?string $connectionError = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get or create singleton PDO instance
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                self::$connectionError = $e->getMessage();
                error_log("Database connection failure: " . $e->getMessage());
                // In development, we can store error message to show setup assistant
                return null;
            }
        }

        return self::$instance;
    }

    /**
     * Return connection error string if any
     */
    public static function getError(): ?string {
        return self::$connectionError;
    }

    /**
     * Check if database connection is alive
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }
}

/**
 * Global helper function to get DB instance
 */
function getDB(): ?PDO {
    return Database::getConnection();
}
