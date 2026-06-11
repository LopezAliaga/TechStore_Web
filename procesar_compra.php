<?php
session_start();
include 'includes/db.php';

if (isset($_SESSION['usuario_id']) && isset($_POST['direccion'])) {
    $user_id = $_SESSION['usuario_id'];
    $direccion = $_POST['direccion'];
    
    $tarjeta_full = $_POST['tarjeta'];
    $tarjeta_oculta = "**** **** **** " . substr($tarjeta_full, -4);
    
    // 1. Calculamos el total
    $sql_total = "SELECT SUM(p.precio * c.cantidad) as total FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = $user_id";
    $resultado = $conn->query($sql_total);
    $fila = $resultado->fetch_assoc();
    $total_pagado = $fila['total'];
    
    if ($total_pagado > 0) {
        
        // 2. Leemos los productos del carrito (¡AHORA JALAMOS LA IMAGEN TAMBIÉN!)
        $items_carrito = $conn->query("SELECT c.cantidad, p.nombre, p.id, p.imagen FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = $user_id");
        $resumen_compras = "";
        
        while ($item = $items_carrito->fetch_assoc()) {
            $id_prod = $item['id'];
            $cant_comprada = $item['cantidad'];
            $nombre_prod = $item['nombre'];
            
            // Arreglamos la ruta de la imagen por si acaso
            $img_bd = trim($item['imagen']);
            if (filter_var($img_bd, FILTER_VALIDATE_URL)) {
                $ruta_img = $img_bd;
            } else {
                $img_bd = str_replace("img/", "", $img_bd);
                $img_bd = str_replace(" ", "%20", $img_bd);
                $ruta_img = "img/productos/" . $img_bd;
            }
            
            // Armamos el diseño visual (Una mini tarjetita por producto, SIN emojis problemáticos)
            $resumen_compras .= '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; background: rgba(0,0,0,0.1); padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.05);">';
            $resumen_compras .= '<img src="/' . $ruta_img . '" style="width: 35px; height: 35px; object-fit: contain;" onerror="this.src=\'/img/placeholder.jpg\'">';
            $resumen_compras .= '<span style="font-size: 13px;"><strong>' . $cant_comprada . 'x</strong> ' . $nombre_prod . '</span>';
            $resumen_compras .= '</div>';
            
            // Restamos stock
            $conn->query("UPDATE productos SET stock = stock - $cant_comprada WHERE id = $id_prod");
        }
        
        // Limpiamos el texto para que la Base de Datos no explote con las comillas del HTML
        $resumen_escapado = $conn->real_escape_string($resumen_compras);
        
        // 3. Guardamos la compra incluyendo el HTML de las imágenes
        $conn->query("INSERT INTO compras (usuario_id, total, direccion, tarjeta_oculta, productos_resumen) VALUES ($user_id, '$total_pagado', '$direccion', '$tarjeta_oculta', '$resumen_escapado')");
        
        // 4. Vaciamos el carrito
        $conn->query("DELETE FROM carrito WHERE usuario_id = $user_id");
    }
}

echo "<script>
    alert('¡Pago aprobado! Tu pedido está en proceso.');
    window.location.href = 'mis_compras.php';
</script>";
?>