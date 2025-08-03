<main class="seccion contenedor">
    <div class="favoritos__seccion">
        <div class="favoritos__header">
            <h2>Disponibles</h2>
        </div>
        
        <div class="contenedor-productos">
            <?php if (!empty($productosDisponibles)): ?>
                <?php foreach ($productosDisponibles as $producto): ?>
                    <?php include __DIR__ . '/../templates/producto-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="t-align-center">No tienes productos disponibles guardados.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($productosAgotados)): ?>
        <div class="favoritos__seccion">
            <div class="favoritos__header">
                <h2>Agotados (Guardados para después)</h2>
            </div>
            <div class="contenedor-productos">
                <?php foreach ($productosAgotados as $producto): ?>
                    <?php include __DIR__ . '/../templates/producto-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>