<?php if(isset($productoChat) && isset($contactoChat)): ?>
    <!-- Cabecera dinámica -->
    <div class="chat__header">
        <picture>
            <img src="/img/usuarios/<?php echo isset($contactoChat->imagen) && $contactoChat->imagen ? $contactoChat->imagen . '.png' : 'default.png'; ?>"
                    class="chat__imagen" 
                    alt="<?= $contactoChat->nombre ?>">
        </picture>
        <div class="chat__info">
            <h3><?= $contactoChat->nombre . ' • ' . $productoChat->nombre ?></h3>
        </div>
    </div>

    <!-- Mensajes -->
    <div class="chat__mensajes" id="mensajes-container">
        <!-- Mensaje del sistema -->
        <div class="mensaje-sistema">
            <div class="mensaje-sistema__contenido">
                <i class="fa-solid fa-shield-halved mensaje-sistema__icono"></i>
                <div class="mensaje-sistema__texto">
                    <strong>Chat seguro con cifrado de extremo a extremo</strong>
                    <p>• Evite realizar pagos en efectivo en lugares públicos<br>
                    • No compartimos su información personal<br>
                    • Mensajes cifrados con protocolo TLS/SSL</p>
                </div>
            </div>
        </div>

        <?php foreach($mensajes as $mensaje): ?>
            <div class="mensaje mensaje--<?= $mensaje->remitenteId == $_SESSION['id'] ? 'enviado' : 'recibido' ?>" data-id="<?= $mensaje->id ?>">
                <div class="mensaje__burbuja <?= ($mensaje->tipo !== 'texto' && $mensaje->tipo !== 'plantilla_auto') ? 'mensaje--contenido-especial' : '' ?>">
                    <?php switch($mensaje->tipo):
                        case 'imagen': ?>
                            <picture>
                                <img loading="lazy" src="/<?= htmlspecialchars($mensaje->contenido) ?>" 
                                    class="mensaje__imagen" 
                                    alt="Imagen enviada">
                            </picture>
                        <?php break; ?>

                        <?php case 'documento': ?>
                            <a href="/<?= htmlspecialchars($mensaje->contenido) ?>" 
                            class="mensaje__documento"
                            download>
                                <i class="fa-regular fa-file-pdf mensaje__icono-documento"></i>
                                <div class="mensaje__archivo-info">
                                    <div class="mensaje__nombre-archivo">
                                        <?= htmlspecialchars(basename($mensaje->contenido)) ?>
                                    </div>
                                </div>
                            </a>
                        <?php break; ?>
                        <?php case 'contacto': 
                            // Limpiar escapes y parsear
                            $contenidoLimpio = stripslashes($mensaje->contenido);
                            $contactoData = json_decode($contenidoLimpio);
                            
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $contactoData = null; // Manejar error si es necesario
                            }
                            ?>
                            <div class="mensaje__contacto-info">
                                <?php if ($contactoData && isset($contactoData->direccion) && isset($contactoData->direccion->calle)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($contactoData->direccion->calle) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($contactoData && !empty($contactoData->telefono)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-phone"></i>
                                        <span><?= htmlspecialchars($contactoData->telefono) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($contactoData && !empty($contactoData->email)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= htmlspecialchars($contactoData->email) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php break; ?>
                        <?php default: ?>
                            <?php echo htmlspecialchars(stripslashes($mensaje->contenido)); ?>
                    <?php endswitch; ?>

                    <?php if ($mensaje->tipo === 'plantilla_auto'): ?>
                        <small class="mensaje__indicador-auto">Mensaje automático</small>
                    <?php endif; ?>
                    
                    <span class="mensaje__fecha">
                        <?= date('h:i a', strtotime($mensaje->creado)) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario de envío -->
    <form class="chat__entrada" id="form-chat" enctype="multipart/form-data">
        <input type="hidden" name="productoId" value="<?= $productoChat->id ?>">
        <input type="hidden" name="destinatarioId" value="<?= $contactoChat->id ?>">
        <input type="hidden" id="vendedorId" value="<?= $productoChat->usuarioId ?>">
        <input type="hidden" name="tipo" id="input-tipo" value="texto">

        <!-- Campos ocultos con datos del VENDEDOR -->
        <input type="hidden" id="vendedorTelefono" value="<?= $vendedor->telefono ?? '' ?>">
        <input type="hidden" id="vendedorEmail" value="<?= $vendedor->email ?? '' ?>">
        <input type="hidden" id="direccionComercial" value="<?= htmlspecialchars(json_encode($direccionComercial ?? new stdClass()), ENT_QUOTES) ?>">

        <div class="preview-archivo" id="preview-archivo">
            <div class="preview-archivo-contenido">
                <div class="preview-archivo-imagen"></div>
                <div class="preview-archivo-documento">
                    <i class="fa-regular fa-file-pdf"></i>
                    <span class="preview-archivo-nombre"></span>
                </div>
                <span class="preview-archivo-cerrar">&times;</span>
            </div>
        </div>
        
        <button type="button" class="chat__adjuntar" title="Adjuntar archivo (imagen o PDF)">
            <i class="fa-regular fa-file"></i>
            <input type="file" 
                    class="chat__input-archivo" 
                    accept="image/*,.pdf"
                    name="archivo"
                    id="input-archivo">
        </button>

        <button type="button" class="chat__contacto" id="btn-contacto" title="Compartir información de contacto del vendedor">
            <i class="fa-regular fa-address-card"></i>
        </button>

        <div class="chat__plantillas-container">
            <button type="button" class="chat__boton-plantillas" id="btn-mostrar-plantillas" title="Usar una plantilla de mensaje">
                <i class="fa-regular fa-file-lines"></i> <!-- O un ícono más adecuado -->
            </button>
            <div class="chat__lista-plantillas" id="lista-plantillas" style="display: none;">
                <!-- Las plantillas se cargarán aquí con JS -->
            </div>
        </div>
        
        <input type="text" 
                class="chat__campo" 
                name="mensaje"
                id="input-mensaje-chat"
                placeholder="Escribe un mensaje...">
        <button type="submit" class="chat__boton" title="Enviar mensaje">
            <i class="fa-regular fa-paper-plane"></i>
        </button>
    </form>
<?php else: ?>
    <div class="chat__vacio">
        Selecciona una conversación para comenzar a chatear
    </div>
<?php endif; ?>