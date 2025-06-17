<main class="contenedor seccion">
    <h2 class="dashboard__heading t-align-center">Contacta a Soporte</h2>
    <p class="t-align-center">Envíanos tu consulta o problema y te ayudaremos lo antes posible.</p>

    <?php include_once __DIR__ . '/../templates/alertas.php'; // CAMBIO AQUÍ: Eliminado un nivel '../' ?>

    <div class="dashboard__formulario">
        <form class="formulario" method="POST">
            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Detalles de la Consulta</legend>
                
                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Tu Correo Electrónico*</label>
                    <input
                        type="email"
                        class="formulario__input"
                        id="email"
                        name="email"
                        placeholder="Tu Correo Electrónico"
                        value="<?php echo htmlspecialchars($consulta->email ?? ''); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="asunto" class="formulario__label">Asunto*</label>
                    <input
                        type="text"
                        class="formulario__input"
                        id="asunto"
                        name="asunto"
                        placeholder="Asunto de tu consulta"
                        value="<?php echo htmlspecialchars($consulta->asunto ?? ''); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="mensaje" class="formulario__label">Describe tu Problema*</label>
                    <textarea
                        class="formulario__input"
                        id="mensaje"
                        name="mensaje"
                        placeholder="Describe tu problema o consulta a detalle"
                        rows="10"
                    ><?php echo htmlspecialchars($consulta->mensaje ?? ''); ?></textarea>
                </div>
            </fieldset>

            <input type="submit" value="Enviar Consulta" class="formulario__submit">
        </form>
    </div>
</main>