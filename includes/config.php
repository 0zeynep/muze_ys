<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1'; 
$db   = 'museum';
$user = 'root'; 
$pass = '';       
$port = 3308;    
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ 
    ]);
    
    
    global $db;
    $db = $pdo;

    

} catch (PDOException $e) {
   
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>
