<aside class="dashboard__sidebar">
    <nav class="dashboard__menu">
        <a href="/admin/dashboard" class="dashboard__enlace <?php echo pagina_actual('/dashboard') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-house dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Inicio
            </span>  
        </a>

        <a href="/admin/usuarios" class="dashboard__enlace <?php echo pagina_actual('/usuarios') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-users dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Usuarios
            </span>  
        </a>

        <a href="/admin/perfil" class="dashboard__enlace <?php echo pagina_actual('/perfil') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-user dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Perfil
            </span>  
        </a>
    </nav>
</aside>