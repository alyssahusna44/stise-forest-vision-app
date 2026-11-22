<?php
// forest_vision/includes/db_connection.php
require_once __DIR__ . '/../config.php';

function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        // Log the error for debugging purposes instead of dying.
        error_log("Database Connection Failed: " . $conn->connect_error);
        return false; // Return false to indicate connection failure.
    }
    return $conn;
}
