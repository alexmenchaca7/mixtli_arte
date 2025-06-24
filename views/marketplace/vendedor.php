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
            <?php if ($totalCalificaciones > 0): ?>
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

            <h3 style="margin-top: 4rem; margin-bottom: 2rem;">Comentarios de Compradores</h3>
            <?php if(!empty($valoraciones)): ?>
                <?php foreach($valoraciones as $valoracion): ?>
                    <div class="valoracion-item">
                        <div class="valoracion-item__header">
                            <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                        </div>
                        <?php if($valoracion->comentario): ?>
                            <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                        <?php endif; ?>
                        <div class="valoracion-item__footer">
                            <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                            <span><?php echo date('d/m/Y', strtotime($valoracion->creado)); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Este vendedor aún no ha recibido calificaciones.</p>
            <?php endif; ?>
        </main>
    </div>
</div>

<style> .mapa-detalle { height: 25rem; border-radius: .8rem; margin: 1rem 0; } </style>

<script>
// --- Lógica para el Botón de Favoritos ---
document.querySelectorAll('.favorito-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        const productoId = button.dataset.productoId;
        const icon = button.querySelector('i');

        try {
            const response = await fetch('/favoritos/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `productoId=${encodeURIComponent(productoId)}`
            });

            const data = await response.json();
            
            if(!response.ok) throw new Error(data.error || 'Error en la solicitud');
            
            // Toggle del ícono
            icon.classList.toggle('fa-regular');
            icon.classList.toggle('fa-solid');

            // Mostrar notificación
            const existingAlert = document.querySelector('.alert-notification');
            if (existingAlert) existingAlert.remove();

            const message = data.action === 'added' 
                ? `<i class="fas fa-check-circle"></i> Agregado a favoritos` 
                : `<i class="fas fa-trash-alt"></i> Eliminado de favoritos`;

            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert-notification';
            alertDiv.innerHTML = message;
            alertDiv.style.backgroundColor = data.action === 'added' 
                ? '#4CAF50' 
                : '#f44336';

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 2500);

        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        }
    });
});

// --- Lógica del Mapa (si existe la dirección) ---
<?php if ($direccionComercial && !empty($direccionComercial->calle)): ?>
document.addEventListener('DOMContentLoaded', function() {
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
});
<?php endif; ?>
</script>