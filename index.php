<?php 
include 'header.php'; 
include 'includes/db.php'; 
?>

<div style="
    position: relative; 
    height: 500px; 
    background-image: linear-gradient(rgba(0,0,0,0.6), var(--bg-dark)), url('img/banners/hero-bg.jpg'); 
    background-size: cover; 
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    border-bottom: 1px solid rgba(0, 255, 136, 0.2);
">
    <div style="padding: 20px; backdrop-filter: blur(2px);">
        <h1 style="font-size: 4rem; font-weight: 800; margin: 0; text-transform: uppercase; line-height: 1;">
            THE FUTURE OF <span class="neon-text">GAMING</span>
        </h1>
        <p style="color: #e6edf3; font-size: 1.3rem; margin: 20px 0 40px 0; font-weight: 300; letter-spacing: 1px;">
            Hardware de última generación para mentes exigentes.
        </p>
        
        <a href="productos.php" class="btn-neon" style="padding: 18px 50px; font-size: 16px; letter-spacing: 2px;">
            VER OFERTAS <i class="fa-solid fa-bolt" style="margin-left: 10px;"></i>
        </a>
    </div>
</div>

<div class="container">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 40px;">
        <div style="width: 50px; height: 2px; background: var(--primary); box-shadow: var(--neon-glow);"></div>
        <h2 style="margin: 0; text-transform: uppercase; letter-spacing: 3px;">Top Productos</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
        <?php
// ACTUALIZACIÓN: Muestra 8 productos en orden aleatorio (RAND())
$res = $conn->query("SELECT * FROM productos ORDER BY RAND() LIMIT 15");
while($p = $res->fetch_assoc()) {
    echo '<div class="producto-card" style="text-align: center; position: relative; overflow: hidden; padding: 15px;">';
    
    // Badge de oferta
    echo '<div style="position: absolute; top: 10px; right: 10px; background: var(--primary); color: #000; font-size: 10px; font-weight: 900; padding: 3px 8px; border-radius: 5px; z-index: 2;">DESTACADO</div>';
    
    // Lógica de Imagen
    $imagen_db = $p['imagen'];
    if (filter_var($imagen_db, FILTER_VALIDATE_URL)) {
        $ruta_index = $imagen_db;
    } else {
        $ruta_index = "img/productos/" . trim($imagen_db);
    }

    // ACTUALIZACIÓN: Enlace al detalle del producto envolviendo la imagen y el título
    echo '<a href="detalleProducto.php?id='.$p['id'].'" style="text-decoration: none; color: inherit; display: block;">';
    
    echo '<img src="' . $ruta_index . '" 
               style="height: 180px; width: 100%; object-fit: contain; margin: 10px 0; transition: transform 0.3s;" 
               onerror="this.src=\'https://via.placeholder.com/300?text=Hardware\'"
               onmouseover="this.style.transform=\'scale(1.05)\'" 
               onmouseout="this.style.transform=\'scale(1)\'">';

    echo '<h3 style="margin: 10px 0; font-size: 16px; min-height: 40px; color: var(--text-main);">'.$p['nombre'].'</h3>';
    echo '</a>'; // Cierre del enlace

    echo '<h2 class="neon-text" style="font-size: 24px; margin-bottom: 20px;">S/ '.$p['precio'].'</h2>';
    
    echo '<form method="POST" action="productos.php">';
    echo '<input type="hidden" name="producto_id" value="'.$p['id'].'">';
    echo '<button type="submit" name="agregar_carrito" class="btn-neon" style="width: 100%; font-size: 12px; padding: 12px;">';
    echo '<i class="fa-solid fa-cart-plus"></i> AÑADIR AL SETUP';
    echo '</button>';
    echo '</form>';
    echo '</div>';
}
?>
    </div>
</div>

<footer style="margin-top: 100px; padding: 50px; text-align: center; border-top: 1px solid #1a1f26; background: rgba(0,0,0,0.3);">
    <p style="color: #444; font-size: 12px; letter-spacing: 2px;">TECHSTORE &copy; 2026 | SISTEMAS OPERATIVOS PRO</p>
</footer>

</body>
</html>