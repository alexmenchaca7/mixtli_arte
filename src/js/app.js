document.addEventListener("DOMContentLoaded", () => {
    // Variables para el Modal de Categorías
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

    // --- LÓGICA PARA EL NUEVO MENÚ DESPLEGABLE "EXPLORAR" ---
    const dropdown = document.querySelector('.dropdown');

    if (dropdown) {
        const dropdownButton = dropdown.querySelector('.dropdown__boton');

        dropdownButton.addEventListener('click', (event) => {
            event.stopPropagation(); // Evita que el evento se propague al 'window'
            dropdown.classList.toggle('mostrar');
        });

        // Cierra el menú si se hace clic fuera de él
        window.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('mostrar');
            }
        });
    }

    /** CONTADORES ANIMADOS **/
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

    // --- DASHBOARD RESPONSIVE ---
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

    // --- FAVORITOS ---
    document.querySelectorAll('.favorito-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            if (this.disabled) return;
            const productoId = button.dataset.productoId;
            const icon = button.querySelector('i');

            try {
                const response = await fetch('/favoritos/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `productoId=${encodeURIComponent(productoId)}`
                });

                const data = await response.json();
                
                if(!response.ok) throw new Error(data.error || 'Error en la solicitud');
                
                // Toggle del ícono
                icon.classList.toggle('fa-regular');
                icon.classList.toggle('fa-solid');

                // Mostrar notificación
                const existingAlert = document.querySelector('.alert-notification');
                if (existingAlert) existingAlert.remove();

                const message = data.action === 'added' 
                    ? `<i class="fas fa-check-circle"></i> Agregado a favoritos` 
                    : `<i class="fas fa-trash-alt"></i> Eliminado de favoritos`;

                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert-notification';
                alertDiv.innerHTML = message;
                alertDiv.style.backgroundColor = data.action === 'added' 
                    ? '#4CAF50' 
                    : '#f44336';

                document.body.appendChild(alertDiv);

                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 2500);

            } catch (error) {
                console.error('Error:', error);
                alert(error.message);
            }
        });
    });


    // NO INTERESA
    document.body.addEventListener('click', async function(e) {
        if (e.target.closest('.no-interesa-btn')) {
            const button = e.target.closest('.no-interesa-btn');
            const productoId = button.dataset.productoId;
            
            try {
                const response = await fetch('/api/productos/no-interesa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productoId: productoId })
                });

                const data = await response.json();
                if (data.success) {
                    // Ocultar la tarjeta del producto
                    const card = button.closest('.producto[data-producto-card-id="' + productoId + '"]');
                    if (card) {
                        card.style.transition = 'opacity 0.5s ease';
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 500);
                    }
                    
                    // Mostrar notificación
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert-notification';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> No volveremos a mostrarte este producto.';
                    alertDiv.style.backgroundColor = '#2196F3'; // Un color azul para la notificación
                    document.body.appendChild(alertDiv);

                    setTimeout(() => {
                        alertDiv.style.opacity = '0';
                        setTimeout(() => alertDiv.remove(), 300);
                    }, 3000);

                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('No se pudo completar la acción.');
            }
        }
    });


    // ELIMINAR PRODUCTO NO INTERESADO DE LA LISTA DE PREFERENCIAS
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-eliminar-preferencia')) {
            const itemDiv = e.target.closest('.item-no-interesado');
            const productoId = itemDiv.dataset.productoId;

            try {
                // --- CORRECCIÓN FINAL AQUÍ ---
                // La URL ahora coincide exactamente con tu archivo de rutas.
                const response = await fetch('/perfil/eliminar-no-interesa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productoId: productoId })
                });
                
                if (!response.ok) {
                    // Esto te ayudará a ver errores HTTP en la consola en el futuro.
                    const errorText = await response.text();
                    throw new Error(`Error HTTP ${response.status}: ${errorText}`);
                }

                const data = await response.json();
                if (data.success) {
                    itemDiv.remove();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error al procesar la solicitud:', error);
                // Mostramos el error para que sea más fácil depurar.
                alert('No se pudo completar la acción. Revisa la consola para más detalles.');
            }
        }
    });

    

    // MODAL REPORTE DE VALORACIÓN
    const modal = document.getElementById('modal-reporte-valoracion');
    if (!modal) return;

    const form = document.getElementById('form-reporte-valoracion');
    const closeBtnValoracion = modal.querySelector('.modal-reporte__cerrar');
    const valoracionIdInput = document.getElementById('reporte-valoracion-id');

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.reportar-btn')) {
            const valoracionId = e.target.closest('.reportar-btn').dataset.valoracionId;
            valoracionIdInput.value = valoracionId;
            modal.style.display = 'flex';
        }
    });

    closeBtnValoracion.onclick = () => {
        modal.style.display = 'none';
    };

    window.onclick = (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    };

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/valoraciones/reportar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            
            alert(result.message || result.error);

            if(response.ok) {
                modal.style.display = 'none';
                form.reset();
            }
        } catch (error) {
            alert('Error al conectar con el servidor. Inténtalo de nuevo.');
        }
    });
});