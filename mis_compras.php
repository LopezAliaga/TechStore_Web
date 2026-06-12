<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'header.php';
include 'includes/db.php';
$user_id = $_SESSION['usuario_id'];
?>
    <div class="container" style="padding: 40px 15px;">
        <h2 style="border-left: 5px solid var(--primary); padding-left: 15px; margin-bottom: 30px; color: var(--text-main); text-transform: uppercase;">Historial de Compras y Tracking</h2>
        
        <div>
            <?php
            $sql = "SELECT * FROM compras WHERE usuario_id = $user_id ORDER BY fecha DESC";
            $resultado = $conn->query($sql);
            
            if($resultado->num_rows > 0) {
                while($compra = $resultado->fetch_assoc()) {
                    
                    // Colores según el estado del tracking
                    $estado = $compra['estado_tracking'] ?? 'Procesando';
                    $color_estado = '#f59e0b'; // Amarillo por defecto
                    if($estado == 'Enviado') $color_estado = '#3b82f6'; // Azul
                    if($estado == 'Entregado') $color_estado = '#10b981'; // Verde

                    echo '<div style="background: var(--card-bg); padding: 25px; border-radius: 12px; margin-bottom: 25px; border-left: 5px solid var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.2); border: 1px solid var(--search-border);">';
                    
                    // Cabecera de la tarjeta
                    echo '<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--search-border); padding-bottom: 15px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">';
                    echo '<h4 style="color: var(--text-main); margin: 0; font-size: 18px;"><i class="fa-solid fa-box-open" style="color: var(--primary);"></i> Pedido #000'.$compra['id'].'</h4>';
                    
                    // BARRA DE TRACKING
                    echo '<div style="background: '.$color_estado.'; color: black; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: bold;"><i class="fa-solid fa-location-dot"></i> Tracking: '.strtoupper($estado).'</div>';
                    
                    echo '<span style="color: var(--text-muted); font-size: 14px;"><i class="fa-regular fa-clock"></i> '.$compra['fecha'].'</span>';
                    echo '</div>';
                    
                    // Cuerpo
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">';
                    
                    echo '<div>';
                    echo '<p style="font-size: 14px; margin: 8px 0; color: var(--text-muted);"><strong>Enviado a:</strong> <span style="color: var(--text-main);">'.$compra['direccion'].'</span></p>';
                    echo '<p style="font-size: 14px; margin: 8px 0; color: var(--text-muted);"><strong>Tarjeta:</strong> <span style="color: var(--text-main);">'.$compra['tarjeta_oculta'].'</span></p>';
                    $envio = $compra['costo_envio'] ?? '0.00';
                    echo '<p style="font-size: 14px; margin: 8px 0; color: var(--text-muted);"><strong>Costo Envio Logistico:</strong> <span style="color: var(--primary);">S/ '.$envio.'</span></p>';
                    echo '</div>';
                    
                    echo '<div style="background: var(--search-bg); padding: 15px; border-radius: 8px;">';
                    echo '<p style="font-size: 13px; margin: 0 0 10px 0; color: var(--primary); font-weight: bold; text-transform: uppercase;">Articulos Comprados:</p>';
                    $resumen = !empty($compra['productos_resumen']) ? $compra['productos_resumen'] : '<span style="color: #ef4444;">Sin detalles (Compra antigua)</span>';
                    echo '<div style="color: var(--text-main);">'.$resumen.'</div>';
                    echo '</div>';
                    
                    echo '</div>'; 
                    
                    // Total abajo
                    echo '<h3 style="color: var(--primary); margin-bottom: 0; margin-top: 20px; text-align: right; border-top: 1px solid var(--search-border); padding-top: 15px;">Total Final: S/ '.$compra['total'].'</h3>';
                    echo '</div>';
                }
            } else {
                echo '<div style="background: var(--card-bg); padding: 30px; border-radius: 10px; text-align: center; border: 1px solid var(--search-border);">';
                echo '<p style="font-size: 18px; color: var(--text-muted);">Aún no has realizado ninguna compra.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>