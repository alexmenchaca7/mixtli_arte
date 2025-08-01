<h2 class="dashboard__heading"><?= $titulo ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/faqs/frequent-questions">
        <i class="fa-solid fa-clipboard-question"></i>
        Preg. Frecuentes Usuarios
    </a>

    <a class="dashboard__boton" href="/admin/faqs/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir FAQ
    </a>

    <a class="dashboard__boton" href="/admin/palabras-clave">
        <i class="fa-solid fa-tags"></i>
        Palabras Clave
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($faqs)): ?>
        <?php endif; ?>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($faqs)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Pregunta</th>
                        <th scope="col" class="table__th">Categoría</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($faqs as $faq): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= htmlspecialchars($faq->pregunta) ?></td>
                            <td class="table__td"><?= htmlspecialchars($faq->categoria->nombre ?? 'N/A') ?></td>
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/admin/faqs/editar?id=<?= $faq->id ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Editar
                                </a>
                                <form action="/admin/faqs/eliminar" method="POST" class="table__formulario" onsubmit="return confirm('¿Estás seguro de eliminar esta FAQ?');">
                                    <input type="hidden" name="id" value="<?= $faq->id ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        <i class="fa-solid fa-trash-can"></i>
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
        <p class="t-align-center">No hay FAQs registradas aún.</p>
    <?php endif; ?>
</div>