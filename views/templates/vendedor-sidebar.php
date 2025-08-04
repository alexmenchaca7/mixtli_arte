<aside class="dashboard__sidebar">
    <nav class="dashboard__menu">
        <a href="/vendedor/dashboard" class="dashboard__enlace <?php echo pagina_actual('/dashboard') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-house dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Inicio
            </span>  
        </a>

        <a href="/vendedor/productos" class="dashboard__enlace <?php echo pagina_actual('/productos') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-palette dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Productos
            </span>  
        </a>

        <a href="/vendedor/mensajes" class="dashboard__enlace <?php echo pagina_actual('/mensajes') ? 'dashboard__enlace--actual' : ''; ?>">
            <div class="icono-badge-container">    
                <i class="fa-solid fa-comments dashboard__icono"></i>
                <span class="message-badge" style="display: none;"></span>
            </div>
            <span class="dashboard__menu-texto">
                Mensajes
            </span>  
        </a>

        <a href="/notificaciones" class="dashboard__enlace <?php echo pagina_actual('/notificaciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <div class="icono-badge-container">
                <i class="fa-solid fa-bell dashboard__icono"></i>
                <span class="notification-badge" style="display: none;"></span>
            </div>
            <span class="dashboard__menu-texto">Notificaciones</span>
        </a>

        <a href="/vendedor/valoraciones" class="dashboard__enlace <?php echo pagina_actual('/valoraciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-star-half-stroke dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Valoraciones
            </span>
        </a>
        
        <a href="/vendedor/perfil" class="dashboard__enlace <?php echo pagina_actual('/perfil') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-user dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Perfil
            </span>  
        </a>
        
        <a href="/faqs" class="dashboard__enlace <?php echo pagina_actual('/faqs') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-question-circle dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                FAQs
            </span>
        </a>

        <a href="/manual-usuario?tipo=vendedor" class="dashboard__enlace <?php echo pagina_actual('/manual-usuario') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-book-open dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Manual de Usuario
            </span>
        </a>

        <a href="/terminos-condiciones" class="dashboard__enlace <?php echo pagina_actual('/terminos-condiciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-file-contract dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Términos
            </span>
        </a>

        <a href="/politica-privacidad" class="dashboard__enlace <?php echo pagina_actual('/politica-privacidad') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-shield-alt dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Privacidad
            </span>
        </a>

        <a href="/contacto" class="dashboard__enlace <?php echo pagina_actual('/contacto') ? 'dashboard__enlace--actual' : ''; ?>"> <i class="fa-solid fa-headset dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Contacto
            </span>  
        </a>

        <form method="POST" action="/logout" class="dashboard__enlace dashboard__enlace--logout">
            <button type="submit" class="dashboard__menu-texto">
                <i class="fa-solid fa-right-from-bracket dashboard__icono"></i>
                <span class="dashboard__menu-texto">
                    Cerrar Sesión
                </span>
            </button>
        </form>
    </nav>
</aside>