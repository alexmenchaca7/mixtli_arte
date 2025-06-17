<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/soporte">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por email, asunto, #caso..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
        <div class="campo-filtro">
            <label for="estado_filtro" class="formulario__label">Estado:</label>
            <select name="estado" id="estado_filtro" class="formulario__input">
                <option value="">Todos</option>
                <option value="pendiente" <?php echo (($_GET['estado'] ?? '') === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                <option value="en_proceso" <?php echo (($_GET['estado'] ?? '') === 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                <option value="resuelto" <?php echo (($_GET['estado'] ?? '') === 'resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                <option value="cerrado" <?php echo (($_GET['estado'] ?? '') === 'cerrado') ? 'selected' : ''; ?>>Cerrado</option>
            </select>
            <button type="submit" class="boton-busqueda">Filtrar</button>
        </div>
    </form>
</div>

<div class="dashboard__contenedor">
    <?php if (!empty($consultas)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th"># Caso</th>
                        <th scope="col" class="table__th">Email</th>
                        <th scope="col" class="table__th">Asunto</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th">Creado</th>
                        <th scope="col" class="table__th">Tiempo Abierto</th> <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach ($consultas as $consulta): ?>
                        <?php
                            $clase_fila = '';
                            $tiempo_abierto_texto = '';

                            $fecha_creacion = new DateTime($consulta->creado);
                            $fecha_actual = new DateTime();
                            $fecha_comparacion = $consulta->fecha_resolucion ? new DateTime($consulta->fecha_resolucion) : $fecha_actual;
                            $intervalo = $fecha_creacion->diff($fecha_comparacion);

                            if ($intervalo->y > 0) $tiempo_abierto_texto .= $intervalo->y . 'a ';
                            if ($intervalo->m > 0) $tiempo_abierto_texto .= $intervalo->m . 'm ';
                            if ($intervalo->d > 0) $tiempo_abierto_texto .= $intervalo->d . 'd ';
                            if ($intervalo->h > 0 && $intervalo->d == 0) $tiempo_abierto_texto .= $intervalo->h . 'h ';
                            if ($intervalo->i > 0 && $intervalo->d == 0 && $intervalo->h == 0) $tiempo_abierto_texto .= $intervalo->i . 'min';
                            if ($tiempo_abierto_texto === '') $tiempo_abierto_texto = 'pocos seg.';

                            // Lógica para resaltar filas que exceden un plazo
                            $plazo_dias_critico = 3; // Ejemplo: 3 días para consultas pendientes/en proceso
                            if (in_array($consulta->estado, ['pendiente', 'en_proceso']) && $intervalo->d >= $plazo_dias_critico) {
                                $clase_fila = 'consulta-critica';
                            } else if (in_array($consulta->estado, ['resuelto', 'cerrado'])) {
                                $clase_fila = 'consulta-resuelta';
                            }
                        ?>
                        <tr class="table__tr <?php echo $clase_fila; ?>">
                            <td class="table__td"><?php echo htmlspecialchars($consulta->numero_caso); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($consulta->email); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($consulta->asunto); ?></td>
                            <td class="table__td">
                                <?php 
                                    $clase_estado = '';
                                    switch($consulta->estado) {
                                        case 'pendiente': $clase_estado = 'estado-pendiente'; break;
                                        case 'en_proceso': $clase_estado = 'estado-enproceso'; break;
                                        case 'resuelto': $clase_estado = 'estado-resuelto'; break;
                                        case 'cerrado': $clase_estado = 'estado-cerrado'; break;
                                    }
                                ?>
                                <span class="<?php echo $clase_estado; ?>"><?php echo ucfirst(htmlspecialchars($consulta->estado)); ?></span>
                            </td>
                            <td class="table__td"><?php echo date('d/m/Y H:i', strtotime($consulta->creado)); ?></td>
                            <td class="table__td"><?php echo $tiempo_abierto_texto; ?></td> <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/admin/soporte/ver?id=<?php echo $consulta->id; ?>">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </a>
                                <form class="table__formulario" action="/admin/soporte/eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta consulta?');">
                                    <input type="hidden" name="id" value="<?php echo $consulta->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        <i class="fa-solid fa-trash-can"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="t-align-center">No hay consultas de soporte registradas.</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>

<style>
    /* Estilos para el filtro y los estados en la tabla */
    .dashboard__busqueda .campo-filtro {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }
    .dashboard__busqueda .campo-filtro .formulario__label {
        margin-bottom: 0;
        white-space: nowrap;
    }
    .dashboard__busqueda .campo-filtro .formulario__input {
        flex-grow: 1;
        max-width: 200px;
        padding: 0.8rem;
        font-size: 1.4rem;
        height: auto;
    }
    .dashboard__busqueda .campo-filtro .boton-busqueda {
        padding: 0.8rem 1.5rem;
        font-size: 1.4rem;
    }

    .estado-pendiente { color: #f0ad4e; font-weight: bold; } /* Amarillo/Naranja */
    .estado-enproceso { color: #5bc0de; font-weight: bold; } /* Azul claro */
    .estado-resuelto { color: #5cb85c; font-weight: bold; }  /* Verde */
    .estado-cerrado { color: #d9534f; font-weight: bold; }    /* Rojo */

    /* Estilos para las filas de la tabla según el plazo */
    .consulta-critica {
        background-color: #ffe0b2; /* Naranja claro para consultas críticas */
        border-left: 5px solid #ff9800;
    }
    .consulta-resuelta {
        background-color: #e8f5e9; /* Verde muy claro para consultas resueltas */
        border-left: 5px solid #4caf50;
    }
</style>