<?php
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';

try {
    echo "Connecting to MySQL server at $host:$port...\n";
    // Connect without selecting a database
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database 'pgn_datalens' if it does not exist...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pgn_datalens CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database 'pgn_datalens' created successfully (or already exists).\n";
    
    // Verify existence
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'pgn_datalens'");
    if ($stmt->fetch()) {
        echo "VERIFICATION: Database 'pgn_datalens' FOUND.\n";
    } else {
        echo "VERIFICATION: Database 'pgn_datalens' NOT FOUND even after creation attempt.\n";
        exit(1);
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please check if your XAMPP MySQL is running.\n";
    exit(1);
}
