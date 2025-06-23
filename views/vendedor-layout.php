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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/build/css/app.css">
</head>
<body class="dashboard">
    <div class="overlay"></div> <?php 
        include_once __DIR__ .'/templates/vendedor-header.php';
    ?>
    <div class="dashboard__grid">
        <?php
            include_once __DIR__ .'/templates/vendedor-sidebar.php';  
        ?>

        <main class="dashboard__contenido <?php echo $titulo === 'Mensajes' ? 'vendedor-mensajes' : ''; ?>">
            <?php 
                echo $contenido; 
            ?> 
        </main>
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
    </div

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="/build/js/app.js" defer></script>

    <?php if(is_auth()): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Función para enviar el "latido" al servidor y mantener la sesión activa
                const sendHeartbeat = () => {
                    fetch('/api/heartbeat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    }).catch(error => console.error('Error en el heartbeat:', error));
                };

                // Enviar un latido inicial tan pronto como la página cargue
                sendHeartbeat();

                // Configurar para que se envíe un latido cada 60 segundos (1 minuto)
                setInterval(sendHeartbeat, 60000); 
            });
        </script>
    <?php endif; ?>
</body>
</html>