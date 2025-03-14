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
        <label for="imagenes" class="formulario__label">Imágenes del Producto</label>
        <input 
            type="file"
            class="formulario__input formulario__input--file"
            id="imagenes"
            name="imagenes[]"
            accept="image/*"
            multiple
        >
        <div id="preview" class="formulario__preview"></div> <!-- Contenedor para las vistas previas -->
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