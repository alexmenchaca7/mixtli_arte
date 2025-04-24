<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <!-- Estado del producto -->
    <div class="formulario__campo">
        <label for="estado" class="formulario__label">Tipo de Producto</label>
        <select class="formulario__input" name="estado" id="estado">
            <option value="" disabled selected>Selecciona un tipo</option>
            <option value="disponible" <?php echo ($producto->estado === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
            <option value="unico" <?php echo ($producto->estado === 'unico') ? 'selected' : ''; ?>>Articulo Unico</option>
        </select>
    </div>

    <!-- Subir imágenes (máximo 5) -->
    <div class="formulario__campo">
        <label class="formulario__label">Imágenes del Producto (Máximo 5)</label>
        <div class="contenedor-imagenes-preview" id="previewContainer">
            <?php if(isset($imagenes_existentes) && !empty($imagenes_existentes)): ?>
                <?php foreach($imagenes_existentes as $imagen): ?>
                    <div class="imagen-preview">
                        <picture>
                            <source srcset="<?php echo $_ENV['HOST'] ?>/img/productos/<?php echo $imagen->url ?>.webp" type="image/webp">
                            <img src="<?php echo $_ENV['HOST'] ?>/img/productos/<?php echo $imagen->url ?>.png" alt="Imagen del producto">
                        </picture>
                        <label class="eliminar-imagen">
                            <input type="checkbox" name="eliminar_imagenes[]" value="<?php echo $imagen->id ?>"> Eliminar
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Contenedor para nuevas imágenes -->
            <div class="imagen-preview" id="imagenPreview">
                <span class="imagen-placeholder">+</span>
                <input 
                    type="file"
                    class="imagen-input"
                    id="imagenes"
                    name="imagenes[]"
                    accept="image/*"
                    multiple
                    style="display: none;"
                    onchange="previewImages(event)"
                >
            </div>
        </div>
    </div>
    
    <!-- Nombre del producto -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
        type="text"
        class="formulario__input"
        id="nombre"
        name="nombre"
        placeholder="Nombre Producto"
        value="<?php echo $producto->nombre ?? ''; ?>"
        >
    </div>
    
    <!-- Precio del producto -->
    <div class="formulario__campo">
        <label for="precio" class="formulario__label">Precio</label>
        <input 
            type="number"
            class="formulario__input"
            id="precio"
            name="precio"
            placeholder="Precio Producto"
            value="<?php echo $producto->precio ?? ''; ?>"
            min="0"
        >
    </div>

    <!-- Selección de la categoría -->
    <div class="formulario__campo">
        <label for="categoria" class="formulario__label">Categoría</label>
        <select class="formulario__input" id="categoria" name="categoria">
            <option value="" disabled selected>Selecciona una Categoría</option>

            <!-- Aquí irían las categorías dinámicamente cargadas desde la base de datos -->
        </select>
    </div>
    
    <!-- Descripción del producto -->
    <div class="formulario__campo">
        <label for="descripcion" class="formulario__label">Descripcion</label>
        <textarea class="formulario__input" name="descripcion" id="descripcion" rows="4"><?php echo $producto->descripcion ?? ''; ?></textarea>
    </div>
</fieldset>

<script>
// Array para almacenar las imágenes seleccionadas
let imagenesSeleccionadas = [];

function previewImages(event) {
    const previewContainer = document.getElementById('previewContainer');
    const files = event.target.files;
    const maxFiles = 5;
    const existingImages = document.querySelectorAll('.imagen-preview:not(#imagenPreview)').length;
    
    // Verificar límite de imágenes
    if (files.length + existingImages > maxFiles) {
        alert('Máximo 5 imágenes permitidas');
        event.target.value = '';
        return;
    }

    // Limpiar previsualizaciones temporales (no las existentes)
    document.querySelectorAll('.nueva-imagen-preview').forEach(el => el.remove());
    
    // Procesar cada archivo
    Array.from(files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            // Crear elemento para previsualización
            const div = document.createElement('div');
            div.className = 'imagen-preview nueva-imagen-preview';
            div.dataset.index = imagenesSeleccionadas.length;
            div.innerHTML = `
                <img src="${e.target.result}" alt="Previsualización">
                <button type="button" class="eliminar-previa" onclick="eliminarPrevia(${imagenesSeleccionadas.length})">×</button>
            `;
            
            // Insertar antes del contenedor de añadir imágenes
            previewContainer.insertBefore(div, document.getElementById('imagenPreview'));
            
            // Agregar al array de imágenes seleccionadas
            imagenesSeleccionadas.push({
                file: file,
                preview: div
            });
        };
        reader.readAsDataURL(file);
    });
    
    // Resetear el input para permitir nuevas selecciones
    event.target.value = '';
}

function eliminarPrevia(index) {
    // Eliminar del array
    imagenesSeleccionadas.splice(index, 1);
    
    // Reindexar los elementos restantes
    imagenesSeleccionadas.forEach((img, i) => {
        img.preview.dataset.index = i;
        img.preview.querySelector('button').setAttribute('onclick', `eliminarPrevia(${i})`);
    });
    
    // Actualizar vista
    actualizarVistaPrevia();
}

function actualizarVistaPrevia() {
    const previewContainer = document.getElementById('previewContainer');
    
    // Eliminar todas las previsualizaciones temporales
    document.querySelectorAll('.nueva-imagen-preview').forEach(el => el.remove());
    
    // Volver a agregar las imágenes del array
    imagenesSeleccionadas.forEach((img, index) => {
        const div = document.createElement('div');
        div.className = 'imagen-preview nueva-imagen-preview';
        div.dataset.index = index;
        div.innerHTML = `
            <img src="${URL.createObjectURL(img.file)}" alt="Previsualización">
            <button type="button" class="eliminar-previa" onclick="eliminarPrevia(${index})">×</button>
        `;
        previewContainer.insertBefore(div, document.getElementById('imagenPreview'));
    });
}

// Click abre el selector
document.getElementById('imagenPreview').addEventListener('click', function() {
    document.getElementById('imagenes').click();
});

// Efectos hover
document.getElementById('imagenPreview').addEventListener('mouseenter', function() {
    this.style.opacity = '0.8';
    this.querySelector('.imagen-placeholder').style.fontSize = '4rem';
});

document.getElementById('imagenPreview').addEventListener('mouseleave', function() {
    this.style.opacity = '1';
    this.querySelector('.imagen-placeholder').style.fontSize = '3.5rem';
});

// Antes de enviar el formulario, crear un FormData con todas las imágenes
document.querySelector('form').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    
    // Agregar todas las imágenes seleccionadas al FormData
    imagenesSeleccionadas.forEach(img => {
        formData.append('imagenes[]', img.file);
    });
    
    // Aquí podrías hacer un envío AJAX o permitir que el formulario se envíe normalmente
    // Para AJAX:
    // e.preventDefault();
    // fetch(this.action, {
    //     method: 'POST',
    //     body: formData
    // }).then(...);
});
</script>