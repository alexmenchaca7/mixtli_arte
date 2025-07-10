<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/sanciones">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($ajustes)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Fecha de Ajuste</th>
                        <th scope="col" class="table__th">Vendedor</th>
                        <th scope="col" class="table__th">Sanción Anterior</th>
                        <th scope="col" class="table__th">Sanción Nueva</th>
                        <th scope="col" class="table__th">Comentario del Ajuste</th>
                        <th scope="col" class="table__th">Administrador</th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <?php foreach($ajustes as $ajuste): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?php echo date('d/m/Y H:i', strtotime($ajuste->fecha_ajuste)); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($ajuste->vendedor_nombre . ' ' . $ajuste->vendedor_apellido); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($ajuste->sancion_anterior); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($ajuste->sancion_nueva); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($ajuste->comentario); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($ajuste->admin_nombre . ' ' . $ajuste->admin_apellido); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="t-align-center">No se han realizado ajustes de sanciones todavía.</p>
    <?php endif; ?>
</div>