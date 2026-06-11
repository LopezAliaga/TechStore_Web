<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'dark';
}

if (isset($_POST['toggle_theme'])) {
    $_SESSION['theme'] = ($_SESSION['theme'] === 'dark') ? 'light' : 'dark';
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/x-icon" href="img/ts.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore | Premium Hardware</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, h1, h2, h3, h4, h5, h6, p, a, span, div, input, button, label, th, td, summary { 
            font-family: 'Poppins', sans-serif !important; 
        }
        i.fa-solid, i.fa-regular, i.fa-brands, i.fas, i.far, i.fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        :root { 
            --primary: #00ff88; 
            --neon-glow: 0 0 10px rgba(0, 255, 136, 0.8), 0 0 20px rgba(0, 255, 136, 0.4);
            --bg-dark: #05070a; 
            --card-bg: rgba(22, 27, 34, 0.9);
            --text-muted: #8b949e;
            --text-main: #ffffff;
            --header-bg: rgba(0, 0, 0, 0.85);
            --search-bg: rgba(255,255,255,0.05);
            --search-border: #333;
        }

        <?php if($_SESSION['theme'] == 'light'): ?>
        :root {
            --primary: #00b35f;
            --neon-glow: 0 2px 10px rgba(0, 179, 95, 0.2);
            --bg-dark: #f0f2f5;
            --card-bg: #ffffff;
            --text-muted: #555555;
            --text-main: #1a1a1a;
            --header-bg: rgba(255, 255, 255, 0.95);
            --search-bg: #e9ecef;
            --search-border: #ccc;
        }
        body { background-image: none !important; }
        .logo { color: var(--text-main) !important; }
        .producto-card { box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-color: #ddd !important; }
        .neon-text { text-shadow: none !important; }
        <?php endif; ?>

        body { 
            margin: 0; background: var(--bg-dark); color: var(--text-main);
            <?php if($_SESSION['theme'] == 'dark'): ?>
            background-image: radial-gradient(circle at top, #111827 0%, #05070a 100%);
            <?php endif; ?>
            background-attachment: fixed; transition: background 0.3s ease, color 0.3s ease;
        }

        header { 
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 5%; background: var(--header-bg); backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(0, 255, 136, 0.2); position: sticky; top: 0; z-index: 1000;
            transition: 0.3s ease;
        }

        .logo { font-size: 26px; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: 1px; transition: 0.3s; }
        .logo span { color: var(--primary); text-shadow: var(--neon-glow); }

        .buscador { display: flex; background: var(--search-bg); border-radius: 50px; border: 1px solid var(--search-border); padding: 5px 15px; transition: 0.3s; }
        .buscador:focus-within { border-color: var(--primary); box-shadow: var(--neon-glow); }
        .buscador input { background: transparent; border: none; color: var(--text-main); padding: 8px; outline: none; width: 250px; transition: 0.3s; }
        .buscador button { background: transparent; border: none; color: var(--primary); cursor: pointer; }

        nav { display: flex; align-items: center; gap: 20px; }
        nav a { color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        nav a:hover { color: var(--primary); text-shadow: var(--neon-glow); }

        .neon-text { color: var(--primary); text-shadow: var(--neon-glow); font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        
        .btn-neon { 
            background: transparent; color: var(--primary); border: 2px solid var(--primary);
            padding: 8px 20px; border-radius: 50px; font-weight: bold; cursor: pointer; text-decoration: none; 
            box-shadow: var(--neon-glow); transition: 0.3s; display: inline-block; text-transform: uppercase; font-size: 12px;
        }
        .btn-neon:hover { background: var(--primary); color: <?php echo ($_SESSION['theme'] == 'dark') ? '#000' : '#fff'; ?>; box-shadow: 0 0 30px var(--primary); transform: translateY(-2px); }

        .theme-toggle-btn { background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); transition: transform 0.3s ease, color 0.3s ease; outline: none; }
        .theme-toggle-btn:hover { transform: scale(1.2) rotate(15deg); color: var(--primary); }

        /* 🔥 MAGIA RESPONSIVA PARA CELULARES 🔥 */
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 15px; padding: 15px; }
            .buscador { width: 100%; box-sizing: border-box; }
            .buscador input { width: 100%; }
            nav { flex-wrap: wrap; justify-content: center; gap: 15px; width: 100%; }
            .theme-toggle-btn { position: absolute; top: 20px; right: 20px; }
        }

        /* 🔥 ESTILOS DEL CHATBOT AÑADIDOS AQUÍ 🔥 */
        #chat-container { position:fixed; bottom:20px; right:20px; z-index:9999; }
        #chat-window { display:none; width:300px; height:400px; background:var(--card-bg); border:1px solid var(--primary); border-radius:10px; box-shadow:0 0 20px rgba(0,255,136,0.2); overflow:hidden; }
        .chat-btn { background:var(--primary); color:black; border:none; padding:15px; border-radius:50%; cursor:pointer; box-shadow:var(--neon-glow); font-size:20px; }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">TECH<span>STORE</span></a>

        <form class="buscador" action="productos.php" method="GET">
            <input type="text" name="buscar" placeholder="Buscar hardware...">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <nav>
            <form method="POST" style="margin: 0;">
                <button type="submit" name="toggle_theme" class="theme-toggle-btn" title="Cambiar Tema">
                    <?php echo ($_SESSION['theme'] === 'dark') ? '☀️' : '🌙'; ?>
                </button>
            </form>

            <a href="productos.php">TIENDA</a>
            <a href="feed.php" style="color: var(--text-main); text-decoration: none; font-weight: bold; margin-right: 20px;">
                <i class="fa-solid fa-camera-retro" style="color: var(--primary);"></i> Comunidad
            </a>
            <a href="carrito.php"><i class="fa-solid fa-cart-shopping"></i></a>
            <a href="mis_compras.php" style="margin-left: 10px; font-weight: bold;">📦 Mis Compras</a>
            
            <?php if(isset($_SESSION['usuario_id'])): ?>
                
                <?php if(isset($_SESSION['rol']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'vendedor')): ?>
                    <a href="admin.php" title="Panel de Control" style="color: <?php echo ($_SESSION['rol'] === 'administrador') ? '#58a6ff' : 'var(--primary)'; ?>;">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </a>
                <?php endif; ?>

                <a href="perfil.php" class="neon-text">
                    <i class="fa-solid fa-circle-user"></i> <?php echo strtoupper($_SESSION['nombre']); ?>
                </a>
                
                <a href="logout.php" style="color: #ff4a4a;" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-neon">INGRESAR</a>
            <?php endif; ?>
        </nav>
    </header>

    <div id="chat-container">
        <button class="chat-btn" onclick="toggleChat()">💬</button>
        <div id="chat-window">
            <iframe src="chat.php" style="width:100%; height:100%; border:none;"></iframe>
        </div>
    </div>
    <script>
    function toggleChat() {
        var win = document.getElementById('chat-window');
        win.style.display = (win.style.display === 'none') ? 'block' : 'none';
    }
    </script>