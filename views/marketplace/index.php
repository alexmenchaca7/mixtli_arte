<main class="seccion contenedor">
    <?php 
        // MOSTRAR FILTROS ACTIVOS
        $filtrosAplicados = [];
        if (!empty($busqueda)) {
            // Usamos htmlspecialchars para prevenir XSS al mostrar la búsqueda del usuario
            $filtrosAplicados[] = "Búsqueda: '" . htmlspecialchars($busqueda) . "'";
        }
        if (!empty($categoria_seleccionada)) {
            // Asumimos que $categorias está disponible desde el controlador
            foreach($categorias as $cat) {
                if ($cat->id == $categoria_seleccionada) {
                    $filtrosAplicados[] = "Categoría: " . htmlspecialchars($cat->nombre);
                    break;
                }
            }
        }
        if (!empty($_GET['precio_min'])) $filtrosAplicados[] = "Precio Mín: $" . htmlspecialchars($_GET['precio_min']);
        if (!empty($_GET['precio_max'])) $filtrosAplicados[] = "Precio Máx: $" . htmlspecialchars($_GET['precio_max']);
        if (!empty($_GET['ubicacion'])) $filtrosAplicados[] = "Ubicación: '" . htmlspecialchars($_GET['ubicacion']) . "'";

        if (!empty($filtrosAplicados)): ?>
            <div class="filtros-activos">
                <p>
                    <strong>Filtros aplicados:</strong> <?php echo implode(' / ', $filtrosAplicados); ?>. 
                    <a href="/marketplace">Limpiar filtros</a>
                </p>
            </div>
        <?php endif; 
    ?>

    <div class="contenedor-productos">          
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <?php include __DIR__ . '/../templates/producto-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay productos disponibles en este momento.</p>
        <?php endif; ?>
    </div>

    <?php echo $paginacion; ?>
</main>