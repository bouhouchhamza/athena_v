<?php
class DatabaseConfig {
    const HOST = 'localhost';
    const DBNAME = 'scrum_management';
    const USERNAME = 'root';
    const PASSWORD = '';
    const CHARSET = 'utf8mb4';
    public static function getDsn(): string {
        return "mysql:host=" . self::HOST . ";dbname=" . self::DBNAME . ";charset=" . self::CHARSET;
    }
}
