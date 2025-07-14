<main class="contenedor seccion">
    <div class="dashboard__contenedor-boton" style="margin-bottom: 2rem;">
        <a class="dashboard__boton" href="/comprador/perfil">
            <i class="fa-solid fa-circle-arrow-left"></i>
            Volver
        </a>
        <div class="botones-perfil" style="display: flex; justify-content: space-between;">
            <a href="/comprador/perfil/cambiar-password" class="dashboard__boton" style="background-color: #555;">Cambiar Contraseña</a>
            <a href="/seguridad/2fa" class="dashboard__boton" style="background-color: #555;">Seguridad (2FA)</a>
        </div>
    </div>

    <div class="formulario-contenedor">
        <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
        
        <form method="POST" action="/comprador/perfil/editar" enctype="multipart/form-data" class="formulario mb-5">
            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Información Personal</legend>
                <div class="formulario__campo">
                    <label class="formulario__label">Imagen de Perfil</label>
                    <div class="contenedor-imagen-preview">
                        <div class="imagen-preview" id="imagenPreview">
                            <?php if(!empty($usuario->imagen_actual)): ?>
                                <picture>
                                    <source srcset="<?php echo $_ENV['HOST'] ?>/img/usuarios/<?php echo $usuario->imagen_actual ?>.webp" type="image/webp">
                                    <img src="<?php echo $_ENV['HOST'] ?>/img/usuarios/<?php echo $usuario->imagen_actual ?>.png" alt="Imagen actual del usuario" id="imagenActual" class="imagen-cargada">
                                </picture>
                            <?php else: ?>
                                <img src="<?php echo $_ENV['HOST'] ?>/img/usuarios/default.png" alt="Imagen por defecto" id="imagenActual" class="imagen-cargada">
                            <?php endif; ?>
                        </div>
                        <input type="file" class="imagen-input" id="imagen" name="imagen" accept="image/*" style="display: none;" onchange="previewImage(event)">
                        
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
                    <label for="nombre" class="formulario__label">Nombre*</label>
                    <input type="text" class="formulario__input" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario->nombre ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="apellido" class="formulario__label">Apellido*</label>
                    <input type="text" class="formulario__input" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario->apellido ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Email*</label>
                    <input type="email" class="formulario__input" id="email" name="email" value="<?php echo htmlspecialchars($usuario->email ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="telefono" class="formulario__label">Teléfono</label>
                    <input type="tel" class="formulario__input" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario->telefono ?? ''); ?>">
                </div>
            </fieldset>

            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Dirección Residencial</legend>
                <div class="formulario__campo">
                    <label for="calle_residencial" class="formulario__label">Calle y número</label>
                    <input type="text" class="formulario__input" id="calle_residencial" name="calle_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'calle'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="colonia_residencial" class="formulario__label">Colonia</label>
                    <input type="text" class="formulario__input" id="colonia_residencial" name="colonia_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'colonia'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="codigo_postal_residencial" class="formulario__label">Código Postal</label>
                    <input type="text" class="formulario__input" id="codigo_postal_residencial" name="codigo_postal_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'codigo_postal'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="ciudad_residencial" class="formulario__label">Ciudad</label>
                    <input type="text" class="formulario__input" id="ciudad_residencial" name="ciudad_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'ciudad'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="estado_residencial" class="formulario__label">Estado</label>
                    <input type="text" class="formulario__input" id="estado_residencial" name="estado_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'estado'); ?>">
                </div>
            </fieldset>

            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Preferencias de Compra</legend>
                <p>Selecciona las categorías de artesanías que más te interesan.</p>
                <div class="preferencias__grid">
                    <?php foreach($categorias as $categoria): ?>
                        <label class="preferencia__label">
                            <input type="checkbox" name="categorias[]" value="<?php echo $categoria->id; ?>" <?php echo in_array($categoria->id, $categoriasSeleccionadas) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($categoria->nombre); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <input type="submit" class="formulario__submit" value="Guardar Cambios">
        </form>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Productos que no te interesan</legend>
            <p>Aquí puedes ver los productos que marcaste como "No me interesa". Puedes eliminarlos de esta lista para que vuelvan a aparecer en tus recomendaciones.</p>
            <div id="lista-no-interesados">
                <?php if(!empty($productosExcluidos)): ?>
                    <?php foreach($productosExcluidos as $producto): ?>
                        <div class="item-no-interesado" data-producto-id="<?php echo $producto->id; ?>">
                            <div class="item-no-interesado__info">
                                <img src="/img/productos/<?php echo htmlspecialchars($producto->imagen_principal ?? 'placeholder.jpg'); ?>.png" alt="Miniatura de <?php echo htmlspecialchars($producto->nombre); ?>" class="item-no-interesado__imagen">
                                <span><?php echo htmlspecialchars($producto->nombre); ?></span>
                            </div>
                            <button type="button" class="btn-eliminar-preferencia">Eliminar</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No has marcado ningún producto como "No me interesa".</p>
                <?php endif; ?>
            </div>
        </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Gestión de Datos</legend>
            <p>Puedes descargar todos los datos asociados a tu cuenta en formato JSON.</p>
            <a href="/perfil/exportar-datos" class="formulario__submit" style="background-color: #007bff; max-width: 30rem;" download>Descargar mis Datos</a>
        </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Zona de Peligro</legend>
            <p>La eliminación de tu cuenta es una acción permanente y no se puede deshacer. Se borrarán todos tus datos personales, historial de compras, mensajes y productos guardados.</p>
            <form action="/perfil/solicitar-eliminacion" method="POST" onsubmit="alert('Se ha enviado un correo al correo proporcionado para confirmar la eliminación de tu cuenta.');">
                <input type="submit" class="formulario__submit formulario__submit--peligro" value="Solicitar Eliminación de Cuenta">
            </form>
        </fieldset>
    </div>
</main>

<style>
    /* Estilos para centrar y dar formato al contenedor del formulario */
    .formulario-contenedor {
        max-width: 80rem;
        margin: 0 auto;
        padding: 3rem;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .preferencias__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .preferencia__label {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .preferencia__label:hover {
        background-color: #f7f7f7;
    }
    .item-no-interesado {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    .item-no-interesado__info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .item-no-interesado__imagen {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }
    .btn-eliminar-preferencia {
        background-color: #C62828;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Se activa el input de archivo al hacer clic en el contenedor de la imagen
    document.getElementById('imagenPreview').addEventListener('click', () => document.getElementById('imagen').click());

    function previewImage(event) {
        const previewContainer = document.getElementById('imagenPreview');
        const file = event.target.files[0];
        
        if (file && previewContainer) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Se limpia el contenedor de la imagen para poner la nueva
                previewContainer.innerHTML = ''; 

                // Se crea la estructura <picture> para la nueva imagen
                const picture = document.createElement('picture');
                const source = document.createElement('source');
                source.type = 'image/webp';
                source.srcset = e.target.result;

                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('imagen-cargada');
                img.alt = 'Nueva imagen seleccionada';

                picture.appendChild(source);
                picture.appendChild(img);
                previewContainer.appendChild(picture);
            }
            
            reader.readAsDataURL(file);
        }
    }

    // LISTA PRODUCTOS NO INTERESADOS
    document.getElementById('lista-no-interesados').addEventListener('click', async function(e) {
        if (e.target.classList.contains('btn-eliminar-preferencia')) {
            const itemDiv = e.target.closest('.item-no-interesado');
            const productoId = itemDiv.dataset.productoId;

            try {
                const response = await fetch('/perfil/eliminar-no-interesa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productoId: productoId })
                });
                const data = await response.json();
                if(data.success) {
                    itemDiv.remove();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });
});
</script>