<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <?php
        // Variable para controlar si el formulario debe estar deshabilitado
        $esUnicoAgotadoPermanente = (isset($edicion) && $producto->tipo_original === 'unico' && $producto->estado === 'agotado');
    ?>

    <!-- Estado del producto -->
    <div class="formulario__campo">
        <label for="estado" class="formulario__label">Estado del Producto</label>
        <select class="formulario__input" name="estado" id="estado" data-estado-actual="<?php echo $producto_estado_actual ?? ''; ?>" data-tipo-original="<?php echo $producto->tipo_original ?? 'disponible'; ?>" <?php if($esUnicoAgotadoPermanente) echo 'disabled'; ?>>
             <option value="" disabled>-- Selecciona un tipo --</option>

            <?php if (isset($edicion) && $edicion): ?>
                <?php if ($producto->tipo_original === 'unico'): ?>
                    <option value="unico" <?php echo ($producto->estado === 'unico') ? 'selected' : ''; ?>>Articulo Unico</option>
                    <option value="agotado" <?php echo ($producto->estado === 'agotado') ? 'selected' : ''; ?>>Agotado (Acción Permanente)</option>
                <?php else: ?>
                    <option value="disponible" <?php echo ($producto->estado === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="agotado" <?php echo ($producto->estado === 'agotado') ? 'selected' : ''; ?>>Agotado</option>
                <?php endif; ?>
            <?php else: ?>
                <option value="disponible" selected>Disponible</option>
                <option value="unico">Articulo Unico</option>
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
            <?php if($esUnicoAgotadoPermanente) echo 'disabled'; ?>
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
            min="0"
            value="<?php echo $producto->precio ?? ''; ?>" 
            <?php if($esUnicoAgotadoPermanente) echo 'disabled'; ?>
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
            min="0"
            value="<?php echo $producto->stock ?? ''; ?>"
            <?php if($esUnicoAgotadoPermanente) echo 'disabled'; ?>
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

<div id="agotadoModal" class="modal-eliminar">
    <div class="modal-eliminar__content">
        <h3>Acción Permanente</h3>
        <p id="modalMessageAgotado">Estás a punto de marcar un 'Artículo Único' como 'Agotado'. Esta acción es irreversible y el producto se moverá a tu historial permanentemente, sin opción de reabastecerlo. ¿Deseas continuar?</p>
        <div class="modal-eliminar__acciones">
            <button id="cancelAgotado" type="button" class="modal-eliminar__cancel">Cancelar</button>
            <button id="confirmAgotado" type="button" class="modal-eliminar__confirm">Sí, Continuar</button>
        </div>
    </div>
</div>

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
        const form = document.querySelector('.formulario');
        const estadoSelect = document.getElementById('estado');
        const stockInput = document.getElementById('stock');

        // Obtenemos los datos desde los atributos data-*
        const estadoActual = estadoSelect.dataset.estadoActual;
        const tipoOriginal = estadoSelect.dataset.tipoOriginal;

        // --- LÓGICA DEL MODAL ---
        if (form) {
            form.addEventListener('submit', function(e) {
                // Condición para mostrar el modal:
                // 1. Es un artículo originalmente único.
                // 2. Su estado actual NO es 'agotado'.
                // 3. El nuevo estado seleccionado ES 'agotado'.
                // 4. El formulario aún no tiene la confirmación.
                if (tipoOriginal === 'unico' && estadoActual !== 'agotado' && estadoSelect.value === 'agotado' && !form.querySelector('[name="confirmacion_agotado_unico"]')) {
                    e.preventDefault(); // Detenemos el envío
                    
                    // Mostramos el modal de confirmación de 'agotado'
                    const modal = document.getElementById('agotadoModal');
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        const confirmAgotadoBtn = document.getElementById('confirmAgotado');
        if (confirmAgotadoBtn) {
            confirmAgotadoBtn.addEventListener('click', function() {
                // Creamos un input oculto para enviar la confirmación al backend
                const confirmationInput = document.createElement('input');
                confirmationInput.type = 'hidden';
                confirmationInput.name = 'confirmacion_agotado_unico';
                confirmationInput.value = 'true';
                form.appendChild(confirmationInput);
                
                // Ahora sí, enviamos el formulario
                form.submit();
            });
        }
        
        const cancelAgotadoBtn = document.getElementById('cancelAgotado');
        if(cancelAgotadoBtn) {
            cancelAgotadoBtn.addEventListener('click', function() {
                const modal = document.getElementById('agotadoModal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                // Opcional: revertir la selección del select al estado original
                estadoSelect.value = estadoActual; 
            });
        }

        // --- FINALIZA LOGICA DEL MODAL ---

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

        function actualizarEstadoPorStock() {
            const stockValue = parseInt(stockInput.value, 10);
            const tipoOriginal = estadoSelect.dataset.tipoOriginal;

            // Solo aplica a productos que no son de tipo 'unico'
            if (tipoOriginal !== 'unico') {
                if (stockValue === 0) {
                    estadoSelect.value = 'agotado';
                } else if (stockValue > 0 && estadoSelect.value === 'agotado') {
                    // Si se añade stock a un producto que estaba agotado
                    estadoSelect.value = 'disponible';
                }
            }
        }

        // Actualizar al cargar la página
        actualizarCampoStock();

        
        // ------------- Event Listeners -------------
        // Escuchar cambios en el estado
        estadoSelect.addEventListener('change', actualizarCampoStock);

        // Escuchar cambios en el campo de stock para actualizar el estado
        stockInput.addEventListener('input', actualizarEstadoPorStock);

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