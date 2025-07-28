<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/vendedor/productos">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por nombre, descripción, estado..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/vendedor/productos/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Producto
    </a>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="dashboard__contenedor mb-5">
    <h3 class="dashboard__subtitle mt-0">Productos Activos</h3>
    <?php if(!empty($productos_activos)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Imagen</th>
                        <th scope="col" class="table__th">Nombre</th>
                        <th scope="col" class="table__th">Precio</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($productos_activos as $producto): ?>
                        <tr class="table__tr">
                            <td class="table__td table__td--imagen">
                                <?php if(!empty($producto->imagen_principal)): ?>
                                    <picture>
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.webp" type="image/webp">
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" type="image/png">
                                        <img src="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" alt="Imagen producto" class="table__imagen" loading="lazy">
                                    </picture>
                                <?php else: ?>
                                    <span class="table__no-imagen">S/I</span>
                                <?php endif; ?>
                            </td>
                            <td class="table__td"><?php echo $producto->nombre; ?></td>
                            <td class="table__td"><?php echo '$' . number_format($producto->precio, 2); ?></td>
                            <td class="table__td"><?php echo ucfirst($producto->estado); ?></td>
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/vendedor/productos/editar?id=<?php echo $producto->id; ?>">
                                    <i class="fa-solid fa-user-pen"></i> Editar
                                </a>
                                <form class="table__formulario" action="/vendedor/productos/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $producto->id; ?>)">
                                    <input type="hidden" name="id" value="<?php echo $producto->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        <i class="fa-solid fa-circle-xmark"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo $paginacion_activos; ?>
    <?php else: ?>
        <p class="t-align-center">No Hay Productos Activos.</p>
    <?php endif; ?>
</div>

<div class="dashboard__contenedor dashboard__contenedor--historial">
    <h3 class="dashboard__subtitle mt-0">Historial de Productos Agotados</h3>
    <?php if(!empty($productos_historial)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Imagen</th>
                        <th scope="col" class="table__th">Nombre</th>
                        <th scope="col" class="table__th">Precio</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($productos_historial as $producto): ?>
                        <tr class="table__tr">
                            <td class="table__td table__td--imagen">
                                <?php if(!empty($producto->imagen_principal)): ?>
                                    <picture>
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.webp" type="image/webp">
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" type="image/png">
                                        <img src="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" alt="Imagen producto" class="table__imagen" loading="lazy">
                                    </picture>
                                <?php else: ?>
                                    <span class="table__no-imagen">S/I</span>
                                <?php endif; ?>
                            </td>
                            <td class="table__td"><?php echo $producto->nombre; ?></td>
                            <td class="table__td"><?php echo '$' . number_format($producto->precio, 2); ?></td>
                            <td class="table__td"><span class="texto-rojo"><?php echo ucfirst($producto->estado); ?></span></td>
                            <td class="table__td--acciones">
                                <?php if($producto->tipo_original !== 'unico'): ?>
                                    <a class="table__accion table__accion--editar" href="/vendedor/productos/editar?id=<?php echo $producto->id; ?>">
                                        <i class="fa-solid fa-user-pen"></i> Reabastecer
                                    </a>
                                <?php else: ?>
                                    <span class="table__accion--inactivo">
                                        <i class="fa-solid fa-lock"></i> Permanente
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo $paginacion_historial; ?>
    <?php else: ?>
        <p class="t-align-center">No Hay Productos en el Historial.</p>
    <?php endif; ?>
</div>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="modal-eliminar">
    <div class="modal-eliminar__content">
        <h3>Advertencia</h3>
        <p id="modalMessage">¡Al eliminar este producto se borrarán todos sus datos asociados! ¿Estás seguro de que deseas continuar?</p>
        <div class="modal-eliminar__acciones">
            <button id="cancelDelete" class="modal-eliminar__cancel">Cancelar</button>
            <button id="confirmDelete" class="modal-eliminar__confirm">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let currentId = null;
    let currentForm = null;

    function openDeleteModal(event, id, type) {
        event.preventDefault(); // Evita que el formulario se envíe
        currentId = id; // Guarda el ID del producto actual
        currentForm = event.target.closest('form'); // Encuentra el formulario más cercano al botón
        document.getElementById('deleteModal').style.display = 'block'; // Muestra el modal
        document.body.style.overflow = 'hidden'; // Evita el scroll en el fondo

        // Actualiza el mensaje del modal
        const message = '¡Al eliminar este producto se borrarán todos sus datos asociados! ¿Estás seguro de que deseas continuar?';
        document.getElementById('modalMessage').textContent = message;
    }

    document.getElementById('cancelDelete').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    document.getElementById('deleteModal').addEventListener('click', (event) => {
        if (event.target === document.getElementById('deleteModal')) {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    document.getElementById('confirmDelete').addEventListener('click', () => {
        if (currentForm) {
            currentForm.submit();
        }
    });
</script>