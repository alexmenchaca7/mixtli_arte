<h2 class="dashboard__heading"><?= $titulo ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/faqs">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver a FAQs
    </a>
</div>

<div class="dashboard__contenedor">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

    <?php if(!empty($preguntas)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Pregunta</th>
                        <th scope="col" class="table__th">Categoría Sugerida</th>
                        <th scope="col" class="table__th">Frecuencia</th>
                        <th scope="col" class="table__th">Enviado por</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($preguntas as $pregunta): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= htmlspecialchars($pregunta->pregunta) ?></td>
                            <td class="table__td"><?= htmlspecialchars($pregunta->categoria->nombre ?? 'N/A') ?></td>
                            <td class="table__td"><?= htmlspecialchars($pregunta->frecuencia) ?></td>
                            <td class="table__td"><?= htmlspecialchars($pregunta->usuario->nombre . ' ' . $pregunta->usuario->apellido) ?></td>
                            <td class="table__td--acciones">
                                <form action="/admin/faqs/convert-frequent" method="POST" class="table__formulario" style="margin:0;">
                                    <input type="hidden" name="id" value="<?= $pregunta->id ?>">
                                    <button class="table__accion table__accion--editar" type="submit">
                                        <i class="fa-solid fa-plus-circle"></i> Convertir a FAQ
                                    </button>
                                </form>
                                <form action="/admin/faqs/mark-frequent-reviewed" method="POST" class="table__formulario" style="margin:0;">
                                    <input type="hidden" name="id" value="<?= $pregunta->id ?>">
                                    <input type="hidden" name="estado_revision" value="descartada">
                                    <button class="table__accion table__accion--eliminar" type="submit" title="Descartar esta pregunta">
                                        <i class="fa-solid fa-times-circle"></i> Descartar
                                    </button>
                                </form>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="t-align-center">No hay preguntas frecuentes de usuarios pendientes de revisión.</p>
    <?php endif; ?>
</div>