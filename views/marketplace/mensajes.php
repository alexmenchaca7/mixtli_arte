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
                            <?php 
                                $prefix = ($mensaje->remitenteId === $_SESSION['id']) ? 'Tú: ' : ''; 
                                $contenidoPreview = '';
                                if ($mensaje->tipo === 'imagen') {
                                    $contenidoPreview = '<i class="fa-regular fa-image"></i> ' . $prefix . 'Imagen';
                                } elseif ($mensaje->tipo === 'documento') {
                                    $contenidoPreview = '<i class="fa-regular fa-file-pdf"></i> ' . $prefix . 'Documento';
                                } elseif ($mensaje->tipo === 'contacto') {
                                    $jsonContenido = stripslashes($mensaje->contenido);
                                    $datosContactoPreview = json_decode($jsonContenido, true);
                                    if (json_last_error() === JSON_ERROR_NONE && isset($datosContactoPreview['direccion']['calle']) && !empty($datosContactoPreview['direccion']['calle'])) {
                                        $textoPreviewContacto = $datosContactoPreview['direccion']['calle'];
                                        if (!empty($datosContactoPreview['direccion']['colonia'])) {
                                            $textoPreviewContacto .= ', ' . $datosContactoPreview['direccion']['colonia'];
                                        }
                                    } elseif (json_last_error() === JSON_ERROR_NONE && !empty($datosContactoPreview['telefono'])) {
                                        $textoPreviewContacto = 'Tel: ' . $datosContactoPreview['telefono'];
                                    } elseif (json_last_error() === JSON_ERROR_NONE && !empty($datosContactoPreview['email'])) {
                                        $textoPreviewContacto = 'Email: ' . $datosContactoPreview['email'];
                                    } else {
                                        $textoPreviewContacto = 'Información de contacto';
                                    }
                                    $contenidoPreview = $prefix . '📌 ' . ((strlen($textoPreviewContacto) > 25) ? htmlspecialchars(substr($textoPreviewContacto, 0, 25)) . '...' : htmlspecialchars($textoPreviewContacto));
                                } else { // Para 'texto' y 'plantilla_auto'
                                    // Aplicar stripslashes ANTES de htmlspecialchars y substr
                                    $contenidoLimpio = stripslashes($mensaje->contenido);
                                    $contenidoPreview = $prefix . ((strlen($contenidoLimpio) > 30) ? htmlspecialchars(substr($contenidoLimpio, 0, 30)) . '...' : htmlspecialchars($contenidoLimpio));
                                }
                            ?>
                            <small class="mensaje-preview">
                                <?= $contenidoPreview ?>
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

    const POLLING_RATE_ACTIVE = 1500;    // 1.5 segundos
    const POLLING_RATE_INACTIVE = 10000; // 10 segundos

    let sidebarPollingInterval;
    const SIDEBAR_POLLING_RATE = 7000; // Consultar cada 7 segundos 

    // Función para iniciar el polling si un chat ya está cargado en la página
    function iniciarPollingParaChatActivo() {
        const form = document.getElementById('form-chat');
        if (form) { // Si el formulario del chat existe, significa que hay un chat activo
            const productoId = form.querySelector('input[name="productoId"]').value;
            const contactoId = form.querySelector('input[name="destinatarioId"]').value;
            
            // Obtenemos el ID del último mensaje renderizado por PHP
            const mensajes = document.querySelectorAll('.mensaje[data-id]');
            if (mensajes.length > 0) {
                currentUltimoId = mensajes[mensajes.length - 1].dataset.id;
            }

            if (productoId && contactoId) {
                console.log('Iniciando polling para chat activo...');
                inicializarPolling(productoId, contactoId);
            }
        }
    }

    // Llama a la nueva función cuando la página se carga
    iniciarPollingParaChatActivo();

    // Iniciar el polling para la barra lateral cuando la página carga
    inicializarSidebarPolling();
    
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

    document.addEventListener('click', (e) => {
        // Corregir detección del botón
        const btnContacto = e.target.closest('#btn-contacto');
        if (!btnContacto) return;

        const direccionComercialInput = document.getElementById('direccionComercial');
        const direcciones = JSON.parse(direccionComercialInput.value) || []; 
        const telefono = document.getElementById('vendedorTelefono').value.trim();
        const email = document.getElementById('vendedorEmail').value.trim();
        const direccion = direcciones.length > 0 ? direcciones[0] : {};

        // Validar estructura mínima
        const contactoData = {
            tipo: 'contacto',
            direccion: direccion && direccion.calle ? direccion : null,
            telefono: telefono,
            email: email
        };

        console.log('Datos a enviar:', contactoData); // Verificar en consola

        // Configurar el formulario
        const form = document.getElementById('form-chat');
        const formData = new FormData(form);
        
        // Establecer valores específicos para contacto
        formData.set('tipo', 'contacto');
        formData.set('mensaje', JSON.stringify(contactoData));

        // Realizar petición
        fetch('/mensajes/enviar', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Restablecer valores
                document.getElementById('input-tipo').value = 'texto';
                form.reset();
                
                // Agregar mensaje al chat
                if(data.mensaje) {
                    appendMessage(data.mensaje);
                    currentUltimoId = data.mensaje.id;
                    scrollToBottom();

                    // Actualizar lista de conversaciones
                    fetch(`/mensajes/buscar?term=`)
                        .then(response => response.json())
                        .then(data => actualizarListaConversaciones(data.conversaciones))
                        .catch(error => console.error('Error actualizando conversaciones:', error));
                }
            } else {
                console.error('Error del servidor:', data.errores);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            // Restablecer tipo en caso de error
            document.getElementById('input-tipo').value = 'texto';
        });
    });

    function inicializarSidebarPolling() {
        if (sidebarPollingInterval) clearInterval(sidebarPollingInterval);

        const fetchListaConversaciones = async () => {
            try {
                // Solo hacer fetch si la ventana está visible para ahorrar recursos
                if (document.hidden) {
                    return;
                }
                const response = await fetch('/mensajes/lista-conversaciones');
                if (!response.ok) {
                    // Si no está autenticado o hay otro error, detener el polling
                    if (response.status === 401 || response.status === 403) {
                        clearInterval(sidebarPollingInterval);
                        console.warn('Polling de sidebar detenido por error de autenticación o autorización.');
                    }
                    throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                }
                const data = await response.json();

                if (data.conversaciones) {
                    actualizarListaConversaciones(data.conversaciones);
                }
            } catch (error) {
                console.error('Error obteniendo lista de conversaciones para sidebar:', error);
                // Podrías querer detener el polling aquí también si el error es persistente
                // clearInterval(sidebarPollingInterval);
            }
        };

        // Ejecutar inmediatamente la primera vez
        fetchListaConversaciones();
        // Luego, establecer el intervalo
        sidebarPollingInterval = setInterval(fetchListaConversaciones, SIDEBAR_POLLING_RATE);
    }


    function inicializarPolling(productoId, contactoId) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        const fetchMensajes = async () => {
            // No hacer polling si la pestaña no está visible
            if (document.hidden) {
                return;
            }

            try {
                const response = await fetch(
                    `/mensajes/nuevos?productoId=${productoId}&contactoId=${contactoId}&ultimoId=${currentUltimoId}`
                );
                const data = await response.json();
                
                if (data.success) {
                    // Procesar mensajes nuevos
                    if (data.mensajes.length > 0) {
                        data.mensajes.forEach(mensaje => {
                            const existe = document.querySelector(`.mensaje[data-id="${mensaje.id}"]`);
                            if (!existe) {
                                appendMessage(mensaje);
                            }
                        });
                        currentUltimoId = Math.max(...data.mensajes.map(m => m.id), currentUltimoId);
                        scrollToBottom();

                        // Al recibir un mensaje en el chat activo, eliminamos la notificación
                        // del sidebar de inmediato para que la UI esté sincronizada.
                        const conversacionActivaEnSidebar = document.querySelector(
                            `.contacto[data-producto-id="${productoId}"][data-contacto-id="${contactoId}"]`
                        );
                        if (conversacionActivaEnSidebar) {
                            conversacionActivaEnSidebar.classList.remove('contacto--no-leido');
                            const unreadDot = conversacionActivaEnSidebar.querySelector('.unread-dot');
                            if (unreadDot) {
                                unreadDot.remove();
                            }
                        }

                        // Forzamos la actualización del sidebar para que se sincronice al instante.
                        fetchListaConversaciones(); 
                    }

                    // Procesar actualizaciones de estado "leído" 
                    if (data.read_updates && data.read_updates.length > 0) {
                        data.read_updates.forEach(msgId => {
                            const mensajeElement = document.querySelector(`.mensaje[data-id="${msgId}"] .mensaje__burbuja`);
                            if (mensajeElement && !mensajeElement.querySelector('.mensaje__leido')) {
                                // Añadir el indicador de "leído" (ej. doble check azul)
                                const leidoIndicator = document.createElement('i');
                                leidoIndicator.className = 'fas fa-check-double mensaje__leido'; 
                                mensajeElement.appendChild(leidoIndicator);
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Error obteniendo nuevos mensajes:', error);
            }
        };

        const setPollingRate = () => {
            if (pollingInterval) clearInterval(pollingInterval);
            const rate = document.hidden ? POLLING_RATE_INACTIVE : POLLING_RATE_ACTIVE;
            pollingInterval = setInterval(fetchMensajes, rate);
        };

        // Iniciar polling y ajustar la frecuencia cuando cambia la visibilidad de la pestaña
        fetchMensajes(); // Llamada inicial
        setPollingRate();
        document.addEventListener('visibilitychange', setPollingRate);
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

        // Restablecer tipo a 'texto' después de enviar
        document.getElementById('input-tipo').value = 'texto';

        // Obtener el archivo y validar sin errores
        const archivo = formData.get('archivo');
        const tieneArchivo = archivo && archivo.size > 0;
        
        // Validar que haya contenido
        if (!formData.get('mensaje') && !tieneArchivo) {
            return;
        }

        // Enviar la solicitud
        fetch(tieneArchivo ? '/mensajes/upload' : '/mensajes/enviar', {
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

                    // Obtener conversaciones actualizadas del servidor
                    fetchListaConversaciones();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Método para actualizar conversaciones
    function actualizarListaConversaciones(conversaciones) {
        const lista = document.querySelector('.contactos__lista');
        if (!lista) return; // Salir si no se encuentra la lista

        // Almacenar el chat activo para no perderlo si sigue en la lista
        const chatActivo = document.querySelector('.contacto.activo');
        const activoId = chatActivo ? chatActivo.dataset.productoId + '-' + chatActivo.dataset.contactoId : null;
        
        lista.innerHTML = ''; // Limpiar lista actual

        conversaciones.forEach(conv => {
            const contacto = conv.contacto;
            const producto = conv.producto;
            const mensaje = conv.ultimoMensaje;
            const esNoLeido = conv.unread_count > 0;

            let preview = '';
            if (mensaje) {
                const prefix = mensaje.remitenteId == <?= $_SESSION['id'] ?> ? 'Tú: ' : '';
                let contenidoMensaje = mensaje.contenido; // Este es el string que puede tener slashes

                // Aplicar "stripslashes" en JS si es necesario
                if (typeof contenidoMensaje === 'string') {
                    // Solo si sabes que el backend podría estar enviando slashes escapados en el JSON
                    // para comillas simples. JSON_UNESCAPED_SLASHES en PHP es para '/', no para '\''.
                    // json_encode por defecto no escapa comillas simples, pero si lo hiciera como \', esto lo limpiaría.
                    contenidoMensaje = contenidoMensaje.replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\\\/g, "\\");
                }

                if (mensaje.tipo === 'contacto') {
                    let textoPreviewContacto = 'Información de contacto'; // Fallback
                    try {
                        const datosContactoPreview = (typeof contenidoMensaje === 'string') ? JSON.parse(contenidoMensaje) : contenidoMensaje;
                        if (datosContactoPreview && datosContactoPreview.direccion && datosContactoPreview.direccion.calle && datosContactoPreview.direccion.calle.trim() !== '') {
                            textoPreviewContacto = datosContactoPreview.direccion.calle;
                            if (datosContactoPreview.direccion.colonia) textoPreviewContacto += ', ' + datosContactoPreview.direccion.colonia;
                        } else if (datosContactoPreview && datosContactoPreview.telefono) {
                            textoPreviewContacto = 'Tel: ' + datosContactoPreview.telefono;
                        } else if (datosContactoPreview && datosContactoPreview.email) {
                            textoPreviewContacto = 'Email: ' + datosContactoPreview.email;
                        }
                    } catch (e) { /* Mantener fallback */ }
                    previewHTML = `${prefix}📌 ${ (textoPreviewContacto.length > 25) ? escapeHTML(textoPreviewContacto.substring(0, 25)) + '...' : escapeHTML(textoPreviewContacto) }`;
                
                } else if (mensaje.tipo === 'imagen') {
                    previewHTML = `${prefix}<i class="fa-regular fa-image"></i> Imagen`;
                } else if (mensaje.tipo === 'documento') {
                    previewHTML = `${prefix}<i class="fa-regular fa-file-pdf"></i> Documento`;
                } else { // 'texto', 'plantilla_auto'
                    previewHTML = prefix + ((contenidoMensaje.length > 30) ? escapeHTML(contenidoMensaje.substring(0, 30)) + '...' : escapeHTML(contenidoMensaje));
                }
            }
            
            const contactoHTML = `
                <div class="contacto ${ esNoLeido ? 'contacto--no-leido' : '' } ${ (activoId === producto.id + '-' + contacto.id) ? 'activo' : '' }" 
                    data-producto-id="${producto.id}"
                    data-contacto-id="${contacto.id}">
                    <picture>
                        <img src="/img/usuarios/${contacto.imagen ? contacto.imagen + '.png' : 'default.png'}" 
                            alt="${escapeHTML(contacto.nombre)}"
                            class="contacto__imagen">
                    </picture>
                    <div class="contacto__info">
                        <div class="contacto__titulo">
                            <h3>${escapeHTML(contacto.nombre)} • ${escapeHTML(producto.nombre)}</h3>
                            ${esNoLeido ? `<span class="unread-dot"></span>` : ''} </div>
                        ${mensaje ? `<small class="mensaje-preview">${previewHTML}</small>` : ''}
                    </div>
                    <span class="contacto__fecha">
                        ${new Date(conv.fecha).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                </div>
            `;
            
            lista.insertAdjacentHTML('beforeend', contactoHTML);
        });
    }

    // Para manejar JSON con escapes en el cliente
    function stripslashes(str) {
        return (str + '').replace(/\\(.?)/g, function (s, n1) {
            switch (n1) {
                case '\\':
                    return '\\';
                case '0':
                    return '\u0000';
                case '':
                    return '';
                default:
                    return n1;
            }
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
        let contenidoParaMostrar = mensaje.contenido;

        if (typeof contenidoParaMostrar === 'string') {
            contenidoParaMostrar = contenidoParaMostrar.replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\\\/g, "\\");
        }
        
        if (mensaje.tipo === 'contacto') {
            try {
                // Limpiar y parsear el contenido
                const contactoData = (typeof contenidoParaMostrar === 'string') ? JSON.parse(contenidoParaMostrar) : contenidoParaMostrar;
                
                // Construir HTML
                let html = '<div class="mensaje__contacto-info">';
                
                if (contactoData.direccion) {
                    const dir = contactoData.direccion;
                    if (dir.calle) {
                        html += `
                            <div class="mensaje__contacto-item">
                                <i class="fa-solid fa-map-marker-alt"></i>
                                <span>${dir.calle}${dir.colonia ? ', ' + dir.colonia : ''}</span>
                            </div>
                            <div class="mensaje__contacto-item direccion-completa">
                                ${dir.ciudad ? dir.ciudad + ', ' : ''}
                                ${dir.estado ? dir.estado + ', ' : ''}
                                ${dir.codigo_postal || ''}
                            </div>`;
                    }
                }

                if (contactoData.telefono) {
                    html += `
                        <div class="mensaje__contacto-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>${contactoData.telefono}</span>
                        </div>`;
                }

                if (contactoData.email) {
                    html += `
                        <div class="mensaje__contacto-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>${contactoData.email}</span>
                        </div>`;
                }

                return html + `</div>`;
            } catch (e) {
                return '📌 Información de contacto';
            }
        }

        switch(mensaje.tipo) {
            case 'imagen':
                return `
                    <picture>
                        <img loading="lazy" src="/${mensaje.contenido}" 
                            class="mensaje__imagen" 
                            alt="Imagen enviada">
                    </picture>
                `;
            case 'documento':
                const nombreArchivo = mensaje.contenido.split('/').pop(); // Obtiene "archivo.pdf"
                return `
                    <a href="/${mensaje.contenido}" 
                    class="mensaje__documento"
                    download>
                        <i class="fa-regular fa-file-pdf mensaje__icono-documento"></i>
                        <div class="mensaje__archivo-info">
                            <div class="mensaje__nombre-archivo">
                                ${escapeHTML(nombreArchivo)}
                            </div>
                        </div>
                    </a>
                `;
            default:
                return contenidoParaMostrar ? escapeHTML(contenidoParaMostrar) : '';
        }
    }

    function escapeHTML(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/[&<>"']/g, function (match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[match];
        });
    }

    const searchInput = document.getElementById('input-busqueda');

    if (searchInput) {
        searchInput.addEventListener('input', async function(e) {
            const searchTerm = e.target.value.trim();
            
            if (sidebarPollingInterval) clearInterval(sidebarPollingInterval);

            if (searchTerm === "") {
                inicializarSidebarPolling(); // Si se borra el término, reiniciar polling normal
                return;
            }
            
            try {
                const response = await fetch(`/mensajes/buscar?term=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                if (data.conversaciones) {
                    actualizarListaConversaciones(data.conversaciones);
                }
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