<?php

namespace Controllers;

use MVC\Router;
use Model\Faq;
// Antes: use Model\Categoria;
use Model\CategoriaFaq; // Nuevo modelo para categorías de FAQ
use Model\PreguntaUsuario;
use Classes\Email;

class FaqsController {
    // Lista de palabras clave para el etiquetado (puedes expandirla)
    private static $palabrasClavePredefinidas = [
        'registro', 'cuenta', 'iniciar sesion', 'password', 'verificacion',
        'producto', 'vender', 'comprar', 'publicar', 'stock', 'precio', 'imagenes',
        'favoritos', 'lista de deseos',
        'reseñas', 'calificar', 'valoracion', 'moderacion',
        'envio', 'entrega', 'pago', 'transaccion',
        'chat', 'mensajes', 'contacto', 'comunicacion',
        'seguridad', '2fa', 'autenticacion',
        'devoluciones', 'garantia', 'soporte'
    ];

    public static function index(Router $router) {
        // --- Esta parte es pública y se ejecuta para todos ---
        $categoriasFaq = CategoriaFaq::all();
        $faqs = Faq::metodoSQL(['orden' => 'categoriaFaqId ASC']);
        foreach ($faqs as $faq) {
            $faq->categoria = CategoriaFaq::find($faq->categoriaFaqId);
        }

        $alertas = [];
        $preguntaUsuario = new PreguntaUsuario();
        
        // Determinamos el layout y el estado de la navegación
        $autenticado = is_auth();
        $inicio = !$autenticado; // Si no está autenticado, $inicio = true para el layout público
        $layout = 'layout';      // Layout por defecto

        // --- Esta parte solo se ejecuta para usuarios autenticados ---
        if ($autenticado) {
            // Ajustar el layout según el rol del usuario
            if ($_SESSION['rol'] === 'vendedor') {
                $layout = 'vendedor-layout';
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $preguntaUsuario->sincronizar($_POST);
                $preguntaUsuario->categoriaFaqId = $_POST['categoriaFaqId'] ?? null;
                $preguntaUsuario->usuarioId = $_SESSION['id'];
                $alertas = $preguntaUsuario->validar();

                if (empty($alertas)) {
                    $palabrasClaveEncontradas = self::etiquetarPalabrasClave($preguntaUsuario->pregunta);
                    $preguntaUsuario->palabras_clave = json_encode($palabrasClaveEncontradas);

                    $preguntasSimilares = PreguntaUsuario::buscarPreguntasSimilares($preguntaUsuario->pregunta);
                    
                    if (!empty($preguntasSimilares)) {
                        $preguntaExistente = array_shift($preguntasSimilares);
                        $preguntaExistente->frecuencia = $preguntaExistente->frecuencia + 1;

                        $umbralFrecuencia = 3; // Reduced for testing. Adjust as needed.
                        if ($preguntaExistente->frecuencia >= $umbralFrecuencia && $preguntaExistente->marcada_frecuente == 0) {
                            $preguntaExistente->marcada_frecuente = 1;
                            $preguntaExistente->estado_revision = 'pendiente'; // Set initial review status
                            self::notificarSoporteNuevaFaq($preguntaExistente);
                            Faq::setAlerta('exito', 'Hemos recibido tu pregunta. ¡Parece que es una pregunta frecuente y la añadiremos pronto a nuestras FAQs!');
                        } else {
                            Faq::setAlerta('exito', 'Hemos recibido tu pregunta. Te contactaremos pronto con una respuesta.');
                        }
                        $preguntaExistente->guardar();
                    } else {
                        $preguntaUsuario->guardar();
                        Faq::setAlerta('exito', 'Hemos recibido tu pregunta. Te contactaremos pronto con una respuesta.');
                    }
                }
            }
        }

        // Renderizar la vista para todos, con la información y layout correctos
        $router->render('paginas/faqs/index', [
            'titulo' => 'Preguntas Frecuentes',
            'categorias' => $categoriasFaq,
            'faqs' => $faqs,
            'preguntaUsuario' => $preguntaUsuario,
            'inicio' => $inicio // Pasamos la variable para que el layout sepa qué navegación mostrar
        ], $layout);
    }

    private static function etiquetarPalabrasClave($pregunta) {
        $preguntaNormalizada = mb_strtolower($pregunta, 'UTF-8');
        $encontradas = [];
        foreach (self::$palabrasClavePredefinidas as $palabra) {
            if (str_contains($preguntaNormalizada, $palabra)) {
                $encontradas[] = $palabra;
            }
        }
        return array_unique($encontradas);
    }

    private static function notificarSoporteNuevaFaq(PreguntaUsuario $preguntaFrecuente) {
        $adminEmail = $_ENV['EMAIL_ADMIN_SUPPORT'];
        $adminName = 'Equipo de Soporte MixtliArte';

        // Antes: $categoria = Categoria::find($preguntaFrecuente->categoriaId);
        $categoria = CategoriaFaq::find($preguntaFrecuente->categoriaFaqId); // Usar CategoriaFaq
        $categoriaNombre = $categoria->nombre ?? 'N/A';

        $palabrasClave = json_decode($preguntaFrecuente->palabras_clave, true);

        $email = new Email($adminEmail, $adminName, '');

        $email->enviarNotificacionNuevaFaq(
            $preguntaFrecuente->pregunta,
            $categoriaNombre,
            $palabrasClave,
            $preguntaFrecuente->frecuencia
        );
    }

    // Métodos para el panel de administración de FAQs (CRUD)
    public static function adminIndex(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $faqs = Faq::all();
        // Antes: foreach ($faqs as $faq) { $faq->categoria = Categoria::find($faq->categoriaId); }
        foreach ($faqs as $faq) {
            $faq->categoria = CategoriaFaq::find($faq->categoriaFaqId); // Usar CategoriaFaq
        }

        $router->render('admin/faqs/index', [
            'titulo' => 'Administrar FAQs',
            'faqs' => $faqs
        ], 'admin-layout');
    }

    public static function adminCrear(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $faq = new Faq();
        $categoriasFaq = CategoriaFaq::all();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $faq->sincronizar($_POST);
            $faq->categoriaFaqId = $_POST['categoriaFaqId'] ?? null;

            // Limpiar los saltos de línea antes de validar y guardar
            $faq->pregunta = trim(str_replace("\r\n", "\n", $faq->pregunta));
            $faq->respuesta = trim(str_replace("\r\n", "\n", $faq->respuesta));

            $alertas = $faq->validar();

            if (empty($alertas)) {
                $resultado = $faq->guardar();
                if ($resultado) {
                    header('Location: /admin/faqs');
                    exit();
                }
            }
        }

        $router->render('admin/faqs/crear', [
            'titulo' => 'Crear FAQ',
            'faq' => $faq,
            'categorias' => $categoriasFaq,
        ], 'admin-layout');
    }

    public static function adminEditar(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/faqs');
            exit();
        }

        $faq = Faq::find($id);
        if (!$faq) {
            header('Location: /admin/faqs');
            exit();
        }

        $categoriasFaq = CategoriaFaq::all();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $faq->sincronizar($_POST);
            $faq->categoriaFaqId = $_POST['categoriaFaqId'] ?? null;

            // Limpiar los saltos de línea antes de validar y guardar
            $faq->pregunta = trim(str_replace("\r\n", "\n", $faq->pregunta));
            $faq->respuesta = trim(str_replace("\r\n", "\n", $faq->respuesta));

            $alertas = $faq->validar();

            if (empty($alertas)) {
                $resultado = $faq->guardar();
                if ($resultado) {
                    header('Location: /admin/faqs');
                    exit();
                }
            }
        }

        $router->render('admin/faqs/editar', [
            'titulo' => 'Editar FAQ',
            'faq' => $faq,
            'categorias' => $categoriasFaq,
        ], 'admin-layout');
    }

    public static function adminEliminar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $faq = Faq::find($id);
            if ($faq) {
                $faq->eliminar();
            }
        }
        header('Location: /admin/faqs');
        exit();
    }

    public static function adminFrequentQuestions(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $preguntasFrecuentes = PreguntaUsuario::findFrequentPendingReview();
        
        foreach ($preguntasFrecuentes as $pregunta) {
            $pregunta->categoria = CategoriaFaq::find($pregunta->categoriaFaqId);
            $pregunta->usuario = \Model\Usuario::find($pregunta->usuarioId); // Assuming you have a Usuario model
        }

        $router->render('admin/faqs/frequent-questions', [ // Create this new view
            'titulo' => 'Preguntas Frecuentes de Usuarios',
            'preguntas' => $preguntasFrecuentes
        ], 'admin-layout');
    }

    public static function adminMarkFrequentQuestionReviewed() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $pregunta = PreguntaUsuario::find($id);
            if ($pregunta) {
                $pregunta->estado_revision = $_POST['estado_revision'] ?? 'en_revision'; // 'en_revision' or 'descartada'
                $pregunta->guardar();
            }
        }
        header('Location: /admin/faqs/frequent-questions');
        exit();
    }

    public static function adminConvertFrequentToFaq(Router $router) {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/faqs/frequent-questions');
            exit();
        }

        $preguntaUsuario = PreguntaUsuario::find($id);
        if (!$preguntaUsuario) {
            header('Location: /admin/faqs/frequent-questions');
            exit();
        }

        $faq = new Faq([
            'pregunta' => $preguntaUsuario->pregunta,
            'respuesta' => 'Respuesta pendiente. Por favor, edita esta FAQ.', // Placeholder
            'categoriaFaqId' => $preguntaUsuario->categoriaFaqId,
        ]);
        
        $alertas = $faq->validar(); // Basic validation

        if (empty($alertas)) {
            $resultado = $faq->guardar();
            if ($resultado) {
                $preguntaUsuario->estado_revision = 'faq_creada'; // Mark as converted
                $preguntaUsuario->marcada_frecuente = 1; // Ensure it stays marked
                $preguntaUsuario->guardar();
                Faq::setAlerta('exito', 'FAQ creada exitosamente. Por favor, edita la respuesta.');
                header('Location: /admin/faqs/editar?id=' . $faq->id); // Redirect to edit the new FAQ
                exit();
            } else {
                Faq::setAlerta('error', 'Hubo un error al crear la FAQ.');
            }
        }
        
        $router->render('admin/faqs/frequent-questions', [ // Render the list with alerts
            'titulo' => 'Preguntas Frecuentes de Usuarios',
            'preguntas' => PreguntaUsuario::findFrequentPendingReview(), // Reload list
        ], 'admin-layout');
    }
}