<?php

require_once __DIR__ . '/../config/database.php';

class Database {
    private static ?PDO $instance = null;
    
    private function __construct() {}
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    DatabaseConfig::getDsn(),
                    DatabaseConfig::USERNAME,
                    DatabaseConfig::PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    public static function closeConnection(): void {
        self::$instance = null;
    }
}
