<?php if(isset($productoChat) && isset($contactoChat)): ?>
    <?php
    // --- Lógica para determinar el estado de la venta y calificación ---
    $esVendedor = ($_SESSION['id'] == $productoChat->usuarioId);
    $ventaRealizada = false;
    $miCalificacion = null;
    $ratingExpired = false; 

    if (!empty($valoraciones)) {
        $ventaRealizada = true;
        foreach($valoraciones as $v) {
            if ($v->calificadorId == $_SESSION['id']) {
                $miCalificacion = $v;
                break;
            }
        }
    }
    
    if ($miCalificacion && is_null($miCalificacion->estrellas)) {
        $fechaCreacion = new \DateTime($miCalificacion->creado);
        $fechaActual = new \DateTime();
        $diferencia = $fechaActual->diff($fechaCreacion);
        if ($diferencia->days > 30) {
            $ratingExpired = true;
        }
    }
    ?>
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

    <div class="chat__mensajes" id="mensajes-container">
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
                            $contenidoLimpio = stripslashes($mensaje->contenido);
                            $contactoData = json_decode($contenidoLimpio);
                            
                            if (json_last_error() === JSON_ERROR_NONE):
                        ?>
                            <div class="mensaje__contacto-info">
                                <?php if (!empty($contactoData->direccion)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-map-marker-alt"></i>
                                        <span>
                                            <?= htmlspecialchars($contactoData->direccion->calle) ?>
                                            <?= !empty($contactoData->direccion->colonia) ? ', ' . htmlspecialchars($contactoData->direccion->colonia) : '' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($contactoData->direccion->ciudad) || !empty($contactoData->direccion->estado) || !empty($contactoData->direccion->codigo_postal)): ?>
                                        <div class="direccion-completa">
                                            <?= htmlspecialchars($contactoData->direccion->ciudad) ?>
                                            <?= !empty($contactoData->direccion->estado) ? ', ' . htmlspecialchars($contactoData->direccion->estado) : '' ?>
                                            <?= !empty($contactoData->direccion->codigo_postal) ? ' • ' . htmlspecialchars($contactoData->direccion->codigo_postal) : '' ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($contactoData->telefono)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-phone"></i>
                                        <span><?= htmlspecialchars($contactoData->telefono) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($contactoData->email)): ?>
                                    <div class="mensaje__contacto-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= htmlspecialchars($contactoData->email) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="error-datos">Información de contacto no válida</div>
                        <?php endif; ?>
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

    <div class="chat__acciones-y-form">
        <div id="chat-error-message" style="color: red; text-align: center; margin-bottom: 1rem; display: none;"></div>
        
        <div class="chat__acciones-finales">
            <?php if($esVendedor && !$ventaRealizada && $productoChat->estado !== 'agotado'): ?>
                <button type="button" class="chat__boton chat__boton--accion" id="btn-marcar-vendido">
                    <i class="fa-solid fa-handshake"></i> Marcar como Vendido
                </button>
            <?php endif; ?>

            <?php if($ventaRealizada && $miCalificacion && is_null($miCalificacion->estrellas) && !$ratingExpired): ?>
                <button type="button" class="chat__boton chat__boton--accion" id="btn-calificar" data-valoracion-id="<?= $miCalificacion->id ?>" data-tipo-calificacion="<?= $miCalificacion->tipo === 'vendedor' ? 'comprador' : 'vendedor' ?>">
                    <i class="fa-solid fa-star"></i> Calificar <?= $miCalificacion->tipo === 'vendedor' ? 'Comprador' : 'Vendedor' ?>
                </button>
            
            <?php elseif ($ventaRealizada && $miCalificacion && is_null($miCalificacion->estrellas) && $ratingExpired): ?>
                <div class="chat__accion-completa chat__accion-completa--expirado">
                    <i class="fa-solid fa-clock"></i> El período para calificar ha expirado.
                </div>

            <?php elseif($ventaRealizada && $miCalificacion && !is_null($miCalificacion->estrellas)): ?>
                <div class="chat__accion-completa">
                    <i class="fa-solid fa-check-circle"></i> Ya has calificado esta transacción.
                </div>
            <?php endif; ?>
        </div>
        
        <form class="chat__entrada" id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="productoId" value="<?= $productoChat->id ?>">
            <input type="hidden" name="destinatarioId" value="<?= $contactoChat->id ?>">
            <input type="hidden" id="vendedorId" value="<?= $productoChat->usuarioId ?>">
            <input type="hidden" name="tipo" id="input-tipo" value="texto">

            <input type="hidden" id="vendedorTelefono" value="<?= $vendedor->telefono ?? '' ?>">
            <input type="hidden" id="vendedorEmail" value="<?= $vendedor->email ?? '' ?>">
            <input type="hidden" id="direccionComercial" value="<?= htmlspecialchars(json_encode($direccionComercial ?? []), ENT_QUOTES) ?>">

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
            
            <input type="text" 
                    class="chat__campo" 
                    name="mensaje" 
                    placeholder="Escribe un mensaje...">
            <button type="submit" class="chat__boton" title="Enviar mensaje">
                <i class="fa-regular fa-paper-plane"></i>
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="chat__vacio">
        Selecciona una conversación para comenzar a chatear
    </div>
<?php endif; ?>