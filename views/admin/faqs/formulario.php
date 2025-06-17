<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información de la FAQ</legend>

    <div class="formulario__campo">
        <label for="pregunta" class="formulario__label">Pregunta</label>
        <input 
            type="text"
            class="formulario__input"
            id="pregunta"
            name="pregunta"
            placeholder="Escribe la pregunta frecuente"
            value="<?= htmlspecialchars($faq->pregunta ?? ''); ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="respuesta" class="formulario__label">Respuesta</label>
        <textarea 
            class="formulario__input"
            id="respuesta"
            name="respuesta"
            placeholder="Escribe la respuesta a la pregunta"
            rows="8"
        ><?= htmlspecialchars($faq->respuesta ?? ''); ?></textarea>
    </div>

    <div class="formulario__campo">
        <label for="categoriaFaqId" class="formulario__label">Categoría</label> <select class="formulario__input" name="categoriaFaqId" id="categoriaFaqId"> <option value="" disabled selected>-- Selecciona una categoría --</option>
            <?php foreach($categorias as $categoriaFaq): // Usar un nombre de variable claro ?>
                <option value="<?= $categoriaFaq->id ?>" <?= ($faq->categoriaFaqId == $categoriaFaq->id) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($categoriaFaq->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>