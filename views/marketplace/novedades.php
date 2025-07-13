<main class="seccion contenedor">
    <div class="contenedor-productos">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <?php include __DIR__ . '/../templates/producto-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay productos nuevos en este momento.</p>
        <?php endif; ?>
    </div>
    <?php echo $paginacion; ?>
</main>