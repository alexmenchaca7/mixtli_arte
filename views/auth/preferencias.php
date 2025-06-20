<main class="auth">
    <p class="auth__texto">Ayúdanos a personalizar tu experiencia seleccionando las categorías que más te interesan.</p>

    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <form method="POST" class="formulario">
        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Categorías de Artesanías</legend>
            <div class="preferencias__grid">
                <?php foreach($categorias as $categoria): ?>
                    <label class="preferencia__label">
                        <input type="checkbox" name="categorias[]" value="<?php echo $categoria->id; ?>">
                        <?php echo htmlspecialchars($categoria->nombre); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        
        <input type="submit" class="formulario__submit" value="Guardar y Continuar">

        <div class="acciones">
            <a href="/marketplace" class="acciones__enlace">Omitir por ahora</a>
        </div>
    </form>
</main>

<style>
    .preferencias__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .preferencia__label {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, border-color 0.3s;
    }
    .preferencia__label:hover {
        background-color: #f9f9f9;
        border-color: #EE4BBA;
    }
    .preferencia__label input[type="checkbox"] {
        width: auto;
        margin-bottom: 0;
    }
</style>