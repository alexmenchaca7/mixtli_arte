document.addEventListener("DOMContentLoaded", () => {
    // --- FUNCIÓN REUTILIZABLE PARA NOTIFICACIONES ---
    function mostrarNotificacion(message, type = 'info') {
        const existingAlert = document.querySelector('.alert-notification');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert-notification';
        alertDiv.innerHTML = message;

        // Asignar color según el tipo de notificación
        switch (type) {
            case 'success':
                alertDiv.style.backgroundColor = '#4CAF50'; // Verde
                break;
            case 'error':
                alertDiv.style.backgroundColor = '#f44336'; // Rojo
                break;
            case 'info':
            default:
                alertDiv.style.backgroundColor = '#2196F3'; // Azul
                break;
        }

        document.body.appendChild(alertDiv);

        // Configura la desaparición
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300); // Espera a que la transición termine
        }, 3000);
    }


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

                mostrarNotificacion(message, data.action === 'added' ? 'success' : 'error');

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
                    mostrarNotificacion('<i class="fas fa-check-circle"></i> No volveremos a mostrarte este producto.', 'info');
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



    
    
    // --- LOGICA PARA LAS NOTIFICACIONES ---
    
    /**
     * Función genérica para crear un sistema de polling para contadores de no leídos.
     * @param {string} badgeSelector - El selector CSS para los badges a actualizar (ej. '.message-badge').
     * @param {string} endpoint - La URL del API para obtener el contador (ej. '/mensajes/unread-count').
     */
    const setupUnreadPolling = (badgeSelector, endpoint) => {
        const badges = document.querySelectorAll(badgeSelector);
        if (badges.length === 0) return;

        let pollingInterval;

        const fetchUnreadCount = async () => {
            if (document.hidden) return;
            try {
                const response = await fetch(endpoint);
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        clearInterval(pollingInterval);
                    }
                    return;
                }
                const data = await response.json();
                updateBadges(data.unread_count);
            } catch (error) {
                console.error(`Error de polling en ${endpoint}:`, error);
                clearInterval(pollingInterval); // Detener en caso de error de red
            }
        };

        const updateBadges = (count) => {
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
        };

        fetchUnreadCount();
        pollingInterval = setInterval(fetchUnreadCount, 3000);
    };

    // Función para combinar los contadores de mensajes y notificaciones para el menú hamburguesa
    const setupCombinedNotificationPolling = () => {
        // Seleccionamos los badges específicos del menú móvil y del dashboard de vendedor
        const combinedBadges = document.querySelectorAll('.mobile-menu .hamburger-badge, .dashboard__mobile-menu .hamburger-badge');
        if (combinedBadges.length === 0) return;

        let pollingInterval;

        const fetchCombinedCount = async () => {
            if (document.hidden) return;
            try {
                // Hacemos las dos peticiones en paralelo
                const [messagesRes, notificationsRes] = await Promise.all([
                    fetch('/mensajes/unread-count'),
                    fetch('/notificaciones/unread-count')
                ]);

                // Verificamos si alguna de las respuestas indica un problema de autenticación
                if (!messagesRes.ok || !notificationsRes.ok) {
                    if (messagesRes.status === 401 || messagesRes.status === 403 || notificationsRes.status === 401 || notificationsRes.status === 403) {
                        clearInterval(pollingInterval);
                    }
                    return;
                }

                const messagesData = await messagesRes.json();
                const notificationsData = await notificationsRes.json();

                // Sumamos los contadores
                const totalCount = (messagesData.unread_count || 0) + (notificationsData.unread_count || 0);
                
                // Actualizamos los badges del menú hamburguesa
                combinedBadges.forEach(badge => {
                    if (totalCount > 0) {
                        badge.textContent = totalCount > 9 ? '9+' : totalCount;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                });

            } catch (error) {
                console.error('Error en el polling combinado:', error);
                clearInterval(pollingInterval);
            }
        };

        fetchCombinedCount(); // Primera llamada inmediata
        pollingInterval = setInterval(fetchCombinedCount, 3000); // Polling cada 3 segundos
    };

    // Inicializar polling para los badges de mensajes, notificaciones y menu hamburguesa
    setupUnreadPolling('.dashboard__enlace .message-badge, .navegacion-principal .message-badge', '/mensajes/unread-count');
    setupUnreadPolling('.dashboard__enlace .notification-badge, .navegacion-principal .notification-badge', '/notificaciones/unread-count');
    setupCombinedNotificationPolling();

    const botonMarcarTodas = document.getElementById('marcar-todas-leidas');
    const notificacionesContenedor = document.querySelector('.notificaciones-contenedor');

    // Si no estamos en la página de notificaciones, no hacer nada más.
    if (!notificacionesContenedor) return;

    // Revisa si existen notificaciones no leídas en el DOM y ajusta la visibilidad del botón "Marcar todas como leídas".
    const checkUnreadButtonVisibility = () => {
        if (!botonMarcarTodas) return;
        const unreadItemsCount = document.querySelectorAll('.notificacion-item.no-leida').length;
        botonMarcarTodas.style.display = unreadItemsCount > 0 ? 'inline-flex' : 'none';
    };

    // Manejo del botón "Marcar todas como leídas"
    if (botonMarcarTodas) {
        // Guardamos el contenido original del botón una sola vez, fuera del listener.
        const originalButtonHTML = botonMarcarTodas.innerHTML;

        botonMarcarTodas.addEventListener('click', async () => {
            try {
                botonMarcarTodas.disabled = true;
                botonMarcarTodas.textContent = 'Marcando...';

                const response = await fetch('/notificaciones/marcar-todas-leidas', { method: 'POST' });
                const data = await response.json();

                if (data.success) {
                    document.querySelectorAll('.notificacion-item.no-leida').forEach(item => {
                        item.classList.remove('no-leida');
                        const boton = item.querySelector('.accion--marcar-leida');
                        if (boton) {
                            boton.className = 'accion--marcar-no-leida';
                            boton.title = 'Marcar como no leída';
                            boton.innerHTML = '<i class="fa-solid fa-envelope"></i>';
                        }
                    });
                    // Actualizamos el contador global de inmediato.
                    setupUnreadPolling('.notification-badge', '/notificaciones/unread-count');
                }
            } catch (error) {
                console.error('Error al marcar todas como leídas:', error);
                // Aquí podrías mostrar una alerta al usuario si la operación falla.
            } finally {
                // 1. Restauramos el estado visual original del botón.
                botonMarcarTodas.disabled = false;
                botonMarcarTodas.innerHTML = originalButtonHTML;

                // 2. Ahora, comprobamos si debe estar visible.
                // Si la operación tuvo éxito, el contador de '.no-leida' será 0 y la función lo ocultará.
                // Si la operación falló, el contador será > 0 y lo dejará visible (ya con el texto correcto).
                checkUnreadButtonVisibility();
            }
        });
    }


    // Interactividad en la pagina de notificaciones
    if (notificacionesContenedor) {
        notificacionesContenedor.addEventListener('click', async (e) => {
            const item = e.target.closest('.notificacion-item');
            if (!item) return;

            const notificacionId = item.dataset.id;
            const formData = new FormData();
            formData.append('id', notificacionId);
            
            // Delegación de eventos
            const enlaceNotificacion = e.target.closest('.notificacion-item__enlace');
            const botonMarcarLeida = e.target.closest('.accion--marcar-leida');
            const botonMarcarNoLeida = e.target.closest('.accion--marcar-no-leida');
            const botonEliminar = e.target.closest('.accion--eliminar');

            // Prevenir acción en botones al hacer clic en el enlace
            if (enlaceNotificacion && (botonMarcarLeida || botonMarcarNoLeida || botonEliminar)) {
                 e.preventDefault();
            }

            // Lógica para verificar producto antes de redirigir
            if (enlaceNotificacion && !botonMarcarLeida && !botonEliminar) {
                e.preventDefault(); // Prevenimos la redirección para verificar primero
                const url = enlaceNotificacion.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const productoId = urlParams.get('id');

                // Si la notificación no es de un producto (no tiene ID), redirigir directamente.
                if (!productoId) {
                    window.location.href = url;
                    return;
                }
                
                try {
                    const response = await fetch(`/api/producto/estado?id=${productoId}`);
                    const data = await response.json();

                    if (data.disponible) {
                        window.location.href = url; // Producto OK, redirigir
                    } else {
                        mostrarNotificacion(data.mensaje, 'error');
                    }
                } catch (error) {
                    console.error('Error al verificar el estado del producto:', error);
                }
            }

            // Acción para marcar como leída
            if (botonMarcarLeida) {
                e.preventDefault();
                const response = await fetch('/notificaciones/marcar-leida', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    item.classList.remove('no-leida');
                    botonMarcarLeida.className = 'accion--marcar-no-leida';
                    botonMarcarLeida.title = 'Marcar como no leída';
                    botonMarcarLeida.innerHTML = '<i class="fa-solid fa-envelope"></i>';
                    
                    setupUnreadPolling('.notification-badge', '/notificaciones/unread-count');
                    checkUnreadButtonVisibility(); // Revisa si el botón "Marcar Todas" debe ocultarse.
                }
            }

            // Acción para marcar como no leida
            if (botonMarcarNoLeida) {
                e.preventDefault();
                const response = await fetch('/notificaciones/marcar-no-leida', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.success) {
                    item.classList.add('no-leida');
                    botonMarcarNoLeida.className = 'accion--marcar-leida';
                    botonMarcarNoLeida.title = 'Marcar como leída';
                    botonMarcarNoLeida.innerHTML = '<i class="fa-solid fa-check"></i>';
                    
                    setupUnreadPolling('.notification-badge', '/notificaciones/unread-count');
                    checkUnreadButtonVisibility(); // Revisa si el botón "Marcar Todas" debe aparecer.
                }
            }

            // Acción para eliminar
            if (botonEliminar) {
                e.preventDefault();
                if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                    const formData = new FormData();
                    formData.append('id', item.dataset.id);

                    try {
                        const response = await fetch('/notificaciones/eliminar', { method: 'POST', body: formData });
                        const data = await response.json();

                        if (data.success) {
                            // Animación de salida y eliminación del DOM
                            item.style.transition = 'opacity 0.3s ease';
                            item.style.opacity = '0';
                            setTimeout(() => {
                                item.remove(); // Se elimina el elemento del DOM

                                // Después de eliminar, se actualiza el contador y se revisa la visibilidad del botón.
                                setupUnreadPolling('.notification-badge', '/notificaciones/unread-count');
                                checkUnreadButtonVisibility();

                            }, 300);
                        }
                    } catch (error) {
                        console.error('Error al eliminar:', error);
                    }
                }
            }
        });
    }
});