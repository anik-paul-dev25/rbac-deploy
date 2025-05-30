<?php

$host = getenv('DB_HOST') ?: 'db4free.net';
$dbname = getenv('DB_NAME') ?: 'rbacsytem';
$username = getenv('DB_USER') ?: 'anik24';
$password = getenv('DB_PASS') ?: 'anik2425';
$port = getenv('DB_PORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}