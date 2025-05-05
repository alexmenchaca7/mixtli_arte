<div class="mensajeria">
    <!-- Lista de contactos -->
    <div class="mensajeria__contactos">
        <!-- Header y buscador -->
        <div class="contactos__header">
            <h2 class="contactos__titulo">Chats</h2>
            <div class="contactos__busqueda">
                <input type="text" 
                       class="contactos__campo-busqueda"
                       id="input-busqueda"
                       placeholder="Buscar conversaciones...">
                <i class="fa-solid fa-magnifying-glass contactos__icono-busqueda"></i>
            </div>
        </div>

        <!-- Lista de contactos -->
        <div class="contactos__lista">
            <?php foreach ($conversaciones as $conv):
                $contacto = $conv['contacto'];
                $producto = $conv['producto'];
                $mensaje = $conv['ultimoMensaje'];
            ?>
                <div class="contacto" 
                     data-producto-id="<?= $producto->id ?>"
                     data-contacto-id="<?= $contacto->id ?>">
                    <picture>
                        <img src="/img/usuarios/<?php echo isset($contacto->imagen) && $contacto->imagen ? $contacto->imagen . '.png' : 'default.png'; ?>" 
                             alt="<?= $contacto->nombre ?>"
                             class="contacto__imagen">
                    </picture>
                    <div class="contacto__info">
                        <div class="contacto__titulo">
                            <h3><?php echo $contacto->nombre . ' • ' . $producto->nombre; ?></h3>
                        </div>
                        <?php if ($mensaje): ?>
                            <?php $prefix = ($mensaje->remitenteId === $_SESSION['id']) ? 'Tú: ' : ''; ?>
                            <small class="mensaje-preview">
                                <?php if($mensaje->tipo === 'imagen'): ?>
                                    <i class="fa-regular fa-image"></i> <?= $prefix ?>Imagen
                                <?php elseif($mensaje->tipo === 'documento'): ?>
                                    <i class="fa-regular fa-file-pdf"></i> <?= $prefix ?>Documento
                                <?php else: ?>
                                    <?= $prefix . ((strlen($mensaje->contenido) > 30) ? (substr($mensaje->contenido, 0, 30) . '...') : $mensaje->contenido) ?>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <span class="contacto__fecha">
                        <?= date('h:i a', strtotime($conv['fecha'])) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Área de chat -->
    <div class="chat" id="chat-activo">
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
                        <div class="mensaje__burbuja <?= $mensaje->tipo !== 'texto' ? 'mensaje--contenido-especial' : '' ?>">
                            <?php switch($mensaje->tipo):
                                case 'imagen': ?>
                                    <picture>
                                        <source srcset="<?= $mensaje->contenido ?>.webp" type="image/webp">
                                        <img loading="lazy" src="/mensajes/img/<?= $mensaje->contenido ?>" 
                                             class="mensaje__imagen" 
                                             alt="Imagen enviada">
                                    </picture>
                                    <?php break; ?>
                                <?php case 'documento': ?>
                                    <a href="/mensajes/pdf/<?= $mensaje->contenido ?>" 
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
    </div>
</div>

<script>
let pollingInterval;
let currentUltimoId = 0;

document.addEventListener('DOMContentLoaded', () => {
    const chatActivo = document.getElementById('chat-activo');
    const formChat = document.getElementById('form-chat');
    
    // Cargar conversación al hacer clic en contacto
    document.querySelector('.contactos__lista').addEventListener('click', async (e) => {
        const contacto = e.target.closest('.contacto');
        if (!contacto) return;

        const productoId = contacto.dataset.productoId;
        const contactoId = contacto.dataset.contactoId;
        
        try {
            const response = await fetch(`/mensajes/chat?productoId=${productoId}&contactoId=${contactoId}`);
            const data = await response.json();
            
            document.getElementById('chat-activo').innerHTML = data.html;
            scrollToBottom();

            currentUltimoId = data.ultimoId;
            inicializarPolling(productoId, contactoId);
        } catch (error) {
            console.error('Error cargando el chat:', error);
        }
    }); 

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'input-archivo') {
            const file = e.target.files[0];
            const preview = document.getElementById('preview-archivo');
            const previewImagen = preview.querySelector('.preview-archivo-imagen');
            const previewDocumento = preview.querySelector('.preview-archivo-documento');
            
            preview.style.display = 'block';
            previewImagen.style.display = 'none';
            previewDocumento.style.display = 'none';

            if (file) {
                if (file.type.startsWith('image/')) {
                    previewImagen.innerHTML = `<img src="${URL.createObjectURL(file)}">`;
                    previewImagen.style.display = 'block';
                } else if (file.type === 'application/pdf') {
                    previewDocumento.querySelector('.preview-archivo-nombre').textContent = file.name;
                    previewDocumento.style.display = 'flex';
                }
            }
        }
    });

    function inicializarPolling(productoId, contactoId) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        const fetchMensajes = async () => {
            try {
                const response = await fetch(
                    `/mensajes/nuevos?productoId=${productoId}&contactoId=${contactoId}&ultimoId=${currentUltimoId}`
                );
                const data = await response.json();
                
                if (data.success && data.mensajes.length > 0) {
                    data.mensajes.forEach(mensaje => {
                        const existe = document.querySelector(`.mensaje[data-id="${mensaje.id}"]`);
                        if (!existe) {
                            appendMessage(mensaje);
                        }
                    });
                    // Actualizar el último ID con el máximo recibido
                    currentUltimoId = Math.max(currentUltimoId, ...data.mensajes.map(m => m.id));
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error obteniendo nuevos mensajes:', error);
            }
        };

        // Ejecutar inmediatamente y luego cada 3 segundos
        fetchMensajes();
        pollingInterval = setInterval(fetchMensajes, 3000);
    }

    // Función cerrar preview actualizada
    function cerrarPreview() {
        const preview = document.getElementById('preview-archivo');
        preview.style.display = 'none';
        preview.querySelector('.preview-archivo-imagen').innerHTML = '';
        preview.querySelector('.preview-archivo-nombre').textContent = '';
        document.getElementById('input-archivo').value = '';
    }

    // Enviar mensaje
    function manejarEnvio(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        // Validar que haya contenido
        if (!formData.get('mensaje') && !formData.get('archivo').size > 0) {
            return;
        }

        // Enviar la solicitud
        fetch(formData.get('archivo').size > 0 ? '/mensajes/upload' : '/mensajes/enviar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cerrarPreview(); // Resetear preview
                form.reset();
                if(data.mensaje) {
                    appendMessage(data.mensaje);
                    currentUltimoId = data.mensaje.id; // Actualizar último ID aquí
                    scrollToBottom();
                    actualizarListaConversaciones(data.mensaje);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Método para actualizar conversaciones
    function actualizarListaConversaciones(conversaciones) {
        const lista = document.querySelector('.contactos__lista');
        lista.innerHTML = ''; // Limpiar lista actual

        conversaciones.forEach(conv => {
            const contacto = conv.contacto;
            const producto = conv.producto;
            const mensaje = conv.ultimoMensaje;
            
            const contactoHTML = `
                <div class="contacto" 
                    data-producto-id="${producto.id}"
                    data-contacto-id="${contacto.id}">
                    <picture>
                        <img src="/img/usuarios/${contacto.imagen ? contacto.imagen + '.png' : 'default.png'}" 
                            alt="${contacto.nombre}"
                            class="contacto__imagen">
                    </picture>
                    <div class="contacto__info">
                        <div class="contacto__titulo">
                            <h3>${contacto.nombre} • ${producto.nombre}</h3>
                        </div>
                        ${mensaje ? `
                            <small class="mensaje-preview">
                                ${mensaje.remitenteId == <?= $_SESSION['id'] ?> ? 'Tú: ' : ''}
                                ${mensaje.tipo === 'imagen' ? 
                                    '<i class="fa-regular fa-image"></i> Imagen' : 
                                mensaje.tipo === 'documento' ? 
                                    '<i class="fa-regular fa-file-pdf"></i> Documento' : 
                                mensaje.contenido.length > 30 ? 
                                    mensaje.contenido.substring(0, 30) + '...' : 
                                    mensaje.contenido}
                            </small>
                        ` : ''}
                    </div>
                    <span class="contacto__fecha">
                        ${new Date(conv.fecha).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                </div>
            `;
            
            lista.insertAdjacentHTML('beforeend', contactoHTML);
        });
    }

    // Auto-scroll al final
    function scrollToBottom() {
        const container = document.getElementById('mensajes-container');
        if(container) {
            // Usar comportamiento smooth para casos de actualización dinámica
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    // Agregar mensaje al DOM
    function appendMessage(mensaje) {
        const container = document.getElementById('mensajes-container');
        const isEnviado = mensaje.remitenteId == <?= $_SESSION['id'] ?? 0 ?>;

        const messageHtml = `
            <div class="mensaje mensaje--${isEnviado ? 'enviado' : 'recibido'}" data-id="${mensaje.id}">
                <div class="mensaje__burbuja ${mensaje.tipo !== 'texto' ? 'mensaje--contenido-especial' : ''}">
                    ${renderContent(mensaje)}
                    <span class="mensaje__fecha">
                        ${new Date(mensaje.creado).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', messageHtml); // Añadir al final
        scrollToBottom(); // Asegurar scroll al final
    }

    function renderContent(mensaje) {
        switch(mensaje.tipo) {
            case 'imagen':
                return `
                    <picture>
                        <source srcset="${mensaje.contenido}.webp" type="image/webp">
                        <img loading="lazy" src="${mensaje.contenido}" 
                             class="mensaje__imagen" 
                             alt="Imagen enviada">
                    </picture>
                `;
            case 'documento':
                return `
                    <a href="${mensaje.contenido}" 
                       class="mensaje__documento"
                       download>
                        <i class="fa-regular fa-file-pdf mensaje__icono-documento"></i>
                        <div class="mensaje__archivo-info">
                            <div class="mensaje__nombre-archivo">
                                ${mensaje.contenido.split('/').pop()}
                            </div>
                        </div>
                    </a>
                `;
            default:
                return mensaje.contenido ? mensaje.contenido : '';
        }
    }

    const searchInput = document.getElementById('input-busqueda');

    if (searchInput) {
        searchInput.addEventListener('input', async function(e) {
            const searchTerm = e.target.value.trim();
            
            try {
                const response = await fetch(`/mensajes/buscar?term=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                actualizarListaConversaciones(data.conversaciones);
            } catch (error) {
                console.error('Error buscando conversaciones:', error);
            }
        });
    }

    // Usar delegación de eventos para el formulario
    document.addEventListener('submit', (e) => {
        if (e.target && e.target.matches('#form-chat')) {
            manejarEnvio(e);
        }
    });

});
</script>