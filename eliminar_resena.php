<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;
        
        // Filtramos por ID usando la clase de MongoDB
        $filtro = ['_id' => new MongoDB\BSON\ObjectId($resena_id)];
        $bulk->delete($filtro);
        
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        
        echo "<script>alert('Reseña eliminada.'); window.location.href='detalleProducto.php?id=$producto_id';</script>";
    } catch (Exception $e) {
        echo "Error al eliminar: " . $e->getMessage();
    }
}
?>