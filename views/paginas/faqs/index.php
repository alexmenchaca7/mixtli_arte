<main class="seccion contenedor">
    <h2 class="dashboard__heading">Preguntas Frecuentes</h2>

    <div class="faq-container">
        <div class="faq-list">
            <?php if (!empty($faqs)): ?>
                <?php 
                $faqsPorCategoria = [];
                foreach ($faqs as $faq) {
                    $categoriaNombre = $faq->categoria->nombre ?? 'General'; 
                    $faqsPorCategoria[$categoriaNombre][] = $faq;
                }
                ?>

                <?php foreach ($faqsPorCategoria as $categoriaNombre => $faqsEnCategoria): ?>
                    <div class="faq-category">
                        <h3><?= htmlspecialchars($categoriaNombre) ?></h3>
                        <?php foreach ($faqsEnCategoria as $faq): ?>
                            <details class="faq-item">
                                <summary class="faq-question">
                                    <?= htmlspecialchars($faq->pregunta) ?>
                                </summary>
                                <div class="faq-answer">
                                    <p><?= nl2br(htmlspecialchars($faq->respuesta)) ?></p> </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="t-align-center">No hay preguntas frecuentes disponibles por el momento.</p>
            <?php endif; ?>
        </div>

        <?php if(is_auth()): ?>
            <div class="faq-form-section">
                <h3 class="dashboard__subtitle">¿No encuentras tu pregunta?</h3>
                <p>Envíanos tu consulta y te responderemos lo antes posible.</p>

                <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

                <form method="POST" class="formulario">
                    <div class="formulario__campo">
                        <label for="pregunta" class="formulario__label">Tu Pregunta*</label>
                        <textarea 
                            class="formulario__input"
                            placeholder="Escribe tu pregunta aquí..."
                            id="pregunta"
                            name="pregunta"
                            rows="5"
                        ><?php echo htmlspecialchars($preguntaUsuario->pregunta ?? ''); ?></textarea>
                    </div>

                    <div class="formulario__campo">
                        <label for="categoriaFaqId" class="formulario__label">Categoría (opcional)</label>
                        <select class="formulario__input" name="categoriaFaqId" id="categoriaFaqId">
                            <option value="" selected>-- Selecciona una categoría --</option>
                            <?php foreach($categorias as $categoriaFaq): ?>
                                <option value="<?= $categoriaFaq->id ?>" <?= ($preguntaUsuario->categoriaFaqId == $categoriaFaq->id) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($categoriaFaq->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="submit" class="formulario__submit" value="Enviar Pregunta">
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Estilos específicos para la página de FAQs */
    .faq-container {
        display: flex;
        flex-direction: column;
        gap: 4rem;
    }
    @media (min-width: 768px) {
        .faq-container {
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
        }
        .faq-list {
            flex: 2; /* Ocupa 2/3 del espacio */
        }
        .faq-form-section {
            flex: 1; /* Ocupa 1/3 del espacio */
            padding-left: 2rem;
            border-left: 1px solid #eee;
        }
    }

    .faq-category {
        margin-bottom: 3rem;
    }

    .faq-category h3 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 2rem;
        border-bottom: 2px solid #eee;
        padding-bottom: 1rem;
    }

    .faq-item {
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .faq-question {
        display: block;
        padding: 1.5rem 2rem;
        font-weight: bold;
        font-size: 1.8rem;
        color: #333;
        cursor: pointer;
        position: relative;
        list-style: none; /* Oculta el marcador de lista por defecto */
    }

    /* Estilo para el ícono de expandir/colapsar */
    .faq-question::marker {
        display: none;
    }
    .faq-question::after {
        content: '\25B6'; /* Triángulo derecho */
        font-size: 1.5rem;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%) rotate(0deg);
        transition: transform 0.3s ease;
    }

    details[open] .faq-question::after {
        transform: translateY(-50%) rotate(90deg); /* Gira 90 grados al abrir */
    }

    .faq-answer {
        padding: 0 2rem 1.5rem;
        font-size: 1.6rem;
        color: #555;
        line-height: 1.6;
        border-top: 1px solid #eee;
        margin-top: -1px; /* Para solapar el borde */
    }

    .faq-answer p {
        margin: 0;
    }
</style>