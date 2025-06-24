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
    <div id="modal-imagen" class="modal-imagen">
        <span class="modal-imagen__cerrar">&times;</span>
        <img class="modal-imagen__contenido" id="img-zoom">
    </div>

    <div id="modal-reporte" class="modal-reporte">
        <div class="modal-reporte__contenido">
            <span class="modal-reporte__cerrar">&times;</span>
            <h3>Reportar Producto</h3>
            <p>Ayúdanos a mantener una comunidad segura. ¿Por qué reportas este producto?</p>
            <form id="form-reporte">
                <input type="hidden" name="productoId" value="<?= $producto->id ?>">
                <div class="formulario__campo">
                    <label for="motivo" class="formulario__label">Motivo</label>
                    <select name="motivo" id="motivo" class="formulario__input" required>
                        <option value="" disabled selected>-- Selecciona un motivo --</option>
                        <option value="informacion_falsa">Información Falsa o Engañosa</option>
                        <option value="contenido_inapropiado">Contenido Inapropiado</option>
                        <option value="articulo_prohibido">Artículo Prohibido</option>
                        <option value="spam">Spam o Publicidad</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="formulario__campo">
                    <label for="comentarios" class="formulario__label">Comentarios (opcional)</label>
                    <textarea name="comentarios" id="comentarios" class="formulario__input" rows="3"></textarea>
                </div>
                <button type="submit" class="boton-rosa-block">Enviar Reporte</button>
            </form>
        </div>
    </div>

    <div class="producto-vista__grid">
        
        <div class="producto-vista__imagenes">
            <div class="imagen-principal">
                <?php if (!empty($producto->imagenes[0])): ?>
                    <img id="imagen-principal-display" src="/img/productos/<?php echo htmlspecialchars($producto->imagenes[0]->url); ?>.png" alt="Imagen principal de <?php echo htmlspecialchars($producto->nombre); ?>" style="cursor: zoom-in;">
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
                <p><?= formatear_descripcion($producto->descripcion); ?></p>
            </div>

            <?php if (!empty($vendedor->preferencias_entrega)): ?>
                <div class="producto-entrega">
                    <h2>Opciones de Entrega</h2>
                    <p><?= nl2br(htmlspecialchars($vendedor->preferencias_entrega)); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($vendedor->direccion && !empty(trim($vendedor->direccion->calle))): ?>
                <div class="producto-mapa">
                    <h2>Ubicación Aproximada del Vendedor</h2>
                    <div id="mapa" class="mapa-detalle"></div>
                </div>
            <?php endif; ?>
        </div>

        <aside class="producto-vista__panel">
            <div class="panel-contenido">
                <div class="panel-precio">
                    <p>$<?php echo number_format($producto->precio, 2); ?> MXN</p>
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
                
                <div class="panel-acciones-adicionales">
                    <button id="reportar-btn"><i class="fa-solid fa-flag"></i> Reportar producto</button>
                    <div class="compartir-links">
                        <span>Compartir:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_ENV['HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" title="Compartir en Facebook"><i class="fa-brands fa-facebook"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_ENV['HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?= urlencode('¡Mira este producto en MixtliArte: ' . $producto->nombre); ?>" target="_blank" title="Compartir en Twitter"><i class="fa-brands fa-twitter"></i></a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode('¡Mira este producto en MixtliArte! ' . $_ENV['HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" title="Compartir en WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="valoraciones-seccion">
        <h2>Opiniones sobre el Vendedor</h2>
        <?php if(!empty($valoraciones)): ?>
            <?php foreach($valoraciones as $valoracion): ?>
                <div class="valoracion-item">
                    <div class="valoracion-item__header">
                        <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                        <span class="valoracion-item__comprador">Por: <strong><?= htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                    </div>
                    <?php if($valoracion->comentario): ?>
                        <p class="valoracion-item__comentario">"<?= htmlspecialchars($valoracion->comentario); ?>"</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Este vendedor aún no tiene opiniones.</p>
        <?php endif; ?>
    </div>

    <div class="productos-relacionados-seccion">
        <h2><?= ($producto->estado === 'agotado') ? 'Productos Similares que Podrían Interesarte' : 'También te Podría Gustar'; ?></h2>
        <?php if($producto->estado === 'agotado'): ?>
            <p class="alerta alerta__error">Este producto no está disponible por el momento.</p>
        <?php endif; ?>

        <?php if (!empty($productosRelacionados)): ?>
            <div class="contenedor-productos">
                <?php foreach ($productosRelacionados as $relacionado): 
                    $producto = $relacionado; // Reasignar para que el template funcione
                ?>
                    <?php include __DIR__ . '/../templates/producto-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay otros productos similares en este momento.</p>
        <?php endif; ?>
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
        // Solo ejecutar el código del mapa si existe una dirección
        <?php if ($vendedor->direccion && !empty(trim($vendedor->direccion->calle))): ?>
        
        // --- Lógica para el Mapa ---
        const direccion = "<?php echo htmlspecialchars($vendedor->direccion->calle . ', ' . $vendedor->direccion->colonia . ', ' . $vendedor->direccion->ciudad . ', ' . $vendedor->direccion->estado); ?>";
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}`)
            .then(response => response.json())
            .then(data => {
                let lat = 20.6736; // Coordenadas de fallback
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
        
        <?php endif; ?>

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

        // --- Lógica para Modal de Zoom de Imagen ---
        const modalImagen = document.getElementById("modal-imagen");
        const imgPrincipal = document.getElementById("imagen-principal-display");
        const modalImg = document.getElementById("img-zoom");
        const spanCerrarImg = document.querySelector(".modal-imagen__cerrar");

        if(imgPrincipal) {
            imgPrincipal.onclick = function(){
                modalImagen.style.display = "block";
                modalImg.src = this.src;
            }
        }
        if(spanCerrarImg) {
            spanCerrarImg.onclick = function() {
                modalImagen.style.display = "none";
            }
        }

        // --- Lógica para Modal de Reporte ---
        const modalReporte = document.getElementById("modal-reporte");
        const btnReportar = document.getElementById("reportar-btn");
        const spanCerrarReporte = document.querySelector(".modal-reporte__cerrar");
        const formReporte = document.getElementById('form-reporte');

        if(btnReportar) {
            btnReportar.onclick = () => modalReporte.style.display = "flex";
        }
        if(spanCerrarReporte) {
            spanCerrarReporte.onclick = () => modalReporte.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modalReporte) {
                modalReporte.style.display = "none";
            }
            if (event.target == modalImagen) {
                modalImagen.style.display = "none";
            }
        }

        // Envío del formulario de reporte
        if(formReporte) {
            formReporte.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch('/producto/reportar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if(result.success) {
                        alert(result.message);
                        modalReporte.style.display = "none";
                        this.reset();
                    } else {
                        alert('Error: ' + result.error);
                    }
                } catch (error) {
                    alert('No se pudo enviar el reporte. Inténtalo más tarde.');
                }
            });
        }
    });
</script>