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

    public function enviarAvisoEliminacionConversacion($otherUserName, $productName, $conversationUrl, $daysRemaining) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Aviso Importante: Conversación será eliminada en MixtliArte';

        $contenido = '<html>';
        $contenido .= "<body>";
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) . ",</h1>";
        $contenido .= "<p>Te informamos que la conversación que mantuviste con <strong>" . htmlspecialchars($otherUserName) . "</strong> sobre el producto \"<strong>" . htmlspecialchars($productName) . "</strong>\" será eliminada permanentemente en <strong>{$daysRemaining} días</strong>.</p>";
        $contenido .= "<p>Esto se debe a que la transacción relacionada con este producto ha finalizado.</p>";
        $contenido .= "<p>Si hay información importante que deseas conservar de esta conversación, por favor, guárdala antes de la fecha límite.</p>";
        $contenido .= "<p>Puedes acceder a la conversación aquí: <a href='" . htmlspecialchars($_ENV['HOST'] . $conversationUrl) . "'>Ver Conversación</a></p>";
        $contenido .= "<p>Gracias por usar MixtliArte.</p>";
        $contenido .= "<p>Atentamente,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar email de aviso de eliminación de conversación a {$this->email}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function enviarNotificacionNuevaFaq($preguntaUsuario, $categoriaNombre, $palabrasClave, $frecuencia) {
        $mail = $this->configurarEmailBasico(); // Ya configura el FROM y el ADDADDRESS con los datos del constructor (email del admin soporte)
        $mail->Subject = 'NUEVA PREGUNTA FRECUENTE IDENTIFICADA - REQUIERE ACTUALIZACIÓN FAQ';

        $contenido = '<html>';
        $contenido .= "<body>";
        $contenido .= "<h1>Aviso: Nueva Pregunta Frecuente Identificada</h1>";
        $contenido .= "<p>Una pregunta de usuario ha superado el umbral de frecuencia y debe ser considerada para añadir a las FAQs.</p>";
        $contenido .= "<p><strong>Pregunta del Usuario:</strong> " . htmlspecialchars($preguntaUsuario) . "</p>";
        $contenido .= "<p><strong>Categoría Sugerida:</strong> " . htmlspecialchars($categoriaNombre) . "</p>";
        $contenido .= "<p><strong>Palabras Clave:</strong> " . htmlspecialchars(implode(', ', $palabrasClave)) . "</p>";
        $contenido .= "<p><strong>Frecuencia Actual:</strong> " . htmlspecialchars($frecuencia) . "</p>";
        $contenido .= "<p>Por favor, revisa esta pregunta y añade la respuesta correspondiente a la sección de Preguntas Frecuentes de la plataforma.</p>";
        $contenido .= "<p>Gracias,<br>Sistema Automático MixtliArte</p>";
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar notificación de FAQ a {$this->email}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function enviarConfirmacionSoporteUsuario($numeroCaso, $asuntoConsulta) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Confirmación de Recepción de Consulta - MixtliArte (Caso #' . $numeroCaso . ')';

        $contenido = '<html>';
        $contenido .= "<body>";
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) . ",</h1>";
        $contenido .= "<p>Hemos recibido tu consulta de soporte con el asunto: <strong>" . htmlspecialchars($asuntoConsulta) . "</strong>.</p>";
        $contenido .= "<p>Tu número de caso es: <strong>" . htmlspecialchars($numeroCaso) . "</strong>. Por favor, guarda este número para futuras referencias.</p>";
        $contenido .= "<p>Nuestro equipo de soporte revisará tu consulta y te responderá a la brevedad posible.</p>";
        $contenido .= "<p>Gracias por contactarnos,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar email de confirmación de soporte a {$this->email}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function enviarNotificacionSoporteAdmin($emailUsuario, $asunto, $mensaje, $numeroCaso) {
        $mail = $this->configurarEmailBasico();
        $mail->clearAddresses(); // Limpiar direcciones previas
        $mail->addAddress($this->email, $this->nombre); // Asignar la dirección del admin de soporte
        $mail->Subject = 'NUEVA CONSULTA DE SOPORTE - MixtliArte (Caso #' . $numeroCaso . ')';

        $contenido = '<html>';
        $contenido .= "<body>";
        $contenido .= "<h1>Nueva Consulta de Soporte Recibida</h1>";
        $contenido .= "<p>Se ha recibido una nueva consulta de soporte en MixtliArte con los siguientes detalles:</p>";
        $contenido .= "<p><strong>Número de Caso:</strong> " . htmlspecialchars($numeroCaso) . "</p>";
        $contenido .= "<p><strong>Email del Usuario:</strong> " . htmlspecialchars($emailUsuario) . "</p>";
        $contenido .= "<p><strong>Asunto:</strong> " . htmlspecialchars($asunto) . "</p>";
        $contenido .= "<p><strong>Mensaje:</strong></p>";
        $contenido .= "<p style='border: 1px solid #ccc; padding: 10px; border-radius: 5px; background-color: #f9f9f9;'>" . nl2br(htmlspecialchars($mensaje)) . "</p>";
        $contenido .= "<p>Por favor, accede al panel de administración para gestionar esta consulta.</p>";
        $contenido .= "<p>Atentamente,<br>Sistema Automático MixtliArte</p>";
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar email de notificación de soporte al admin {$this->email}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }

    public function enviarRespuestaSoporte($usuarioEmail, $numeroCaso, $asuntoOriginal, $respuestaMensaje, $nombreAdminRemitente) {
        $mail = $this->configurarEmailBasico();
        $mail->clearAddresses(); // Limpiar direcciones previas
        $mail->addAddress($usuarioEmail, 'Usuario MixtliArte'); // Enviar al email del usuario

        $mail->Subject = 'Respuesta a tu consulta de soporte (Caso #' . htmlspecialchars($numeroCaso) . ') - ' . htmlspecialchars($asuntoOriginal);

        $contenido = '<html>';
        $contenido .= "<body>";
        $contenido .= "<h1>Hola,</h1>";
        $contenido .= "<p>Hemos recibido tu consulta de soporte con el número de caso <strong>#" . htmlspecialchars($numeroCaso) . "</strong> y el asunto \"<strong>" . htmlspecialchars($asuntoOriginal) . "</strong>\".</p>";
        $contenido .= "<p>El equipo de soporte te ha respondido lo siguiente:</p>";
        $contenido .= "<div style='border: 1px solid #ccc; padding: 15px; border-radius: 8px; background-color: #f9f9f9; margin: 15px 0;'>";
        $contenido .= "<p style='font-weight: bold; margin-top: 0;'>Respuesta de " . htmlspecialchars($nombreAdminRemitente) . ":</p>";
        $contenido .= "<p>" . nl2br(htmlspecialchars($respuestaMensaje)) . "</p>";
        $contenido .= "</div>";
        $contenido .= "<p>Si tienes más preguntas o tu problema persiste, puedes responder a este correo o abrir una nueva consulta referenciando este número de caso.</p>";
        $contenido .= "<p>Gracias,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        if(!$mail->send()) {
            error_log("Error al enviar email de respuesta de soporte a {$usuarioEmail}: " . $mail->ErrorInfo);
            return false;
        }
        return true;
    }
}