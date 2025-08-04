<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/seguridad/2fa">
        <i class="fa-solid fa-shield-halved"></i>
        Seguridad de Cuenta (2FA)
    </a>

    <a class="dashboard__boton" href="/admin/cambiar-password">
        <i class="fa-solid fa-key"></i>
        Cambiar Contraseña
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/admin/perfil" enctype="multipart/form-data" class="formulario mb-5">
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
                            <img 
                                src="<?php echo $_ENV['HOST'] ?>/img/usuarios/default.png" 
                                alt="Imagen actual del usuario"
                                id="imagenActual"
                                class="imagen-cargada">
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
                    disabled
                >
                <p style="font-size: 1.2rem; color: #666; margin-top: 0.5rem;">El correo electrónico no se puede modificar.</p>
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
        </fieldset>

        <div class="formulario__campo">
            <label class="formulario__label">Cuando se genere un nuevo reporte de publicación</label>
            
            <div class="formulario__opciones-notificacion">
                <label>
                    <input type="checkbox" name="prefs[notif_nuevo_reporte_email]" value="1" <?php echo ($prefs['notif_nuevo_reporte_email'] ?? true) ? 'checked' : ''; ?>>
                    Por Correo Electrónico
                </label>
                <label>
                    <input type="checkbox" name="prefs[notif_nuevo_reporte_sistema]" value="1" <?php echo ($prefs['notif_nuevo_reporte_sistema'] ?? true) ? 'checked' : ''; ?>>
                    Dentro de la Plataforma
                </label>
            </div>

            <div class="formulario__campo">
                <label class="formulario__label">Resumen Diario de Reportes</label>
                <div class="formulario__opciones-notificacion">
                    <label>
                        <input type="checkbox" name="prefs[notif_resumen_diario_email]" value="1" <?php echo ($prefs['notif_resumen_diario_email'] ?? true) ? 'checked' : ''; ?>>
                        Por Correo Electrónico
                    </label>
                    <label>
                        <input type="checkbox" name="prefs[notif_resumen_diario_sistema]" value="1" <?php echo ($prefs['notif_resumen_diario_sistema'] ?? true) ? 'checked' : ''; ?>>
                        Dentro de la Plataforma
                    </label>
                </div>
            </div>
        </div>

        <input type="submit" class="formulario__submit" value="Actualizar Perfil">
    </form>

    <fieldset class="formulario__fieldset">
        <legend class="formulario__legend">Políticas y Documentos</legend>
        <p>Consulta nuestras políticas y gestiona tus datos.</p>
        <a href="/terminos-condiciones" class="formulario__submit" style="background-color: #007bff; max-width: 30rem;">Términos y Condiciones</a>
        <a href="/politica-privacidad" class="formulario__submit" style="background-color: #007bff; max-width: 30rem; margin-top: 1rem;">Política de Privacidad</a>
        <a href="/perfil/exportar-datos" class="formulario__submit" style="background-color: #007bff; max-width: 30rem; margin-top: 1rem;" download>Descargar mis Datos</a>
    </fieldset>
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
</script>