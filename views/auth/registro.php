<main class="auth">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>

    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <form method="POST" action="/registro" class="formulario">
        <div class="formulario__campo">
            <label for="nombre" class="formulario__label">Nombre</label>
            <input 
                type="text"
                class="formulario__input"
                placeholder="Tu Nombre"
                id="nombre"
                name="nombre"
                value="<?php echo $usuario->nombre; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="apellido" class="formulario__label">Apellido</label>
            <input 
                type="text"
                class="formulario__input"
                placeholder="Tu Apellido"
                id="apellido"
                name="apellido"
                value="<?php echo $usuario->apellido; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="fecha_nacimiento" class="formulario__label">Fecha de Nacimiento</label>
            <input 
                type="date"
                class="formulario__input"
                id="fecha_nacimiento"
                name="fecha_nacimiento"
                value="<?php echo $usuario->fecha_nacimiento; ?>"
                max="<?php echo $fecha_hoy; ?>"
            >
        </div>
        
        <div class="formulario__campo">
            <label for="sexo" class="formulario__label">Sexo</label>
            <select class="formulario__input" name="sexo" id="sexo">
                <option value="" disabled <?php echo empty($usuario->sexo) ? 'selected' : ''; ?>>--Seleccione--</option>
                <option value="Femenino" <?php echo ($usuario->sexo === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                <option value="Masculino" <?php echo ($usuario->sexo === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
            </select>
        </div>
        
        <div class="formulario__campo">
            <label for="rol" class="formulario__label">¿Para qué piensas usar Mixtli Arte?</label>
            <select class="formulario__input" name="rol" id="rol">
                <option value="" disabled <?php echo empty($usuario->rol) ? 'selected' : ''; ?>>--Seleccione--</option>
                <option value="comprador" <?php echo ($usuario->rol === 'comprador') ? 'selected' : ''; ?>>Comprar</option>
                <option value="vendedor" <?php echo ($usuario->rol === 'vendedor') ? 'selected' : ''; ?>>Vender</option>
            </select>
        </div>

        

        <div class="formulario__campo">
            <label for="email" class="formulario__label">Email</label>
            <input 
                type="email"
                class="formulario__input"
                placeholder="Tu Email"
                id="email"
                name="email"
                value="<?php echo $usuario->email; ?>"
            >
        </div>
        
        <div class="formulario__campo">
            <label for="pass" class="formulario__label">Password</label>
            <input 
                type="password"
                class="formulario__input"
                placeholder="Tu Password"
                id="pass"
                name="pass"
                value="<?php echo $usuario->pass; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="pass2" class="formulario__label">Repetir Password</label>
            <input 
                type="password"
                class="formulario__input"
                placeholder="Repetir Password"
                id="pass2"
                name="pass2"
                value="<?php echo $usuario->pass2; ?>"
            >
        </div>

        <input type="submit" class="formulario__submit" value="Crear Cuenta">
    </form>

    <div class="acciones">
        <a href="/login" class="acciones__enlace">¿Ya tienes una cuenta?</a>
    </div>
</main>