<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 
include 'header.php';
include 'includes/db.php';

if(!isset($_GET['id'])){
    die("<div class='container'><h3 style='color:var(--text-muted); padding:50px; text-align:center;'>Producto no encontrado.</h3></div>");
}

$id = (int)$_GET['id'];

// --- LÓGICA PARA AGREGAR AL CARRITO (MariaDB) ---
if(isset($_POST['agregar_carrito'])) {
    if(!isset($_SESSION['usuario_id'])) {
        echo "<script>alert('Debes iniciar sesion para poder comprar.'); window.location.href='login.php';</script>";
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
                echo "<script>alert('STOCK MAXIMO ALCANZADO Solo nos quedan $stock_real unidades.');</script>";
            } else {
                $conn->query("UPDATE carrito SET cantidad = $nueva_cantidad WHERE usuario_id = $user_id AND producto_id = $prod_id");
                echo "<script>alert('$cantidad_deseada unidades anadidas a tu carrito');</script>";
            }
        } else {
            if($cantidad_deseada > $stock_real) {
                echo "<script>alert('STOCK MAXIMO ALCANZADO Solo nos quedan $stock_real unidades.');</script>";
            } else {
                $conn->query("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES ($user_id, $prod_id, $cantidad_deseada)");
                echo "<script>alert('$cantidad_deseada unidades anadidas a tu carrito');</script>";
            }
        }
        echo "<script>window.location.href='detalleProducto.php?id=$id';</script>";
    }
}

$sql = "SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = $id";
$resultado = $conn->query($sql);

if($resultado->num_rows == 0){
    die("<div class='container'><h3 style='color:var(--text-muted); padding:50px; text-align:center;'>Producto no encontrado.</h3></div>");
}

$producto = $resultado->fetch_assoc();
?>

<style>
    .layout-detalle { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start; margin-top: 40px; }
    @media (max-width: 768px) { .layout-detalle { grid-template-columns: 1fr; gap: 20px; } }
    
    .respuesta-box {
        margin-top: 10px; margin-left: 30px; padding: 12px;
        background: rgba(255,255,255,0.03); border-left: 2px solid var(--primary);
        border-radius: 0 8px 8px 0; font-size: 14px;
    }

    .share-menu {
        display: none; position: absolute; background: var(--card-bg); border: 1px solid var(--search-border);
        border-radius: 8px; padding: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 100;
        flex-direction: column; gap: 8px; min-width: 150px;
    }
    .share-btn {
        background: none; border: none; color: var(--text-main); text-align: left; padding: 8px;
        cursor: pointer; border-radius: 5px; font-size: 14px; width: 100%; transition: 0.2s;
    }
    .share-btn:hover { background: rgba(255,255,255,0.1); color: var(--primary); }
</style>

<div class="container layout-detalle">
    <div>
        <?php
        $imagen = trim($producto['imagen']);
        $ruta = (filter_var($imagen, FILTER_VALIDATE_URL)) ? $imagen : "img/productos/" . str_replace(" ", "%20", str_replace("img/", "", $imagen));
        ?>
        <img src="<?php echo $ruta; ?>" 
             style="width:100%; max-height:500px; object-fit:contain; background:#fff; border-radius:10px; padding:20px; box-sizing: border-box;" 
             onerror="this.src='img/placeholder.jpg'">
    </div>

    <div>
        <h1 style="color: var(--text-main); margin-top: 0;"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
        <p style="color:var(--text-muted); font-size:16px;">
            <i class="fa-solid fa-tags"></i> Categor&iacute;a: <strong><?php echo $producto['categoria'] ?? 'Sin categor&iacute;a'; ?></strong>
        </p>
        <h2 style="color:var(--primary); font-size: 32px; margin: 20px 0;">
            S/ <?php echo number_format($producto['precio'], 2); ?>
        </h2>

        <?php if ($producto['stock'] <= $producto['stock_minimo'] && $producto['stock'] > 0): ?>
            <p style="color: #ff4a4a; font-size: 14px;"><i class="fa-solid fa-fire"></i> ALERTA Solo quedan <?php echo $producto['stock']; ?> unidades.</p>
        <?php elseif ($producto['stock'] == 0): ?>
            <p style="color: #ff4a4a; font-size: 14px;"><i class="fa-solid fa-xmark"></i> Agotado temporalmente</p>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 14px;"><i class="fa-solid fa-check"></i> Stock disponible: <strong><?php echo $producto['stock']; ?></strong></p>
        <?php endif; ?>

        <hr style="border-color: var(--search-border); margin: 20px 0;">
        <h3>Descripci&oacute;n</h3>
        <p style="line-height:1.8; color: #b0b0b0;">
            <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
        </p>
        <hr style="border-color: var(--search-border); margin: 20px 0;">

        <div style="position: relative; display: inline-block; width: 100%; margin-bottom: 15px;">
            <button onclick="toggleShareMenu('menu-pub')" class="btn-neon" style="width: 100%; padding: 12px; background: transparent; border: 1px solid var(--primary); color: var(--primary);">
                <i class="fa-solid fa-share-nodes"></i> Compartir este Producto
            </button>
            <div id="menu-pub" class="share-menu" style="width: 100%; top: 100%; left: 0; margin-top: 5px;">
                <button class="share-btn" onclick="shareSocial('whatsapp', 'pub', null)"><i class="fa-brands fa-whatsapp" style="color:#25D366; width:20px;"></i> WhatsApp</button>
                <button class="share-btn" onclick="shareSocial('instagram', 'pub', null)"><i class="fa-brands fa-instagram" style="color:#E1306C; width:20px;"></i> Instagram</button>
                <button class="share-btn" onclick="shareSocial('tiktok', 'pub', null)"><i class="fa-brands fa-tiktok" style="color:#ffffff; width:20px;"></i> TikTok</button>
                <button class="share-btn" onclick="shareSocial('copy', 'pub', null)"><i class="fa-solid fa-link" style="color:var(--text-muted); width:20px;"></i> Copiar Enlace</button>
            </div>
        </div>

        <?php if ($producto['stock'] > 0): ?>
            <form method="POST" action="" style="display: flex; gap: 15px; align-items: center;">
                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>" required 
                       style="width: 70px; padding: 12px; border-radius: 8px; background: var(--search-bg); color: var(--text-main); border: 1px solid var(--search-border); text-align: center; font-weight: bold; font-size: 16px;">
                <button type="submit" name="agregar_carrito" class="btn-neon" style="flex: 1; padding: 12px;">
                    <i class="fa-solid fa-cart-plus"></i> A&ntilde;adir al carrito
                </button>
            </form>
        <?php else: ?>
            <button disabled style="width: 100%; background: #334155; color: #94a3b8; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: not-allowed; font-size: 16px;">
                <i class="fa-solid fa-ban"></i> No disponible
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="margin-top: 50px;">
    <hr style="border-color: var(--search-border); margin-bottom: 40px;">
    <h2><i class="fa-solid fa-comments"></i> Opiniones de clientes</h2>
    <br>

    <?php
    $hay_resenas = false;
    try {
        $query = new MongoDB\Driver\Query(['producto_id' => $id], ['sort' => ['fecha' => -1]]);
        $cursor = $mongo_manager->executeQuery('techstore_interacciones.resenas', $query);

        foreach($cursor as $r) {
            $hay_resenas = true;
            $id_resena_str = (string)$r->_id; 
            
            $estrellas_html = str_repeat("&#9733;", (int)$r->estrellas);
            
            $comentario_mostrar = htmlspecialchars($r->comentario, ENT_QUOTES, 'UTF-8');
            $comentario_textarea = htmlspecialchars($r->comentario, ENT_QUOTES, 'UTF-8');
            $autor = htmlspecialchars($r->usuario, ENT_QUOTES, 'UTF-8');
            $fecha_str = $r->fecha->toDateTime()->format('d/m/Y H:i');
            
            $es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador');
            $es_dueno = (isset($_SESSION['nombre']) && $_SESSION['nombre'] === $autor);

            // --- LÓGICA DE LIKES ---
            $likes_array = isset($r->likes) ? (array)$r->likes : [];
            $likes_count = count($likes_array);
            $user_has_liked = (isset($_SESSION['usuario_id']) && in_array($_SESSION['usuario_id'], $likes_array));
            $icono_corazon = $user_has_liked ? "fa-solid fa-heart" : "fa-regular fa-heart";
            $color_corazon = $user_has_liked ? "#ef4444" : "var(--text-muted)";
            // -----------------------

            echo '<div id="resena-'.$id_resena_str.'" class="producto-card" style="margin-bottom:15px; padding: 20px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--search-border); scroll-margin-top: 100px;">';
            echo '  <strong style="color: var(--text-main);"><i class="fa-solid fa-user"></i> '.$autor.'</strong>'; 
            echo '  <span style="float: right; color: var(--text-muted); font-size: 12px;"><i class="fa-regular fa-clock"></i> '.$fecha_str.'</span><br>';
            echo '  <div style="margin: 5px 0; font-size: 16px; color:#fbbf24;">'.$estrellas_html.'</div>';
            echo '  <p style="color: #b0b0b0; margin: 10px 0 15px 0;">'.$comentario_mostrar.'</p>';
            
            echo '  <div style="display:flex; gap:15px; align-items:center; position: relative; flex-wrap:wrap;">';
            
            // BOTON DE ME GUSTA (REACCIONES)
            if(isset($_SESSION['usuario_id'])){
                echo "  <form method='POST' action='reaccionar.php' style='margin:0;'>
                            <input type='hidden' name='resena_id' value='$id_resena_str'>
                            <input type='hidden' name='producto_id' value='$id'>
                            <button type='submit' style='background:transparent; border:none; color:$color_corazon; cursor:pointer; font-size:13px; font-weight:bold;'>
                                <i class='$icono_corazon'></i> Me gusta ($likes_count)
                            </button>
                        </form>";
            } else {
                echo "  <span style='color:var(--text-muted); font-size:13px;'><i class='fa-regular fa-heart'></i> $likes_count Me gusta</span>";
            }

            // BOTON COMPARTIR COMENTARIO
            echo '      <button onclick="toggleShareMenu(\'menu-com-'.$id_resena_str.'\')" style="background:transparent; border:1px solid var(--primary); color:var(--primary); padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px;">
                            <i class="fa-solid fa-share"></i> Compartir
                        </button>';
            
            echo '      <div id="menu-com-'.$id_resena_str.'" class="share-menu" style="bottom: 100%; left: 0; margin-bottom: 5px;">
                            <button class="share-btn" onclick="shareSocial(\'whatsapp\', \'com\', \''.$id_resena_str.'\')"><i class="fa-brands fa-whatsapp" style="color:#25D366; width:20px;"></i> WhatsApp</button>
                            <button class="share-btn" onclick="shareSocial(\'instagram\', \'com\', \''.$id_resena_str.'\')"><i class="fa-brands fa-instagram" style="color:#E1306C; width:20px;"></i> Instagram</button>
                            <button class="share-btn" onclick="shareSocial(\'tiktok\', \'com\', \''.$id_resena_str.'\')"><i class="fa-brands fa-tiktok" style="color:#ffffff; width:20px;"></i> TikTok</button>
                            <button class="share-btn" onclick="shareSocial(\'copy\', \'com\', \''.$id_resena_str.'\')"><i class="fa-solid fa-link" style="color:var(--text-muted); width:20px;"></i> Copiar Link</button>
                        </div>';
            
            if(isset($_SESSION['usuario_id'])){
                echo '  <button onclick="document.getElementById(\'resp-form-'.$id_resena_str.'\').style.display=\'flex\'" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:12px;">
                            <i class="fa-solid fa-reply"></i> Responder
                        </button>';
            }

            if($es_dueno || $es_admin){
                echo '  <button onclick="document.getElementById(\'edit-form-'.$id_resena_str.'\').style.display=\'flex\'" style="background:transparent; border:none; color:#f59e0b; cursor:pointer; font-size:12px;">
                            <i class="fa-solid fa-pen"></i> Editar
                        </button>';
                
                echo '  <form method="POST" action="eliminar_resena.php" style="display:inline; margin:0;" onsubmit="return confirm(\'Seguro que quieres eliminar esta resena?\');">
                            <input type="hidden" name="resena_id" value="'.$id_resena_str.'">
                            <input type="hidden" name="producto_id" value="'.$id.'">
                            <button type="submit" style="background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:12px;">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        </form>';
            }

            echo '  </div>';

            if($es_dueno || $es_admin){
                echo "
                <form id='edit-form-$id_resena_str' method='POST' action='editar_resena.php' style='display:none; margin-top:15px; gap:10px; flex-direction:column; background:rgba(0,0,0,0.2); padding:15px; border-radius:8px;'>
                    <input type='hidden' name='resena_id' value='$id_resena_str'>
                    <input type='hidden' name='producto_id' value='$id'>
                    
                    <label style='font-size:12px; color:var(--text-muted);'>Editar Calificacion:</label>
                    <select name='estrellas' class='input-cyber' style='padding:8px; border-radius:5px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);'>
                        <option value='5' ".($r->estrellas==5?'selected':'').">5 Estrellas (Excelente)</option>
                        <option value='4' ".($r->estrellas==4?'selected':'').">4 Estrellas (Bueno)</option>
                        <option value='3' ".($r->estrellas==3?'selected':'').">3 Estrellas (Regular)</option>
                        <option value='2' ".($r->estrellas==2?'selected':'').">2 Estrellas (Malo)</option>
                        <option value='1' ".($r->estrellas==1?'selected':'').">1 Estrella (Muy malo)</option>
                    </select>
                    
                    <label style='font-size:12px; color:var(--text-muted);'>Editar Comentario:</label>
                    <textarea name='comentario' required style='width:100%; box-sizing: border-box; padding:10px; border-radius:5px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);'>$comentario_textarea</textarea>
                    
                    <div style='display:flex; gap:10px;'>
                        <button type='submit' class='btn-neon' style='padding:8px 15px; font-size:12px;'>Guardar Cambios</button>
                        <button type='button' onclick=\"document.getElementById('edit-form-$id_resena_str').style.display='none'\" style='padding:8px 15px; font-size:12px; background:#444; color:white; border:none; border-radius:5px; cursor:pointer;'>Cancelar</button>
                    </div>
                </form>";
            }

            if(isset($r->respuestas)) {
                foreach($r->respuestas as $index => $resp) {
                    $resp_autor = htmlspecialchars($resp->usuario, ENT_QUOTES, 'UTF-8');
                    $resp_texto = htmlspecialchars($resp->respuesta, ENT_QUOTES, 'UTF-8');
                    $resp_fecha = $resp->fecha->toDateTime()->format('d/m/Y H:i');
                    
                    $es_dueno_resp = (isset($_SESSION['nombre']) && $_SESSION['nombre'] === $resp_autor);
                    $form_resp_id = $id_resena_str . '_' . $index;

                    echo "<div class='respuesta-box' style='position:relative;'>
                            <strong style='color: var(--primary);'><i class='fa-solid fa-comment-dots'></i> $resp_autor</strong> 
                            <span style='font-size:11px; color:var(--text-muted); margin-left:10px;'>$resp_fecha</span>
                            <p style='margin: 5px 0 10px 0; color:#cbd5e1;'>$resp_texto</p>";
                    
                    echo "<div style='display:flex; gap:10px;'>";
                    
                    if(isset($_SESSION['usuario_id'])) {
                        echo "<button type='button' onclick=\"prepararRespuesta('resp-form-$id_resena_str', '$resp_autor')\" style='background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:11px;'>
                                <i class='fa-solid fa-reply'></i> Responder
                              </button>";
                    }

                    if($es_dueno_resp || $es_admin) {
                        echo "  <button type='button' onclick=\"document.getElementById('edit-resp-$form_resp_id').style.display='flex'\" style='background:transparent; border:none; color:#f59e0b; cursor:pointer; font-size:11px;'>
                                    <i class='fa-solid fa-pen'></i> Editar
                                </button>
                                <form method='POST' action='eliminar_respuesta.php' style='margin:0;' onsubmit='return confirm(\"Seguro que quieres eliminar esta respuesta?\");'>
                                    <input type='hidden' name='resena_id' value='$id_resena_str'>
                                    <input type='hidden' name='producto_id' value='$id'>
                                    <input type='hidden' name='respuesta_texto' value='".$resp_texto."'>
                                    <input type='hidden' name='respuesta_autor' value='".$resp_autor."'>
                                    <button type='submit' style='background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:11px;'>
                                        <i class='fa-solid fa-trash'></i> Eliminar
                                    </button>
                                </form>";
                        
                        echo "</div>"; 

                        echo "<form id='edit-resp-$form_resp_id' method='POST' action='editar_respuesta.php' style='display:none; margin-top:10px; gap:10px; flex-direction:column; background:rgba(0,0,0,0.3); padding:10px; border-radius:8px;'>
                                <input type='hidden' name='resena_id' value='$id_resena_str'>
                                <input type='hidden' name='producto_id' value='$id'>
                                <input type='hidden' name='old_respuesta' value='".$resp_texto."'>
                                <input type='hidden' name='respuesta_autor' value='".$resp_autor."'>
                                <textarea name='nueva_respuesta' required style='width:100%; box-sizing: border-box; padding:8px; border-radius:5px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);'>$resp_texto</textarea>
                                <div style='display:flex; gap:10px;'>
                                    <button type='submit' class='btn-neon' style='padding:5px 10px; font-size:11px;'>Guardar</button>
                                    <button type='button' onclick=\"document.getElementById('edit-resp-$form_resp_id').style.display='none'\" style='padding:5px 10px; font-size:11px; background:#444; color:white; border:none; border-radius:5px; cursor:pointer;'>Cancelar</button>
                                </div>
                              </form>";
                    } else {
                        echo "</div>"; 
                    }

                    echo "</div>"; 
                }
            }

            if(isset($_SESSION['usuario_id'])){
                echo "
                <form id='resp-form-$id_resena_str' method='POST' action='guardar_respuesta.php' style='display:none; margin-top:15px; margin-left:30px; gap:10px;'>
                    <input type='hidden' name='resena_id' value='$id_resena_str'>
                    <input type='hidden' name='producto_id' value='$id'>
                    <input type='text' name='respuesta' required placeholder='Escribe una respuesta...' style='flex:1; padding:10px; border-radius:5px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);'>
                    <button type='submit' class='btn-neon' style='padding:10px 20px; font-size:12px;'>Enviar</button>
                </form>";
            }

            echo '</div>'; 
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error al cargar las opiniones.</p>";
    }

    if(!$hay_resenas){
        echo '
        <div class="producto-card" style="padding: 30px; text-align: center; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--search-border); color: var(--text-muted);">
            <i class="fa-regular fa-star-half-stroke fa-2x" style="margin-bottom: 10px;"></i><br>
            Aun no existen opiniones para este producto. Se el primero en comentar
        </div>';
    }
    ?>

    <?php if(isset($_SESSION['usuario_id'])): ?>
        <br><br>
        <h3>Deja tu rese&ntilde;a</h3>
        <form method="POST" action="guardar_resena.php" style="background: var(--card-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--search-border);">
            <input type="hidden" name="producto_id" value="<?php echo $id; ?>">
            <label style="display:block; margin-bottom: 8px;">Calificaci&oacute;n:</label>
            
            <select name="estrellas" class="input-cyber" style="padding: 10px; width: 200px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);">
                <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (Excelente)</option>
                <option value="4">&#9733;&#9733;&#9733;&#9733; (Bueno)</option>
                <option value="3">&#9733;&#9733;&#9733; (Regular)</option>
                <option value="2">&#9733;&#9733; (Malo)</option>
                <option value="1">&#9733; (Muy malo)</option>
            </select>
            
            <br><br>
            <label style="display:block; margin-bottom: 8px;">Tu comentario:</label>
            <textarea name="comentario" rows="5" class="input-cyber" style="width:100%; box-sizing: border-box; padding: 10px; background:var(--search-bg); color:var(--text-main); border:1px solid var(--search-border);" required placeholder="Escribe aqui..."></textarea>
            <br><br>
            <button type="submit" class="btn-neon" style="padding: 10px 25px;">Enviar</button>
        </form>
    <?php else: ?>
        <p style="margin-top: 30px; color: var(--text-muted); font-size: 14px; background: var(--search-bg); padding:15px; border-radius:8px;">
            <i class="fa-solid fa-circle-info"></i> Debes <a href="login.php" style="color: var(--primary); font-weight:bold;">iniciar sesi&oacute;n</a> para poder dejar una opini&oacute;n y calificar este producto.
        </p>
    <?php endif; ?>
</div>

<script>
function toggleShareMenu(menuId) {
    let menus = document.querySelectorAll('.share-menu');
    menus.forEach(m => { if(m.id !== menuId) m.style.display = 'none'; });
    
    let menu = document.getElementById(menuId);
    menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}

function shareSocial(network, type, idResena) {
    let baseLink = window.location.origin + window.location.pathname + "?id=<?php echo $id; ?>";
    let finalLink = (type === 'com') ? baseLink + "#resena-" + idResena : baseLink;
    let mensaje = (type === 'com') ? "Mira este comentario en TechStore: " : "Mira este producto en TechStore: ";

    let urlToOpen = "";

    switch(network) {
        case 'whatsapp':
            urlToOpen = 'https://wa.me/?text=' + encodeURIComponent(mensaje + finalLink);
            break;
        case 'instagram':
        case 'tiktok':
            let appName = (network === 'instagram') ? 'Instagram' : 'TikTok';
            navigator.clipboard.writeText(finalLink).then(() => {
                alert("Enlace copiado. Abre " + appName + " y pegalo para compartirlo con tus amigos.");
            });
            toggleShareMenu((type === 'com') ? 'menu-com-' + idResena : 'menu-pub');
            return; 
        case 'copy':
            navigator.clipboard.writeText(finalLink).then(() => {
                alert("Enlace copiado al portapapeles.");
            });
            toggleShareMenu((type === 'com') ? 'menu-com-' + idResena : 'menu-pub');
            return; 
    }
    
    window.open(urlToOpen, '_blank', 'width=600,height=400');
    toggleShareMenu((type === 'com') ? 'menu-com-' + idResena : 'menu-pub'); 
}

function prepararRespuesta(formId, autorAResponder) {
    let form = document.getElementById(formId);
    form.style.display = 'flex';
    let input = form.querySelector('input[name="respuesta"]');
    
    if (!input.value.includes('@' + autorAResponder)) {
        input.value = '@' + autorAResponder + ' ' + input.value;
    }
    input.focus();
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.share-menu') && !event.target.closest('button[onclick^="toggleShareMenu"]')) {
        document.querySelectorAll('.share-menu').forEach(m => m.style.display = 'none');
    }
});
</script>

</body>
</html>