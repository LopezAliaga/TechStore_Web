<?php
session_start();
include 'includes/db.php';

if (isset($_SESSION['usuario_id']) && isset($_POST['direccion'])) {
    $user_id = $_SESSION['usuario_id'];
    $direccion = $_POST['direccion'];
    
    $tarjeta_full = $_POST['tarjeta'];
    $tarjeta_oculta = "**** **** **** " . substr($tarjeta_full, -4);
    
    // 1. Calculamos el subtotal (Precio de los productos)
    $sql_total = "SELECT SUM(p.precio * c.cantidad) as total FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = $user_id";
    $resultado = $conn->query($sql_total);
    $fila = $resultado->fetch_assoc();
    $subtotal = $fila['total'];
    
    if ($subtotal > 0) {
        
        // --- ?? LÓGICA DE CUPÓN Y ENVÍO LÓGISTICO ?? ---
        $descuento_monto = 0;
        if(isset($_SESSION['cupon_descuento'])) {
            $descuento_monto = $subtotal * ($_SESSION['cupon_descuento'] / 100);
        }
        
        $costo_envio = 15.00; // El envío que configuramos en el checkout
        $total_final_pagado = ($subtotal - $descuento_monto) + $costo_envio;
        // ----------------------------------------------
        
        // 2. Leemos los productos del carrito y restamos stock
        $items_carrito = $conn->query("SELECT c.cantidad, p.nombre, p.id, p.imagen FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = $user_id");
        $resumen_compras = "";
        
        while ($item = $items_carrito->fetch_assoc()) {
            $id_prod = $item['id'];
            $cant_comprada = $item['cantidad'];
            $nombre_prod = $item['nombre'];
            
            $img_bd = trim($item['imagen']);
            if (filter_var($img_bd, FILTER_VALIDATE_URL)) {
                $ruta_img = $img_bd;
            } else {
                $img_bd = str_replace("img/", "", $img_bd);
                $img_bd = str_replace(" ", "%20", $img_bd);
                $ruta_img = "img/productos/" . $img_bd;
            }
            
            $resumen_compras .= '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; background: rgba(0,0,0,0.1); padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.05);">';
            $resumen_compras .= '<img src="/' . $ruta_img . '" style="width: 35px; height: 35px; object-fit: contain;" onerror="this.src=\'/img/placeholder.jpg\'">';
            $resumen_compras .= '<span style="font-size: 13px;"><strong>' . $cant_comprada . 'x</strong> ' . $nombre_prod . '</span>';
            $resumen_compras .= '</div>';
            
            // Restamos stock
            $conn->query("UPDATE productos SET stock = stock - $cant_comprada WHERE id = $id_prod");
        }
        
        $resumen_escapado = $conn->real_escape_string($resumen_compras);
        
        // 3. Guardamos la compra en la Base de Datos (Incluyendo el envío y estado)
        $sql_insert = "INSERT INTO compras (usuario_id, total, direccion, tarjeta_oculta, productos_resumen, costo_envio, estado_tracking) 
                       VALUES ($user_id, '$total_final_pagado', '$direccion', '$tarjeta_oculta', '$resumen_escapado', '$costo_envio', 'Procesando')";
        $conn->query($sql_insert);
        
        // 4. Limpieza post-compra
        $conn->query("DELETE FROM carrito WHERE usuario_id = $user_id"); // Vaciamos carrito
        unset($_SESSION['cupon_codigo']); // Borramos el cupón usado
        unset($_SESSION['cupon_descuento']); 
    }
}

echo "<script>
    alert('¡Pago aprobado! Tu pedido esta en proceso.');
    window.location.href = 'mis_compras.php';
</script>";
?>