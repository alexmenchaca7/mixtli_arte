<?php
// Obtener el ID del usuario actual para la lógica de favoritos
$usuarioId = $_SESSION['id'] ?? null;
$esFavorito = false;
if ($usuarioId) {
    $favorito = \Model\Favorito::whereArray([
        'usuarioId' => $usuarioId,
        'productoId' => $producto->id
    ]);
    if ($favorito) {
        $esFavorito = true;
    }
}
?>

<main class="contenedor seccion producto-vista">

    <div class="producto-vista__grid">
        
        <div class="producto-vista__imagenes">
            <div class="imagen-principal">
                <?php if (!empty($producto->imagenes[0])): ?>
                    <img id="imagen-principal-display" src="/img/productos/<?php echo htmlspecialchars($producto->imagenes[0]->url); ?>.png" alt="Imagen principal de <?php echo htmlspecialchars($producto->nombre); ?>">
                <?php else: ?>
                    <img id="imagen-principal-display" src="/img/productos/placeholder.jpg" alt="Imagen no disponible">
                <?php endif; ?>
            </div>
            <?php if (count($producto->imagenes) > 1): ?>
                <div class="galeria-thumbnails">
                    <?php foreach ($producto->imagenes as $index => $imagen): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'activa' : ''; ?>" onclick="cambiarImagen(this, '/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.png')">
                            <img src="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.png" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="producto-vista__detalles">
            <div class="producto-header">
                <h1><?= htmlspecialchars($producto->nombre) ?></h1>
                <button class="favorito-btn-detalle" data-producto-id="<?php echo $producto->id; ?>" title="Añadir a favoritos">
                    <i class="fa-heart <?php echo $esFavorito ? 'fa-solid' : 'fa-regular'; ?>"></i>
                </button>
            </div>

            <div class="producto-descripcion">
                <h2>Descripción</h2>
                <p><?= nl2br(htmlspecialchars($producto->descripcion)) ?></p>
            </div>
            
            <?php if ($vendedor->direccion && !empty(trim($vendedor->direccion->calle))): ?>
                <div class="producto-mapa">
                    <h2>Ubicación del Vendedor</h2>
                    <div id="mapa" class="mapa-detalle"></div>
                    <small>La ubicación que se muestra en el mapa es una aproximación.</small>
                </div>
            <?php endif; ?>
        </div>

        <aside class="producto-vista__panel">
            <div class="panel-contenido">
                <div class="panel-precio">
                    <p>$<?php echo number_format($producto->precio, 2); ?> MXN</p>
                </div>

                <div class="panel-vendedor">
                    <h4>Vendido por</h4>
                    <a href="/perfil?id=<?php echo $vendedor->id; ?>" class="vendedor-info-link">
                        <div class="vendedor-info">
                            <picture>
                                <img src="/img/usuarios/<?php echo $vendedor->imagen ? htmlspecialchars($vendedor->imagen) . '.png' : 'default.png'; ?>" alt="Perfil de <?php echo htmlspecialchars($vendedor->nombre); ?>">
                            </picture>
                            <div class="vendedor-datos">
                                <span><?php echo htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido); ?></span>
                                <?php if ($totalCalificaciones > 0): ?>
                                    <div class="vendedor-rating">
                                        <span class="estrellas">
                                            <?php
                                            $promedioRedondeado = round($promedioEstrellas);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $promedioRedondeado) ? '★' : '☆';
                                            }
                                            ?>
                                        </span>
                                        <span class="total-calificaciones">
                                            <?php echo $promedioEstrellas; ?> (<?php echo $totalCalificaciones; ?> calificaciones)
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="panel-contacto">
                    <form id="form-mensaje" method="POST">
                        <input type="hidden" name="productoId" value="<?= $producto->id ?>">
                        <input type="hidden" name="destinatarioId" value="<?= $vendedor->id ?>">
                        <input type="hidden" name="mensaje" value="Hola, estoy interesado/a en '<?php echo htmlspecialchars($producto->nombre); ?>'. ¿Sigue disponible?">
                        <button type="submit" class="boton-rosa-block">
                            <span class="texto-boton">Contactar al vendedor</span>
                            <div class="spinner"></div>
                        </button>
                    </form>
                    <div id="mensaje-exito" class="mensaje-exito"></div>
                    <div id="mensaje-error" class="mensaje-error"></div>
                </div>
            </div>
        </aside>

    </div>
</main>

<script>
    // --- Lógica para la Galería de Imágenes ---
    function cambiarImagen(thumbnail, nuevaImagenSrc) {
        document.getElementById('imagen-principal-display').src = nuevaImagenSrc;
        
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('activa'));
        thumbnail.classList.add('activa');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // --- Lógica para el Mapa ---
        const direccion = "<?php echo $vendedor->direccion->calle . ', ' . $vendedor->direccion->colonia . ', ' . $vendedor->direccion->ciudad . ', ' . $vendedor->direccion->estado; ?>";
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}`)
            .then(response => response.json())
            .then(data => {
                let lat = 20.6736; // Coordenadas de fallback (Guadalajara)
                let lon = -103.344;
                if (data && data.length > 0) {
                    lat = data[0].lat;
                    lon = data[0].lon;
                }
                const mapa = L.map('mapa').setView([lat, lon], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
                L.marker([lat, lon]).addTo(mapa);
            }).catch(() => { // En caso de error en el fetch
                const mapa = L.map('mapa').setView([20.6736, -103.344], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
            });

        // --- Lógica para el Formulario de Contacto ---
        const formMensaje = document.getElementById('form-mensaje');
        if (formMensaje) {
            formMensaje.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const successDiv = document.getElementById('mensaje-exito');
                const errorDiv = document.getElementById('mensaje-error');
                const submitBtn = form.querySelector('button[type="submit"]');

                submitBtn.disabled = true;
                submitBtn.querySelector('.texto-boton').textContent = 'Enviando...';
                submitBtn.querySelector('.spinner').style.display = 'inline-block';

                fetch('/mensajes/enviar', { // La ruta es la misma
                    method: 'POST',
                    body: formData,
                    headers: {'Accept': 'application/json'}
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successDiv.textContent = '✓ Mensaje enviado. Redirigiendo al chat...';
                        successDiv.style.display = 'block';
                        errorDiv.style.display = 'none';
                        setTimeout(() => {
                            window.location.href = `/mensajes?productoId=${formData.get('productoId')}&contactoId=${formData.get('destinatarioId')}`;
                        }, 1500);
                    } else {
                        throw new Error(data.errores?.join(', ') || 'Error desconocido al enviar');
                    }
                })
                .catch(error => {
                    errorDiv.textContent = 'Error: ' + error.message;
                    errorDiv.style.display = 'block';
                    successDiv.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.texto-boton').textContent = 'Contactar al vendedor';
                    submitBtn.querySelector('.spinner').style.display = 'none';
                });
            });
        }

        // --- Lógica para el Botón de Favoritos ---
        const favButton = document.querySelector('.favorito-btn-detalle');
        if (favButton) {
            favButton.addEventListener('click', async function() {
                const productoId = this.dataset.productoId;
                const icon = this.querySelector('i');

                try {
                    const response = await fetch('/favoritos/toggle', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `productoId=${productoId}`
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);

                    icon.classList.toggle('fa-regular');
                    icon.classList.toggle('fa-solid');
                } catch (error) {
                    console.error('Error al cambiar favorito:', error);
                }
            });
        }
    });
</script>