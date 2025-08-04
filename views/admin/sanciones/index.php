<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/sanciones/historial">
        <i class="fa-solid fa-clock-rotate-left"></i>
        Ver Historial de Ajustes
    </a>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="dashboard__contenedor">
    <?php if(!empty($usuarios)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Usuario</th>
                        <th scope="col" class="table__th">Sanciones Actuales</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th">Ajustar Sanciones</th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($usuarios as $usuario): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?php echo htmlspecialchars($usuario->nombre . ' ' . $usuaio->apellido); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($usuario->violaciones_count); ?></td>
                            <td class="table__td">
                                <?php
                                    $estado = $usuario->estaBloqueado();
                                    if ($estado['bloqueado']) {
                                        if ($estado['tipo'] === 'permanente') {
                                            echo '<span class="texto-rojo">Bloqueado Permanentemente</span>';
                                        } else {
                                            echo '<span class="texto-rojo">Bloqueado hasta: ' . date('d/m/Y', strtotime($estado['hasta'])) . '</span>';
                                        }
                                    } else {
                                        echo '<span>Activo</span>';
                                    }
                                ?>
                            </td>
                            <td class="table__td">
                                <form action="/admin/sanciones/ajustar" method="POST" class="formulario-ajuste">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario->id; ?>">
                                    <div class="ajuste-campos">
                                        <input type="number" name="sancion_nueva" class="formulario__input" min="0" value="<?php echo $usuario->violaciones_count; ?>" required>
                                        <input type="text" name="comentario" class="formulario__input" placeholder="Comentario del ajuste" required>
                                        <button type="submit" class="boton-rosa">Ajustar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo $paginacion; ?>
    <?php else: ?>
        <p class="t-align-center">No hay usuarios registrados.</p>
    <?php endif; ?>
</div>

<style>
    .formulario-ajuste {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .ajuste-campos {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .ajuste-campos .formulario__input {
        margin-bottom: 0;
    }
</style>