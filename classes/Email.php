<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;
    
    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }


    private function configurarEmailBasico() {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->setFrom('noreply@mixtliarte.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    }


    public function enviarConfirmacion() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Confirma tu Cuenta';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Has registrado correctamente tu cuenta en MixtliArte, pero es necesario confirmarla...</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/confirmar-cuenta?token=" . $this->token . "'>Confirmar Cuenta</a>";       
        $contenido .= "<p>Si no creaste esta cuenta puedes ignorar el mensaje.</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }

    public function enviarConfirmacionContraseña() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Establece tu Contraseña';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Has registrado correctamente tu cuenta en MixtliArte, pero es necesario establecer tu contraseña...</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/establecer-password?token=" . $this->token . "'>Establecer Contraseña</a>";       
        $contenido .= "<p>Si no creaste esta cuenta puedes ignorar el mensaje.</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }


    public function enviarNotificacionContraseña() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Cambiaste tu Contraseña';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Tu contraseña en MixtliArte ha sido cambiada exitosamente.</p>";     
        $contenido .= "<p>Si no realizaste este cambio, por favor contacta a soporte inmediatamente.</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }


    public function enviarInstrucciones() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Reestablece tu password';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Has solicitado reestablecer tu password, sigue el siguiente enlace para hacerlo.</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Password</a>";        
        $contenido .= "<p>Si tu no solicitaste este cambio, puedes ignorar el mensaje</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }
}