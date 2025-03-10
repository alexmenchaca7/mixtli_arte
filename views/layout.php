<?php
    if(!isset($inicio)) {
        $inicio = false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Para Ti - MixtliArte</title>
    <link rel="stylesheet" href="../build/css/app.css">
</head>
<body class="layout">
    <div class="layout__header">
        <header class="header <?php echo $inicio ? 'inicio' : ''; ?>">
            <div class="contenedor">
                <nav class="barra">
                    <a class="logo" href="<?php echo $inicio ? '/' : '/marketplace.html'; ?>">
                        <?php if(!$inicio): ?>
                            <img src="../build/img/logo.png" alt="Logo de Mixtli Arte">
                        <?php endif; ?>
                        <h2>MixtliArte</h2>
                    </a>
                    
                    <?php if(!$inicio): ?>
                        <div class="busqueda">
                            <input type="text" placeholder="¿Que es lo que buscas hoy?" aria-label="Buscar productos">
                            <button type="submit">
                                <img src="../build/img/icon_search.svg" alt="Icono de busqueda">
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="enlaces">
                        <?php if($inicio): ?>
                            <a href="/">Inicio</a>
                            <a href="/nosotros">Nosotros</a>
                            <a href="/contacto">Contacto</a>
            
                            <a href="login.html">
                                <img class="icono_perfil" src="../build/img/icon_user.svg" alt="Icono de perfil">
                            </a>
                        <?php else: ?>
                            <!-- Botón de Categorías que activa el modal -->
                            <button id="categorias-btn" class="categorias-btn">Categorías</button>
                            
                            <a href="/marketplace.html">Para Ti</a>
                            <a href="favoritos.html">
                                <img class="icono_favorito" src="../build/img/icon_heart.svg" alt="Icono de favoritos">
                            </a>
            
                            <a href="perfil.html">
                                <img class="icono_perfil" src="../build/img/icon_user.svg" alt="Icono de perfil">
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>
                
                <?php if(!$inicio): ?>
                    <!-- Modal de Categorías -->
                    <div id="categorias-modal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Categorías</h2>
                            <ul>
                                <li><a href="categoria1.html">Pinturas</a></li>
                                <li><a href="categoria2.html">Esculturas</a></li>
                                <li><a href="categoria3.html">Textiles</a></li>
                                <li><a href="categoria4.html">Cerámica</a></li>
                                <li><a href="categoria5.html">Joyería</a></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(!$inicio): ?>
                <div class="hero">
                    <section class="contenido-header contenedor">
                        <h1>Para ti</h1>
                        <p>Descubre las mejores recomendaciones en artesanías y arte mexicano, seleccionadas según tus preferencias</p>
                    </section>
                </div>
            <?php endif; ?>
        </header>
    </div>

    <div class="layout__contenido">
        <?php echo $contenido; ?>
    </div>

    <div class="layout__foter">
        <footer class="footer seccion">
            <div class="contenedor footer-contenedor">
                <!-- Sección del logo y derechos de autor -->
                <div class="footer-logo">
                    <h2>MixtliArte</h2>
                    <p>Copyright © 2025 MixtliArte</p>
                    <p>Todos los derechos reservados</p>
    
                    <div class="footer-social">
                        <a href="#"><img src="../build/img/icon_instagram.svg" alt="Instagram"></a>
                        <a href="#"><img src="../build/img/icon_facebook.svg" alt="Facebook"></a>
                        <a href="#"><img src="../build/img/icon_youtube.svg" alt="YouTube"></a>
                    </div>
                </div>
    
                <!-- Sección de enlaces -->
                <div class="footer-links">
                    <h3>Enlaces</h3>
    
                    <?php if($inicio): ?>
                        <a href="/">Inicio</a>
                        <a href="/nosotros.html">Nosotros</a>
                        <a href="/contacto.html">Contacto</a>
                        <a href="/login.html">Iniciar sesión</a>
                    <?php else: ?>
                        <a href="#">Categorias</a>
                        <a href="#">Para Ti</a>
                        <a href="#">Lista de Deseos</a>
                        <a href="#">Perfil</a>
                    <?php endif; ?>
                    
                </div>
    
                <div class="footer-links">
                    <h3>Soporte</h3>
                    <a href="#">Centro de ayuda</a>
                    <a href="#">Términos de servicio</a>
                    <a href="#">Legal</a>
                    <a href="#">Política de privacidad</a>
                </div>
        
                <!-- Sección de suscripción -->
                <div class="footer-subscribe">
                    <h3>Mantente actualizado</h3>
                    <form action="subscribe.php" method="POST">
                        <input type="email" name="email" placeholder="Tu correo electrónico" required>
                        <button type="submit">
                            <img src="../build/img/icon_send.svg" alt="Enviar">
                        </button>
                    </form>
                </div>
            </div>
        </footer>
    </div>

    <script src="../build/js/app.js"></script>
</body>
</html>