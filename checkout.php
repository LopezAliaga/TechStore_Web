<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'header.php';
?>
<div class="container" style="padding: 40px 15px;">
    <h2 style="border-left: 5px solid var(--primary); padding-left: 15px; text-transform: uppercase; color: var(--text-main);">Checkout y Logística</h2>
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px;">
        
        <div style="flex: 2; background: var(--card-bg); padding: 40px; border-radius: 12px; border: 1px solid var(--search-border); box-shadow: 0 4px 15px rgba(0,0,0,0.05); min-width: 300px;">
            <h3 style="margin-top: 0; color: var(--primary);"><i class="fa-solid fa-credit-card"></i> Detalles de Facturación y Envío</h3>
            <hr style="border-color: var(--search-border); margin-bottom: 25px;">
            
            <form action="procesar_compra.php" method="POST">
                
                <div style="margin-bottom: 25px;">
                    <label style="color: var(--text-muted); font-size: 14px; font-weight: bold; display: block; margin-bottom: 8px;">Dirección de Envío Completa:</label>
                    <input type="text" name="direccion" required placeholder="Ej: Av. Las Palmeras 123, Los Olivos" style="width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none; font-family: 'Poppins', sans-serif;">
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="color: var(--text-muted); font-size: 14px; font-weight: bold; display: block; margin-bottom: 8px;">Nombre en la Tarjeta:</label>
                    <input type="text" required pattern="[A-Za-z áéíóúÁÉÍÓÚñÑ]+" title="Solo se permiten letras" placeholder="Ej: Adrian Ramos" style="width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none; font-family: 'Poppins', sans-serif;">
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="color: var(--text-muted); font-size: 14px; font-weight: bold; display: block; margin-bottom: 8px;">Número de Tarjeta (16 dígitos):</label>
                    <input type="text" name="tarjeta" placeholder="0000 0000 0000 0000" maxlength="16" minlength="16" pattern="\d{16}" title="Debe contener exactamente 16 números" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none; font-family: 'Poppins', sans-serif; letter-spacing: 2px;">
                </div>
                
                <div style="display: flex; gap: 20px; margin-bottom: 35px;">
                    <div style="flex: 1;">
                        <label style="color: var(--text-muted); font-size: 14px; font-weight: bold; display: block; margin-bottom: 8px;">Vencimiento (MM/YY):</label>
                        <input type="text" placeholder="12/28" maxlength="5" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" title="Formato MM/YY, ej: 12/28" required oninput="this.value = this.value.replace(/[^0-9\/]/g, '');" style="width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none; font-family: 'Poppins', sans-serif;">
                    </div>
                    <div style="flex: 1;">
                        <label style="color: var(--text-muted); font-size: 14px; font-weight: bold; display: block; margin-bottom: 8px;">CVV:</label>
                        <input type="password" placeholder="123" maxlength="3" minlength="3" pattern="\d{3}" title="Código de 3 números" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); outline: none; font-family: 'Poppins', sans-serif; letter-spacing: 3px;">
                    </div>
                </div>
                
                <button type="submit" style="width: 100%; background: var(--primary); color: black; padding: 15px; font-size: 16px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-family: 'Poppins', sans-serif;">Confirmar Pedido</button>
            </form>
        </div>

        <div style="flex: 1; background: var(--bg-dark); padding: 30px; border-radius: 12px; border: 1px solid var(--primary); min-width: 300px; height: fit-content;">
            <h3 style="margin-top: 0; color: var(--text-main);">Resumen de tu Orden</h3>
            <hr style="border-color: var(--search-border); margin-bottom: 20px;">
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                <span>Costo de Envío:</span>
                <span style="color: var(--text-main);">S/ 15.00</span>
            </div>
            
            <?php if(isset($_SESSION['cupon_descuento'])): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #10b981;">
                <span>Descuento Promocional:</span>
                <span>- <?php echo $_SESSION['cupon_descuento']; ?>%</span>
            </div>
            <?php endif; ?>
            
            <hr style="border-color: var(--search-border); margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: bold; color: var(--text-main); font-size: 18px;">Total a Cobrar:</span>
                <span style="color: var(--primary); font-size: 24px; font-weight: bold;">(Calculado al pagar)</span>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; text-align: center;">
                <i class="fa-solid fa-truck-fast" style="font-size: 24px; color: var(--primary); margin-bottom: 10px;"></i>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0;">Tu pedido incluirá un código de seguimiento (Tracking) una vez procesado el pago.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>