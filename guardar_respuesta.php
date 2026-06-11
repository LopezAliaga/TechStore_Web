<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    
    // Filtramos la respuesta para evitar caracteres raros
    $respuesta_texto = preg_replace('/[^a-zA-Z0-9\s?.,!]/', '', trim($_POST['respuesta'])); 
    $nombre_usuario = $_SESSION['nombre'];
    
    // IP DE TU MONGODB
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;
        
        // Creamos el objeto de la respuesta
        $nueva_respuesta = [
            'usuario'   => $nombre_usuario,
            'respuesta' => $respuesta_texto,
            'fecha'     => new MongoDB\BSON\UTCDateTime()
        ];
        
        // La magia de Mongo: Usamos $push para meter la respuesta dentro del arreglo de la reseña original
        $bulk->update(
            ['_id' => new MongoDB\BSON\ObjectId($resena_id)],
            ['$push' => ['respuestas' => $nueva_respuesta]]
        );
        
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        
        // Redirigir de regreso
        header("Location: detalleProducto.php?id=" . $producto_id);
        exit();

    } catch (Exception $e) {
        echo "Error guardando la respuesta: " . $e->getMessage();
    }
} else {
    header("Location: productos.php");
    exit();
}
?>