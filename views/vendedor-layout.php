<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MixtliArte | <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/build/css/app.css">
</head>
<body class="dashboard">
        <?php 
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

    <script src="/build/js/bundle.min.js" defer></script>
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