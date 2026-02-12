<?php
$host = '127.0.0.1';
$db   = 'medkey_core';
$user = 'root';
$pass = ''; // Modifiez si vous avez un mot de passe
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Update ID 1
     $stmt1 = $pdo->prepare("UPDATE hospitals SET name = ?, domain = ?, database_name = ?, status = 'active' WHERE id = 1");
     $stmt1->execute(['Hopital Central de Casablanca', 'hopital1.localhost', 'medkey_hopital_central']);
     echo "Hosp 1 Updated\n";
     
     // Update ID 2
     $stmt2 = $pdo->prepare("UPDATE hospitals SET name = ?, domain = ?, database_name = ?, status = 'active' WHERE id = 2");
     $stmt2->execute(['Clinique Ibn Sina', 'hopital2.localhost', 'medkey_hospital_2']);
     echo "Hosp 2 Updated\n";

} catch (\PDOException $e) {
     echo "ERROR: " . $e->getMessage();
}
