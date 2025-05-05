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
            <div class="mensaje mensaje--<?= $mensaje->remitenteId == $_SESSION['id'] ? 'enviado' : 'recibido' ?>">
                <div class="mensaje__burbuja <?= $mensaje->tipo !== 'texto' ? 'mensaje--contenido-especial' : '' ?>">
                    <?php switch($mensaje->tipo):
                        case 'imagen': ?>
                            <picture>
                                <source srcset="<?= $mensaje->contenido ?>.webp" type="image/webp">
                                <img loading="lazy" src="<?= $mensaje->contenido ?>" 
                                        class="mensaje__imagen" 
                                        alt="Imagen enviada">
                            </picture>
                            <?php break; ?>
                        <?php case 'documento': ?>
                            <a href="<?= $mensaje->contenido ?>" 
                                class="mensaje__documento"
                                download>
                                <i class="fa-regular fa-file-pdf mensaje__icono-documento"></i>
                                <div class="mensaje__archivo-info">
                                    <div class="mensaje__nombre-archivo">
                                        <?= basename($mensaje->contenido) ?>
                                    </div>
                                </div>
                            </a>
                            <?php break; ?>
                        <?php default: ?>
                            <?= htmlspecialchars($mensaje->contenido) ?>
                    <?php endswitch; ?>
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
        
        <button type="button" class="chat__adjuntar">
            <i class="fa-regular fa-file"></i>
            <input type="file" 
                    class="chat__input-archivo" 
                    accept="image/*,.pdf"
                    name="archivo"
                    id="input-archivo">
        </button>
        
        <input type="text" 
                class="chat__campo" 
                name="mensaje" 
                placeholder="Escribe un mensaje...">
        <button type="submit" class="chat__boton">
            <i class="fa-regular fa-paper-plane"></i>
        </button>
    </form>
<?php else: ?>
    <div class="chat__vacio">
        Selecciona una conversación para comenzar a chatear
    </div>
<?php endif; ?>