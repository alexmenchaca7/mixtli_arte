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
            <i class="fa-solid fa-comments dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Mensajes
            </span>  
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

        <a href="/contacto" class="dashboard__enlace <?php echo pagina_actual('/contacto') ? 'dashboard__enlace--actual' : ''; ?>"> <i class="fa-solid fa-headset dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Contacto
            </span>  
        </a>
    </nav>
</aside>