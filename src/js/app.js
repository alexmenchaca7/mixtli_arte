document.addEventListener("DOMContentLoaded", () => {
    // 🟢 Variables para el Modal de Categorías
    const categoriasBtn = document.getElementById("categorias-btn");
    const categoriasModal = document.getElementById("categorias-modal");
    const closeBtn = document.querySelector(".close");

    if (categoriasBtn && categoriasModal && closeBtn) {
        function enableScroll() {
            document.body.style.overflow = "";
        }

        categoriasModal.classList.remove("show");

        categoriasBtn.addEventListener("click", () => {
            categoriasModal.classList.add("show");
            document.body.style.overflow = "hidden";
        });

        closeBtn.addEventListener("click", () => {
            categoriasModal.classList.remove("show");
            enableScroll();
        });

        window.addEventListener("click", (e) => {
            if (e.target === categoriasModal) {
                categoriasModal.classList.remove("show");
                enableScroll();
            }
        });
    }

    // 🟢 Contadores Animados
    const contadores = document.querySelectorAll(".contador");
    const duracion = 2000; // Duración total en milisegundos (2 segundos)

    if (contadores.length === 0) return;

    const iniciarContador = (contador) => {
        const objetivo = parseInt(contador.dataset.target, 10);
        let inicio = 0;
        const incremento = objetivo / (duracion / 16);

        const actualizarContador = () => {
            inicio += incremento;
            if (inicio >= objetivo) {
                contador.innerText = `+${objetivo}`;
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
                entrada.target.classList.remove("animado"); // Elimina la clase para reiniciar
                entrada.target.innerText = "+0"; // Resetea el número a 0
                iniciarContador(entrada.target);
            }
        });
    }, { threshold: 0.3 }); // Sensibilidad del observador    

    // Aplicar el observador a cada contador
    contadores.forEach((contador) => observarContadores.observe(contador));

    // 🚀 Forzar ejecución manual si el IntersectionObserver falla
    window.addEventListener("load", () => {
        contadores.forEach((contador) => {
            if (!contador.classList.contains("animado")) {
                iniciarContador(contador);
            }
        });
    });
});