<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'transport_ims');
define('DB_USER', 'postgres');
define('DB_PASS', '1212');
define('DB_PORT', '5432');

// Application configuration
define('APP_NAME', 'Transport IMS');
define('APP_URL', 'http://localhost:8000');
define('APP_ROOT', dirname(__DIR__));

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'transportims_session');

// Security configuration
define('PASSWORD_HASH_COST', 10);
define('REMEMBER_TOKEN_LIFETIME', 2592000); // 30 days

// Application settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15); // minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/logs/error.log'); 