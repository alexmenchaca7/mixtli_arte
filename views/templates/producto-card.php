<div class="producto <?php echo ($producto->estado === 'agotado') ? 'agotado' : ''; ?>" data-producto-card-id="<?php echo $producto->id; ?>">

    <a href="/marketplace/producto?id=<?php echo $producto->id; ?>" class="producto__enlace-imagen">
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
            
            <div class="producto__acciones">
                <?php
                // Determina si el producto está en la lista de favoritos.
                $esFavorito = isset($favoritosIds) && in_array($producto->id, $favoritosIds);
                ?>

                <?php if ($producto->estado !== 'agotado' || $esFavorito): ?>
                    <button class="favorito-btn" data-producto-id="<?php echo $producto->id; ?>" title="Me gusta">
                        <i class="fa-heart <?php echo $esFavorito ? 'fa-solid' : 'fa-regular'; ?>"></i>
                    </button>
                <?php endif; ?>
                
                <?php if($producto->estado !== 'agotado'): ?>
                    <button class="no-interesa-btn" data-producto-id="<?php echo $producto->id; ?>" title="No me interesa">
                        <i class="fa-regular fa-thumbs-down"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($producto->estado === 'agotado'): ?>
        <div class="producto__agotado-tag">
            <span>Agotado</span>
        </div>
    <?php endif; ?>
</div>