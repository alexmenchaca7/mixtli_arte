<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor">
    <h3 class="dashboard__subtitle">Pendientes de Moderación</h3>
    <?php if(!empty($valoracionesPendientes)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Calificador</th>
                        <th scope="col" class="table__th">Calificado</th>
                        <th scope="col" class="table__th">Producto</th>
                        <th scope="col" class="table__th">Estrellas</th>
                        <th scope="col" class="table__th" style="width: 30%;">Comentario</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($valoracionesPendientes as $valoracion): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= htmlspecialchars($valoracion->calificador->nombre . ' ' . $valoracion->calificador->apellido); ?></td>
                            <td class="table__td"><?= htmlspecialchars($valoracion->calificado->nombre . ' ' . $valoracion->calificado->apellido); ?></td>
                            <td class="table__td"><?= htmlspecialchars($valoracion->producto->nombre); ?></td>
                            <td class="table__td" style="font-size: 1.5rem;">
                                <?php if ($valoracion->estrellas !== null): ?>
                                    <?= str_repeat('⭐', $valoracion->estrellas); ?>
                                <?php else: ?>
                                    N/A <?php endif; ?>
                            </td>
                            <td class="table__td"><?= $valoracion->comentario ? htmlspecialchars($valoracion->comentario) : '<em>Sin comentario</em>'; ?></td>
                            
                            <td class="table__td--acciones">
                                <form class="table__formulario" action="/admin/valoraciones/aprobar" method="POST" style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $valoracion->id; ?>">
                                    <button class="table__accion table__accion--editar" type="submit">Aprobar</button>
                                </form>
                                <form class="table__formulario" action="/admin/valoraciones/rechazar" method="POST" style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $valoracion->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No hay valoraciones pendientes de moderación.</p>
    <?php endif; ?>
</div>

<div class="dashboard__contenedor" style="margin-top: 4rem;">
    <h3 class="dashboard__subtitle">Historial de Moderaciones</h3>
    <?php if(!empty($valoracionesProcesadas)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Calificador</th>
                        <th scope="col" class="table__th">Calificado</th>
                        <th scope="col" class="table__th">Estrellas</th>
                        <th scope="col" class="table__th" style="width: 40%;">Comentario</th>
                        <th scope="col" class="table__th">Estado</th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($valoracionesProcesadas as $valoracion): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= htmlspecialchars($valoracion->calificador->nombre . ' ' . $valoracion->calificador->apellido); ?></td>
                            <td class="table__td"><?= htmlspecialchars($valoracion->calificado->nombre . ' ' . $valoracion->calificado->apellido); ?></td>
                            <td class="table__td" style="font-size: 1.5rem;"><?= str_repeat('⭐', $valoracion->estrellas); ?></td>
                            <td class="table__td"><?= $valoracion->comentario ? htmlspecialchars($valoracion->comentario) : '<em>Sin comentario</em>'; ?></td>
                            <td class="table__td">
                                <?php if($valoracion->moderado == 1): ?>
                                    <span style="color: #2E7D31; font-weight: bold;">Aprobado</span>
                                <?php elseif($valoracion->moderado == 2): ?>
                                    <span style="color: #C62828; font-weight: bold;">Rechazado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center;">Aún no se ha procesado ninguna valoración.</p>
    <?php endif; ?>
</div>