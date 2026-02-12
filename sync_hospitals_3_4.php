<?php
$host = '127.0.0.1';
$db   = 'medkey_core';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Update ID 3 - Centre Hospitalier Universitaire Mohammed VI
     $stmt3 = $pdo->prepare("UPDATE hospitals SET name = ?, domain = ?, database_name = ?, status = 'active' WHERE id = 3");
     $stmt3->execute(['Centre Hospitalier Universitaire Mohammed VI', 'hopital3.localhost', 'medkey_hospital_3']);
     echo "Hosp 3 Updated\n";
     
     // Update ID 4 - Hôpital Moulay Youssef
     $stmt4 = $pdo->prepare("UPDATE hospitals SET name = ?, domain = ?, database_name = ?, status = 'active' WHERE id = 4");
     $stmt4->execute(['Hôpital Moulay Youssef', 'hopital4.localhost', 'medkey_hospital_4']);
     echo "Hosp 4 Updated\n";

} catch (\PDOException $e) {
     echo "ERROR: " . $e->getMessage();
}
