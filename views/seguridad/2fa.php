<main class="auth">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>

    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <div class="configuracion-2fa">
        <?php if($usuario2fa->auth_enabled): ?>
            <div class="configuracion-2fa__estado">
                <p class="configuracion-2fa__texto">Estado: <span class="configuracion-2fa__activo">Activo</span></p>
                
                <form method="POST" class="formulario">
                    <input type="submit" name="desactivar" class="formulario__submit formulario__submit--danger" 
                           value="Desactivar 2FA">
                </form>
            </div>
        <?php else: ?>
            <div class="configuracion-2fa__estado">
                <p class="configuracion-2fa__texto">Estado: <span class="configuracion-2fa__inactivo">Inactivo</span></p>
                
                <div class="configuracion-2fa__qr">
                    <p class="configuracion-2fa__instrucciones">
                        Escanea este código QR con Google Authenticator:
                    </p>
                    <img src="<?php echo $qrUrl; ?>" alt="Código QR para 2FA" class="configuracion-2fa__imagen">
                    
                    <p class="configuracion-2fa__codigo-secreto">
                        O ingresa manualmente este código: <br>
                        <span class="configuracion-2fa__secret"><?php echo chunk_split($usuario2fa->auth_secret, 4, ' '); ?></span>
                    </p>
                    
                    <form method="POST" class="formulario">
                        <div class="formulario__campo">
                            <label for="codigo" class="formulario__label">Código de verificación</label>
                            <input type="text" id="codigo" name="codigo" class="formulario__input"
                                   placeholder="Ingresa el código de 6 dígitos" required>
                        </div>
                        
                        <input type="submit" name="activar" class="formulario__submit" value="Activar 2FA">
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="configuracion-2fa__backup-codes">
            <h3 class="configuracion-2fa__subheading">Códigos de respaldo</h3>
            <p class="configuracion-2fa__instrucciones">
                Guarda estos códigos en un lugar seguro. Cada código solo se puede usar una vez.
            </p>
            
            <div class="backup-codes">
                <?php foreach(json_decode($usuario2fa->backup_codes, true) ?? [] as $code): ?>
                    <div class="backup-code"><?php echo htmlspecialchars($code); ?></div>
                <?php endforeach; ?>
            </div>
            
            <form method="POST" class="formulario">
                <input type="submit" name="regenerar_backup" 
                    class="formulario__submit formulario__submit--secondary" 
                    value="Generar nuevos códigos">
            </form>
        </div>
    </div>
</main>