<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/soporte">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver a Consultas
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

    <fieldset class="formulario__fieldset">
        <legend class="formulario__legend">Detalles de la Consulta #<?php echo htmlspecialchars($consulta->numero_caso); ?></legend>

        <div class="formulario__campo">
            <label class="formulario__label">Email del Usuario:</label>
            <p class="formulario__texto-valor"><?php echo htmlspecialchars($consulta->email); ?></p>
        </div>

        <div class="formulario__campo">
            <label class="formulario__label">Asunto:</label>
            <p class="formulario__texto-valor"><?php echo htmlspecialchars($consulta->asunto); ?></p>
        </div>

        <div class="formulario__campo">
            <label class="formulario__label">Mensaje:</label>
            <p class="formulario__texto-valor mensaje-preformateado"><?php echo nl2br(htmlspecialchars($consulta->mensaje)); ?></p>
        </div>

        <div class="formulario__campo">
            <label class="formulario__label">Fecha de Creación:</label>
            <p class="formulario__texto-valor"><?php echo date('d/m/Y H:i', strtotime($consulta->creado)); ?></p>
        </div>

        <div class="formulario__campo">
            <label class="formulario__label">Última Actualización:</label>
            <p class="formulario__texto-valor"><?php echo date('d/m/Y H:i', strtotime($consulta->actualizado)); ?></p>
        </div>

        <?php if ($consulta->fecha_resolucion): ?>
            <div class="formulario__campo">
                <label class="formulario__label">Fecha de Resolución/Cierre:</label>
                <p class="formulario__texto-valor"><?php echo date('d/m/Y H:i', strtotime($consulta->fecha_resolucion)); ?></p>
            </div>
            <?php
                $fecha_creacion_dt = new DateTime($consulta->creado);
                $fecha_resolucion_dt = new DateTime($consulta->fecha_resolucion);
                $intervalo_resolucion = $fecha_creacion_dt->diff($fecha_resolucion_dt);

                $tiempo_total_resolucion = '';
                if ($intervalo_resolucion->y > 0) $tiempo_total_resolucion .= $intervalo_resolucion->y . ' años ';
                if ($intervalo_resolucion->m > 0) $tiempo_total_resolucion .= $intervalo_resolucion->m . ' meses ';
                if ($intervalo_resolucion->d > 0) $tiempo_total_resolucion .= $intervalo_resolucion->d . ' días ';
                if ($intervalo_resolucion->h > 0) $tiempo_total_resolucion .= $intervalo_resolucion->h . ' horas ';
                if ($intervalo_resolucion->i > 0) $tiempo_total_resolucion .= $intervalo_resolucion->i . ' minutos';
                if ($tiempo_total_resolucion === '') $tiempo_total_resolucion = 'pocos segundos';
            ?>
            <div class="formulario__campo">
                <label class="formulario__label">Tiempo Total de Resolución:</label>
                <p class="formulario__texto-valor"><?php echo $tiempo_total_resolucion; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="formulario">
            <div class="formulario__campo">
                <label for="estado" class="formulario__label">Estado de la Consulta:</label>
                <select name="estado" id="estado" class="formulario__input">
                    <option value="pendiente" <?php echo ($consulta->estado === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="en_proceso" <?php echo ($consulta->estado === 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="resuelto" <?php echo ($consulta->estado === 'resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                    <option value="cerrado" <?php echo ($consulta->estado === 'cerrado') ? 'selected' : ''; ?>>Cerrado</option>
                </select>
            </div>
            <input type="submit" name="actualizar_estado" value="Actualizar Estado" class="formulario__submit">
        </form>
    </fieldset>

    <fieldset class="formulario__fieldset">
        <legend class="formulario__legend">Responder al Usuario</legend>
        <form method="POST" class="formulario">
            <input type="hidden" name="action" value="responder_consulta">
            <input type="hidden" name="consulta_id" value="<?php echo htmlspecialchars($consulta->id); ?>">
            
            <?php // TEMPORAL: Depuración para ver el ID que se envía ?>
            <div class="formulario__campo">
                <label for="respuesta_mensaje" class="formulario__label">Tu Respuesta:</label>
                <textarea
                    class="formulario__input"
                    id="respuesta_mensaje"
                    name="respuesta_mensaje"
                    placeholder="Escribe tu respuesta aquí para el usuario..."
                    rows="8"
                ></textarea>
            </div>
            <input type="submit" value="Enviar Respuesta" class="formulario__submit formulario__submit--registrar">
        </form>
    </fieldset>
</div>

<style>
    .formulario__texto-valor {
        padding: 1rem 0;
        font-size: 1.6rem;
        color: #555;
    }
    .mensaje-preformateado {
        white-space: pre-wrap; /* Mantiene saltos de línea y espacios */
        background-color: #f9f9f9;
        border: 1px solid #eee;
        padding: 1.5rem;
        border-radius: 8px;
    }
</style>