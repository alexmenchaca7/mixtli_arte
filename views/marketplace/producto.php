<main class="seccion contenedor contenedor-producto">
    <!-- Imagen del Producto -->
    <div class="producto-imagen">
        <?php if (!empty($producto->imagenes)): ?>
            <div class="slider">
                <div class="slider__contenedor">
                    <?php foreach ($producto->imagenes as $imagen): ?>
                        <picture class="slider__imagen">
                            <source srcset="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.webp" type="image/webp">
                            <source srcset="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.jpg" type="image/jpeg">
                            <img loading="lazy" src="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.jpg" alt="Imagen de <?php echo htmlspecialchars($producto->nombre); ?>">
                        </picture>
                    <?php endforeach; ?>
                </div>
                <button class="slider__boton slider__boton--izquierda" id="prevBtn">&lt;</button>
                <button class="slider__boton slider__boton--derecha" id="nextBtn">&gt;</button>
            </div>
        <?php else: ?>
            <img loading="lazy" src="/img/productos/placeholder.jpg" alt="Imagen no disponible">
        <?php endif; ?>
    </div>

    <!-- Información del Producto -->
    <div class="producto-detalle">
        <div class="producto-header">
            <h1><?= htmlspecialchars($producto->nombre) ?></h1>
            <p class="producto-precio">$<?php echo number_format($producto->precio, 2); ?> MXN</p>
            <p class="producto-fecha">Publicado hace 2 días en Guadalajara, Jal</p>
        </div>

        <h2>Detalles</h2>
        <div class="producto-descripcion">
            <p><?= htmlspecialchars($producto->descripcion) ?></p>
        </div>

        <div class="producto-ubicacion">
            <img src="/build/img/mapa.png" alt="Mapa de ubicación">
            <p>Guadalajara, Jal</p>
            <small>La ubicación es aproximada</small>
        </div>

        <!-- Información del Vendedor -->
        <h2>Información del vendedor</h2>
        <div class="producto-vendedor">
            <div class="vendedor-info">
                <a href="/perfil?id=<?php echo $vendedor->id; ?>">
                    <picture>
                        <?php if (!empty($vendedor->imagen)): ?>
                            <source srcset="/img/usuarios/<?php echo htmlspecialchars($vendedor->imagen); ?>.webp" type="image/webp">
                            <source srcset="/img/usuarios/<?php echo htmlspecialchars($vendedor->imagen); ?>.jpg" type="image/jpeg">
                            <img loading="lazy" src="/img/usuarios/<?php echo htmlspecialchars($vendedor->imagen); ?>.jpg" alt="Imagen de perfil de <?php echo htmlspecialchars($vendedor->nombre); ?>">
                        <?php else: ?>
                            <source srcset="/img/usuarios/default.png" type="image/png">
                            <img loading="lazy" src="/img/usuarios/default.png" alt="Imagen por defecto">
                        <?php endif; ?>
                    </picture>
                </a>
                <div>
                    <p class="vendedor-nombre"><?php echo htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido); ?></p>
                    <p class="vendedor-reputacion">⭐️⭐️⭐️⭐️⭐️ (10 calificaciones)</p>
                </div>
            </div>
        </div>

        <!-- Contacto con el Vendedor -->
        <h2>Contacto</h2>
        <div class="producto-contacto">
            <textarea placeholder="Hola. ¿Sigue disponible?" rows="3"></textarea>
            <button class="boton-rosa-block">Enviar mensaje</button>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('.slider__contenedor');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        let currentIndex = 0;

        function updateSlider() {
            const width = slider.clientWidth;
            slider.style.transform = `translateX(-${currentIndex * width}px)`;
        }

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : slider.children.length - 1;
            updateSlider();
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex < slider.children.length - 1) ? currentIndex + 1 : 0;
            updateSlider();
        });

        window.addEventListener('resize', updateSlider);
    });
</script>