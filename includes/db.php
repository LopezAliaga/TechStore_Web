<?php
// 1. CONEXIÓN A MARIADB (Para catálogo, stock, carrito y usuarios)
$host = "172.31.36.27"; // <-- Tu máquina de base de datos AWS
$usuario = "tech_user";
$password = "123456";
$base_datos = "techstore_db";

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die("Error fatal, no se pudo conectar a MariaDB: " . $conn->connect_error);
}

// 2. CONEXIÓN A MONGODB (Para reseñas y chatbot)
$mongo_ip = "172.31.36.27"; // <-- La misma máquina

try {
    $mongo_manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
} catch (Exception $e) {
    die("Error fatal, no se pudo conectar al servidor de MongoDB: " . $e->getMessage());
}
?>