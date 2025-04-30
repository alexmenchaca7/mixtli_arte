<div class="mensajeria">
    <!-- Lista de contactos -->
    <div class="mensajeria__contactos">
        <div class="contacto">
            <picture>
                <source srcset="/img/usuarios/default.png" type="image/png">
                <img loading="lazy" src="/img/usuarios/default.png" class="contacto__imagen" alt="Perfil">
            </picture>
            <div class="contacto__info">
                <h3 class="contacto__nombre">Juan Pérez</h3>
                <p class="contacto__estado">En línea</p>
            </div>
            <span class="contacto__fecha">15:32</span>
        </div>
        <!-- Repetir más contactos -->
    </div>

    <!-- Área de chat -->
    <div class="chat">
        <!-- Cabecera -->
        <div class="chat__header">
            <picture>
                <source srcset="/img/usuarios/default.png" type="image/png">
                <img loading="lazy" src="/img/usuarios/default.png" class="chat__imagen" alt="Perfil">
            </picture>
            <div class="chat__info">
                <h3 class="chat__nombre">Juan Pérez</h3>
                <p class="chat__estado">En línea</p>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="chat__mensajes">
            <!-- Mensaje recibido -->
            <div class="mensaje mensaje--recibido">
                <div class="mensaje__burbuja">
                    ¡Hola! ¿Cómo estás?
                    <span class="mensaje__fecha mensaje__fecha--recibido">15:30</span>
                </div>
            </div>

            <!-- Mensaje enviado -->
            <div class="mensaje mensaje--enviado">
                <div class="mensaje__burbuja">
                    ¡Hola! Estoy bien, gracias.
                    <span class="mensaje__fecha mensaje__fecha--enviado">15:32</span>
                </div>
            </div>

            <!-- Mensaje con imagen recibido -->
            <div class="mensaje mensaje--recibido">
                <div class="mensaje__burbuja mensaje--contenido-especial">
                    <picture>
                        <source srcset="/img/productos/c951c15d407ba02e2d56faecf74a2631.webp" type="image/webp">
                        <source srcset="/img/productos/c951c15d407ba02e2d56faecf74a2631.png" type="image/png">
                        <img
                            loading="lazy"
                            src="/img/productos/c951c15d407ba02e2d56faecf74a2631.png" 
                            class="mensaje__imagen" 
                            alt="Imagen compartida">
                    </picture>
                    <span class="mensaje__fecha mensaje__fecha--recibido">15:34</span>
                </div>
            </div>

            <!-- Mensaje con PDF enviado -->
            <div class="mensaje mensaje--enviado">
                <div class="mensaje__burbuja mensaje--contenido-especial">
                    <a href="/documentos/reporte.pdf" 
                    class="mensaje__documento"
                    download>
                        <i class="fa-regular fa-file-pdf mensaje__icono-documento mensaje__icono-documento--enviado"></i>
                        <div class="mensaje__archivo-info">
                            <div class="mensaje__nombre-archivo">Reporte-final.pdf</div>
                            <div class="mensaje__tamaño-archivo">2.4 MB</div>
                        </div>
                    </a>
                    <span class="mensaje__fecha mensaje__fecha--enviado">15:35</span>
                </div>
            </div>

            <!-- Mensaje con imagen enviada -->
            <div class="mensaje mensaje--enviado">
                <div class="mensaje__burbuja mensaje--contenido-especial">
                    <picture>
                        <source srcset="/img/productos/c951c15d407ba02e2d56faecf74a2631.webp" type="image/webp">
                        <source srcset="/img/productos/c951c15d407ba02e2d56faecf74a2631.png" type="image/png">
                        <img
                            loading="lazy"
                            src="/img/productos/c951c15d407ba02e2d56faecf74a2631.png" 
                            class="mensaje__imagen" 
                            alt="Foto vacaciones">
                    </picture>
                    <span class="mensaje__fecha mensaje__fecha--enviado">15:36</span>
                </div>
            </div>

            <!-- Mensaje con PDF recibido -->
            <div class="mensaje mensaje--recibido">
                <div class="mensaje__burbuja mensaje--contenido-especial">
                    <a href="/documentos/reporte.pdf" 
                    class="mensaje__documento"
                    download>
                        <i class="fa-regular fa-file-pdf mensaje__icono-documento mensaje__icono-documento--recibido"></i>
                        <div class="mensaje__archivo-info">
                            <div class="mensaje__nombre-archivo">Reporte-final.pdf</div>
                            <div class="mensaje__tamaño-archivo">2.4 MB</div>
                        </div>
                    </a>
                    <span class="mensaje__fecha mensaje__fecha--recibido">15:38</span>
                </div>
            </div>
        </div>

        <!-- Entrada de mensajes -->
        <div class="chat__entrada">
            <input type="text" class="chat__campo" placeholder="Escribe un mensaje...">
            <button class="chat__boton">
                <i class="fa-regular fa-paper-plane"></i>
                Enviar
            </button>
        </div>
    </div>
</div>