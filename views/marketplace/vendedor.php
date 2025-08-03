<div class="perfil-usuario contenedor seccion">

    <div class="perfil-usuario__info-principal" style="margin-bottom: 3rem;">
        <div class="perfil-usuario__imagen">
            <img src="/img/usuarios/<?php echo $vendedor->imagen ? $vendedor->imagen . '.png' : 'default.png'; ?>" alt="Imagen de <?php echo htmlspecialchars($vendedor->nombre); ?>">
        </div>
        <div class="perfil-usuario__datos">
            <h1><?php echo htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido); ?></h1>
            <p>
                <?php echo ($vendedor->sexo === 'Femenino') ? 'Artesana / Vendedora' : 'Artesano / Vendedor'; ?>
            </p>
            <?php if ($totalCalificaciones >= 5): ?>
                <div class="vendedor-rating">
                    <span><?php echo $promedioEstrellas; ?> ⭐</span>
                    <span>(<?php echo $totalCalificaciones; ?> calificaciones)</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="perfil-usuario__acciones">
            <button class="boton-rosa" id="follow-btn" data-vendedor-id="<?php echo $vendedor->id; ?>">
                <i class="fa-solid <?php echo $esSeguidor ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                <span id="follow-text"><?php echo $esSeguidor ? 'Siguiendo' : 'Seguir'; ?></span>
            </button>
        </div>
    </div>

    <div class="perfil-usuario__grid">
        <aside class="perfil-usuario__sidebar">
            <div class="perfil-card">
                <h3>Sobre este artesano</h3>
                <p><?php echo htmlspecialchars($vendedor->biografia ?? 'Este vendedor aún no ha añadido una biografía.'); ?></p>
            </div>
            
            <div class="perfil-card">
                <h3>Información de Contacto</h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($vendedor->email); ?></p>
                <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($vendedor->creado)); ?></p>
            </div>

            <?php if ($direccionComercial && !empty($direccionComercial->calle)): ?>
                <div class="perfil-card">
                    <h3>Ubicación del Taller/Tienda</h3>
                    <p>
                        <?php 
                            echo htmlspecialchars($direccionComercial->calle) . ", " . 
                                 htmlspecialchars($direccionComercial->colonia) . ", " .
                                 htmlspecialchars($direccionComercial->ciudad) . ", " .
                                 htmlspecialchars($direccionComercial->estado) . ", C.P. " .
                                 htmlspecialchars($direccionComercial->codigo_postal);
                        ?>
                    </p>
                    <div id="mapa-vendedor" class="mapa-detalle"></div>
                    <small>La ubicación en el mapa es una aproximación.</small>
                </div>
            <?php endif; ?>
        </aside>

        <main class="perfil-usuario__valoraciones">
            <h3 style="margin-bottom: 2rem;">Productos de <?php echo htmlspecialchars($vendedor->nombre); ?></h3>
            <div class="contenedor-productos">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <?php include __DIR__ . '/../templates/producto-card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Este vendedor no tiene productos a la venta en este momento.</p>
                <?php endif; ?>
            </div>

            <?php echo $paginacion; ?>

            <h3 style="margin-top: 4rem; margin-bottom: 2rem;">Calificaciones de este Vendedor</h3>
            
            <?php if($totalCalificaciones >= 5): ?>
                <div class="valoraciones-estadisticas">
                    <h4>Resumen de Calificaciones</h4>
                    <p><strong>Promedio General:</strong> <?php echo $promedioEstrellas; ?> ⭐ (Basado en <?php echo $totalCalificaciones; ?> reseñas)</p>
                    <div class="desglose-barras">
                        <?php foreach($desgloseEstrellas as $estrellas => $cantidad): ?>
                            <div class="barra-item">
                                <span class="barra-label"><?php echo $estrellas; ?> estrella<?php echo $estrellas > 1 ? 's' : ''; ?></span>
                                <div class="barra-fondo">
                                    <div class="barra-progreso" style="width: <?php echo $totalCalificaciones > 0 ? ($cantidad / $totalCalificaciones) * 100 : 0; ?>%;"></div>
                                </div>
                                <span class="barra-porcentaje"><?php echo $totalCalificaciones > 0 ? round(($cantidad / $totalCalificaciones) * 100) : 0; ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php 
                    if ($totalCalificaciones >= 5) {
                        $esPublico = true; // Variable para limitar a 3 puntos en la vista pública
                        include __DIR__ . '/../templates/_puntos-fuertes.php';
                    }
                ?>
                
                <?php if(!empty($valoracionesConComentario)): ?>
                    <?php foreach($valoracionesConComentario as $valoracion): ?>
                        <div class="valoracion-item">
                            <div class="valoracion-item__header">
                                <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                                <span class="valoracion-item__contexto">
                                    Sobre: <strong><?php echo htmlspecialchars($valoracion->producto->nombre); ?></strong>
                                    el <?php echo date('d/m/Y', strtotime($valoracion->creado)); ?>
                                </span>
                            </div>

                            <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                            
                            <div class="valoracion-item__footer">
                                <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                                <button class="reportar-btn" data-valoracion-id="<?= $valoracion->id ?>">
                                    <i class="fa-solid fa-flag"></i> Reportar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Este vendedor aún no ha recibido ninguna calificación con comentario.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Este vendedor necesita al menos 5 calificaciones para que se muestren públicamente.</p>
            <?php endif; ?>
        </main>
    </div>
</div>

<style> .mapa-detalle { height: 25rem; border-radius: .8rem; margin: 1rem 0; } </style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica para el Botón de Seguir ---
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', async function() {
            const vendedorId = this.dataset.vendedorId;
            const icon = this.querySelector('i');
            const text = this.querySelector('#follow-text');

            // Prevenir múltiples clics mientras se procesa
            this.disabled = true;

            try {
                const formData = new FormData();
                formData.append('vendedorId', vendedorId);

                const response = await fetch('/follow/toggle', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Ocurrió un error en el servidor.');
                }

                if (data.success) {
                    if (data.action === 'followed') {
                        icon.classList.remove('fa-user-plus');
                        icon.classList.add('fa-user-check');
                        text.textContent = 'Siguiendo';
                    } else { // 'unfollowed'
                        icon.classList.remove('fa-user-check');
                        icon.classList.add('fa-user-plus');
                        text.textContent = 'Seguir';
                    }
                }

            } catch (error) {
                console.error('Error al intentar seguir/dejar de seguir:', error);
                alert('No se pudo completar la acción. Por favor, intenta de nuevo.');
            } finally {
                // Reactivar el botón después de la operación
                this.disabled = false;
            }
        });
    }

    // --- Lógica del Mapa (si existe la dirección) ---
    <?php if ($direccionComercial && !empty($direccionComercial->calle)): ?>
        const direccion = "<?php echo htmlspecialchars($direccionComercial->calle . ', ' . $direccionComercial->colonia . ', ' . $direccionComercial->ciudad . ', ' . $direccionComercial->estado); ?>";
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}`)
            .then(response => response.json())
            .then(data => {
                let lat = 20.6736; // Coordenadas de fallback (Guadalajara)
                let lon = -103.344;
                if (data && data.length > 0) {
                    lat = data[0].lat;
                    lon = data[0].lon;
                }
                const mapa = L.map('mapa-vendedor').setView([lat, lon], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
                L.marker([lat, lon]).addTo(mapa)
                    .bindPopup('<?php echo htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido); ?>')
                    .openPopup();
            }).catch(() => {
                const mapa = L.map('mapa-vendedor').setView([20.6736, -103.344], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
            });
    <?php endif; ?>
});
</script>