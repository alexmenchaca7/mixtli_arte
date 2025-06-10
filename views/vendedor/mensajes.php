<div class="mensajeria">
    <div class="mensajeria__contactos">
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

    <div class="chat" id="chat-activo">
        <?php if(isset($productoChat) && isset($contactoChat)): ?>
            <?php include __DIR__ . '/partials/chat.php'; ?>
        <?php else: ?>
            <div class="chat__vacio">
                Selecciona una conversación para comenzar a chatear
            </div>
        <?php endif; ?>
    </div>

    <div class="modal-plantilla" id="modal-personalizar-plantilla" style="display:none;">
        <div class="modal-plantilla__contenido">
            <h3 id="modal-plantilla-titulo">Personalizar Plantilla</h3>
            <textarea id="modal-plantilla-texto" rows="5"></textarea>
            <div id="modal-plantilla-placeholders">
                </div>
            <div class="modal-plantilla__acciones">
                <button type="button" id="btn-enviar-plantilla-personalizada" title="Enviar este mensaje de plantilla">Enviar</button>
                <button type="button" id="btn-cerrar-modal-plantilla" title="Cancelar y cerrar esta ventana">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
const plantillasDesdePHP = <?= json_encode($plantillasDefinidas ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', () => {
    // --- Configuración y Variables Globales ---
    let pollingInterval;
    let sidebarPollingInterval;
    let currentUltimoId = 0;
    const POLLING_RATE_ACTIVE = 1500; // 1.5 segundos
    const POLLING_RATE_INACTIVE = 10000; // 10 segundos
    const SIDEBAR_POLLING_RATE = 7000; // 7 segundos

    const chatActivo = document.getElementById('chat-activo');
    const formChat = document.getElementById('form-chat');

    // Referencias a elementos del modal y plantillas
    const btnMostrarPlantillas = document.getElementById('btn-mostrar-plantillas');
    const listaPlantillasEl = document.getElementById('lista-plantillas');
    const modalPlantilla = document.getElementById('modal-personalizar-plantilla');
    const modalPlantillaTitulo = document.getElementById('modal-plantilla-titulo');
    const modalPlantillaTexto = document.getElementById('modal-plantilla-texto');
    const modalPlantillaPlaceholders = document.getElementById('modal-plantilla-placeholders');
    const btnEnviarPlantillaPersonalizada = document.getElementById('btn-enviar-plantilla-personalizada');
    const btnCerrarModalPlantilla = document.getElementById('btn-cerrar-modal-plantilla');
    const inputMensajeChat = document.getElementById('input-mensaje-chat'); // El campo de texto normal

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

    let plantillasDisponibles = {}; // Para almacenar las plantillas cargadas
    let plantillaSeleccionadaId = null;

    // Variables para almacenar información del chat actual
    let nombreClienteActual = '';
    let nombreProductoActual = '';

    // --- INICIO: LÓGICA DEL SISTEMA DE CALIFICACIÓN ---
    const puntosFuertesParaVendedor = ["Negociación justa", "Puntualidad", "Honestidad", "Comunicación efectiva"];
    const puntosFuertesParaComprador = ["Puntualidad", "Buena comunicación", "Pago oportuno"];

    // Usar delegación de eventos en un elemento estático superior
    document.body.addEventListener('click', async function(e) {
        const btnMarcarVendido = e.target.closest('#btn-marcar-vendido');
        const btnCalificar = e.target.closest('#btn-calificar');
        const btnCancelarValoracion = e.target.closest('#btn-cancelar-valoracion');

        // --- Manejar clic en "Marcar como Vendido" ---
        if (btnMarcarVendido) {
            btnMarcarVendido.disabled = true;
            btnMarcarVendido.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Marcando...';

            const form = document.getElementById('form-chat');
            const productoId = form.querySelector('input[name="productoId"]').value;
            const compradorId = form.querySelector('input[name="destinatarioId"]').value;

            const formData = new FormData();
            formData.append('productoId', productoId);
            formData.append('compradorId', compradorId);

            try {
                const response = await fetch('/mensajes/marcar-vendido', { method: 'POST', body: formData });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Error en el servidor');
                
                // Recargar el área del chat para mostrar el botón de calificar
                const chatResponse = await fetch(`/mensajes/chat?productoId=${productoId}&contactoId=${compradorId}`);
                const chatData = await chatResponse.json();
                document.getElementById('chat-activo').innerHTML = chatData.html;
                scrollToBottom();

            } catch(error) {
                alert(`Error: ${error.message}`);
                btnMarcarVendido.disabled = false;
                btnMarcarVendido.innerHTML = '<i class="fa-solid fa-handshake"></i> Marcar como Vendido';
            }
        }

        // --- Manejar clic en "Calificar Usuario" ---
        if (btnCalificar) {
            const valoracionId = btnCalificar.dataset.valoracionId;
            const tipoCalificacion = btnCalificar.dataset.tipoCalificacion; // 'comprador' o 'vendedor'

            document.getElementById('input-valoracion-id').value = valoracionId;

            const contenedorPuntos = document.getElementById('puntos-fuertes-contenedor');
            contenedorPuntos.innerHTML = '';
            const puntos = tipoCalificacion === 'vendedor' ? puntosFuertesParaVendedor : puntosFuertesParaComprador;
            
            document.getElementById('modal-valoracion-titulo').textContent = `Calificar ${tipoCalificacion === 'vendedor' ? 'Vendedor' : 'Comprador'}`;

            puntos.forEach(punto => {
                contenedorPuntos.innerHTML += `
                    <label>
                        <input type="checkbox" name="puntos_fuertes[]" value="${punto}">
                        ${punto}
                    </label>
                `;
            });
            
            document.getElementById('modal-valoracion').style.display = 'flex';
        }

        // --- Manejar clic en "Cancelar Calificación" ---
        if (btnCancelarValoracion) {
            document.getElementById('modal-valoracion').style.display = 'none';
        }
    });

    // --- Lógica del Rating con Estrellas ---
    const estrellasContenedor = document.querySelector('.rating-estrellas');
    if (estrellasContenedor) {
        const estrellas = estrellasContenedor.querySelectorAll('i');
        const inputEstrellas = document.getElementById('input-estrellas');

        estrellas.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.valor;
                inputEstrellas.value = rating;
                estrellas.forEach(s => {
                    s.classList.remove('fa-solid', 'fa-regular');
                    s.classList.add(s.dataset.valor <= rating ? 'fa-solid' : 'fa-regular');
                });
            });
        });
    }

    // --- Envío del Formulario de Calificación ---
    const formValoracion = document.getElementById('form-valoracion');
    if(formValoracion) {
        formValoracion.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            if (!formData.get('estrellas')) {
                alert('Por favor, selecciona una calificación de estrellas.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const response = await fetch('/valoraciones/guardar', { method: 'POST', body: formData });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Error al guardar');

                alert('¡Gracias por tu calificación!');
                document.getElementById('modal-valoracion').style.display = 'none';
                this.reset();

                const mainForm = document.getElementById('form-chat');
                const productoId = mainForm.querySelector('input[name="productoId"]').value;
                const contactoId = mainForm.querySelector('input[name="destinatarioId"]').value;
                const chatResponse = await fetch(`/mensajes/chat?productoId=${productoId}&contactoId=${contactoId}`);
                const chatData = await chatResponse.json();
                document.getElementById('chat-activo').innerHTML = chatData.html;
                scrollToBottom();

            } catch (error) {
                alert(`Error: ${error.message}`);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Calificación';
            }
        });
    }

    function popularListaPlantillas(listaEl) {
        if (!listaEl) return;
        listaEl.innerHTML = ''; 
        if (Object.keys(plantillasDisponibles).length === 0) {
            listaEl.innerHTML = '<p style="padding:1rem; color:grey; text-align:center;">No hay plantillas.</p>';
            return;
        }
        for (const id in plantillasDisponibles) {
            const plantilla = plantillasDisponibles[id];
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = plantilla.nombre;
            btn.dataset.plantillaId = id;
            // El listener para estos botones se manejará por delegación
            listaEl.appendChild(btn);
        }
    }
    
    async function cargarYPrepararPlantillas() {
        if (plantillasDesdePHP && Object.keys(plantillasDesdePHP).length > 0) {
            plantillasDisponibles = plantillasDesdePHP;
        } else {
            // Simulación si es necesario para desarrollo local sin PHP
            plantillasDisponibles = { /* ... tu objeto de simulación ... */ };
        }
        // Si la lista de plantillas ya existe en el DOM (chat inicial cargado), la populamos
        const listaPlantillasInicial = document.getElementById('lista-plantillas');
        if (listaPlantillasInicial) {
            popularListaPlantillas(listaPlantillasInicial);
        }
    }

    function seleccionarPlantilla(id) {
        actualizarInfoChatActualParaPlantillas();
        plantillaSeleccionadaId = id;
        const plantilla = plantillasDisponibles[id];

        if (modalPlantilla && plantilla) {
            modalPlantillaTitulo.textContent = `Personalizar: ${plantilla.nombre}`;
            let textoConPlaceholders = plantilla.texto;
            if (nombreClienteActual) textoConPlaceholders = textoConPlaceholders.replace(/\[nombre_cliente\]/gi, nombreClienteActual);
            if (nombreProductoActual) textoConPlaceholders = textoConPlaceholders.replace(/\[nombre_producto\]/gi, nombreProductoActual);
            modalPlantillaTexto.value = textoConPlaceholders;
            
            modalPlantillaPlaceholders.innerHTML = ''; 
            if (plantilla.placeholders && plantilla.placeholders.length > 0) {
                const p = document.createElement('p');
                p.textContent = 'Puedes reemplazar o completar los siguientes campos:';
                modalPlantillaPlaceholders.appendChild(p);
                plantilla.placeholders.forEach(ph => {
                    if (textoConPlaceholders.includes(ph)) { 
                        const code = document.createElement('code');
                        code.textContent = ph;
                        modalPlantillaPlaceholders.appendChild(code);
                        modalPlantillaPlaceholders.appendChild(document.createTextNode(' '));
                    }
                });
            }
            modalPlantilla.style.display = 'flex';
            const listaPlantillasEl = document.getElementById('lista-plantillas'); // Re-obtener por si acaso
            if(listaPlantillasEl) listaPlantillasEl.style.display = 'none'; 
        }
    }


    // --- MANEJO DE EVENTOS CON DELEGACIÓN para elementos dentro de #chat-activo ---
    if (chatActivo) {
        chatActivo.addEventListener('click', function(event) {
            const target = event.target;

            // Botón para mostrar/ocultar lista de plantillas
            const btnMostrar = target.closest('#btn-mostrar-plantillas');
            if (btnMostrar) {
                const listaEl = document.getElementById('lista-plantillas'); // Buscarla dentro del chat actual
                if (listaEl) {
                    if (listaEl.children.length === 0 || Object.keys(plantillasDisponibles).length === 0) {
                        popularListaPlantillas(listaEl);
                    }
                    listaEl.style.display = listaEl.style.display === 'none' ? 'block' : 'none';
                }
                return; // Prevenir que otros listeners (como el de cerrar lista) se disparen inmediatamente
            }

            // Botón para seleccionar una plantilla de la lista
            const plantillaBtn = target.closest('#lista-plantillas button[data-plantilla-id]');
            if (plantillaBtn) {
                seleccionarPlantilla(plantillaBtn.dataset.plantillaId);
                return;
            }

            // Botón de compartir contacto
            const btnContacto = target.closest('#btn-contacto');
            if (btnContacto) {
                manejarClickBotonContacto();
                return;
            }

            // Botón de adjuntar archivo (solo para abrir el input file)
            const btnAdjuntar = target.closest('.chat__adjuntar');
            if (btnAdjuntar && !target.matches('input[type="file"]')) { // Evitar si el clic es en el input mismo
                const inputFile = btnAdjuntar.querySelector('.chat__input-archivo');
                if (inputFile) inputFile.click();
                return;
            }
        });
        
        // Listener para el cambio en el input de archivo (dentro del chat)
            chatActivo.addEventListener('change', function(event) {
            if (event.target && event.target.id === 'input-archivo') {
                manejarCambioInputArchivo(event.target);
            }
        });
    }

    function manejarClickBotonContacto() {
        const formChatEl = document.getElementById('form-chat');
        if (!formChatEl) {
            console.error("Formulario de chat no encontrado para botón de contacto.");
            return;
        }

        const direccionComercialInput = formChatEl.querySelector('#direccionComercial');
        const vendedorTelefonoInput = formChatEl.querySelector('#vendedorTelefono');
        const vendedorEmailInput = formChatEl.querySelector('#vendedorEmail');
        
        let direccionObj = null; // Por defecto null si no hay datos válidos
        if (direccionComercialInput && direccionComercialInput.value) {
            try {
                const dirData = JSON.parse(direccionComercialInput.value);
                // El valor es un array con un objeto, o un objeto directamente
                const parsedDir = Array.isArray(dirData) ? dirData[0] : dirData;
                // Solo construimos el objeto direccion si hay una calle
                if (parsedDir && typeof parsedDir === 'object' && parsedDir.calle && parsedDir.calle.trim() !== '') {
                    direccionObj = { // Crear un nuevo objeto con solo los campos que quieres
                        calle: parsedDir.calle || '',
                        colonia: parsedDir.colonia || '',
                        ciudad: parsedDir.ciudad || '',
                        estado: parsedDir.estado || '',
                        codigo_postal: parsedDir.codigo_postal || ''
                    };
                    // Filtrar propiedades vacías del objeto direccionObj si es necesario
                    direccionObj = Object.fromEntries(Object.entries(direccionObj).filter(([_, v]) => v !== ''));
                    if (Object.keys(direccionObj).length === 0) { // Si quedó vacío después de filtrar
                        direccionObj = null;
                    }

                }
            } catch (e) { 
                console.error("Error parseando direccionComercialInput para contacto:", e);
                direccionObj = null; // En caso de error, no enviar dirección
            }
        }

        const telefono = vendedorTelefonoInput ? vendedorTelefonoInput.value.trim() : '';
        const email = vendedorEmailInput ? vendedorEmailInput.value.trim() : '';

        // Este es el objeto que se convertirá en el string JSON para el contenido del mensaje
        const datosParaMensajeJSON = {
            direccion: direccionObj, // Será null si no hay calle, o el objeto de dirección
            telefono: telefono,
            email: email
        };

        // console.log('Datos de contacto (objeto) a enviar:', datosParaMensajeJSON);

        const formData = new FormData(formChatEl);
        formData.set('tipo', 'contacto'); // Tipo general del mensaje
        formData.set('mensaje', JSON.stringify(datosParaMensajeJSON)); // Contenido del mensaje es el JSON

        fetch('/mensajes/enviar', { method: 'POST', body: formData })
        .then(response => response.ok ? response.json() : response.json().then(err => Promise.reject(err)))
        .then(data => {
            if (data.success && data.mensaje) {
                const tipoInput = formChatEl.querySelector('input[name="tipo"]');
                if(tipoInput) tipoInput.value = 'texto';
                formChatEl.reset(); 
                
                appendMessage(data.mensaje);
                currentUltimoId = data.mensaje.id;
                scrollToBottom();
                fetch(`/mensajes/lista-conversaciones`).then(res => res.json()).then(sData => { // Endpoint correcto
                    if(sData.conversaciones) actualizarListaConversaciones(sData.conversaciones);
                });
            } else {
                alert('Error al enviar contacto: ' + (data.errores ? data.errores.join(', ') : 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en petición al enviar contacto:', error);
            alert('Error en petición: ' + (error.errores ? error.errores.join(', ') : (error.message || 'Error de red')));
        });
    }


    // Listeners para el modal (que está fuera de #chat-activo, según mi sugerencia)
    if (btnCerrarModalPlantilla) {
        btnCerrarModalPlantilla.addEventListener('click', () => {
            if (modalPlantilla) modalPlantilla.style.display = 'none';
            plantillaSeleccionadaId = null;
        });
    }

    if (modalPlantilla) { // Para cerrar el modal al hacer clic fuera
        modalPlantilla.addEventListener('click', function(event) {
            if (event.target === modalPlantilla) {
                modalPlantilla.style.display = 'none';
                plantillaSeleccionadaId = null;
            }
        });
    }

    if (btnEnviarPlantillaPersonalizada) {
        btnEnviarPlantillaPersonalizada.addEventListener('click', async () => {
            if (!plantillaSeleccionadaId) return;
            const formChatEl = document.getElementById('form-chat'); 
            if (!formChatEl) return;

            const formData = new FormData(formChatEl); 
            formData.set('mensaje', modalPlantillaTexto.value);
            formData.set('tipo', 'plantilla_auto'); 
            formData.set('plantilla_id', plantillaSeleccionadaId); 

            try {
                const response = await fetch('/mensajes/enviar', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success && data.mensaje) {
                    appendMessage(data.mensaje);
                    currentUltimoId = data.mensaje.id;
                    scrollToBottom();
                    fetch(`/mensajes/lista-conversaciones`).then(res => res.json()).then(sData => {
                        if(sData.conversaciones) actualizarListaConversaciones(sData.conversaciones);
                    });
                } else {
                    console.error('Error enviando plantilla:', data.errores || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error en fetch al enviar plantilla:', error);
            } finally {
                if (modalPlantilla) modalPlantilla.style.display = 'none';
                plantillaSeleccionadaId = null;
            }
        });
    }

    document.querySelector('.contactos__lista').addEventListener('click', async (e) => {
        const contactoDiv = e.target.closest('.contacto');
        if (!contactoDiv) return;

        const productoId = contactoDiv.dataset.productoId;
        const contactoId = contactoDiv.dataset.contactoId;
        
        try {
            const response = await fetch(`/mensajes/chat?productoId=${productoId}&contactoId=${contactoId}`);
            const data = await response.json();
            
            if (chatActivo) {
                chatActivo.innerHTML = data.html; 
                actualizarInfoChatActualParaPlantillas(); 
                const nuevaListaPlantillasEl = document.getElementById('lista-plantillas');
                if (nuevaListaPlantillasEl) { // Si el nuevo HTML contiene la lista
                    popularListaPlantillas(nuevaListaPlantillasEl);
                }
            }
            scrollToBottom();
            currentUltimoId = data.ultimoId;
            inicializarPolling(productoId, contactoId);
        } catch (error) {
            console.error('Error cargando el chat:', error);
        }
    });
    
    function actualizarInfoChatActualParaPlantillas() {
        const chatHeaderInfo = document.querySelector('#chat-activo .chat__info h3');
        if (chatHeaderInfo && chatHeaderInfo.offsetParent !== null) {
            const parts = chatHeaderInfo.textContent.split('•');
            nombreClienteActual = parts[0] ? parts[0].trim() : '';
            nombreProductoActual = parts[1] ? parts[1].trim() : '';
        } else {
            nombreClienteActual = '';
            nombreProductoActual = '';
        }
    }
    
    function setupInitialPageLoadFeatures() {
        const chatActivoInicial = document.getElementById('chat-activo');
        const chatVacioInfo = chatActivoInicial ? chatActivoInicial.querySelector('.chat__vacio') : null;

        if (chatActivoInicial && (!chatVacioInfo || chatVacioInfo.offsetParent === null )) {
            actualizarInfoChatActualParaPlantillas();
            const listaEl = document.getElementById('lista-plantillas');
            if (listaEl) { // Si el chat inicial ya tiene la lista
                popularListaPlantillas(listaEl);
            }
        }
    }

    cargarYPrepararPlantillas(); // Carga las plantillas desde PHP a la variable JS
    setupInitialPageLoadFeatures(); // Configura para el chat cargado inicialmente (si existe)

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
                            const mensajeElement = document.querySelector(`.mensaje[data-id="${msgId}"]`);
                            if(mensajeElement) {
                                const statusContainer = mensajeElement.querySelector('.mensaje__status');
                                // Añadir el indicador de "leído" solo si no existe ya
                                if (statusContainer && !statusContainer.querySelector('.mensaje__leido')) {
                                    const leidoIndicator = document.createElement('i');
                                    leidoIndicator.className = 'fa-solid fa-check-double mensaje__leido'; 
                                    statusContainer.appendChild(leidoIndicator);
                                }
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
        if (!container) return;
        const isEnviado = mensaje.remitenteId == <?= $_SESSION['id'] ?? 0 ?>;

        // Construir el indicador de mensaje automático
        let indicadorAutoHTML = '';
        if (mensaje.tipo === 'plantilla_auto') {
            indicadorAutoHTML = '<small class="mensaje__indicador-auto">Mensaje automático</small>';
        }

        const messageHtml = `
            <div class="mensaje mensaje--${isEnviado ? 'enviado' : 'recibido'}" data-id="${mensaje.id}">
                <div class="mensaje__burbuja ${mensaje.tipo !== 'texto' && mensaje.tipo !== 'plantilla_auto' ? 'mensaje--contenido-especial' : ''}">
                    ${renderContent(mensaje)} 
                    ${indicadorAutoHTML} 
                    <div class="mensaje__status">
                        <span class="mensaje__fecha">
                            ${new Date(mensaje.creado).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}
                        </span>
                        ${ isEnviado && mensaje.leido == 1 ? '<i class="fa-solid fa-check-double mensaje__leido"></i>' : '' }
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', messageHtml);
        scrollToBottom();
    }

    function renderContent(mensaje) {
        let contenidoParaMostrar = mensaje.contenido;

        if (typeof contenidoParaMostrar === 'string') {
            // Quita slashes que podrían haber venido del servidor si no se limpiaron antes de json_encode
            // o si el proceso de renderizado PHP inicial los tenía y se re-leyó de alguna manera.
            // Esto es una capa extra de seguridad/limpieza.
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

    // Listener para cerrar la lista desplegable de plantillas (no el modal)
    document.addEventListener('click', function(event) {
        const listaEl = document.getElementById('lista-plantillas'); // Re-obtener por si se recreó
        const btnMostrar = document.getElementById('btn-mostrar-plantillas'); // Re-obtener

        if (listaEl && btnMostrar && listaEl.style.display === 'block') {
            const isClickInsideLista = listaEl.contains(event.target);
            const isClickOnBoton = btnMostrar.contains(event.target);
            if (!isClickInsideLista && !isClickOnBoton) {
                listaEl.style.display = 'none';
            }
        }
    });

    // Listener para el submit del formulario principal
    const formChatEl = document.getElementById('form-chat'); // Obtener una sola vez
    if (formChatEl) {
        formChatEl.addEventListener('submit', function(e) { // Pasar 'e' para llamar a manejarEnvio
            // ... tu lógica para setear tipo 'texto' ...
            // Llamar a manejarEnvio si es necesario o dejar que el listener de delegación lo haga
        });
    }

    // Delegación para el submit del formulario principal
    document.addEventListener('submit', (e) => {
        if (e.target && e.target.matches('#form-chat')) {
            // Antes de llamar a manejarEnvio, asegurarse que el tipo es 'texto' si no es archivo/plantilla
            const form = e.target;
            const inputFile = form.querySelector('input[type="file"]');
            const tipoInput = form.querySelector('input[name="tipo"]');

            if (tipoInput && tipoInput.value !== 'plantilla_auto' && tipoInput.value !== 'contacto') {
                if (!inputFile || !inputFile.files || inputFile.files.length === 0) {
                    tipoInput.value = 'texto';
                }
            }
            manejarEnvio(e);
        }
    });
});
</script>