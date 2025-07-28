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
        // Nos aseguramos de que el objetivo sea el botón correcto
        if (e.target.classList.contains('btn-eliminar-preferencia')) {
            
            const boton = e.target; // Guardamos la referencia al botón
            const itemDiv = boton.closest('.item-no-interesado');
            const productoId = itemDiv.dataset.productoId;

            // 1. Si el botón ya fue presionado, no hacemos nada más.
            if (boton.disabled) {
                return;
            }

            // 2. Deshabilitamos el botón INMEDIATAMENTE para prevenir doble clic.
            boton.disabled = true;
            boton.textContent = 'Eliminando...'; // Feedback visual

            try {
                const response = await fetch('/perfil/eliminar-no-interesa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productoId: productoId })
                });
                
                const data = await response.json();

                // Verificamos si la operación en el servidor fue exitosa (código 200)
                if (response.ok && data.success) {
                    // Animación suave para eliminar el elemento
                    itemDiv.style.transition = 'opacity 0.3s ease';
                    itemDiv.style.opacity = '0';
                    setTimeout(() => itemDiv.remove(), 300);
                } else {
                    // Si el servidor devuelve un error, lo mostramos y reactivamos el botón
                    throw new Error(data.error || 'Error desconocido del servidor.');
                }

            } catch (error) {
                console.error('Error al procesar la solicitud:', error);
                alert(error.message); // Mostramos un mensaje de error claro
                
                // Reactivamos el botón para que el usuario pueda intentarlo de nuevo
                boton.disabled = false;
                boton.textContent = 'Eliminar';
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



    // --- CONTADOR DE MENSAJES NO LEÍDOS ---
    const badges = document.querySelectorAll('.notification-badge');

    if (badges.length > 0) {
        const fetchUnreadCount = async () => {
            // No hacer la petición si la pestaña no está visible
            if (document.hidden) {
                return;
            }
            try {
                const response = await fetch('/mensajes/unread-count');
                if (!response.ok) {
                    // Si la sesión expira o hay un error, detenemos el polling
                    if(response.status === 401 || response.status === 403) {
                        clearInterval(pollingInterval);
                    }
                    return;
                }
                const data = await response.json();
                updateBadges(data.unread_count);
            } catch (error) {
                console.error('Error al obtener el contador de no leídos:', error);
            }
        };

        const updateBadges = (count) => {
            // Itera sobre cada badge encontrado y lo actualiza
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
        };

        // Iniciar polling
        fetchUnreadCount(); // Llamada inicial
        const pollingInterval = setInterval(fetchUnreadCount, 15000); // Consultar cada 15 segundos
    }
});