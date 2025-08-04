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
    
    <form method="POST" action="/vendedor/perfil" enctype="multipart/form-data" class="formulario mb-5">
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

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Preferencias de Entrega</legend>
            <div class="formulario__campo">
                <label for="preferencias_entrega" class="formulario__label">Opciones de Entrega/Encuentro</label>
                <textarea 
                    class="formulario__input"
                    placeholder="Describe tus opciones de entrega. Ej: 'Entregas personales solo en la Zona Centro de Zapopan los fines de semana. Envíos a todo el país por paquetería.'"
                    id="preferencias_entrega"
                    name="preferencias_entrega"
                    rows="4"
                ><?php echo s($usuario->preferencias_entrega); ?></textarea>
            </div>
        </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Preferencias de Notificación</legend>
            <p>Elige cómo quieres recibir las notificaciones de la plataforma.</p>
            
            <?php $prefs = json_decode($usuario->preferencias_notificaciones ?? '{}', true); ?>

            <div class="formulario__campo">
                <label class="formulario__label">Alertas de Stock Crítico (3 unidades)</label>
                <div class="formulario__opciones-notificacion">
                    <label>
                        <input type="checkbox" name="prefs[notif_stock_critico_email]" value="1" <?php echo ($prefs['notif_stock_critico_email'] ?? true) ? 'checked' : ''; ?>>
                        Por Correo Electrónico
                    </label>
                    <label>
                        <input type="checkbox" name="prefs[notif_stock_critico_sistema]" value="1" <?php echo ($prefs['notif_stock_critico_sistema'] ?? true) ? 'checked' : ''; ?>>
                        Dentro de la Plataforma
                    </label>
                </div>
            </div>
        </fieldset>

        <input type="submit" class="formulario__submit" value="Actualizar Perfil">
    </form>

    <fieldset class="formulario__fieldset">
        <legend class="formulario__legend">Políticas y Documentos</legend>
        <p>Consulta nuestras políticas y gestiona tus datos.</p>
        <a href="/terminos-condiciones" class="formulario__submit" style="background-color: #007bff; max-width: 30rem;">Términos y Condiciones</a>
        <a href="/politica-privacidad" class="formulario__submit" style="background-color: #007bff; max-width: 30rem; margin-top: 1rem;">Política de Privacidad</a>
        <a href="/perfil/exportar-datos" class="formulario__submit" style="background-color: #007bff; max-width: 30rem; margin-top: 1rem;" download>Descargar mis Datos</a>
    </fieldset>

    <fieldset class="formulario__fieldset">
        <legend class="formulario__legend">Zona de Peligro</legend>
        <p>La eliminación de tu cuenta es una acción permanente y no se puede deshacer. Se borrarán todos tus datos personales, productos, historial de ventas y mensajes. Se enviará un correo de confirmación para completar el proceso.</p>
        <form action="/perfil/solicitar-eliminacion" method="POST" onsubmit="alert('Se ha enviado un correo al correo proporcionado para confirmar la eliminación de tu cuenta.');">
            <input type="submit" class="formulario__submit formulario__submit--peligro" value="Solicitar Eliminación de Cuenta">
        </form>
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

    document.querySelector('form').addEventListener('submit', function(e) {
        const tipos = ['residencial', 'comercial'];
        let errores = [];

        tipos.forEach(tipo => {
            const campos = [
                document.getElementById(`calle_${tipo}`),
                document.getElementById(`colonia_${tipo}`),
                document.getElementById(`codigo_postal_${tipo}`),
                document.getElementById(`ciudad_${tipo}`),
                document.getElementById(`estado_${tipo}`)
            ];

            const camposLlenos = campos.some(campo => campo.value.trim() !== '');
            const camposVacios = campos.some(campo => campo.value.trim() === '');

            if (camposLlenos && camposVacios) {
                errores.push(`Todos los campos de la dirección ${tipo} son requeridos.`);
            }
        });

        if (errores.length > 0) {
            e.preventDefault();
            alert(errores.join('\n'));
        }
    });
</script>