<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 
include 'header.php';
include 'includes/db.php';

// --- LÓGICA: CREAR PUBLICACIÓN Y ETIQUETAR PRODUCTOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_post']) && isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nombre_img = "post_" . time() . "_" . rand(1000,9999) . "." . $ext;
            // Usamos la carpeta productos que ya sabemos que tiene buenos permisos
            $destino = "img/productos/" . $nombre_img;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                // Guardar publicación
                $conn->query("INSERT INTO publicaciones (usuario_id, imagen, descripcion) VALUES ($usuario_id, '$nombre_img', '$descripcion')");
                $post_id = $conn->insert_id;

                // Guardar productos etiquetados
                if (isset($_POST['productos_etiquetados']) && is_array($_POST['productos_etiquetados'])) {
                    foreach ($_POST['productos_etiquetados'] as $p_id) {
                        $id_limpio = (int)$p_id;
                        $conn->query("INSERT INTO publicacion_productos (publicacion_id, producto_id) VALUES ($post_id, $id_limpio)");
                    }
                }
                echo "<script>alert('¡Publicación subida con éxito!'); window.location.href='feed.php';</script>";
                exit();
            }
        }
    }
}

// --- LÓGICA: COMPRA DIRECTA DESDE LA PUBLICACIÓN (Shop the Post) ---
if(isset($_POST['agregar_carrito_post'])) {
    if(!isset($_SESSION['usuario_id'])) {
        echo "<script>alert('Debes iniciar sesión para comprar.'); window.location.href='login.php';</script>";
    } else {
        $user_id = $_SESSION['usuario_id'];
        $prod_id = (int)$_POST['producto_id'];
        
        $stock_q = $conn->query("SELECT stock FROM productos WHERE id = $prod_id");
        $stock_real = $stock_q->fetch_assoc()['stock'];
        
        $check = $conn->query("SELECT cantidad FROM carrito WHERE usuario_id = $user_id AND producto_id = $prod_id");
        
        if($check->num_rows > 0) {
            $nueva_cantidad = $check->fetch_assoc()['cantidad'] + 1;
            if($nueva_cantidad <= $stock_real) {
                $conn->query("UPDATE carrito SET cantidad = $nueva_cantidad WHERE usuario_id = $user_id AND producto_id = $prod_id");
                echo "<script>alert('¡Producto añadido al carrito desde el post!');</script>";
            }
        } else {
            if($stock_real > 0) {
                $conn->query("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES ($user_id, $prod_id, 1)");
                echo "<script>alert('¡Producto añadido al carrito desde el post!');</script>";
            }
        }
        echo "<script>window.location.href='feed.php';</script>";
    }
}
?>

<div class="container" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: var(--primary); text-transform: uppercase; margin: 0;">
            <i class="fa-solid fa-camera-retro"></i> Comunidad & Setups
        </h2>
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <button onclick="document.getElementById('modal-post').style.display='block'" class="btn-neon" style="padding: 10px 20px;">
                <i class="fa-solid fa-plus"></i> Subir mi Setup
            </button>
        <?php endif; ?>
    </div>

    <div id="modal-post" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; justify-content:center; align-items:center;">
        <div style="background:var(--bg-dark); padding:30px; border-radius:12px; border:1px solid var(--primary); width:90%; max-width:500px; margin: 50px auto; position: relative;">
            <button onclick="document.getElementById('modal-post').style.display='none'" style="position:absolute; top:15px; right:15px; background:none; border:none; color:white; font-size:20px; cursor:pointer;">&times;</button>
            <h3 style="margin-top:0; color:var(--text-main);">Comparte tu Setup</h3>
            
            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:15px;">
                <div>
                    <label style="color:var(--text-muted); font-size:14px;">Foto del setup (JPG, PNG):</label>
                    <input type="file" name="imagen" accept="image/*" required style="width:100%; padding:10px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border); border-radius:5px; margin-top:5px;">
                </div>
                
                <div>
                    <label style="color:var(--text-muted); font-size:14px;">Descripción:</label>
                    <textarea name="descripcion" rows="3" required style="width:100%; box-sizing: border-box; padding:10px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border); border-radius:5px; margin-top:5px;" placeholder="¡Mira cómo quedó mi nueva PC!"></textarea>
                </div>
                
                <div>
                    <label style="color:var(--primary); font-size:14px; font-weight:bold;">Etiquetar Productos (Mantén presionado CTRL para elegir varios):</label>
                    <select name="productos_etiquetados[]" multiple required style="width:100%; height:120px; padding:10px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--primary); border-radius:5px; margin-top:5px;">
                        <?php
                        $prods = $conn->query("SELECT id, nombre FROM productos WHERE stock > 0 ORDER BY nombre ASC");
                        while($p = $prods->fetch_assoc()) {
                            echo "<option value='".$p['id']."'>".$p['nombre']."</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <button type="submit" name="crear_post" class="btn-neon" style="padding:12px; margin-top:10px;">Publicar ahora</button>
            </form>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 40px; margin-bottom: 60px;">
        <?php
        $sql_feed = "SELECT p.*, u.nombre as autor FROM publicaciones p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha DESC";
        $feed = $conn->query($sql_feed);

        if($feed && $feed->num_rows > 0) {
            while($post = $feed->fetch_assoc()) {
                $fecha_post = date('d/m/Y H:i', strtotime($post['fecha']));
                ?>
                <div style="background: var(--card-bg); border: 1px solid var(--search-border); border-radius: 12px; overflow: hidden; display: grid; grid-template-columns: 2fr 1fr; gap: 0;">
                    
                    <div style="padding: 20px; border-right: 1px solid var(--search-border);">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: black; font-weight: bold; font-size: 18px;">
                                <?php echo strtoupper(substr($post['autor'], 0, 1)); ?>
                            </div>
                            <div>
                                <strong style="color: var(--text-main); display: block;"><?php echo htmlspecialchars($post['autor']); ?></strong>
                                <span style="color: var(--text-muted); font-size: 12px;"><?php echo $fecha_post; ?></span>
                            </div>
                        </div>
                        
                        <img src="img/productos/<?php echo $post['imagen']; ?>" style="width: 100%; max-height: 500px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;" onerror="this.src='https://via.placeholder.com/600x400?text=Setup'">
                        
                        <p style="color: #cbd5e1; font-size: 15px; margin: 0; line-height: 1.5;">
                            <?php echo nl2br(htmlspecialchars($post['descripcion'])); ?>
                        </p>
                    </div>

                    <div style="padding: 20px; background: rgba(0,0,0,0.2);">
                        <h4 style="margin-top: 0; color: var(--primary); border-bottom: 1px solid var(--search-border); padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fa-solid fa-tag"></i> En este Setup
                        </h4>
                        
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <?php
                            $post_id = $post['id'];
                            $sql_tags = "SELECT p.id, p.nombre, p.precio, p.imagen, p.stock FROM productos p JOIN publicacion_productos pp ON p.id = pp.producto_id WHERE pp.publicacion_id = $post_id";
                            $tags = $conn->query($sql_tags);
                            
                            if($tags && $tags->num_rows > 0) {
                                while($tag = $tags->fetch_assoc()) {
                                    $img_tag = (filter_var($tag['imagen'], FILTER_VALIDATE_URL)) ? $tag['imagen'] : "img/productos/" . trim($tag['imagen']);
                                    ?>
                                    <div style="background: var(--bg-dark); border: 1px solid var(--search-border); border-radius: 8px; padding: 10px; display: flex; align-items: center; gap: 15px;">
                                        <img src="<?php echo $img_tag; ?>" style="width: 60px; height: 60px; object-fit: contain; background: white; border-radius: 5px;">
                                        <div style="flex: 1;">
                                            <a href="detalleProducto.php?id=<?php echo $tag['id']; ?>" style="color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: bold; display: block; margin-bottom: 5px;"><?php echo $tag['nombre']; ?></a>
                                            <span style="color: var(--primary); font-weight: bold; font-size: 14px;">S/ <?php echo number_format($tag['precio'], 2); ?></span>
                                        </div>
                                        
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="producto_id" value="<?php echo $tag['id']; ?>">
                                            <?php if($tag['stock'] > 0): ?>
                                                <button type="submit" name="agregar_carrito_post" style="background: var(--primary); color: black; border: none; padding: 8px; border-radius: 5px; cursor: pointer;" title="Añadir al Carrito">
                                                    <i class="fa-solid fa-cart-plus"></i>
                                                </button>
                                            <?php else: ?>
                                                <button disabled style="background: #444; color: #888; border: none; padding: 8px; border-radius: 5px;" title="Agotado">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo "<p style='color:var(--text-muted); font-size:13px;'>No hay productos etiquetados.</p>";
                            }
                            ?>
                        </div>
                    </div>
                    
                </div>
                <?php
            }
        } else {
            echo "<div style='text-align:center; padding:50px; color:var(--text-muted);'>Aún no hay publicaciones en la comunidad. ¡Sé el primero!</div>";
        }
        ?>
    </div>
</div>

<script>
window.onclick = function(event) {
    var modal = document.getElementById('modal-post');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
