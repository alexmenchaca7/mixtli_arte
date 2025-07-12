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

    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MixtliArte" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <link rel="stylesheet" href="<?php echo get_asset('app.css'); ?>">
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

                    <div class="mobile-menu">
                        <i class="fa-solid fa-bars"></i>
                    </div>
                    
                    <?php if(!$inicio): ?>
                        <div class="busqueda busqueda-desktop">
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
                        <div class="enlaces--inicio navegacion-principal">
                            <a href="/">Inicio</a>
                            <a href="/nosotros">Nosotros</a>
                            <a href="/contacto">Contacto</a>
                            <a href="/login">Iniciar Sesión</a>
                        </div>      
                    <?php else: ?>
                        <div class="enlaces navegacion-principal">
                            
                            <div class="busqueda busqueda-mobile">
                                <form action="/marketplace" method="GET">
                                    <input 
                                        type="text" 
                                        name="q" 
                                        id="busqueda-mobile" 
                                        placeholder="Buscar productos..." 
                                        aria-label="Buscar productos" 
                                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                        autocomplete="off"
                                    >
                                    <button type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </form>
                                <ul id="sugerencias-mobile" class="sugerencias"></ul>
                            </div>

                            <button id="categorias-btn" class="categorias-btn">Categorías</button>
                            <a href="/marketplace">Para Ti</a>
                            <a href="/mensajes">
                                <i class="fa-regular fa-comment icon-desktop"></i>
                                <span class="link-text-mobile">Mensajes</span>
                            </a>
                            <a href="/favoritos">
                                <i class="fa-regular fa-heart icon-desktop"></i>
                                <span class="link-text-mobile">Favoritos</span>
                            </a>
                            <a href="/notificaciones">
                                <i class="fa-regular fa-bell icon-desktop"></i>
                                <span class="link-text-mobile">Notificaciones</span>
                            </a>

                            <?php
                                // Tu código PHP para la URL del perfil no cambia
                                $perfilUrl = '/login';
                                if (isset($_SESSION['login'])) {
                                    if ($_SESSION['rol'] === 'vendedor') {
                                        $perfilUrl = '/vendedor/perfil';
                                    } elseif ($_SESSION['rol'] === 'comprador') {
                                        $perfilUrl = '/comprador/perfil';
                                    } elseif ($_SESSION['rol'] === 'admin') {
                                        $perfilUrl = '/admin/dashboard';
                                    }
                                }
                            ?>
                            
                            <a href="<?= $perfilUrl ?>">
                                <?php if(isset($usuarioImagen)): ?>
                                    <img class="icono_perfil icon-desktop" src="/img/usuarios/<?=$usuarioImagen;?>.png" alt="Icono de perfil">
                                <?php else: ?>
                                    <img class="icono_perfil icon-desktop" src="/img/usuarios/default.png" alt="Icono de perfil">
                                <?php endif; ?>
                                <span class="link-text-mobile">Perfil</span>
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
                        <a href="/marketplace">Para Ti</a>
                        <a href="/mensajes">Mensajes</a>
                        <a href="/favoritos">Lista de Deseos</a>
                        <a href="<?= $perfilUrl ?>">Perfil</a>
                    <?php endif; ?>
                    
                </div>
    
                <div class="footer-links">
                    <h3>Soporte</h3>
                    <a href="/faqs">Preguntas Frecuentes</a>
                    <a href="/contacto">Contacto</a>
                    <a href="/terminos-condiciones">Términos de servicio</a>
                    <a href="/politica-privacidad">Política de privacidad</a>
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

    <div id="modal-valoracion" class="modal-valoracion" style="display: none;">
        <div class="modal-valoracion__contenido">
            <h3 id="modal-valoracion-titulo">Calificar Usuario</h3>
            <form id="form-valoracion">
                <input type="hidden" name="valoracion_id" id="input-valoracion-id">

                <div class="modal-valoracion__campo">
                    <label>Calificación:</label>
                    <div class="rating-estrellas">
                        <i class="fa-regular fa-star" data-valor="1"></i>
                        <i class="fa-regular fa-star" data-valor="2"></i>
                        <i class="fa-regular fa-star" data-valor="3"></i>
                        <i class="fa-regular fa-star" data-valor="4"></i>
                        <i class="fa-regular fa-star" data-valor="5"></i>
                    </div>
                    <input type="hidden" name="estrellas" id="input-estrellas" required>
                </div>

                <div class="modal-valoracion__campo">
                    <label for="comentario-valoracion">Comentario (opcional):</label>
                    <textarea name="comentario" id="comentario-valoracion" rows="4"></textarea>
                </div>

                <div class="modal-valoracion__campo">
                    <label>Puntos Fuertes (opcional):</label>
                    <div id="puntos-fuertes-contenedor" class="puntos-fuertes">
                        </div>
                </div>

                <div class="modal-valoracion__acciones">
                    <button type="button" class="modal-valoracion__boton-cancelar" id="btn-cancelar-valoracion">Cancelar</button>
                    <button type="submit" class="modal-valoracion__boton-enviar">Enviar Calificación</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-reporte-valoracion" class="modal-reporte" style="display: none;">
        <div class="modal-reporte__contenido">
            <span class="modal-reporte__cerrar">&times;</span>
            <h3>Reportar Comentario</h3>
            <p>Ayúdanos a mantener la comunidad segura. Tu reporte es anónimo.</p>
            <form id="form-reporte-valoracion">
                <input type="hidden" name="valoracionId" id="reporte-valoracion-id">
                <div class="formulario__campo">
                    <label for="reporte-motivo" class="formulario__label">Motivo del Reporte</label>
                    <select name="motivo" id="reporte-motivo" class="formulario__input" required>
                        <option value="" disabled selected>-- Elige un motivo --</option>
                        <option value="inapropiado">Contenido ofensivo o inapropiado</option>
                        <option value="falso">Comentario falso o engañoso</option>
                        <option value="spam">Es spam o publicidad</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="formulario__campo">
                    <label for="reporte-comentarios" class="formulario__label">Comentarios Adicionales</label>
                    <textarea name="comentarios" id="reporte-comentarios" class="formulario__input" rows="4"></textarea>
                </div>
                <button type="submit" class="boton-rosa-block">Enviar Reporte</button>
            </form>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="<?php echo get_asset('app.js'); ?>" defer></script>
    
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
            // --- Lógica de la barra de búsqueda y autocompletado ---
            const formBusqueda = document.querySelector('.busqueda form'); // Apunta al formulario
            const inputBusqueda = document.getElementById('busqueda');
            const listaSugerencias = document.getElementById('sugerencias');

            if (formBusqueda && inputBusqueda && listaSugerencias) {
                
                // Registrar la búsqueda cuando se envía el formulario
                formBusqueda.addEventListener('submit', (e) => {
                    const termino = inputBusqueda.value.trim();
                    if (termino) {
                        registrarInteraccion({
                            tipo: 'busqueda',
                            metadata: { termino: termino }
                        });
                    }
                    // No detenemos el envío, el formulario debe funcionar normalmente.
                });

                inputBusqueda.addEventListener('input', async (e) => {
                    const termino = e.target.value;

                    if (termino.length < 2) {
                        listaSugerencias.style.display = 'none';
                        return;
                    }
                    
                    try {
                        const terminoSanitizado = termino.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/g, '');
                        const response = await fetch(`/marketplace/autocompletar?q=${encodeURIComponent(terminoSanitizado)}`);
                        const data = await response.json();
                        
                        renderizarSugerencias(data);
                    } catch (error) {
                        console.error('Error al obtener sugerencias:', error);
                        listaSugerencias.style.display = 'none';
                    }
                });

                // Cierra las sugerencias si se hace clic fuera
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.busqueda')) {
                        listaSugerencias.style.display = 'none';
                    }
                });
            }

            function renderizarSugerencias(data) {
                listaSugerencias.innerHTML = '';
                let haySugerencias = false;

                const crearItemSugerencia = (item, tipo) => {
                    const li = document.createElement('li');
                    li.classList.add('sugerencia-item');
                    li.dataset.tipo = tipo; 

                    switch (tipo) {
                        case 'producto':
                            li.textContent = item.nombre;
                            li.dataset.id = item.id;
                            li.dataset.url = `/marketplace/producto?id=${item.id}`;
                            break;
                        case 'categoria':
                            li.textContent = item.nombre;
                            li.dataset.id = item.id;
                            li.dataset.nombre = item.nombre; 
                            li.dataset.url = `/marketplace?categoria=${item.id}`;
                            break;
                        case 'usuario':
                            li.textContent = `${item.nombre} ${item.apellido}`;
                            li.dataset.id = item.id;
                            li.dataset.url = `/perfil?id=${item.id}`;
                            break;
                    }
                    return li;
                };

                const agregarSeccion = (titulo, items, tipo) => {
                    if (items.length > 0) {
                        haySugerencias = true;
                        listaSugerencias.innerHTML += `<li class="sugerencia-header">${titulo}</li>`;
                        items.forEach(item => {
                            listaSugerencias.appendChild(crearItemSugerencia(item, tipo));
                        });
                    }
                };
                
                agregarSeccion('Productos', data.productos, 'producto');
                agregarSeccion('Categorías', data.categorias, 'categoria');
                agregarSeccion('Artistas', data.usuarios, 'usuario');

                if (haySugerencias) {
                    listaSugerencias.style.display = 'block';
                    document.querySelectorAll('.sugerencia-item').forEach(item => {
                        item.addEventListener('click', handleSugerenciaClick);
                    });
                } else {
                    listaSugerencias.style.display = 'none';
                }
            }
            
            function handleSugerenciaClick(e) {
                const item = e.currentTarget;
                const { tipo, id, url, nombre } = item.dataset;
                
                let interaccionData = {
                    tipo: `autocompletado_${tipo}`,
                    productoId: tipo === 'producto' ? id : null,
                    metadata: {
                        termino: tipo === 'categoria' ? nombre : item.textContent.trim()
                    }
                };
                
                registrarInteraccion(interaccionData, url);
            }

            function registrarInteraccion(data, redirectUrl = null) {
                fetch('/interaccion/registrar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(res => {
                    if(res.success) console.log(`Interacción '${data.tipo}' registrada.`);
                })
                .catch(error => console.error('Error al registrar interacción:', error))
                .finally(() => {
                    // **MEJORA: Solo redirige si se proporciona una URL**
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            }

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
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.querySelector('.mobile-menu');
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    const navegacion = document.querySelector('.navegacion-principal');
                    navegacion.classList.toggle('mostrar');
                });
            }
        });
    </script>
</body>
</html>