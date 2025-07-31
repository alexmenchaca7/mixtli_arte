<?php if (!empty($puntos_fuertes)): ?>
    <div class="puntos-fuertes-resumen">
        <h4>Puntos Fuertes Destacados</h4>
        <div class="puntos-fuertes-lista">
            <?php $contador = 0; ?>
            <?php foreach ($puntos_fuertes as $punto => $total): ?>
                <?php
                    // En vistas públicas, solo mostramos los 3 primeros
                    if (isset($esPublico) && $esPublico && $contador >= 3) {
                        break;
                    }
                ?>
                <div class="punto-fuerte-tag">
                    <span class="punto-fuerte-nombre"><?php echo htmlspecialchars($punto ?? ''); ?></span>
                    <span class="punto-fuerte-total"><?php echo htmlspecialchars($total ?? '0'); ?></span>
                </div>
                <?php $contador++; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>