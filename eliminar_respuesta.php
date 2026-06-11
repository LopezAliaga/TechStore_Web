<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    
    $respuesta_texto = htmlspecialchars_decode($_POST['respuesta_texto'], ENT_QUOTES);
    $respuesta_autor = $_POST['respuesta_autor'];
    
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;
        
        $filtro = ['_id' => new MongoDB\BSON\ObjectId($resena_id)];
        
        // $pull le dice a Mongo: "saca esta respuesta específica de la lista"
        $sacar_respuesta = [
            '$pull' => [
                'respuestas' => [
                    'usuario' => $respuesta_autor,
                    'respuesta' => $respuesta_texto
                ]
            ]
        ];
        
        $bulk->update($filtro, $sacar_respuesta);
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        
        echo "<script>window.location.href='detalleProducto.php?id=$producto_id';</script>";
    } catch (Exception $e) {
        echo "Error al eliminar respuesta: " . $e->getMessage();
    }
}
?>