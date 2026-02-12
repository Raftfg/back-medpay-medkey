<?php
$host = '127.0.0.1';
$db   = 'medkey_core';
$user = 'root';
$pass = '';
$port = '3306';

echo "Testing connection to $host:$port ($db)...\n";
$start = microtime(true);
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "✅ Connected in " . (microtime(true) - $start) . " seconds\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}

$db2 = 'medkey_nouvelle';
echo "Testing connection to $host:$port ($db2)...\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db2;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "✅ Connected in " . (microtime(true) - $start) . " seconds\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
