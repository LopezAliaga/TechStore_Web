<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); } 
include 'header.php'; 
include 'includes/db.php'; 

// --- LÓGICA PARA AGREGAR AL CARRITO CON CANTIDAD Y STOCK ---
if(isset($_POST['agregar_carrito'])) {
    if(!isset($_SESSION['usuario_id'])) {
        echo "<script>alert('¡Alto ahí! Debes iniciar sesión para poder comprar.'); window.location.href='login.php';</script>";
    } else {
        $user_id = $_SESSION['usuario_id'];
        $prod_id = $_POST['producto_id'];
        $cantidad_deseada = (int)$_POST['cantidad'];
        
        $stock_q = $conn->query("SELECT stock FROM productos WHERE id = $prod_id");
        $stock_real = $stock_q->fetch_assoc()['stock'];
        
        $check = $conn->query("SELECT cantidad FROM carrito WHERE usuario_id = $user_id AND producto_id = $prod_id");
        
        if($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $nueva_cantidad = $row['cantidad'] + $cantidad_deseada;
            
            if($nueva_cantidad > $stock_real) {
                echo "<script>alert('⚠️ ¡STOCK MÁXIMO ALCANZADO! Solo nos quedan $stock_real unidades en total y ya tienes algunas en tu carrito.');</script>";
            } else {
                $conn->query("UPDATE carrito SET cantidad = $nueva_cantidad WHERE usuario_id = $user_id AND producto_id = $prod_id");
                echo "<script>alert('✅ ¡$cantidad_deseada unidades añadidas a tu carrito!');</script>";
            }
        } else {
            if($cantidad_deseada > $stock_real) {
                echo "<script>alert('⚠️ ¡STOCK MÁXIMO ALCANZADO! Solo nos quedan $stock_real unidades.');</script>";
            } else {
                $conn->query("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES ($user_id, $prod_id, $cantidad_deseada)");
                echo "<script>alert('✅ ¡$cantidad_deseada unidades añadidas a tu carrito!');</script>";
            }
        }
    }
}
?>

<style>
    .layout-catalogo { display: grid; grid-template-columns: 250px 1fr; gap: 30px; align-items: start; }
    
    .sidebar { 
        background: var(--card-bg); padding: 20px; border-radius: 12px; 
        border: 1px solid var(--search-border); 
        position: sticky; top: 90px; 
        max-height: calc(100vh - 110px); 
        overflow-y: auto; 
    }
    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-thumb { background: var(--search-border); border-radius: 10px; }
    .sidebar::-webkit-scrollbar-thumb:hover { background: var(--primary); }

    .sidebar h3 { margin-top: 0; color: var(--primary); border-bottom: 1px solid var(--search-border); padding-bottom: 10px; }
    
    .cat-link { 
        display: block; padding: 10px 15px; color: var(--text-muted); 
        text-decoration: none; border-radius: 5px; transition: all 0.3s ease; margin-bottom: 5px; 
    }
    .cat-link:hover, .cat-link.active { 
        background: rgba(0,255,136,0.1); 
        color: var(--primary); 
        transform: translateX(8px);
    }
    
    @media (max-width: 768px) { 
        .layout-catalogo { grid-template-columns: 1fr; } 
        .sidebar { position: static; max-height: none; overflow-y: visible; margin-bottom: 20px; } 
    }
</style>

<div class="container layout-catalogo" style="margin-top: 40px; padding: 0 15px;">
    
    <aside class="sidebar">
        <h3><i class="fa-solid fa-layer-group"></i> Filtros</h3>
        
        <a href="productos.php" class="cat-link <?php echo !isset($_GET['categoria']) && !isset($_GET['buscar']) ? 'active' : ''; ?>" style="margin-bottom: 15px;">
            <i class="fa-solid fa-house"></i> Todas las categorías
        </a>
        
        <?php
        $padres = $conn->query("SELECT * FROM categorias WHERE padre_id IS NULL");
        while($p = $padres->fetch_assoc()) {
            $id_p = $p['id'];
            $active_padre = (isset($_GET['categoria']) && $_GET['categoria'] == $id_p) ? 'active' : '';
            
            echo "<a href='productos.php?categoria=$id_p' class='cat-link $active_padre' style='font-weight: 600; display: flex; align-items: center;'>
                    <i class='fa-solid fa-chevron-right' style='font-size: 10px; margin-right: 8px;'></i> " . $p['nombre'] . "
                  </a>";
            
            $mostrar_hijos = false;
            if(isset($_GET['categoria'])) {
                $cat_actual = $_GET['categoria'];
                if($cat_actual == $id_p) {
                    $mostrar_hijos = true;
                } else {
                    $check_hijo = $conn->query("SELECT id FROM categorias WHERE id = $cat_actual AND padre_id = $id_p");
                    if($check_hijo && $check_hijo->num_rows > 0) { $mostrar_hijos = true; }
                }
            }

            if($mostrar_hijos) {
                $hijos = $conn->query("SELECT * FROM categorias WHERE padre_id = $id_p");
                if($hijos->num_rows > 0) {
                    echo "<div style='padding-left: 15px; border-left: 2px solid var(--search-border); margin-bottom: 15px; margin-left: 10px;'>";
                    while($h = $hijos->fetch_assoc()) {
                        $active_hijo = (isset($_GET['categoria']) && $_GET['categoria'] == $h['id']) ? 'active' : '';
                        echo "<a href='productos.php?categoria=".$h['id']."' class='cat-link $active_hijo' style='font-size: 13px; padding: 8px 10px;'>
                                <i class='fa-solid fa-caret-right'></i> ".$h['nombre']."
                              </a>";
                    }
                    echo "</div>";
                }
            }
        }
        ?>
    </aside>

    <main>
        <?php
        $titulo = "Catálogo Completo";
        $sql = "SELECT * FROM productos";
        
        if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
            $busqueda = $conn->real_escape_string($_GET['buscar']);
            $titulo = "Búsqueda: '$busqueda'";
            $sql = "SELECT * FROM productos WHERE nombre LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%'";
        } elseif (isset($_GET['categoria'])) {
            $cat_id = (int) $_GET['categoria'];
            $sql = "SELECT * FROM productos WHERE categoria_id = $cat_id OR categoria_id IN (SELECT id FROM categorias WHERE padre_id = $cat_id)";
            
            $nom_cat = $conn->query("SELECT nombre FROM categorias WHERE id = $cat_id");
            if ($row_cat = $nom_cat->fetch_assoc()) { $titulo = $row_cat['nombre']; }
        }

        echo "<h2 style='margin-top: 0; display: flex; align-items: center; gap: 10px; color: var(--text-main); text-transform: uppercase;'>
        <i class='fa-solid fa-bolt' style='color: var(--primary);'></i> $titulo</h2>";
        ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php
            $resultado = $conn->query($sql);
            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    echo '<div class="producto-card" style="padding: 20px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--search-border); box-sizing: border-box; display: flex; flex-direction: column;">';
                    
                    $imagen = trim($fila['imagen']);
                    if (!filter_var($imagen, FILTER_VALIDATE_URL)) {
                        $imagen = str_replace("img/", "", $imagen);
                        $imagen = str_replace(" ", "%20", $imagen);
                        $imagen = "img/productos/" . $imagen;
                    }
                    
                    // 👇👇 LA MAGIA DEL ENLACE ESTÁ AQUÍ 👇👇
                    echo '<a href="detalleProducto.php?id=' . $fila['id'] . '" style="text-decoration: none; display:block; cursor:pointer;">';
                    echo '<img src="' . $imagen . '" style="height: 180px; width: 100%; object-fit: contain; margin-bottom: 15px; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" onerror="this.src=\'img/placeholder.jpg\'">';
                    echo '<h3 style="margin: 0 0 10px 0; font-size: 15px; color: var(--text-main); height: 45px; overflow: hidden; transition: color 0.3s;" onmouseover="this.style.color=\'var(--primary)\'" onmouseout="this.style.color=\'var(--text-main)\'">' . $fila['nombre'] . '</h3>';
                    echo '</a>';
                    // 👆👆 FIN DEL ENLACE 👆👆

                    echo '<h2 style="color: var(--primary); margin: 10px 0;">S/ ' . $fila['precio'] . '</h2>';

                    echo '<div style="margin-top: auto;">';
                    if ($fila['stock'] <= $fila['stock_minimo'] && $fila['stock'] > 0) {
                        echo '<p style="color: #ff4a4a; font-size: 12px; margin-bottom: 15px;"><i class="fa-solid fa-fire"></i> ¡ALERTA! Quedan ' . $fila['stock'] . '</p>';
                    } elseif ($fila['stock'] == 0) {
                        echo '<p style="color: #ff4a4a; font-size: 12px; margin-bottom: 15px;"><i class="fa-solid fa-xmark"></i> Sin stock</p>';
                    } else {
                        echo '<p style="color: var(--text-muted); font-size: 12px; margin-bottom: 15px;"><i class="fa-solid fa-check"></i> Stock: ' . $fila['stock'] . '</p>';
                    }

                    if ($fila['stock'] > 0) {
                        echo '<form method="POST" style="margin: 0;">';
                        echo '<input type="hidden" name="producto_id" value="' . $fila['id'] . '">';
                        echo '<div style="display: flex; gap: 10px; align-items: center;">';
                        echo '<input type="number" name="cantidad" value="1" min="1" max="' . $fila['stock'] . '" required style="width: 60px; padding: 10px; border-radius: 8px; background: var(--search-bg); color: var(--text-main); border: 1px solid var(--search-border); text-align: center; font-family: \'Poppins\', sans-serif; font-weight: bold;">';
                        echo '<button type="submit" name="agregar_carrito" style="flex: 1; background: transparent; color: #10b981; border: 2px solid #10b981; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; transition: all 0.3s ease;" onmouseover="this.style.background=\'#10b981\'; this.style.color=\'black\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#10b981\';"><i class="fa-solid fa-cart-plus"></i> Añadir</button>';
                        echo '</div>';
                        echo '</form>';
                    } else {
                        echo '<button disabled style="width: 100%; background: #334155; color: #94a3b8; border: none; padding: 10px; border-radius: 8px; font-weight: bold; cursor: not-allowed; font-size: 14px;"><i class="fa-solid fa-ban"></i> Agotado</button>';
                    }
                    echo '</div>'; 
                    echo '</div>'; 
                }
            } else {
                echo "<div style='grid-column: 1 / -1; padding: 50px; text-align: center; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--search-border);'>";
                echo "<h3 style='color: var(--text-muted);'><i class='fa-solid fa-box-open fa-2x' style='margin-bottom: 15px;'></i><br>No hay productos en esta categoría.</h3>";
                echo "</div>";
            }
            ?>
        </div>
    </main>
</div>
</body>
</html>