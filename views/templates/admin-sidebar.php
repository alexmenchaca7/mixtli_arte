<aside class="dashboard__sidebar">
    <nav class="dashboard__menu">
        <a href="/admin/dashboard" class="dashboard__enlace <?php echo pagina_actual('/dashboard') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-house dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Inicio
            </span>  
        </a>
        
        <a href="/notificaciones" class="dashboard__enlace <?php echo pagina_actual('/notificaciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <div class="icono-badge-container">
                <i class="fa-solid fa-bell dashboard__icono"></i>
                <span class="notification-badge" style="display: none;"></span>
            </div>
            <span class="dashboard__menu-texto">
                Notificaciones
            </span>
        </a>

        <a href="/admin/perfil" class="dashboard__enlace <?php echo pagina_actual('/perfil') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-user dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Perfil
            </span>  
        </a>

        <a href="/admin/categorias" class="dashboard__enlace <?php echo pagina_actual('/categorias') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-th-large dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Categorias
            </span>  
        </a>

        <a href="/admin/usuarios" class="dashboard__enlace <?php echo pagina_actual('/usuarios') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-users dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Usuarios
            </span>  
        </a>

        <a href="/admin/valoraciones" class="dashboard__enlace <?php echo pagina_actual('/valoraciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-gavel dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Moderación
            </span>
        </a>

        <a href="/admin/reportes" class="dashboard__enlace <?php echo pagina_actual('/reportes') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-flag dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Reportes Prod.
            </span>
        </a>

        <a href="/admin/reportes-valoraciones" class="dashboard__enlace <?php echo pagina_actual('/reportes-valoraciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-comment-slash dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Reportes Coment.
            </span>
        </a>

        <a href="/admin/sanciones" class="dashboard__enlace <?php echo pagina_actual('/sanciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-user-slash dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Sanciones
            </span>
        </a>

        <a href="/admin/faqs" class="dashboard__enlace <?php echo pagina_actual('/faqs') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-question-circle dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                FAQs
            </span>
        </a>

        <a href="/admin/soporte" class="dashboard__enlace <?php echo pagina_actual('/soporte') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-headset dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Soporte
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