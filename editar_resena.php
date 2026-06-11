<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    $estrellas = (int)$_POST['estrellas'];
    
    // CORRECCIÓN: Mismo cambio aquí para evitar que editar lo vuelva null
    $comentario = strip_tags(trim($_POST['comentario']));
    
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;
        
        // Buscamos por ID y actualizamos solo las estrellas y el comentario
        $filtro = ['_id' => new MongoDB\BSON\ObjectId($resena_id)];
        $nuevos_datos = ['$set' => ['estrellas' => $estrellas, 'comentario' => $comentario]];
        
        $bulk->update($filtro, $nuevos_datos);
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        
        echo "<script>alert('Valoracion actualizada.'); window.location.href='detalleProducto.php?id=$producto_id';</script>";
    } catch (Exception $e) {
        echo "Error al actualizar: " . $e->getMessage();
    }
}
?>