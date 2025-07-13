<?php
    // --- LÓGICA PARA DETERMINAR LA URL DE RETORNO ---
    $urlVolver = '/'; // URL por defecto
    if (isset($_SESSION['rol'])) {
        switch ($_SESSION['rol']) {
            case 'vendedor':
                $urlVolver = '/vendedor/perfil';
                break;
            case 'comprador':
                $urlVolver = '/comprador/perfil/editar';
                break;
            case 'admin':
                $urlVolver = '/admin/dashboard';
                break;
        }
    }
?>


<main class="contenedor seccion">
    <div class="dashboard__contenedor-boton" style="margin-bottom: 2rem; margin-top: 2rem; justify-content: flex-start;">
        <a class="dashboard__boton" href="<?php echo $urlVolver; ?>">
            <i class="fa-solid fa-circle-arrow-left"></i>
            Volver
        </a>
    </div>

    <div class="auth">
        <?php if($_SESSION['rol'] === 'vendedor'): ?>
            <h2 class="auth__heading"><?php echo $titulo; ?></h2>
        <?php endif; ?>
    
        <?php require_once __DIR__ . '/../templates/alertas.php'; ?>
    
        <div class="configuracion-2fa">
            <?php if($usuario2fa->auth_enabled): ?>
                <div class="configuracion-2fa__estado">
                    <p class="configuracion-2fa__texto">Estado: <span class="configuracion-2fa__activo">Activo</span></p>
                    
                    <form method="POST" class="formulario" onsubmit="return confirm('¿Estás seguro de que quieres desactivar la autenticación de dos factores?');">
                        <input type="submit" name="desactivar" class="formulario__submit" 
                               style="background-color: #C62828;"
                               value="Desactivar 2FA">
                    </form>
                </div>
    
            <?php else: ?>
                <div class="configuracion-2fa__estado">
                    <p class="configuracion-2fa__texto">Estado: <span class="configuracion-2fa__inactivo">Inactivo</span></p>
                    
                    <div class="configuracion-2fa__qr">
                        <p class="configuracion-2fa__instrucciones">
                            1. Escanea este código QR con tu aplicación de autenticación (ej. Google Authenticator).
                        </p>
                        <img src="<?php echo $qrUrl; ?>" alt="Código QR para 2FA" class="configuracion-2fa__imagen">
                        
                        <p class="configuracion-2fa__codigo-secreto">
                            O ingresa manualmente este código: <br>
                            <span class="configuracion-2fa__secret"><?php echo chunk_split($usuario2fa->auth_secret, 4, ' '); ?></span>
                        </p>
                        
                        <p class="configuracion-2fa__instrucciones" style="margin-top: 2rem;">
                            2. Ingresa el código de 6 dígitos que aparece en tu aplicación para verificar y activar.
                        </p>
    
                        <form method="POST" class="formulario">
                            <div class="formulario__campo">
                                <label for="codigo" class="formulario__label">Código de verificación</label>
                                <input type="text" id="codigo" name="codigo" class="formulario__input"
                                       placeholder="Ingresa el código de 6 dígitos" required autocomplete="off" pattern="\d{6}" title="Debe ser un código de 6 dígitos.">
                            </div>
                            
                            <input type="submit" name="activar" class="formulario__submit" value="Activar 2FA">
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="configuracion-2fa__backup-codes" style="margin-top: 5rem;">
                <h3 class="configuracion-2fa__subheading">Códigos de respaldo</h3>
                <p class="configuracion-2fa__instrucciones">
                    Guarda estos códigos en un lugar seguro. Podrás usarlos para iniciar sesión si pierdes acceso a tu dispositivo. Cada código solo se puede usar una vez.
                </p>
                
                <div class="backup-codes" style="background-color: #f5f5f5; padding: 1.5rem; border-radius: 8px;">
                    <?php if (!empty($backupCodes) || ($usuario2fa && !empty($usuario2fa->backup_codes))): ?>
                        <?php 
                            $codesToDisplay = !empty($backupCodes) ? $backupCodes : json_decode($usuario2fa->backup_codes, true);
                        ?>
                        <?php if(!empty($codesToDisplay)): ?>
                            <?php foreach($codesToDisplay as $code): ?>
                                <div class="backup-code" style="font-family: monospace; letter-spacing: 2px;"><?php echo htmlspecialchars($code); ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay códigos de respaldo generados.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No hay códigos de respaldo generados.</p>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="formulario">
                    <input type="submit" name="regenerar_backup" 
                        class="formulario__submit" 
                        style="background-color: #555;"
                        value="Generar Nuevos Códigos">
                </form>
            </div>
        </div>
    </div>
</main>