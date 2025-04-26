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

<div class="dashboard__contenedor">
    <?php if(!empty($productos)): ?>
        <div class="dashboard__contenedor-tabla">

            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Imagen</th>
                        <th scope="col" class="table__th">Nombre</th>
                        <th scope="col" class="table__th">Descripción</th>
                        <th scope="col" class="table__th">Precio</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($productos as $producto): ?>
                        <tr class="table__tr">
                            <td class="table__td table__td--imagen">
                                <?php if(!empty($producto->imagen_principal)): ?>
                                    <picture>
                                        <source 
                                            srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.webp" 
                                            type="image/webp"
                                        >
                                        <source 
                                            srcset="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" 
                                            type="image/png" 
                                        >
                                        <img 
                                            src="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal); ?>.png" 
                                            alt="Imagen producto <?php echo htmlspecialchars($producto->nombre); ?>" 
                                            class="table__imagen" <?php // <-- Usaremos esta clase para estilos ?>
                                            loading="lazy" 
                                        >
                                    </picture>
                                <?php else: ?>
                                    <span class="table__no-imagen">S/I</span> <?php // Placeholder si no hay imagen ?>
                                <?php endif; ?>
                            </td>
                            <td class="table__td"><?php echo $producto->nombre; ?></td>
                            <td class="table__td">
                                <?php echo !empty($producto->descripcion) ? $producto->descripcion : 'Sin descripción'; ?>
                            </td>
                            <td class="table__td"><?php echo '$' . number_format($producto->precio, 2); ?></td>
                            <td class="table__td">
                                <?php if($producto->estado === 'agotado'): ?>
                                    <span class="texto-rojo"><?php echo ucfirst($producto->estado); ?></span>
                                <?php else: ?>
                                    <?php echo ucfirst($producto->estado); ?>
                                <?php endif; ?>
                            </td>
    
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/vendedor/productos/editar?id=<?php echo $producto->id; ?>">
                                    <i class="fa-solid fa-user-pen"></i>
                                    Editar
                                </a>
                                <form class="table__formulario" action="/vendedor/productos/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $producto->id; ?>, 'producto')">
                                    <input type="hidden" name="id" value="<?php echo $producto->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="t-align-center">No Hay Productos Aún</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>

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