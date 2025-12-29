<?php
/**
 * Central PDO connection helper.
 * 
 * Update the DSN, username, and password below to match your MySQL setup.
 */

// Basic configuration – EDIT THESE VALUES FOR YOUR ENVIRONMENT
const DB_HOST = 'localhost';
const DB_NAME = 'bsis';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Returns a shared PDO instance.
 *
 * Usage:
 *   $pdo = getPDO();
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // In production you may want to log this instead of echoing
        die('Database connection failed: ' . $e->getMessage());
    }

    return $pdo;
}


