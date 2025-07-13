<div class="producto <?php echo ($producto->estado === 'agotado') ? 'agotado' : ''; ?>" data-producto-card-id="<?php echo $producto->id; ?>">
    <?php if ($producto->estado === 'agotado'): ?>
        <div class="producto__agotado-overlay">
            <span>Agotado</span>
        </div>
    <?php endif; ?>

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
            // Busca el nombre de la categoría en el array $categorias (si existe)
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
            
            <div class="producto-acciones">
                <?php if($producto->estado !== 'agotado'): ?>
                    <button class="favorito-btn" data-producto-id="<?php echo $producto->id; ?>" title="Me gusta">
                        <i class="fa-heart <?php echo (isset($favoritosIds) && in_array($producto->id, $favoritosIds)) ? 'fa-solid' : 'fa-regular'; ?>"></i>
                    </button>
                    
                    <button class="no-interesa-btn" data-producto-id="<?php echo $producto->id; ?>" title="No me interesa">
                        <i class="fa-regular fa-thumbs-down"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>