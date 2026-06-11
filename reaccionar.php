<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    $usuario_id = $_SESSION['usuario_id'];

    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;

        // 1. Buscar la reseña para ver si el usuario ya le dio Like
        $query = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId($resena_id)]);
        $cursor = $manager->executeQuery('techstore_interacciones.resenas', $query);
        $resenas = $cursor->toArray();
        
        if (!empty($resenas)) {
            $resena = $resenas[0];
            $likes = isset($resena->likes) ? (array)$resena->likes : [];
            $filtro = ['_id' => new MongoDB\BSON\ObjectId($resena_id)];

            // 2. Si ya le dio like, se lo quitamos. Si no, se lo ponemos.
            if (in_array($usuario_id, $likes)) {
                $actualizar = ['$pull' => ['likes' => $usuario_id]]; // Quitar Like
            } else {
                $actualizar = ['$addToSet' => ['likes' => $usuario_id]]; // Añadir Like
            }

            $bulk->update($filtro, $actualizar);
            $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        }

        // 3. Regresar exactamente a donde estaba el usuario (usando un ancla #)
        echo "<script>window.location.href='detalleProducto.php?id=$producto_id#resena-$resena_id';</script>";
    } catch (Exception $e) {
        echo "Error al reaccionar: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>