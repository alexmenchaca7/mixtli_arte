<main class="seccion contenedor">
    <div class="contenedor-productos">          
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <div class="producto">
                    <a href="/marketplace/producto?id=<?php echo $producto->id; ?>">
                        <div class="producto__imagen-contenedor">
                            <picture>
                                <?php if (!empty($producto->imagen_principal)): ?>
                                    <source srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.webp" type="image/webp">
                                    <img loading="lazy" src="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" alt="Imagen de <?php echo htmlspecialchars($producto->nombre); ?>">
                                <?php else: ?>
                                    <img loading="lazy" src="/img/productos/placeholder.jpg" alt="Imagen no disponible">
                                <?php endif; ?>
                            </picture>
                        </div>
                    </a>

                    <div class="producto-info">
                        <h3><?php echo htmlspecialchars($producto->nombre); ?></h3>
                        <p class="producto-categoria">
                            <?php 
                            $categoriaNombre = 'Sin categoría';
                            if (isset($categorias) && is_array($categorias)) {
                                foreach ($categorias as $categoria) {
                                    if ($categoria->id == $producto->categoriaId) {
                                        $categoriaNombre = $categoria->nombre;
                                        break;
                                    }
                                }
                            }
                            echo htmlspecialchars($categoriaNombre);
                            ?>
                        </p>

                        <div class="precio">
                            <p>$<?php echo number_format($producto->precio, 2); ?> MXN</p>
                            <button class="favorito-btn" data-producto-id="<?php echo $producto->id; ?>">
                                <i class="fa-heart <?php echo in_array($producto->id, $favoritosIds) ? 'fa-solid' : 'fa-regular'; ?>"></i>
                            </button>
                        </div>
                    </div>
                </div><!-- producto -->
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay productos disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</main>

<script>
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
</script>