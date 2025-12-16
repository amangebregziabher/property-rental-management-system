<?php
/**
 * Property Rental Management System
 * Entry Point
 * 
 * This is the main entry point for the application.
 * All requests are routed through this file.
 */

// Define application paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');

// Load configuration
$config = require CONFIG_PATH . '/database.php';

// Load routes
require ROUTES_PATH . '/web.php';

// Application initialization
echo "Property Rental Management System - Welcome!";
