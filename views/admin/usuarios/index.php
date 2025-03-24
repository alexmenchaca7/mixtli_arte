<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/usuarios/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Usuario
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($usuarios)): ?>
        <table class="table">
            <thead class="table__thead">
                <tr>
                    <th scope="col" class="table__th">Nombre</th>
                    <th scope="col" class="table__th">Email</th>
                    <th scope="col" class="table__th">Telefono</th>
                    <th scope="col" class="table__th">Rol</th>
                    <th scope="col" class="table__th">Verificado</th>
                    <th scope="col" class="table__th"></th>
                </tr>
            </thead>

            <tbody class="table__tbody">
                <?php foreach($usuarios as $usuario): ?>
                    <tr class="table__tr">
                        <td class="table__td"><?php echo $usuario->nombre . ' ' . $usuario->apellido; ?></td>
                        <td class="table__td"><?php echo $usuario->email; ?></td>
                        <td class="table__td"><?php echo $usuario->telefono; ?></td>
                        <td class="table__td"><?php echo $usuario->rol; ?></td>
                        <td class="table__td"><?php echo $usuario->verificado; ?></td>

                        <td class="table__td--acciones">
                            <a class="table__accion table__accion--editar" href="/admin/usuarios/editar?id=<?php echo $usuario->id; ?>">
                                <i class="fa-solid fa-user-pen"></i>
                                Editar
                            </a>
                            <form id="deleteForm" class="table__formulario" action="/admin/usuarios/eliminar" method="POST">
                                <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">
                                
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
    <?php else: ?>
        <p class="t-align-center">No Hay Usuarios Aún</p>
    <?php endif; ?>
</div>