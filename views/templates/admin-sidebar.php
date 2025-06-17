<aside class="dashboard__sidebar">
    <nav class="dashboard__menu">
        <a href="/admin/dashboard" class="dashboard__enlace <?php echo pagina_actual('/dashboard') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-house dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                Inicio
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

        <a href="/admin/faqs" class="dashboard__enlace <?php echo pagina_actual('/faqs') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-question-circle dashboard__icono"></i>
            <span class="dashboard__menu-texto">
                FAQs
            </span>
        </a>
    </nav>
</aside>