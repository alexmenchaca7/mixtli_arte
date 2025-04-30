<div class="mensajeria">
    <!-- Lista de contactos -->
    <div class="mensajeria__contactos">
        <!-- Header y buscador -->
        <div class="contactos__header">
            <h2 class="contactos__titulo">Chats</h2>
            <div class="contactos__busqueda">
                <input type="text" 
                       class="contactos__campo-busqueda" 
                       placeholder="Buscar conversaciones...">
                <i class="fa-solid fa-magnifying-glass contactos__icono-busqueda"></i>
            </div>
        </div>

        <!-- Lista de contactos -->
        <div class="contactos__lista">
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
            <!-- Mensaje del sistema -->
            <div class="mensaje-sistema">
                <div class="mensaje-sistema__contenido">
                    <i class="fa-solid fa-shield-halved mensaje-sistema__icono"></i>
                    <div class="mensaje-sistema__texto">
                        <strong>Chat seguro con cifrado de extremo a extremo</strong>
                        <p>• Evite realizar pagos en efectivo en lugares públicos<br>
                        • No compartimos su información personal<br>
                        • Mensajes cifrados con protocolo TLS/SSL</p>
                        <small>Última actualización: 01/01/2024</small>
                    </div>
                </div>
            </div>

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

            <!-- Ejemplo de mensaje de contacto en la conversación -->
            <div class="mensaje mensaje--recibido">
                <div class="mensaje__burbuja">
                    <div class="mensaje__contacto-info">
                        <div class="mensaje__contacto-item">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <span>Av. Principal 123, Ciudad</span>
                        </div>
                        <div class="mensaje__contacto-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>+52 55 1234 5678</span>
                        </div>
                        <div class="mensaje__contacto-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>contacto@empresa.com</span>
                        </div>
                    </div>
                    <span class="mensaje__fecha mensaje__fecha--enviado">15:40</span>
                </div>
            </div>
        </div>

        <!-- Entrada de mensajes -->
        <div class="chat__entrada">
            <button class="chat__adjuntar">
                <i class="fa-regular fa-file"></i>
                <input type="file" class="chat__input-archivo" accept="image/*,.pdf" multiple>
            </button>
            
            <!-- Nuevo botón para información de contacto -->
            <button class="chat__contacto">
                <i class="fa-regular fa-address-card"></i>
            </button>
            
            <input type="text" class="chat__campo" placeholder="Escribe un mensaje...">
            <button class="chat__boton">
                <i class="fa-regular fa-paper-plane"></i>
                Enviar
            </button>
        </div>
    </div>
</div>