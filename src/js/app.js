document.addEventListener("DOMContentLoaded", () => {
    // Variables para el Modal de Categorías (Tu código existente)
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

    /** CONTADORES ANIMADOS (Tu código existente) **/
    const contadores = document.querySelectorAll(".contador");
    const duracion = 500;

    if (contadores.length > 0) {
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

        const observarContadores = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) {
                    entrada.target.classList.remove("animado");
                    entrada.target.innerText = "+0";
                    iniciarContador(entrada.target);
                }
            });
        }, { threshold: 0.3 });

        contadores.forEach((contador) => observarContadores.observe(contador));

        window.addEventListener("load", () => {
            contadores.forEach((contador) => {
                if (!contador.classList.contains("animado")) {
                    iniciarContador(contador);
                }
            });
        });
    }

    // --- CÓDIGO DEFINITIVO PARA EL DASHBOARD RESPONSIVE ---
    const mobileMenuBtn = document.querySelector('.dashboard__mobile-menu');
    const sidebar = document.querySelector('.dashboard__sidebar');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar--mostrar');
        });

        // Opcional: Cerrar el menú haciendo clic fuera del sidebar
        document.body.addEventListener('click', function(e) {
            // Si el clic NO fue en el sidebar Y NO fue en el botón del menú
            if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                sidebar.classList.remove('sidebar--mostrar');
            }
        });
    }
});