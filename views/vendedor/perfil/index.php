<?php
// Función helper para obtener valores de dirección
function obtenerDireccion($direcciones, $tipo, $campo) {
    foreach($direcciones as $direccion) {
        if($direccion->tipo === $tipo) {
            return htmlspecialchars($direccion->$campo ?? '');
        }
    }
    return '';
}
?>

<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/seguridad/2fa">
        <i class="fa-solid fa-shield-halved"></i>
        Seguridad de Cuenta (2FA)
    </a>

    <a class="dashboard__boton" href="/vendedor/cambiar-password">
        <i class="fa-solid fa-key"></i>
        Cambiar Contraseña
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/vendedor/perfil" enctype="multipart/form-data" class="formulario">
        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Información Personal</legend>

            <div class="formulario__campo">
                <label for="nombre" class="formulario__label">Nombre*</label>
                <input 
                    type="text"
                    class="formulario__input"
                    placeholder="Nombre"
                    id="nombre"
                    name="nombre"
                    value="<?php echo $usuario->nombre; ?>"
                    required
                >
            </div>

            <div class="formulario__campo">
                <label for="apellido" class="formulario__label">Apellido*</label>
                <input 
                    type="text"
                    class="formulario__input"
                    placeholder="Apellido"
                    id="apellido"
                    name="apellido"
                    value="<?php echo $usuario->apellido; ?>"
                    required
                >
            </div>

            <div class="formulario__campo">
                <label for="fecha_nacimiento" class="formulario__label">Fecha de Nacimiento*</label>
                <input 
                    type="date"
                    class="formulario__input"
                    id="fecha_nacimiento"
                    name="fecha_nacimiento"
                    value="<?php echo $usuario->fecha_nacimiento; ?>"
                    max="<?php echo $fecha_hoy; ?>"
                    required
                >
            </div>

            <div class="formulario__campo">
                <label for="sexo" class="formulario__label">Sexo*</label>
                <select class="formulario__input" name="sexo" id="sexo" required>
                    <option value="" disabled <?php echo empty($usuario->sexo) ? 'selected' : ''; ?>>--Seleccione--</option>
                    <option value="Femenino" <?php echo ($usuario->sexo === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="Masculino" <?php echo ($usuario->sexo === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                </select>
            </div>

            <div class="formulario__campo">
                <label class="formulario__label">Imagen de Perfil</label>
                <div class="contenedor-imagen-preview">
                    <div class="imagen-preview" id="imagenPreview">
                        <?php if(!empty($usuario->imagen_actual)): ?>
                            <picture>
                                <source 
                                    srcset="<?php echo $_ENV['HOST'] ?>/img/usuarios/<?php echo $usuario->imagen_actual ?>.webp" 
                                    type="image/webp">
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
                <label for="email" class="formulario__label">Email*</label>
                <input 
                    type="email"
                    class="formulario__input"
                    placeholder="Email"
                    id="email"
                    name="email"
                    value="<?php echo $usuario->email; ?>"
                    required
                >
            </div>

            <div class="formulario__campo">
                <label for="telefono" class="formulario__label">Teléfono</label>
                <input 
                    type="tel"
                    class="formulario__input"
                    placeholder="Teléfono"
                    id="telefono"
                    name="telefono"
                    value="<?php echo $usuario->telefono; ?>"
                >
            </div>

            <div class="formulario__campo">
                <label for="biografia" class="formulario__label">Biografía</label>
                <textarea 
                    class="formulario__input"
                    placeholder="Describe tu experiencia como vendedor"
                    id="biografia"
                    name="biografia"
                    rows="4"
                ><?php echo $usuario->biografia; ?></textarea>
            </div>
        </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Direcciones</legend>

            <div id="direccion-residencial">
                <h3 class="formulario__sublegend">Dirección Residencial</h3>
                
                <div class="formulario__campo">
                    <label for="calle_residencial" class="formulario__label">Calle y número</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Av. Principal #123"
                        id="calle_residencial"
                        name="calle_residencial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'calle'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="colonia_residencial" class="formulario__label">Colonia</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Centro"
                        id="colonia_residencial"
                        name="colonia_residencial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'colonia'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="codigo_postal_residencial" class="formulario__label">Código Postal</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: 06000"
                        id="codigo_postal_residencial"
                        name="codigo_postal_residencial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'codigo_postal'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="ciudad_residencial" class="formulario__label">Ciudad</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Ciudad de México"
                        id="ciudad_residencial"
                        name="ciudad_residencial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'ciudad'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="estado_residencial" class="formulario__label">Estado</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: CDMX"
                        id="estado_residencial"
                        name="estado_residencial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'estado'); ?>"
                    >
                </div>
            </div>

            <div id="direccion-comercial">
                <h3 class="formulario__sublegend">Dirección Comercial</h3>
                
                <div class="formulario__campo">
                    <label for="calle_comercial" class="formulario__label">Calle y número</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Calle Comercial #456"
                        id="calle_comercial"
                        name="calle_comercial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'comercial', 'calle'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="colonia_comercial" class="formulario__label">Colonia</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Zona Industrial"
                        id="colonia_comercial"
                        name="colonia_comercial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'comercial', 'colonia'); ?>"
                    >
                </div>
                    
                <div class="formulario__campo">
                    <label for="codigo_postal_comercial" class="formulario__label">Código Postal</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: 44100"
                        id="codigo_postal_comercial"
                        name="codigo_postal_comercial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'comercial', 'codigo_postal'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="ciudad_comercial" class="formulario__label">Ciudad</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Guadalajara"
                        id="ciudad_comercial"
                        name="ciudad_comercial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'comercial', 'ciudad'); ?>"
                    >
                </div>

                <div class="formulario__campo">
                    <label for="estado_comercial" class="formulario__label">Estado</label>
                    <input 
                        type="text"
                        class="formulario__input"
                        placeholder="Ej: Jalisco"
                        id="estado_comercial"
                        name="estado_comercial"
                        value="<?php echo obtenerDireccion($direcciones ?? [], 'comercial', 'estado'); ?>"
                    >
                </div>
            </div>
        </fieldset>

        <input type="submit" class="formulario__submit" value="Actualizar Perfil">
    </form>
</div>

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

    // Mostrar/ocultar campo biografía según el rol seleccionado
    document.getElementById('rol').addEventListener('change', function() {
        const campoBiografia = document.getElementById('campo-biografia');
        if (this.value === 'vendedor') {
            campoBiografia.style.display = 'block';
        } else {
            campoBiografia.style.display = 'none';
        }
    });

    // Verificar el rol al cargar la página y mostrar el campo si es necesario
    document.addEventListener('DOMContentLoaded', function() {
        const rolSelect = document.getElementById('rol');
        const campoBiografia = document.getElementById('campo-biografia');
        
        if (rolSelect.value === 'vendedor') {
            campoBiografia.style.display = 'block';
        } else {
            campoBiografia.style.display = 'none';
        }
    });

    document.getElementById('rol').addEventListener('change', function() {
        const fieldsetDirecciones = document.getElementById('fieldset-direcciones');
        const direccionComercial = document.getElementById('direccion-comercial');
        
        if(this.value === 'comprador' || this.value === 'vendedor') {
            fieldsetDirecciones.style.display = 'block';
            direccionComercial.style.display = this.value === 'vendedor' ? 'block' : 'none';
        } else {
            fieldsetDirecciones.style.display = 'none';
        }
    });

    // Mostrar campos según el rol al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const rolSelect = document.getElementById('rol');
        const fieldsetDirecciones = document.getElementById('fieldset-direcciones');
        const direccionComercial = document.getElementById('direccion-comercial');
        
        if(rolSelect.value === 'comprador' || rolSelect.value === 'vendedor') {
            fieldsetDirecciones.style.display = 'block';
            direccionComercial.style.display = rolSelect.value === 'vendedor' ? 'block' : 'none';
        }
    });

</script>