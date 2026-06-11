<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include 'header.php'; 
include 'includes/db.php'; 

$user_id = $_SESSION['usuario_id'];

// --- LÓGICA PARA ELIMINAR DEL CARRITO ---
if(isset($_POST['eliminar_item'])) {
    $id_carrito = $_POST['id_carrito'];
    $conn->query("DELETE FROM carrito WHERE id = $id_carrito AND usuario_id = $user_id");
}

// --- LÓGICA PARA ACTUALIZAR CANTIDAD ---
if(isset($_POST['actualizar_cantidad'])) {
    $id_carrito = $_POST['id_carrito'];
    $nueva_cant = (int)$_POST['nueva_cantidad'];
    $id_prod = (int)$_POST['id_prod'];
    
    $stock_q = $conn->query("SELECT stock FROM productos WHERE id = $id_prod");
    $stock_real = $stock_q->fetch_assoc()['stock'];
    
    if($nueva_cant > $stock_real) {
        echo "<script>alert('⚠️ Solo quedan $stock_real unidades en stock. Se ajustará tu carrito.');</script>";
        $nueva_cant = $stock_real;
    }
    
    if($nueva_cant > 0) {
        $conn->query("UPDATE carrito SET cantidad = $nueva_cant WHERE id = $id_carrito AND usuario_id = $user_id");
    }
}

// --- LÓGICA DE CUPONES DE DESCUENTO ---
if(isset($_POST['aplicar_cupon'])) {
    $codigo = strtoupper($conn->real_escape_string(trim($_POST['codigo_cupon'])));
    $q_cupon = $conn->query("SELECT * FROM promociones WHERE codigo = '$codigo' AND activo = 1");
    
    if($q_cupon->num_rows > 0) {
        $cupon = $q_cupon->fetch_assoc();
        $_SESSION['cupon_codigo'] = $cupon['codigo'];
        $_SESSION['cupon_descuento'] = $cupon['descuento_porcentaje'];
        echo "<script>alert('¡Cupón del ".$cupon['descuento_porcentaje']."% aplicado con éxito!');</script>";
    } else {
        echo "<script>alert('Cupón inválido o expirado.');</script>";
    }
}

if(isset($_POST['quitar_cupon'])) {
    unset($_SESSION['cupon_codigo']);
    unset($_SESSION['cupon_descuento']);
}
?>

    <div class="container" style="padding: 40px 15px;">
        <h2 style="border-left: 5px solid var(--primary); padding-left: 15px; text-transform: uppercase; color: var(--text-main);">Tu Carrito de Compras</h2>
        
        <div style="background: var(--card-bg); border-radius: 12px; padding: 30px; margin-top: 20px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--search-border);">
            <table style="width: 100%; border-collapse: collapse; color: var(--text-main); text-align: left;">
                <tr style="border-bottom: 2px solid var(--search-border);">
                    <th style="padding: 15px;">Componente</th>
                    <th style="padding: 15px;">Precio Unitario</th>
                    <th style="padding: 15px; text-align: center;">Cantidad</th>
                    <th style="padding: 15px; text-align: right;">Subtotal</th>
                    <th style="padding: 15px; text-align: center;">Acción</th>
                </tr>
                
                <?php
                $sql = "SELECT c.id as carrito_id, p.id as producto_id, p.nombre, p.precio, p.imagen, p.stock, c.cantidad 
                        FROM carrito c 
                        JOIN productos p ON c.producto_id = p.id 
                        WHERE c.usuario_id = $user_id";
                
                $resultado = $conn->query($sql);
                $subtotal_carrito = 0;

                if($resultado->num_rows > 0) {
                    while($item = $resultado->fetch_assoc()) {
                        $cant_mostrar = $item['cantidad'];
                        if($cant_mostrar > $item['stock']) { $cant_mostrar = $item['stock']; }
                        
                        $subtotal_item = $item['precio'] * $cant_mostrar;
                        $subtotal_carrito += $subtotal_item;
                        
                        echo '<tr style="border-bottom: 1px solid var(--search-border);">';
                        echo '<td style="padding: 20px 15px; font-weight: bold; display: flex; align-items: center; gap: 15px;">';
                        echo '<img src="/img/productos/'.$item['imagen'].'" width="40" height="40" style="object-fit: contain;"> '.$item['nombre'].'</td>';
                        
                        echo '<td style="padding: 20px 15px;">S/ '.$item['precio'].'</td>';
                        
                        echo '<td style="padding: 20px 15px; text-align: center;">';
                        echo '<form method="POST" style="display: flex; gap: 5px; justify-content: center; align-items: center; margin: 0;">';
                        echo '<input type="hidden" name="id_carrito" value="'.$item['carrito_id'].'">';
                        echo '<input type="hidden" name="id_prod" value="'.$item['producto_id'].'">';
                        echo '<input type="number" name="nueva_cantidad" value="'.$cant_mostrar.'" min="1" max="'.$item['stock'].'" style="width: 50px; padding: 5px; text-align: center; background: var(--search-bg); color: var(--text-main); border: 1px solid var(--search-border); border-radius: 4px;">';
                        echo '<button type="submit" name="actualizar_cantidad" style="background: var(--primary); color: black; border: none; padding: 6px; border-radius: 4px; cursor: pointer;" title="Actualizar cantidad"><i class="fa-solid fa-arrows-rotate"></i></button>';
                        echo '</form></td>';

                        echo '<td style="padding: 20px 15px; text-align: right; color: var(--primary); font-weight: bold;">S/ '.$subtotal_item.'</td>';
                        
                        echo '<td style="padding: 20px 15px; text-align: center;">';
                        echo '<form method="POST" style="margin: 0;">';
                        echo '<input type="hidden" name="id_carrito" value="'.$item['carrito_id'].'">';
                        echo '<button type="submit" name="eliminar_item" style="background: #ff4a4a; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">X</button>';
                        echo '</form></td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">Tu carrito está vacío. ¡Ve a comprar algo de Hardware!</td></tr>';
                }
                ?>
            </table>
            
            <?php if($subtotal_carrito > 0): ?>
            <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-top: 2px solid var(--search-border); padding-top: 20px;">
                
                <div style="background: var(--search-bg); padding: 20px; border-radius: 8px; border: 1px dashed var(--primary); flex: 1; max-width: 400px;">
                    <h4 style="margin-top: 0; color: var(--primary);"><i class="fa-solid fa-ticket"></i> ¿Tienes un código de promoción?</h4>
                    <?php if(isset($_SESSION['cupon_codigo'])): ?>
                        <p style="color: #10b981; font-weight: bold; margin: 0 0 10px 0;">Cupón aplicado: <?php echo $_SESSION['cupon_codigo']; ?> (-<?php echo $_SESSION['cupon_descuento']; ?>%)</p>
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="quitar_cupon" style="background: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;">Quitar Cupón</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display: flex; gap: 10px; margin: 0;">
                            <input type="text" name="codigo_cupon" required placeholder="Ingresa tu código (Ej: PROFE20)" style="flex: 1; padding: 10px; border-radius: 5px; border: 1px solid var(--search-border); background: var(--bg-dark); color: var(--text-main);">
                            <button type="submit" name="aplicar_cupon" class="btn-neon" style="padding: 10px 15px;">Aplicar</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div style="text-align: right; min-width: 300px;">
                    <?php 
                    $descuento_monto = 0;
                    if(isset($_SESSION['cupon_descuento'])) {
                        $descuento_monto = $subtotal_carrito * ($_SESSION['cupon_descuento'] / 100);
                    }
                    $total_final = $subtotal_carrito - $descuento_monto;
                    ?>
                    
                    <p style="color: var(--text-muted); font-size: 16px; margin: 5px 0;">Subtotal: S/ <?php echo number_format($subtotal_carrito, 2); ?></p>
                    
                    <?php if($descuento_monto > 0): ?>
                        <p style="color: #10b981; font-size: 16px; margin: 5px 0;">Descuento (-<?php echo $_SESSION['cupon_descuento']; ?>%): - S/ <?php echo number_format($descuento_monto, 2); ?></p>
                    <?php endif; ?>
                    
                    <h3 style="color: var(--text-muted); margin-top: 15px;">TOTAL A PAGAR: <span style="color: var(--primary); font-size: 30px; margin-left: 10px;">S/ <?php echo number_format($total_final, 2); ?></span></h3>
                    <br>
                    <a href="checkout.php" class="btn" style="display: inline-block; padding: 15px 40px; font-size: 18px; cursor: pointer; background-color: var(--primary); color: black; border: none; border-radius: 5px; font-weight: bold; text-decoration: none; width: 100%; text-align: center; box-sizing: border-box;">Proceder al Pago</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>