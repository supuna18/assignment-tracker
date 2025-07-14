<?php
/**
 * Database Configuration for Assignment Tracker
 * 
 * This file contains database connection settings and helper functions
 * Make sure to keep this file secure and never commit passwords to version control
 */

// Prevent direct access to this file
if (!defined('DB_ACCESS_ALLOWED')) {
    die('Direct access to this file is not allowed');
}

// Database Configuration
class DatabaseConfig {
    // Database credentials - UPDATE THESE WITH YOUR ACTUAL DATABASE DETAILS
    private const DB_HOST = 'localhost';           // Your MySQL server host
    private const DB_NAME = 'assignment_tracker';  // Your database name
    private const DB_USER = 'root';       // Your MySQL username
    private const DB_PASS = '';       // Your MySQL password
    private const DB_CHARSET = 'utf8mb4';          // Character set
    private const DB_PORT = 3306;                  // MySQL port (default: 3306)
    
    // Connection options for security and performance
    private const DB_OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Fetch associative arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                     // Use real prepared statements
        PDO::ATTR_PERSISTENT         => false,                     // Don't use persistent connections
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"        // Set charset on connection
    ];
    
    private static $connection = null;
    
    /**
     * Get database connection using PDO
     * 
     * @return PDO Database connection object
     * @throws Exception If connection fails
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                    self::DB_HOST,
                    self::DB_PORT,
                    self::DB_NAME,
                    self::DB_CHARSET
                );
                
                self::$connection = new PDO($dsn, self::DB_USER, self::DB_PASS, self::DB_OPTIONS);
                
                // Log successful connection (remove in production)
                error_log("Database connection established successfully");
                
            } catch (PDOException $e) {
                // Log the error but don't expose database details to users
                error_log("Database connection failed: " . $e->getMessage());
                
                // Throw a generic error for security
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Test database connection
     * 
     * @return bool True if connection successful, false otherwise
     */
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Close database connection
     */
    public static function closeConnection() {
        self::$connection = null;
    }
    
    /**
     * Get database connection info (for debugging - remove in production)
     * 
     * @return array Connection information
     */
    public static function getConnectionInfo() {
        return [
            'host' => self::DB_HOST,
            'database' => self::DB_NAME,
            'port' => self::DB_PORT,
            'charset' => self::DB_CHARSET
        ];
    }
}

/**
 * Database Helper Functions
 */
class DatabaseHelper {
    
    /**
     * Execute a SELECT query and return results
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return array Query results
     */
    public static function select($query, $params = []) {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database SELECT error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return array|false Single row or false if not found
     */
    public static function selectOne($query, $params = []) {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database SELECT ONE error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Execute an INSERT query and return the last insert ID
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return string Last insert ID
     */
    public static function insert($query, $params = []) {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database INSERT error: " . $e->getMessage());
            throw new Exception("Database insert failed");
        }
    }
    
    /**
     * Execute an UPDATE query and return affected rows
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return int Number of affected rows
     */
    public static function update($query, $params = []) {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database UPDATE error: " . $e->getMessage());
            throw new Exception("Database update failed");
        }
    }
    
    /**
     * Execute a DELETE query and return affected rows
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return int Number of affected rows
     */
    public static function delete($query, $params = []) {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database DELETE error: " . $e->getMessage());
            throw new Exception("Database delete failed");
        }
    }
    
    /**
     * Begin database transaction
     * 
     * @return bool True on success
     */
    public static function beginTransaction() {
        try {
            $pdo = DatabaseConfig::getConnection();
            return $pdo->beginTransaction();
        } catch (PDOException $e) {
            error_log("Database transaction begin error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Commit database transaction
     * 
     * @return bool True on success
     */
    public static function commit() {
        try {
            $pdo = DatabaseConfig::getConnection();
            return $pdo->commit();
        } catch (PDOException $e) {
            error_log("Database transaction commit error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rollback database transaction
     * 
     * @return bool True on success
     */
    public static function rollback() {
        try {
            $pdo = DatabaseConfig::getConnection();
            return $pdo->rollback();
        } catch (PDOException $e) {
            error_log("Database transaction rollback error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a table exists
     * 
     * @param string $tableName Table name to check
     * @return bool True if table exists
     */
    public static function tableExists($tableName) {
        try {
            $query = "SHOW TABLES LIKE ?";
            $result = self::selectOne($query, [$tableName]);
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database version
     * 
     * @return string Database version
     */
    public static function getVersion() {
        try {
            $result = self::selectOne("SELECT VERSION() as version");
            return $result['version'] ?? 'Unknown';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
}

/**
 * User Management Functions
 */
class UserManager {
    
    /**
     * Create a new user
     * 
     * @param array $userData User data array
     * @return int|false User ID or false on failure
     */
    public static function createUser($userData) {
        try {
            $query = "INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $userData['first_name'],
                $userData['last_name'], 
                $userData['email'],
                $userData['password']
            ];
            
            return DatabaseHelper::insert($query, $params);
        } catch (Exception $e) {
            error_log("User creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public static function getUserByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL";
            return DatabaseHelper::selectOne($query, [$email]);
        } catch (Exception $e) {
            error_log("Get user by email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false if not found
     */
    public static function getUserById($userId) {
        try {
            $query = "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL";
            return DatabaseHelper::selectOne($query, [$userId]);
        } catch (Exception $e) {
            error_log("Get user by ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public static function emailExists($email) {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE email = ? AND deleted_at IS NULL";
            $result = DatabaseHelper::selectOne($query, [$email]);
            return ($result['count'] ?? 0) > 0;
        } catch (Exception $e) {
            error_log("Email exists check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     * 
     * @param string $email User email
     * @param string $hashedPassword New hashed password
     * @return bool True on success
     */
    public static function updatePassword($email, $hashedPassword) {
        try {
            $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?";
            $affected = DatabaseHelper::update($query, [$hashedPassword, $email]);
            return $affected > 0;
        } catch (Exception $e) {
            error_log("Password update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user last login
     * 
     * @param int $userId User ID
     * @return bool True on success
     */
    public static function updateLastLogin($userId) {
        try {
            $query = "UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?";
            $affected = DatabaseHelper::update($query, [$userId]);
            return $affected > 0;
        } catch (Exception $e) {
            error_log("Last login update failed: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Password Reset Token Management
 */
class PasswordResetManager {
    
    /**
     * Create password reset token
     * 
     * @param string $email User email
     * @param string $token Reset token
     * @return bool True on success
     */
    public static function createResetToken($email, $token) {
        try {
            // First, delete any existing tokens for this email
            $deleteQuery = "DELETE FROM password_resets WHERE email = ?";
            DatabaseHelper::delete($deleteQuery, [$email]);
            
            // Insert new token
            $insertQuery = "INSERT INTO password_resets (email, token, expires_at, created_at) 
                           VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())";
            
            $insertId = DatabaseHelper::insert($insertQuery, [$email, $token]);
            return $insertId !== false;
        } catch (Exception $e) {
            error_log("Reset token creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify reset token
     * 
     * @param string $token Reset token
     * @return array|false Token data or false if invalid
     */
    public static function verifyResetToken($token) {
        try {
            $query = "SELECT * FROM password_resets 
                      WHERE token = ? AND expires_at > NOW() AND used_at IS NULL";
            return DatabaseHelper::selectOne($query, [$token]);
        } catch (Exception $e) {
            error_log("Reset token verification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark reset token as used
     * 
     * @param string $token Reset token
     * @return bool True on success
     */
    public static function markTokenAsUsed($token) {
        try {
            $query = "UPDATE password_resets SET used_at = NOW() WHERE token = ?";
            $affected = DatabaseHelper::update($query, [$token]);
            return $affected > 0;
        } catch (Exception $e) {
            error_log("Mark token as used failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired tokens
     * 
     * @return int Number of deleted tokens
     */
    public static function cleanupExpiredTokens() {
        try {
            $query = "DELETE FROM password_resets WHERE expires_at < NOW()";
            return DatabaseHelper::delete($query);
        } catch (Exception $e) {
            error_log("Token cleanup failed: " . $e->getMessage());
            return 0;
        }
    }
}
?>