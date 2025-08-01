<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/faqs">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>

    <a class="dashboard__boton" href="/admin/palabras-clave/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Palabra
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($palabras)): ?>
        <div class="dashboard__contenedor-tabla">

            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Palabra</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($palabras as $palabra): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?php echo $palabra->palabra; ?></td>
    
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/admin/palabras-clave/editar?id=<?php echo $palabra->id; ?>">
                                    <i class="fa-solid fa-user-pen"></i>
                                    Editar
                                </a>
                                <form id="deleteForm" class="table__formulario" action="/admin/palabras-clave/eliminar" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $palabra->id; ?>">
                                    
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
        <p class="t-align-center">No Hay Palabras Aún</p>
    <?php endif; ?>
</div>