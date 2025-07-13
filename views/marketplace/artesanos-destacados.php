<main class="seccion contenedor">
    <div class="contenedor-artesanos">
        <?php if (!empty($artesanos)): ?>
            <?php foreach ($artesanos as $artesano): ?>
                <div class="artesano-card">
                    <a href="/perfil?id=<?php echo $artesano->id; ?>">
                        <div class="artesano-card__imagen">
                            <img src="/img/usuarios/<?php echo $artesano->imagen ? $artesano->imagen . '.png' : 'default.png'; ?>" alt="Imagen de <?php echo htmlspecialchars($artesano->nombre); ?>">
                        </div>
                        <div class="artesano-card__info">
                            <h3><?php echo htmlspecialchars($artesano->nombre . ' ' . $artesano->apellido); ?></h3>
                            <div class="artesano-card__rating">
                                <span><?php echo round($artesano->promedio_valoraciones ?? 0, 1); ?> ⭐</span>
                                <span>(<?php echo $artesano->total_valoraciones ?? 0; ?> calificaciones)</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay artesanos destacados en este momento.</p>
        <?php endif; ?>
    </div>
</main>