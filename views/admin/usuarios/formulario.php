<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            placeholder="Nombre"
            id="nombre"
            name="nombre"
            value="<?php echo $usuario->nombre; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="apellido" class="formulario__label">Apellido</label>
        <input 
            type="text"
            class="formulario__input"
            placeholder="Apellido"
            id="apellido"
            name="apellido"
            value="<?php echo $usuario->apellido; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="fecha_nacimiento" class="formulario__label">Fecha de Nacimiento</label>
        <input 
            type="date"
            class="formulario__input"
            id="fecha_nacimiento"
            name="fecha_nacimiento"
            value="<?php echo $usuario->fecha_nacimiento; ?>"
            max="<?php echo $fecha_hoy; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="sexo" class="formulario__label">Sexo</label>
        <select class="formulario__input" name="sexo" id="sexo">
            <option value="" disabled <?php echo empty($usuario->sexo) ? 'selected' : ''; ?>>--Seleccione--</option>
            <option value="Femenino" <?php echo ($usuario->sexo === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
            <option value="Masculino" <?php echo ($usuario->sexo === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
        </select>
    </div>

    <div class="formulario__campo">
        <label for="rol" class="formulario__label">Tipo de usuario</label>
        <select class="formulario__input" name="rol" id="rol">
            <option value="" disabled <?php echo empty($usuario->rol) ? 'selected' : ''; ?>>--Seleccione--</option>
            <option value="comprador" <?php echo ($usuario->rol === 'comprador') ? 'selected' : ''; ?>>Comprador</option>
            <option value="vendedor" <?php echo ($usuario->rol === 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
            <option value="admin" <?php echo ($usuario->rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
        </select>
    </div>

    <div class="formulario__campo">
        <label class="formulario__label">Imagen de Perfil</label>
        <div class="contenedor-imagen-preview">
            <div class="imagen-preview" id="imagenPreview">
                <?php if(!empty($usuario->imagen_actual)): ?>
                    <picture>
                        <!-- Versión WebP -->
                        <source 
                            srcset="<?php echo $_ENV['HOST'] ?>/img/usuarios/<?php echo $usuario->imagen_actual ?>.webp" 
                            type="image/webp">
                        <!-- Fallback PNG -->
                        <img 
                            src="<?php echo $_ENV['HOST'] ?>/img/usuarios/<?php echo $usuario->imagen_actual ?>.png" 
                            alt="Imagen actual del usuario"
                            id="imagenActual"
                            class="imagen-cargada">
                    </picture>
                <?php else: ?>
                    <span class="imagen-placeholder">+</span>
                <?php endif; ?>
                <input 
                    type="file"
                    class="imagen-input"
                    id="imagen"
                    name="imagen"
                    accept="image/*"
                    style="display: none;"
                    onchange="previewImage(event)"
                >
            </div>
            
            <?php if(!empty($usuario->imagen_actual)): ?>
                <div class="formulario__imagen-info">
                    <label class="eliminar-imagen">
                        <input type="checkbox" name="eliminar_imagen"> Eliminar imagen actual
                    </label>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="formulario__campo">
        <label for="email" class="formulario__label">Email</label>
        <input 
            type="email"
            class="formulario__input"
            placeholder="Email"
            id="email"
            name="email"
            value="<?php echo $usuario->email; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="telefono" class="formulario__label">Telefono</label>
        <input 
            type="telefono"
            class="formulario__input"
            placeholder="Telefono"
            id="telefono"
            name="telefono"
            value="<?php echo $usuario->telefono; ?>"
        >
    </div>
</fieldset>

<script>
    function previewImage(event) {
        const preview = document.getElementById('imagenPreview');
        const placeholder = preview.querySelector('.imagen-placeholder');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Actualizar vista previa
                preview.style.backgroundImage = `url(${e.target.result})`;
                if(placeholder) placeholder.style.display = 'none';
                
                // Crear/actualizar elemento picture
                const picture = preview.querySelector('picture') || document.createElement('picture');
                const sourceWebp = picture.querySelector('source') || document.createElement('source');
                const img = picture.querySelector('img') || document.createElement('img');
                
                sourceWebp.srcset = URL.createObjectURL(file);
                sourceWebp.type = 'image/webp';
                
                img.src = URL.createObjectURL(file);
                img.alt = "Nueva imagen seleccionada";
                img.classList.add('imagen-cargada');
                
                if(!picture.parentElement) {
                    picture.appendChild(sourceWebp);
                    picture.appendChild(img);
                    preview.appendChild(picture);
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // Click en cualquier parte del contenedor abre el selector
    document.getElementById('imagenPreview').addEventListener('click', function() {
        document.getElementById('imagen').click();
    });

    // Hover effects
    document.getElementById('imagenPreview').addEventListener('mouseenter', function() {
        this.style.opacity = '0.8';
        this.querySelector('.imagen-placeholder')?.style.setProperty('font-size', '4rem', 'important');
    });
    
    document.getElementById('imagenPreview').addEventListener('mouseleave', function() {
        this.style.opacity = '1';
        this.querySelector('.imagen-placeholder')?.style.setProperty('font-size', '3.5rem', 'important');
    });
</script>