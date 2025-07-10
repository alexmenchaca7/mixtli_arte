<main class="auth">
    <h2 class="auth__heading">Cuenta Bloqueada Temporalmente</h2>
    <p class="auth__texto">Tu cuenta ha sido suspendida por violaciones a nuestras políticas.</p>

    <div class="bloqueo-info mt-5 mb-5">
        <h3>Motivo del Bloqueo:</h3>
        <p class="t-align">Acumulación de 3 violaciones a los términos y condiciones de la plataforma.</p>
        
        <h3>Tiempo Restante:</h3>
        <div id="countdown" data-bloqueo-hasta="<?php echo htmlspecialchars($_SESSION['bloqueado_hasta'] ?? ''); ?>">
            --:--:--:--
        </div>
    </div>

    <div class="acciones">
        <a href="/" class="acciones__enlace">Regresar a Inicio</a>
        <a href="/contacto" class="acciones__enlace">¿Crees que es un error? Contacta a Soporte</a>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownElement = document.getElementById('countdown');
    const fechaBloqueoHasta = new Date(countdownElement.dataset.bloqueoHasta).getTime();

    const updateCountdown = () => {
        const ahora = new Date().getTime();
        const distancia = fechaBloqueoHasta - ahora;

        if (distancia < 0) {
            countdownElement.innerHTML = "¡Tu cuenta ha sido reactivada! Por favor, <a href='/login'>inicia sesión</a>.";
            clearInterval(intervalo);
            return;
        }

        const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
        const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

        countdownElement.innerHTML = `${dias}d ${horas}h ${minutos}m ${segundos}s`;
    };

    const intervalo = setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>