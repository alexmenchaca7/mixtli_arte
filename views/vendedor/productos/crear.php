<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/vendedor/productos">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/vendedor/productos/crear" enctype="multipart/form-data" class="formulario">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <input class="formulario__submit formulario__submit--registrar" type="submit" value="Registrar Producto">
    </form>
</div>

<script>
    const maxImages = 5;
    let imageCount = <?php echo count($imagenes); ?>;

    document.getElementById('imagenes').addEventListener('change', function(event) {
        const previewContainer = document.getElementById('preview');
        const alertContainer = document.getElementById('alertas');
        const files = event.target.files;

        if (imageCount + files.length > maxImages) {
            alertContainer.innerHTML = '<div class="alerta error">No puedes subir más de 5 imágenes.</div>';
            return;
        }

        for (const file of files) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('formulario__preview-img');
                previewContainer.appendChild(img);
            }

            reader.readAsDataURL(file);
            imageCount++;
        }

        // Limpiar el input para permitir agregar más imágenes
        event.target.value = '';
    });

    // Mostrar imágenes cargadas previamente
    <?php if (!empty($imagenes)): ?>
        const previewContainer = document.getElementById('preview');
        <?php foreach ($imagenes as $imagen): ?>
            const img = document.createElement('img');
            img.src = '/img/productos/<?php echo $imagen; ?>.png';
            img.classList.add('formulario__preview-img');
            previewContainer.appendChild(img);
        <?php endforeach; ?>
    <?php endif; ?>
</script>