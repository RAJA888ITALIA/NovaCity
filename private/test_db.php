<?php
// Enable errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your config and db connection
require_once __DIR__ . '/config.php'; // for htdocs files
// require_once __DIR__ . '/includes/db.php'; // optional if you already use db.php

// Try connecting to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "Database connection successful!";

// Optional: test a simple query
$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<br>Tables in database:<br>";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "<br>Failed to list tables: " . $conn->error;
}

$conn->close();