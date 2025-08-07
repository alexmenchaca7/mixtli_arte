<?php

namespace Classes;

use Model\Usuario;
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
        // create a new object
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host       = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port       = $_ENV['EMAIL_PORT'];

        $mail->setFrom('no-reply@mixtliarte.com', 'MixtliArte');
        $mail->addAddress($this->email, $this->nombre);

        // Set HTML
        $mail->isHTML(TRUE);
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

    public function enviarConfirmacionCambioPassword() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Confirma tu Cambio de Contraseña';

        $contenido = '<html>';
        $contenido .= "<head><style>body{font-family: Arial, sans-serif;}</style></head>";
        $contenido .= "<body>";
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) .  ":</h1>";
        $contenido .= "<p>Has solicitado cambiar tu contraseña. Para confirmar esta acción, por favor haz clic en el siguiente enlace:</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/password/confirmar-cambio?token=" . $this->token . "'>Confirmar Cambio de Contraseña</a>";       
        $contenido .= "<p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>";
        $contenido .= "<p>Gracias,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body></html>";
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }


    public function enviarNotificacionContraseña() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Alerta de Seguridad: Tu contraseña ha sido cambiada';

        $contenido = '<html>';
        $contenido .= "<head><style>body{font-family: Arial, sans-serif;}</style></head>";
        $contenido .= "<body>";
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) . ",</h1>";
        $contenido .= "<p>Te informamos que la contraseña de tu cuenta en MixtliArte ha sido cambiada exitosamente.</p>";     
        $contenido .= "<p><strong>Si tú realizaste este cambio, puedes ignorar este mensaje.</strong></p>";
        $contenido .= "<p>Si <strong>no</strong> reconoces esta actividad, por favor, contacta a nuestro equipo de soporte inmediatamente para asegurar tu cuenta.</p>";
        $contenido .= "<p>Gracias,<br>El equipo de MixtliArte</p>";
        $contenido .= "</body></html>";
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

    public function enviarNotificacionCalificacion($nombreUsuario, $nombreProducto, $url, $contexto = null) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = $contexto === 'expira_pronto' ? 'Recordatorio: Tu oportunidad para calificar expira pronto' : 'Califica tu transacción';
        
        $contenido = "<html><body>";
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";

        if ($contexto === 'expira_pronto') {
            $contenido .= "<p>¡Tu oportunidad para calificar está por terminar! <strong>Solo te quedan 3 días</strong> para dejar tu opinión sobre la transacción del producto '<strong>" . htmlspecialchars($nombreProducto) . "</strong>' con " . htmlspecialchars($nombreUsuario) . ".</p>";
            $contenido .= "<p>Tu feedback es muy valioso para mantener la confianza en nuestra comunidad.</p>";
        } else {
            $contenido .= "<p>¡Tu transacción para el producto '<strong>" . htmlspecialchars($nombreProducto) . "</strong>' ha sido completada!</p>";
            $contenido .= "<p>Ya puedes dejar una calificación para " . htmlspecialchars($nombreUsuario) . " sobre tu experiencia. Tu opinión es muy importante para la comunidad de Mixtli.</p>";
        }
        
        $contenido .= "<p>Puedes dejar tu calificación en el siguiente enlace:</p>";
        $contenido .= "<p style='margin: 20px 0;'><a href='" . $_ENV['HOST'] . $url . "' style='background-color:#EE4BBA; color:#ffffff; padding:12px 20px; text-decoration:none; border-radius:5px;'>Calificar Ahora</a></p>";
        $contenido .= "<p>Si el botón no funciona, copia y pega la siguiente URL en tu navegador:</p>";
        $contenido .= "<p>" . $_ENV['HOST'] . $url . "</p>";

        $contenido .= "</body></html>";

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

    public function enviarNotificacionNuevoProducto($nombreVendedor, $nombreProducto, $precioProducto, $urlImagen, $urlProducto) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = '¡Nuevo Producto de un Artesano que Sigues!';

        // Formatear el precio
        $precioFormateado = number_format($precioProducto, 2);

        $contenido = '<html>';
        $contenido .= '<body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #EE4BBA; text-align: center;">¡Novedad en MixtliArte!</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>El artesano <strong>" . htmlspecialchars($nombreVendedor) . "</strong>, a quien sigues, ha publicado un nuevo producto que podría interesarte:</p>";
        
        // Contenedor del producto
        $contenido .= '<div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center;">';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="text-decoration: none; color: inherit;">';
        $contenido .= '<img src="' . $urlImagen . '" alt="' . htmlspecialchars($nombreProducto) . '" style="max-width: 100%; height: auto; border-radius: 8px;">';
        $contenido .= "<h2 style='font-size: 20px; margin: 10px 0;'>" . htmlspecialchars($nombreProducto) . "</h2>";
        $contenido .= '</a>';
        $contenido .= '<p style="font-size: 24px; font-weight: bold; color: #333; margin: 10px 0;">$' . $precioFormateado . ' MXN</p>';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="display: inline-block; background-color: #EE4BBA; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Ver Producto</a>';
        $contenido .= '</div>';

        $contenido .= "<p style='margin-top: 20px;'>¡Gracias por ser parte de la comunidad MixtliArte!</p>";
        $contenido .= '</div>';
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        $mail->send();
    }

    public function enviarNotificacionNuevosProductosAgrupados($nombreVendedor, $cantidadProductos, $productosSugeridos, $urlPerfilVendedor) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = '¡' . htmlspecialchars($nombreVendedor) . ' tiene novedades para ti!';

        $contenido = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #EE4BBA; text-align: center;">¡Nuevos Productos Disponibles!</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>El artesano <strong>" . htmlspecialchars($nombreVendedor) . "</strong>, a quien sigues, ha publicado <strong>" . $cantidadProductos . " nuevos productos</strong> que podrían interesarte.</p>";
        
        // Sección de productos
        $contenido .= "<h3 style='color: #333; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>Descubre sus nuevas creaciones:</h3>";
        
        foreach ($productosSugeridos as $sugerencia) {
            $contenido .= '<div style="border-bottom: 1px solid #eee; padding: 15px 0; display: flex; align-items: center;">';
            $contenido .= '<a href="' . $_ENV['HOST'] . $sugerencia['urlProducto'] . '" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">';
            $contenido .= '<img src="' . $sugerencia['urlImagen'] . '" alt="' . htmlspecialchars($sugerencia['nombre']) . '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-right: 15px;">';
            $contenido .= '<div>';
            $contenido .= "<h4 style='margin: 0 0 5px 0;'>" . htmlspecialchars($sugerencia['nombre']) . "</h4>";
            $contenido .= '<p style="margin: 0; font-size: 18px; font-weight: bold; color: #333;">$' . number_format($sugerencia['precio'], 2) . ' MXN</p>';
            $contenido .= '</div>';
            $contenido .= '</a>';
            $contenido .= '</div>';
        }

        $contenido .= '<div style="text-align: center; margin-top: 25px;">';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlPerfilVendedor . '" style="display: inline-block; background-color: #EE4BBA; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Ver Todos los Productos del Artesano</a>';
        $contenido .= '</div>';

        $contenido .= "<p style='margin-top: 20px;'>¡Gracias por ser parte de la comunidad MixtliArte!</p>";
        $contenido .= '</div></body></html>';

        $mail->Body = $contenido;
        $mail->send();
    }


    public function enviarNotificacionCambioPrecio($nombreProducto, $precioAnterior, $precioNuevo, $urlImagen, $urlProducto) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = '¡Un producto de tu lista de deseos cambió de precio!';

        // Formatear precios
        $precioAnteriorF = number_format($precioAnterior, 2);
        $precioNuevoF = number_format($precioNuevo, 2);

        $tipoCambio = $precioNuevo < $precioAnterior ? "¡Bajó de precio!" : "Actualización de precio";

        $contenido = '<html>';
        $contenido .= '<body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #EE4BBA; text-align: center;">' . $tipoCambio . '</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>Te informamos que el producto <strong>" . htmlspecialchars($nombreProducto) . "</strong>, que tienes en tu lista de deseos, ha cambiado de precio.</p>";

        $contenido .= '<div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center;">';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="text-decoration: none; color: inherit;">';
        $contenido .= '<img src="' . $urlImagen . '" alt="' . htmlspecialchars($nombreProducto) . '" style="max-width: 100%; height: auto; border-radius: 8px;">';
        $contenido .= "<h3 style='font-size: 20px; margin: 10px 0;'>" . htmlspecialchars($nombreProducto) . "</h3>";
        $contenido .= '</a>';
        $contenido .= '<p style="font-size: 18px; margin: 10px 0;">Precio anterior: <span style="text-decoration: line-through;">$' . $precioAnteriorF . ' MXN</span></p>';
        $contenido .= '<p style="font-size: 24px; font-weight: bold; color: #2E7D31; margin: 10px 0;">Nuevo precio: $' . $precioNuevoF . ' MXN</p>';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="display: inline-block; background-color: #EE4BBA; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Ver Producto</a>';
        $contenido .= '</div>';

        $contenido .= "<p style='margin-top: 20px;'>¡No dejes pasar la oportunidad!</p>";
        $contenido .= '</div>';
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        $mail->send();
    }


    public function enviarNotificacionProductoNoDisponible($productoAgotado, $productosSugeridos, $asunto) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = $asunto;

        $contenido = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #EE4BBA; text-align: center;">¡Oh, no!</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>Te informamos que el producto <strong>" . htmlspecialchars($productoAgotado->nombre) . "</strong>, de tu lista de deseos, ya no está disponible.</p>";

        if (!empty($productosSugeridos)) {
            $contenido .= "<h3 style='color: #333; border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;'>Pero no te preocupes, ¡quizás te interesen estos productos!</h3>";
            $contenido .= '<table style="width: 100%; border-collapse: collapse;"><tr>';

            foreach ($productosSugeridos as $sugerencia) {
                $contenido .= '<td style="padding: 10px; text-align: center; width: 33%;">';
                $contenido .= '<a href="' . $sugerencia->urlProducto . '" style="text-decoration: none; color: #333;">';
                $contenido .= '<img src="' . $sugerencia->urlImagen . '" alt="' . htmlspecialchars($sugerencia->nombre) . '" style="max-width: 100%; height: auto; border-radius: 5px;">';
                $contenido .= '<p style="margin: 5px 0; font-size: 14px;">' . htmlspecialchars($sugerencia->nombre) . '</p>';
                $contenido .= '<p style="margin: 5px 0; font-weight: bold; color: #EE4BBA;">$' . number_format($sugerencia->precio, 2) . ' MXN</p>';
                $contenido .= '</a>';
                $contenido .= '</td>';
            }

            $contenido .= '</tr></table>';
        }

        $contenido .= "<p style='margin-top: 20px;'>Puedes gestionar tus preferencias de notificación en tu perfil.</p>";
        $contenido .= '</div></body></html>';

        $mail->Body = $contenido;
        $mail->send();
    }

    public function enviarNotificacionStockBajo($nombreProducto, $stockActual, $urlImagen, $urlProducto) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = '¡Un producto de tu lista de deseos se está agotando!';

        $contenido = '<html>';
        $contenido .= '<body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #EE4BBA; text-align: center;">¡Date prisa! Quedan pocas unidades</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>Te informamos que el producto <strong>" . htmlspecialchars($nombreProducto) . "</strong>, que guardaste en tu lista de deseos, está a punto de agotarse.</p>";

        $contenido .= '<div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center;">';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="text-decoration: none; color: inherit;">';
        $contenido .= '<img src="' . $urlImagen . '" alt="' . htmlspecialchars($nombreProducto) . '" style="max-width: 100%; height: auto; border-radius: 8px;">';
        $contenido .= "<h3 style='font-size: 20px; margin: 10px 0;'>" . htmlspecialchars($nombreProducto) . "</h3>";
        $contenido .= '</a>';
        $contenido .= '<p style="font-size: 24px; font-weight: bold; color: #D32F2F; margin: 10px 0;">¡Solo quedan ' . $stockActual . ' unidades!</p>';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="display: inline-block; background-color: #EE4BBA; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Comprar Ahora</a>';
        $contenido .= '</div>';

        $contenido .= "<p style='margin-top: 20px;'>¡No dejes que se te escape!</p>";
        $contenido .= '</div>';
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        $mail->send();
    }

    public function enviarNotificacionStockCriticoVendedor($nombreProducto, $stockActual, $urlImagen, $urlProducto) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = '¡Alerta de Stock Crítico en tu Producto!';

        $contenido = '<html>';
        $contenido .= '<body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #D32F2F; text-align: center;">¡Atención! Stock Crítico</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>Te informamos que el stock de tu producto <strong>" . htmlspecialchars($nombreProducto) . "</strong> ha alcanzado el nivel crítico.</p>";

        $contenido .= '<div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center;">';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="text-decoration: none; color: inherit;">';
        $contenido .= '<img src="' . $urlImagen . '" alt="' . htmlspecialchars($nombreProducto) . '" style="max-width: 100%; height: auto; border-radius: 8px;">';
        $contenido .= "<h3 style=\'font-size: 20px; margin: 10px 0;\'>" . htmlspecialchars($nombreProducto) . "</h3>";
        $contenido .= '</a>';
        $contenido .= '<p style="font-size: 24px; font-weight: bold; color: #D32F2F; margin: 10px 0;">¡Solo quedan ' . $stockActual . ' unidades!</p>';
        $contenido .= '<a href="' . $_ENV['HOST'] . $urlProducto . '" style="display: inline-block; background-color: #EE4BBA; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Gestionar Producto</a>';
        $contenido .= '</div>';

        $contenido .= "<p style='margin-top: 20px;'>Considera reabastecer tu inventario para no perder ventas.</p>";
        $contenido .= '</div>';
        $contenido .= "</body>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        $mail->send();
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
        // Obtener todos los administradores
        $admins = Usuario::findAdmins();

        foreach ($admins as $admin) {
            $mail = $this->configurarEmailBasico();
            $mail->clearAddresses(); // Limpiar direcciones de la iteración anterior
            $mail->addAddress($admin->email, $admin->nombre . ' ' . $admin->apellido);
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
                error_log("Error al enviar email de notificación de soporte al admin {$admin->email}: " . $mail->ErrorInfo);
                // Puedes decidir si continuar con los demás admins o no
            }
        }
        return true; // Devuelve true si el bucle se completa
    }

    public function enviarNotificacionViolacion($motivo, $violacionesCount) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Advertencia sobre tu cuenta en MixtliArte';
    
        $mensaje = "Has recibido una advertencia por la siguiente razón: " . htmlspecialchars($motivo) . ".";
        $mensaje .= " Llevas " . $violacionesCount . " violación(es) acumulada(s).";
    
        $advertencia = "";
        switch ($violacionesCount) {
            case 1:
            case 2:
                $advertencia = "<strong>Advertencia:</strong> al acumular 3 violaciones, tu cuenta será bloqueada temporalmente.";
                break;
            case 3:
                $advertencia = "<strong>Tu cuenta ha sido bloqueada temporalmente</strong> por una semana.";
                break;
            case 4:
                $advertencia = "<strong>Advertencia final:</strong> con 5 violaciones, tu cuenta será bloqueada permanentemente.";
                break;
            case 5:
                $advertencia = "<strong>Tu cuenta ha sido bloqueada permanentemente.</strong>";
                break;
        }
    
        $contenido = '<html>';
        $contenido .= '<body style="font-family: Arial, sans-serif; color: #333;">';
        $contenido .= '<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';
        $contenido .= '<h1 style="color: #D32F2F; text-align: center;">Aviso Importante de MixtliArte</h1>';
        $contenido .= "<h2>Hola " . htmlspecialchars($this->nombre) . ",</h2>";
        $contenido .= "<p>" . $mensaje . "</p>";
        $contenido .= "<p style='background-color: #FFF9C4; padding: 15px; border-radius: 8px; border-left: 5px solid #FBC02D;'>" . $advertencia . "</p>";
        $contenido .= "<p>Para más detalles o si crees que esto es un error, por favor, contacta a nuestro equipo de soporte.</p>";
        $contenido .= "<p>Gracias,<br>El equipo de MixtliArte</p>";
        $contenido .= '</div>';
        $contenido .= "</body>";
        $contenido .= '</html>';
    
        $mail->Body = $contenido;
    
        $mail->send();
    }

    public function enviarNotificacionNuevoReporte($adminEmail, $adminNombre, $reporte, $producto, $vendedor, $reportador) {
        $mail = $this->configurarEmailBasico();
        $mail->clearAddresses(); 
        $mail->addAddress($adminEmail, $adminNombre);

        $mail->Subject = 'Nuevo Reporte de Producto Recibido (ID: ' . $producto->id . ')';

        $contenido = '<html><body style="font-family: Arial, sans-serif;">';
        $contenido .= "<h1>Hola " . htmlspecialchars($adminNombre) . ",</h1>";
        $contenido .= "<p>Se ha recibido un nuevo reporte de producto en MixtliArte con los siguientes detalles:</p>";
        $contenido .= "<ul style='list-style-type: none; padding: 0;'>";
        $contenido .= "<li><strong>ID del Producto:</strong> " . htmlspecialchars($producto->id) . "</li>";
        $contenido .= "<li><strong>Nombre del Producto:</strong> " . htmlspecialchars($producto->nombre) . "</li>";
        $contenido .= "<li><strong>Vendedor:</strong> " . htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido) . "</li>";
        $contenido .= "<li><strong>Reportado por:</strong> " . htmlspecialchars($reportador->nombre . ' ' . $reportador->apellido) . "</li>";
        $contenido .= "<li><strong>Motivo del Reporte:</strong> " . htmlspecialchars($reporte->motivo) . "</li>";
        $contenido .= "<li><strong>Comentarios del Comprador:</strong> " . nl2br(htmlspecialchars($reporte->comentarios ?? 'N/A')) . "</li>";
        $contenido .= "</ul>";
        $contenido .= "<p>Puedes ver los detalles completos y tomar acción en el siguiente enlace:</p>";
        $contenido .= "<a href='" . $_ENV['HOST'] . "/admin/reportes/ver?id=" . $reporte->id . "' style='display: inline-block; padding: 10px 15px; background-color: #EE4BBA; color: #fff; text-decoration: none; border-radius: 5px;'>Ver Reporte</a>";
        $contenido .= "<p>Atentamente,<br>El Sistema de MixtliArte</p>";
        $contenido .= "</body></html>";
        $mail->Body = $contenido;

        $mail->send();
    }

    public function enviarResumenDiarioReportes($resumenHtml) {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Resumen Diario de Reportes de Productos - MixtliArte';

        $contenido = '<html><body style="font-family: Arial, sans-serif;">';
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) . ",</h1>";
        $contenido .= "<p>Aquí está el resumen de los reportes de productos de las últimas 24 horas:</p>";
        $contenido .= $resumenHtml; // El contenido ya viene formateado en HTML
        $contenido .= "<p>Puedes revisar todos los reportes en el panel de administración.</p>";
        $contenido .= "<p>Atentamente,<br>El Sistema de MixtliArte</p>";
        $contenido .= "</body></html>";
        $mail->Body = $contenido;

        $mail->send();
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

    public function enviarConfirmacionEliminacion() {
        $mail = $this->configurarEmailBasico();
        $mail->Subject = 'Confirmación para Eliminar tu Cuenta en MixtliArte';

        $contenido = '<html><body>';
        $contenido .= "<h1>Hola " . htmlspecialchars($this->nombre) . ",</h1>";
        $contenido .= "<p>Hemos recibido una solicitud para eliminar tu cuenta. Si tú hiciste esta solicitud, por favor, haz clic en el siguiente enlace para confirmar la eliminación. Esta acción es irreversible y todos tus datos serán borrados.</p>";
        $contenido .= "<p><strong>Este enlace es válido por 1 hora.</strong></p>";
        $contenido .= "<p><a href='" . $_ENV['HOST'] . "/perfil/confirmar-eliminacion?token=" . $this->token . "'>Confirmar Eliminación de Cuenta</a></p>";
        $contenido .= "<p>Si no solicitaste esto, puedes ignorar este mensaje de forma segura.</p>";
        $contenido .= '</body></html>';
        $mail->Body = $contenido;

        $mail->send();
    }
}