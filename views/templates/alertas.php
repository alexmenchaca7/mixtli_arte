<?php if(!empty($alertas)): ?>
    <div class="alertas">
        <?php foreach($alertas as $key => $mensajes): ?>
            <?php foreach($mensajes as $mensaje): ?>
                <div class="alerta alerta__<?php echo $key; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>