<?php
session_start();
// PERMITIR ACCESO A ADMIN Y VENDEDORES
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['administrador', 'vendedor'])) {
    header("Location: index.php");
    exit();
}
include 'header.php';
include 'includes/db.php';

// --- LOGICA PARA ACTUALIZAR USUARIOS (SOLO ADMIN) ---
if(isset($_POST['actualizar_usuario']) && $_SESSION['rol'] === 'administrador') {
    $id_edit = (int)$_POST['id_usuario'];
    $nombre_edit = $conn->real_escape_string($_POST['nombre']);
    $email_edit = $conn->real_escape_string($_POST['email']);
    $rol_edit = $conn->real_escape_string($_POST['rol']);

    if($id_edit == $_SESSION['usuario_id'] && $rol_edit == 'cliente') {
        echo "<script>alert('⚠️ Error: No puedes quitarte los permisos de administrador a ti mismo.'); window.location.href='admin.php';</script>";
        exit();
    } else {
        $conn->query("UPDATE usuarios SET nombre='$nombre_edit', email='$email_edit', rol='$rol_edit' WHERE id=$id_edit");
        echo "<script>window.location.href='admin.php';</script>";
        exit();
    }
}

// --- LOGICA PARA ELIMINAR USUARIOS (SOLO ADMIN) ---
if(isset($_POST['eliminar_usuario']) && $_SESSION['rol'] === 'administrador') {
    $id_eliminar = (int)$_POST['id_usuario'];
    if($id_eliminar == $_SESSION['usuario_id']) {
        echo "<script>alert('⚠️ Error crítico: No puedes eliminar tu propia cuenta.'); window.location.href='admin.php';</script>";
        exit();
    } else {
        $conn->query("DELETE FROM carrito WHERE usuario_id = $id_eliminar");
        $conn->query("DELETE FROM compras WHERE usuario_id = $id_eliminar");
        $conn->query("DELETE FROM usuarios WHERE id = $id_eliminar");
        echo "<script>window.location.href='admin.php';</script>";
        exit();
    }
}

// --- LÓGICA PARA INVENTARIO (Vendedores y Admin) ---
if(isset($_POST['actualizar_stock'])) {
    $id_prod = (int)$_POST['id_prod'];
    $nuevo_stock = (int)$_POST['stock'];
    $conn->query("UPDATE productos SET stock = $nuevo_stock WHERE id = $id_prod");
    echo "<script>window.location.href='admin.php';</script>";
    exit();
}

if(isset($_POST['eliminar_producto'])) {
    $id_prod = (int)$_POST['id_prod'];
    $conn->query("DELETE FROM carrito WHERE producto_id = $id_prod");
    $conn->query("DELETE FROM productos WHERE id = $id_prod");
    echo "<script>window.location.href='admin.php';</script>";
    exit();
}

// --- LOGICA: SUBIR IMAGEN DESDE PC ---
if(isset($_POST['actualizar_imagen'])) {
    $id_prod = (int)$_POST['id_prod'];
    
    if(isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] == 0) {
        $filename = $_FILES['nueva_imagen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        
        if(in_array($ext, $permitidos)) {
            $nuevo_nombre = "prod_" . $id_prod . "_" . time() . "." . $ext;
            $destino = "img/productos/" . $nuevo_nombre;
            
            if(move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $destino)) {
                $conn->query("UPDATE productos SET imagen = '$nuevo_nombre' WHERE id = $id_prod");
                echo "<script>alert('🖼️ ¡Imagen subida y actualizada con éxito!'); window.location.href='admin.php';</script>";
                exit();
            } else {
                echo "<script>alert('❌ Error al guardar la imagen en el servidor. Revisa los permisos de la carpeta.'); window.location.href='admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('⚠️ Solo se permiten imágenes JPG, PNG o WEBP.'); window.location.href='admin.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('⚠️ No seleccionaste ningún archivo válido.'); window.location.href='admin.php';</script>";
        exit();
    }
}

// FILTROS
$filtro_sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id";
$condiciones = [];

if (isset($_GET['busqueda_admin']) && !empty($_GET['busqueda_admin'])) {
    $busqueda = $conn->real_escape_string($_GET['busqueda_admin']);
    $condiciones[] = "(p.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

if (isset($_GET['filtro_cat']) && $_GET['filtro_cat'] != '') {
    $cat_id = (int)$_GET['filtro_cat'];
    $condiciones[] = "p.categoria_id = $cat_id";
}

if (count($condiciones) > 0) {
    $filtro_sql .= " WHERE " . implode(" AND ", $condiciones);
}
$filtro_sql .= " ORDER BY p.id DESC";
?>

<div class="container" style="padding: 40px 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="margin: 0; color: var(--primary); text-transform: uppercase;">>_ PANEL DE CONTROL: <?php echo strtoupper($_SESSION['rol']); ?></h2>
        <div style="display: flex; gap: 15px;">
            <a href="gestionar_categorias.php" style="background: transparent; border: 2px solid #3b82f6; color: #3b82f6; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px;"><i class="fa-solid fa-tags"></i> CATEGORÍAS</a>
            <a href="nuevo_producto.php" style="background: transparent; border: 2px solid var(--primary); color: var(--primary); padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px;">+ NUEVO PRODUCTO</a>
        </div>
    </div>

    <?php if($_SESSION['rol'] === 'administrador'): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px;">
        <?php $q_ganancias = $conn->query("SELECT SUM(total) as ingresos FROM compras"); $ingresos = $q_ganancias->fetch_assoc()['ingresos'] ?? 0; ?>
        <div style="background: var(--card-bg); padding: 25px; border-radius: 12px; border-left: 5px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--search-border);">
            <p style="color: var(--text-muted); margin: 0; font-size: 14px; text-transform: uppercase; font-weight: bold;">Ingresos Totales</p>
            <h2 style="color: #10b981; margin: 10px 0 0 0; font-size: 28px;">S/ <?php echo number_format($ingresos, 2); ?></h2>
        </div>

        <?php $q_users = $conn->query("SELECT COUNT(*) as cant FROM usuarios"); $users = $q_users->fetch_assoc()['cant'] ?? 0; ?>
        <div style="background: var(--card-bg); padding: 25px; border-radius: 12px; border-left: 5px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--search-border);">
            <p style="color: var(--text-muted); margin: 0; font-size: 14px; text-transform: uppercase; font-weight: bold;">Usuarios Registrados</p>
            <h2 style="color: #3b82f6; margin: 10px 0 0 0; font-size: 28px;"><i class="fa-solid fa-users"></i> <?php echo $users; ?></h2>
        </div>

        <?php $q_prods = $conn->query("SELECT COUNT(*) as cant FROM productos"); $prods = $q_prods->fetch_assoc()['cant'] ?? 0; ?>
        <div style="background: var(--card-bg); padding: 25px; border-radius: 12px; border-left: 5px solid #f59e0b; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--search-border);">
            <p style="color: var(--text-muted); margin: 0; font-size: 14px; text-transform: uppercase; font-weight: bold;">Catálogo Activo</p>
            <h2 style="color: #f59e0b; margin: 10px 0 0 0; font-size: 28px;"><i class="fa-solid fa-box-open"></i> <?php echo $prods; ?></h2>
        </div>
    </div>

    <h2 style="color: var(--text-main); margin-bottom: 20px;"><i class="fa-solid fa-users-gear" style="color: #3b82f6;"></i> Gestión de Usuarios</h2>
    <div style="background: var(--card-bg); border-radius: 12px; padding: 20px; margin-bottom: 50px; overflow-x: auto; border: 1px solid var(--search-border);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="border-bottom: 2px solid var(--search-border);">
                <th style="padding: 15px; color: #3b82f6;">ID</th>
                <th style="padding: 15px; color: #3b82f6;">Nombre</th>
                <th style="padding: 15px; color: #3b82f6;">Email</th>
                <th style="padding: 15px; color: #3b82f6;">Rango</th>
                <th style="padding: 15px; color: #3b82f6; text-align: center;">Acción</th>
            </tr>
            <?php
            $usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id ASC");
            if($usuarios && $usuarios->num_rows > 0) {
                while($u = $usuarios->fetch_assoc()) {
                    echo '<tr style="border-bottom: 1px solid var(--search-border);">';
                    echo '<td style="padding: 15px; color: var(--text-main); font-weight: bold;">#'.$u['id'].'</td>';
                    echo '<td style="padding: 15px;"><input form="form_user_'.$u['id'].'" type="text" name="nombre" value="'.$u['nombre'].'" style="width: 100%; padding: 8px; background: var(--search-bg); border: 1px solid var(--search-border); color: var(--text-main); border-radius: 4px;"></td>';
                    echo '<td style="padding: 15px;"><input form="form_user_'.$u['id'].'" type="email" name="email" value="'.$u['email'].'" style="width: 100%; padding: 8px; background: var(--search-bg); border: 1px solid var(--search-border); color: var(--text-main); border-radius: 4px;"></td>';
                          
                    $sel_cli = ($u['rol'] == 'cliente') ? 'selected' : '';
                    $sel_ven = ($u['rol'] == 'vendedor') ? 'selected' : '';
                    $sel_adm = ($u['rol'] == 'administrador') ? 'selected' : '';
                    
                    // AÑADIMOS LA OPCIÓN DEL VENDEDOR EN EL SELECT
                    echo '<td style="padding: 15px;">
                            <select form="form_user_'.$u['id'].'" name="rol" style="padding: 8px; background: var(--search-bg); border: 1px solid var(--search-border); color: var(--text-main); border-radius: 4px;">
                                <option value="cliente" style="background: var(--bg-dark); color: var(--text-main);" '.$sel_cli.'>Cliente</option>
                                <option value="vendedor" style="background: var(--bg-dark); color: var(--text-main);" '.$sel_ven.'>Vendedor</option>
                                <option value="administrador" style="background: var(--bg-dark); color: var(--text-main);" '.$sel_adm.'>Administrador</option>
                            </select>
                          </td>';
                          
                    echo '<td style="padding: 15px; text-align: center;">
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <form id="form_user_'.$u['id'].'" method="POST" style="margin: 0;">
                                    <input type="hidden" name="id_usuario" value="'.$u['id'].'">
                                    <button type="submit" name="actualizar_usuario" style="background: #3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold;" title="Guardar cambios"><i class="fa-solid fa-floppy-disk"></i></button>
                                </form>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm(\'¿Estás seguro de eliminar este usuario?\');">
                                    <input type="hidden" name="id_usuario" value="'.$u['id'].'">
                                    <button type="submit" name="eliminar_usuario" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold;" title="Eliminar usuario"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                          </td>';
                    echo '</tr>';
                }
            }
            ?>
        </table>
    </div>

    <h2 style="color: var(--text-main); margin-bottom: 20px;"><i class="fa-solid fa-money-bill-trend-up" style="color: var(--primary);"></i> Registro de Ventas</h2>
    <div style="background: var(--card-bg); border-radius: 12px; padding: 20px; margin-bottom: 50px; overflow-x: auto; border: 1px solid var(--search-border);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="border-bottom: 2px solid var(--search-border);">
                <th style="padding: 15px; color: var(--text-muted);">Pedido ID</th>
                <th style="padding: 15px; color: var(--text-muted);">Cliente (Email)</th>
                <th style="padding: 15px; color: var(--text-muted);">Fecha</th>
                <th style="padding: 15px; color: var(--text-muted);">Dirección Envío</th>
                <th style="padding: 15px; text-align: right; color: var(--text-muted);">Monto</th>
            </tr>
            <?php
            $sql_ventas = "SELECT c.id, c.fecha, c.direccion, c.total, u.email FROM compras c JOIN usuarios u ON c.usuario_id = u.id ORDER BY c.fecha DESC LIMIT 5";
            $ventas = $conn->query($sql_ventas);

            if($ventas && $ventas->num_rows > 0) {
                while($v = $ventas->fetch_assoc()) {
                    echo '<tr style="border-bottom: 1px solid var(--search-border);">';
                    echo '<td style="padding: 15px; font-weight: bold; color: var(--primary);">#000' . $v['id'] . '</td>';
                    echo '<td style="padding: 15px; color: var(--text-main);">' . $v['email'] . '</td>';
                    echo '<td style="padding: 15px; font-size: 13px; color: var(--text-main);">' . $v['fecha'] . '</td>';
                    echo '<td style="padding: 15px; font-size: 13px; color: var(--text-muted);">' . $v['direccion'] . '</td>';
                    echo '<td style="padding: 15px; text-align: right; font-weight: bold; color: var(--primary);">S/ ' . $v['total'] . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">Aún no hay ventas registradas.</td></tr>';
            }
            ?>
        </table>
    </div>
    <?php endif; // Fin restricciones del administrador ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h2 style="color: var(--text-main); margin: 0;"><i class="fa-solid fa-boxes-stacked" style="color: var(--primary);"></i> Gestión de Inventario</h2>
        
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin: 0;">
            <input type="text" name="busqueda_admin" placeholder="Buscar producto..." value="<?php echo isset($_GET['busqueda_admin']) ? $_GET['busqueda_admin'] : ''; ?>" style="padding: 8px 15px; border-radius: 5px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none;">
            
            <select name="filtro_cat" style="background: var(--search-bg); color: var(--text-main); padding: 8px 15px; border: 1px solid var(--search-border); border-radius: 5px; outline: none;">
                <option value="" style="background: var(--bg-dark); color: var(--text-main);">Todas las Categorías</option>
                <?php
                $cats = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                while($c = $cats->fetch_assoc()) {
                    $selected = (isset($_GET['filtro_cat']) && $_GET['filtro_cat'] == $c['id']) ? 'selected' : '';
                    echo "<option value='".$c['id']."' style='background: var(--bg-dark); color: var(--text-main);' $selected>".$c['nombre']."</option>";
                }
                ?>
            </select>
            <button type="submit" style="background: var(--primary); color: black; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;"><i class="fa-solid fa-magnifying-glass"></i></button>
            <a href="admin.php" style="background: #ef4444; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 14px;"><i class="fa-solid fa-xmark"></i></a>
        </form>
    </div>

    <div style="background: var(--card-bg); border-radius: 12px; padding: 20px; overflow-x: auto; border: 1px solid var(--search-border);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="border-bottom: 2px solid var(--search-border);">
                <th style="padding: 15px; color: var(--primary);">Producto</th>
                <th style="padding: 15px; color: var(--primary);">Categoría</th>
                <th style="padding: 15px; color: var(--primary);">Stock</th>
                <th style="padding: 15px; color: var(--primary);">Imagen</th>
                <th style="padding: 15px; color: var(--primary); text-align: center;">Acción</th>
            </tr>
            <?php
            $prods = $conn->query($filtro_sql);
            if($prods && $prods->num_rows > 0) {
                while($p = $prods->fetch_assoc()) {
                    $cat_nombre = $p['categoria_nombre'] ? $p['categoria_nombre'] : 'Sin categoría';
                    echo '<tr style="border-bottom: 1px solid var(--search-border);">';
                    echo '<td style="padding: 15px; color: var(--text-main); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="'.$p['nombre'].'">' . $p['nombre'] . '</td>';
                    echo '<td style="padding: 15px; color: var(--text-muted); font-size: 13px;">' . $cat_nombre . '</td>';
                    
                    // Stock Editable
                    echo '<td style="padding: 15px;">
                            <form method="POST" style="display: flex; align-items: center; gap: 5px; margin: 0;">
                                <input type="hidden" name="id_prod" value="'.$p['id'].'">
                                <input type="number" name="stock" value="'.$p['stock'].'" min="0" style="width: 60px; padding: 5px; background: var(--search-bg); border: 1px solid var(--search-border); color: var(--text-main); border-radius: 4px;">
                                <button type="submit" name="actualizar_stock" style="background: #e2e8f0; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; color: black;" title="Guardar Stock"><i class="fa-solid fa-floppy-disk"></i></button>
                            </form>
                          </td>';
                          
                    // IMPORTADOR DE IMAGEN
                    echo '<td style="padding: 15px;">
                            <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 5px; margin: 0;">
                                <input type="hidden" name="id_prod" value="'.$p['id'].'">
                                <span style="font-size: 11px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;" title="'.$p['imagen'].'">Actual: '.$p['imagen'].'</span>
                                <div style="display: flex; gap: 5px;">
                                    <input type="file" name="nueva_imagen" accept="image/jpeg, image/png, image/webp" required style="width: 130px; font-size: 10px; padding: 4px; background: var(--search-bg); border: 1px solid var(--search-border); color: var(--text-main); border-radius: 4px; cursor: pointer;">
                                    <button type="submit" name="actualizar_imagen" style="background: var(--primary); color: black; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-weight: bold;" title="Subir archivo"><i class="fa-solid fa-cloud-arrow-up"></i></button>
                                </div>
                            </form>
                          </td>';
                          
                    // Botón Eliminar
                    echo '<td style="padding: 15px; text-align: center;">
                            <form method="POST" style="margin: 0;" onsubmit="return confirm(\'¿Eliminar producto DEFINITIVAMENTE?\');">
                                <input type="hidden" name="id_prod" value="'.$p['id'].'">
                                <button type="submit" name="eliminar_producto" style="background: transparent; color: #ef4444; border: none; cursor: pointer; font-size: 16px;"><i class="fa-solid fa-trash"></i></button>
                            </form>
                          </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">No se encontraron productos.</td></tr>';
            }
            ?>
        </table>
    </div>

</div>
</body>
</html>