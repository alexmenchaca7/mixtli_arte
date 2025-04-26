<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/categorias">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por nombre o descripción..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/admin/categorias/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Categoria
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($categorias)): ?>
        <div class="dashboard__contenedor-tabla">

            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Nombre</th>
                        <th scope="col" class="table__th">Descripcion</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($categorias as $categoria): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?php echo $categoria->nombre; ?></td>
                            <td class="table__td">
                                <?php echo !empty($categoria->descripcion) ? $categoria->descripcion : 'Sin descripción'; ?>
                            </td>
    
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/admin/categorias/editar?id=<?php echo $categoria->id; ?>">
                                    <i class="fa-solid fa-user-pen"></i>
                                    Editar
                                </a>
                                <form id="deleteForm" class="table__formulario" action="/admin/categorias/eliminar" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $categoria->id; ?>">
                                    
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
        <p class="t-align-center">No Hay Categorias Aún</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>