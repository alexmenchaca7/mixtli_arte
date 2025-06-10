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
        $mail->setFrom('noreply@mixtliarte.com', 'MixtliArte');
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

    public function enviarNotificacionNuevoMensaje($destinatarioEmail, $destinatarioNombre, $remitenteNombre, $productoNombre, $mensajeCorto, $urlConversacion) {
        $mail = $this->configurarEmailBasico();
        // Clear previous recipient from constructor and set the actual recipient for this notification
        $mail->clearAddresses(); 
        $mail->addAddress($destinatarioEmail, $destinatarioNombre);

        $mail->Subject = 'Nuevo mensaje de ' . $remitenteNombre . ' sobre "' . $productoNombre . '"';

        $contenido = '<html>';
        $contenido .= "<body>"; // Added body tag
        $contenido .= "<h1>Hola " . htmlspecialchars($destinatarioNombre) . ",</h1>";
        $contenido .= "<p>Has recibido un nuevo mensaje de <strong>" . htmlspecialchars($remitenteNombre) . "</strong> sobre el producto \"<strong>" . htmlspecialchars($productoNombre) . "</strong>\".</p>";
        
        if (!empty($mensajeCorto)) {
            $contenido .= "<p>Mensaje: <em>\"" . htmlspecialchars($mensajeCorto) . "...\"</em></p>";
        }
        
        $contenido .= "<p>Para ver la conversación completa y responder, haz clic aquí: <a href='" . htmlspecialchars($urlConversacion) . "'>Ver Conversación</a></p>";
        $contenido .= "<p>Si no esperabas este mensaje, puedes ignorarlo.</p>";
        $contenido .= "<p>Gracias,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body>"; // Added closing body tag
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar email de notificación de mensaje a {$destinatarioEmail}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function enviarNotificacionCalificacion($nombreUsuario, $nombreProducto, $url) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Califica tu transacción';
        
        $contenido = "<html>";
        $contenido .= "<body>";
        $contenido .= "<p><strong>Hola " . htmlspecialchars($this->nombre) . ",</strong></p>";
        $contenido .= "<p>¡Tu transacción para el producto '" . htmlspecialchars($nombreProducto) . "' ha sido completada!</p>";
        $contenido .= "<p>Ya puedes dejar una calificación para " . htmlspecialchars($nombreUsuario) . " sobre tu experiencia. Tu opinión es muy importante para la comunidad de Mixtli.</p>";
        $contenido .= "<p>Puedes dejar tu calificación en el siguiente enlace:</p>";
        
        $contenido .= "<a href='" . $_ENV['HOST'] . $url . "'>Calificar ahora</a>";
        $contenido .= "<p>Si no puedes acceder, copia y pega la siguiente URL en tu navegador:</p>";
        $contenido .= "<p>" . $_ENV['HOST'] . $url . "</p>";

        $contenido .= "</body>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }
}