<?php
function connectDB() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'inventory_management';

    try {
        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }

        if (!$conn->select_db($database)) {
            error_log("Database selection failed: " . $conn->error);
            throw new Exception('Database selection failed: ' . $conn->error);
        }

        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        throw $e;
    }
}