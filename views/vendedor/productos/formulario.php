<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <!-- Estado del producto -->
    <div class="formulario__campo">
        <label for="estado" class="formulario__label">Estado del Producto</label>
        <select class="formulario__input" name="estado" id="estado">
            <option value="" disabled selected>Selecciona un tipo</option>
            <option value="disponible" <?php echo ($producto->estado === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
            <?php if(!isset($edicion) && !$edicion): ?>
                <option value="unico" <?php echo ($producto->estado === 'unico') ? 'selected' : ''; ?>>Articulo Unico</option>
            <?php endif; ?>
            <?php if(isset($edicion) && $edicion): ?>
                <option value="agotado" <?php echo ($producto->estado === 'agotado') ? 'selected' : ''; ?>>Agotado</option>
            <?php endif; ?>
        </select>
    </div>

    <!-- Subir imágenes (máximo 5) -->
    <div class="formulario__campo">
        <label class="formulario__label">Imágenes del Producto (Máximo 5)</label>
        <div class="contenedor-imagenes" id="contenedor-imagenes">
            <?php if (!empty($imagenes)): ?>
                <?php foreach($imagenes as $imagen): ?>
                    <div class="formulario__campo contenedor-imagen" data-existente="true">
                        <div class="contenedor-imagen-preview">
                            <div class="imagen-preview">
                                <img src="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.webp" alt="Imagen del producto">
                                <input type="hidden" name="imagenes_existentes[]" value="<?php echo $imagen->id; ?>">
                            </div>
                            <button type="button" class="formulario__accion--secundario eliminar-imagen">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" class="formulario__accion" id="agregar-imagen">
            <i class="fas fa-plus"></i> Añadir imagen
        </button>
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

    <div class="formulario__campo">
        <label for="stock" class="formulario__label">Stock</label>
        <input 
            type="number"
            class="formulario__input"
            id="stock"
            name="stock"
            placeholder="Stock Disponible"
            value="<?php echo $producto->stock ?? ''; ?>"
            min="0"
        >
    </div>

    <!-- Selección de la categoría -->
    <div class="formulario__campo">
        <label for="categoriaId" class="formulario__label">Categoría</label>
        <select class="formulario__input" id="categoriaId" name="categoriaId">
            <option value="" disabled selected>Selecciona una Categoría</option>
            <?php foreach($categorias as $categoria): ?>
            <option value="<?php echo $categoria->id; ?>" <?php echo ($producto->categoriaId == $categoria->id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($categoria->nombre); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Descripción del producto -->
    <div class="formulario__campo">
        <label for="descripcion" class="formulario__label">Descripcion</label>
        <textarea class="formulario__input" name="descripcion" id="descripcion" rows="4"><?php echo $producto->descripcion ?? ''; ?></textarea>
    </div>
</fieldset>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contenedorImagenes = document.getElementById('contenedor-imagenes');
        const btnAgregarImagen = document.getElementById('agregar-imagen');

        let imageCount = 0;
        const maxImages = 5;

        // ------------- Funciones para imágenes -------------
        function crearNuevaImagen() {
            const totalImagenes = document.querySelectorAll('.contenedor-imagen:not([data-existente="true"])').length;
            if (totalImagenes >= maxImages) {
                alert('Máximo de imágenes alcanzado');
                return;
            }
            
            const nuevoContenedor = document.createElement('div');
            nuevoContenedor.className = 'formulario__campo contenedor-imagen';
            
            nuevoContenedor.innerHTML = `
                <div class="contenedor-imagen-preview">
                    <div class="imagen-preview">
                        <span class="imagen-placeholder">+</span>
                        <input 
                            type="file"
                            class="imagen-input"
                            name="nuevas_imagenes[]"
                            accept="image/*"
                            style="display: none;"
                        >
                    </div>
                    <button type="button" class="formulario__accion--secundario eliminar-imagen">Eliminar</button>
                </div>
            `;

            contenedorImagenes.appendChild(nuevoContenedor);
            
            // Configurar eventos
            const previewElement = nuevoContenedor.querySelector('.imagen-preview');
            const inputFile = nuevoContenedor.querySelector('input[type="file"]');
            
            inputFile.addEventListener('change', previewImage);
            previewElement.addEventListener('click', () => inputFile.click());

            // Añadir evento de eliminación
            nuevoContenedor.querySelector('.eliminar-imagen').addEventListener('click', removeImage);
        }

        function previewImage(e) {
            const input = e.target;
            const preview = input.closest('.contenedor-imagen-preview').querySelector('.imagen-preview');
            const placeholder = preview.querySelector('.imagen-placeholder');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let img = preview.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.classList.add('imagen-cargada');
                        preview.insertBefore(img, placeholder);
                    }
                    img.src = e.target.result;
                    img.alt = "Preview";
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage(e) {
            const contenedor = e.target.closest('.formulario__campo');
        
            // Si es una imagen existente, marcar para eliminación
            if (contenedor.dataset.existente) {
                const inputId = contenedor.querySelector('input[type="hidden"]');
                const nuevoInput = document.createElement('input');
                nuevoInput.type = 'hidden';
                nuevoInput.name = 'imagenes_eliminadas[]';
                nuevoInput.value = inputId.value;
                contenedorImagenes.appendChild(nuevoInput);
            }
            
            contenedor.remove();
        }

        // ------------- Manejar lógica de estado y stock -------------
        const estadoSelect = document.getElementById('estado');
        const stockInput = document.getElementById('stock');

        function actualizarCampoStock() {
            if (estadoSelect.value === 'unico') {
                stockInput.value = 1;
                stockInput.disabled = true;
            } else if (estadoSelect.value === 'agotado') {
                stockInput.value = 0;
                stockInput.disabled = false;
            } else {
                stockInput.disabled = false;
            }
        }

        // Actualizar al cargar la página
        actualizarCampoStock();

        // Escuchar cambios en el estado
        estadoSelect.addEventListener('change', actualizarCampoStock);


        // ------------- Event Listeners -------------
        btnAgregarImagen.addEventListener('click', crearNuevaImagen);

        // Delegación de eventos
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar-imagen')) {
                removeImage(e);
            }
        });

        // ------------- Inicialización -------------
        if (window.location.pathname.includes('/crear')) {
            // Comportamiento para creación
            if (imageCount === 0) crearNuevaImagen();
        } else {
            // Configurar imágenes existentes
            document.querySelectorAll('.contenedor-imagen:not([data-existente="true"])').forEach(contenedor => {
                const preview = contenedor.querySelector('.imagen-preview');
                const input = contenedor.querySelector('input[type="file"]');
                if (preview && input) {
                    preview.addEventListener('click', () => input.click());
                    input.addEventListener('change', previewImage);
                }
            });
        }

        document.querySelectorAll('.contenedor-imagen[data-existente]').forEach(contenedor => {
            const btnEliminar = contenedor.querySelector('.eliminar-imagen');
            btnEliminar.addEventListener('click', function(e) {
                contenedor.remove();
            });
        });
    });
</script>