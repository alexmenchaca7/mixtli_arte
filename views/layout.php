<?php
    use Model\Categoria;
    use Model\Usuario;

    if(!isset($inicio)) {
        $inicio = false;
    }

    // Obtener todas las categorías con sus subcategorías
    $categorias = Categoria::all();

    // Obtener información del usuario logueado
    if (isset($_SESSION['id'])) {
        $usuario = Usuario::find($_SESSION['id']);
        if ($usuario && !empty($usuario->imagen)) {
            $usuarioImagen = "$usuario->imagen";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MixtliArte | <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="/build/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>
<body class="layout">
    <div class="layout__header">
        <header class="header <?php echo $inicio ? 'inicio' : ''; ?>">
            <div class="contenedor">
                <nav class="barra">
                    <a class="logo" href="<?php echo $inicio ? '/' : '/marketplace'; ?>">
                        <?php if(!$inicio): ?>
                            <img src="/build/img/logo.png" alt="Logo de Mixtli Arte">
                        <?php endif; ?>
                        <h2>MixtliArte</h2>
                    </a>
                    
                    <?php if(!$inicio): ?>
                        <div class="busqueda">
                            <form action="/marketplace" method="GET">
                                <input 
                                    type="text" 
                                    name="q" 
                                    id="busqueda" 
                                    placeholder="¿Qué es lo que buscas hoy?" 
                                    aria-label="Buscar productos" 
                                    value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                    autocomplete="off"
                                >
                                <button type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </form>
                            <ul id="sugerencias" class="sugerencias"></ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($inicio): ?>
                        <div class="enlaces--inicio">
                            <a href="/">Inicio</a>
                            <a href="/nosotros">Nosotros</a>
                            <a href="/contacto">Contacto</a>
                            <a href="/login">Iniciar Sesión</a>
                        </div>      
                    <?php else: ?>
                        <div class="enlaces">
                            <!-- Botón de Categorías que activa el modal -->
                            <button id="categorias-btn" class="categorias-btn">Categorías</button>
                            
                            <a href="/marketplace">Para Ti</a>

                            <a href="/mensajes">
                                <i class="fa-regular fa-comment"></i>
                            </a>

                            <a href="/favoritos">
                                <i class="fa-regular fa-heart"></i>
                            </a>

                            <a href="/notificaciones">
                                <i class="fa-regular fa-bell"></i>
                            </a>
            
                            <a href="/perfil">
                                <?php if(isset($usuarioImagen)): ?>
                                    <img class="icono_perfil" src="/img/usuarios/<?=$usuarioImagen;?>.png" alt="Icono de perfil">
                                <?php else: ?>
                                    <img class="icono_perfil" src="/img/usuarios/default.png" alt="Icono de perfil">
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </nav>
                
                <?php if(!$inicio): ?>
                    <!-- Modal de Categorías -->
                    <div id="categorias-modal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Categorías</h2>
                            <ul>
                            <?php foreach($categorias as $categoria): ?>
                                <li><a href="/marketplace?categoria=<?= $categoria->id; ?>"><?=$categoria->nombre;?></a></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(!$inicio): ?>
                <div class="hero">
                    <section class="contenido-header contenedor">
                        <h1><?php echo $titulo; ?></h1>
                    </section>
                </div>
            <?php endif; ?>
        </header>
    </div>

    <div class="layout__contenido">
        <?php echo $contenido; ?>
    </div>

    <div class="layout__foter">
        <footer class="footer">
            <div class="contenedor footer-contenedor">
                <!-- Sección del logo y derechos de autor -->
                <div class="footer-logo">
                    <h2>MixtliArte</h2>
                    <p>Copyright © 2025 MixtliArte</p>
                    <p>Todos los derechos reservados</p>
    
                    <div class="footer-social">
                        <a href="#"><img src="/build/img/icon_instagram.svg" alt="Instagram"></a>
                        <a href="#"><img src="/build/img/icon_facebook.svg" alt="Facebook"></a>
                        <a href="#"><img src="/build/img/icon_youtube.svg" alt="YouTube"></a>
                    </div>
                </div>
    
                <!-- Sección de enlaces -->
                <div class="footer-links">
                    <h3>Enlaces</h3>
    
                    <?php if($inicio): ?>
                        <a href="/">Inicio</a>
                        <a href="/nosotros">Nosotros</a>
                        <a href="/contacto">Contacto</a>
                        <a href="/login">Iniciar sesión</a>
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
                    <form action="/subscribe" method="POST">
                        <input type="email" name="email" placeholder="Tu correo electrónico" required>
                        <button type="submit">
                            <img src="/build/img/icon_send.svg" alt="Enviar">
                        </button>
                    </form>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="/build/js/app.js"></script>
    
    <?php if(is_auth()): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Función para enviar el "latido" al servidor
                const sendHeartbeat = () => {
                    // Usamos fetch para enviar una solicitud POST silenciosa
                    fetch('/api/heartbeat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    }).catch(error => console.error('Error en el heartbeat:', error));
                };

                // Enviar un latido inicial al cargar la página
                sendHeartbeat();

                // Configurar para que se envíe un latido cada 60 segundos
                setInterval(sendHeartbeat, 60000); 
            });
        </script>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputBusqueda = document.getElementById('busqueda');
            const listaSugerencias = document.getElementById('sugerencias');

            // Global Polling and Badge Display (Client-side):
            const mensajeLink = document.querySelector('nav.barra .enlaces a[href="/mensajes"]');
            let unreadBadge = null;
            let unreadPollInterval = null;

            if (mensajeLink) {
                const mensajeIcon = mensajeLink.querySelector('i.fa-comment');
                if (mensajeIcon) {
                    unreadBadge = document.createElement('span');
                    unreadBadge.className = 'notification-badge'; // You'll need to style this
                    unreadBadge.style.display = 'none'; // Initially hidden

                    // Ensure the parent (link) can position the badge
                    mensajeLink.style.position = 'relative'; 
                    mensajeLink.appendChild(unreadBadge); // Append to the <a> tag
                }
            }

            async function fetchUnreadCount() {
                if (!mensajeLink || document.hidden) { // Only poll if element exists and tab is visible
                    // If user logs out or element is not on page, stop polling
                    if (!mensajeLink && unreadPollInterval) {
                        clearInterval(unreadPollInterval);
                        unreadPollInterval = null;
                    }
                    return;
                }

                try {
                    const response = await fetch('/mensajes/unread-count');
                    if (!response.ok) {
                        if (response.status === 401 || response.status === 403) {
                            console.warn('User not authenticated for unread count. Stopping poll.');
                            if (unreadPollInterval) clearInterval(unreadPollInterval);
                            unreadPollInterval = null; // Stop polling
                            if (unreadBadge) unreadBadge.style.display = 'none';
                        }
                        // Do not throw error for 401/403 to allow polling to stop gracefully
                        return;
                    }
                    const data = await response.json();
                    updateUnreadBadge(data.unread_count);
                } catch (error) {
                    console.error('Error fetching unread count:', error);
                    // Consider stopping polling on repeated critical errors
                }
            }

            function updateUnreadBadge(count) {
                if (unreadBadge) {
                    if (count > 0) {
                        unreadBadge.textContent = count > 9 ? '9+' : count;
                        unreadBadge.style.display = 'block';
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                }
            }

            // Start polling only if the message link (and thus the badge placeholder) exists
            // This implies the user is likely logged in and on a page with the main navigation
            if (mensajeLink) {
                fetchUnreadCount(); // Initial fetch
                unreadPollInterval = setInterval(fetchUnreadCount, 15000); // Poll every 15 seconds
            }
            // -- END Global Polling and Badge Display --


            inputBusqueda.addEventListener('input', async (e) => {
                let termino = e.target.value.trim();

                // Eliminar caracteres no permitidos (solo letras, números y espacios)
                termino = termino.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/g, '');
                e.target.value = termino;

                if (termino.length < 2) {
                    listaSugerencias.innerHTML = '';
                    return;
                }

                try {
                    const response = await fetch(`/marketplace/autocompletar?q=${encodeURIComponent(termino)}`);
                    const data = await response.json();

                    listaSugerencias.innerHTML = '';

                    if (data.productos.length > 0) {
                        listaSugerencias.innerHTML += '<li class="sugerencia-header">Productos</li>';
                        data.productos.forEach(producto => {
                            const li = document.createElement('li');
                            li.textContent = producto.nombre;
                            li.classList.add('sugerencia-item');
                            li.dataset.url = `/marketplace/producto?id=${producto.id}`;
                            listaSugerencias.appendChild(li);
                        });
                    }

                    if (data.categorias.length > 0) {
                        listaSugerencias.innerHTML += '<li class="sugerencia-header">Categorías</li>';
                        data.categorias.forEach(categoria => {
                            const li = document.createElement('li');
                            li.textContent = categoria.nombre;
                            li.classList.add('sugerencia-item');
                            li.dataset.url = `/marketplace?categoria=${categoria.id}`;
                            listaSugerencias.appendChild(li);
                        });
                    }

                    if (data.usuarios.length > 0) {
                        listaSugerencias.innerHTML += '<li class="sugerencia-header">Artistas</li>';
                        data.usuarios.forEach(usuario => {
                            const li = document.createElement('li');
                            li.textContent = usuario.nombre + ' ' + usuario.apellido;
                            li.classList.add('sugerencia-item');
                            li.dataset.url = `/perfil?id=${usuario.id}`;
                            listaSugerencias.appendChild(li);
                        });
                    }

                    // Asignar eventos de clic a los elementos de sugerencia
                    document.querySelectorAll('.sugerencia-item').forEach(item => {
                        item.addEventListener('click', () => {
                            window.location.href = item.dataset.url;
                        });
                    });

                } catch (error) {
                    console.error('Error al obtener sugerencias:', error);
                }
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.busqueda')) {
                    listaSugerencias.innerHTML = '';
                }
            });
        });
    </script>
</body>
</html>