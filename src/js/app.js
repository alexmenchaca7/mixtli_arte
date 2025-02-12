document.addEventListener("DOMContentLoaded", () => {

    // 🟢 Contadores Animados
    const contadores = document.querySelectorAll(".contador");
    const duracion = 2000; // Duración total en milisegundos (2 segundos)

    const iniciarContador = (contador) => {
        const objetivo = +contador.dataset.target;
        let inicio = 0;
        const incremento = objetivo / (duracion / 16); // Ajusta el incremento basado en el tiempo total

        const actualizarContador = () => {
            inicio += incremento;
            if (inicio >= objetivo) {
                contador.innerText = `+${objetivo}`; // Muestra el valor final
            } else {
                contador.innerText = `+${Math.floor(inicio)}`;
                requestAnimationFrame(actualizarContador);
            }
        };

        actualizarContador();
    };

    // Detecta si los contadores están en pantalla y los inicia
    const observarContadores = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                // Reiniciar el contador a 0 antes de volver a animar
                entrada.target.innerText = "+0"; 
                iniciarContador(entrada.target);
            }
        });
    }, { threshold: 0.6 });

    contadores.forEach((contador) => observarContadores.observe(contador));



    /** DUPLICANDO LAS MARCAS PARA EFECTO INFINITO DEL CARRUSEL **/
    let copy = document.querySelector(".logos-slide").cloneNode(true);
    document.querySelector('.carrusel-logos').appendChild(copy);
});