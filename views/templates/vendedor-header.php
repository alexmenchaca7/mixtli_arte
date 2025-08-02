<header class="dashboard__header">
    <div class="dashboard__header-grid">
        <a class="dashboard__logo" href="/vendedor/dashboard">
            <img src="/build/img/logo.png" alt="Logo MixtliArte">
            <h2>MixtliArte</h2>
        </a>
        
        <button type="button" class="dashboard__mobile-menu">
            <div class="icono-badge-container">   
                <i class="fa-solid fa-bars"></i>
                <span class="hamburger-badge" style="display: none;"></span>
            </div>
        </button>
        
        <nav class="dashboard__nav">
            <span class="dashboard__usuario">
                <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>
            </span>
            <form class="dashboard__form" method="POST" action="/logout">
                <input type="submit" value="Cerrar Sesión" class="dashboard__submit-logout">
            </form>
        </nav>
        </div>
</header>