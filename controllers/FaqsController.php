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
        if (!is_auth()) {
            header('Location: /login');
            exit();
        }

        // Obtener todas las categorías para el filtro
        // Antes: $categorias = Categoria::all();
        $categoriasFaq = CategoriaFaq::all(); // Cargar desde el nuevo modelo

        // Obtener todas las FAQs, agrupadas por categoría si es necesario
        // Antes: $faqs = Faq::metodoSQL(['orden' => 'categoriaId ASC']);
        $faqs = Faq::metodoSQL(['orden' => 'categoriaFaqId ASC']); // Usar el nuevo ID de categoría

        // Para mostrar el nombre de la categoría en las FAQs, necesitas unirlas
        foreach ($faqs as $faq) {
            $faq->categoria = CategoriaFaq::find($faq->categoriaFaqId);
        }

        // Formulario de pregunta
        $preguntaUsuario = new PreguntaUsuario();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $preguntaUsuario->sincronizar($_POST);
            // Asegurarse de que el campo de categoría se mapee correctamente
            $preguntaUsuario->categoriaFaqId = $_POST['categoriaFaqId'] ?? null; // Usar el nuevo nombre de campo en POST
            $preguntaUsuario->usuarioId = $_SESSION['id'];
            $alertas = $preguntaUsuario->validar();

            if (empty($alertas)) {
                $palabrasClaveEncontradas = self::etiquetarPalabrasClave($preguntaUsuario->pregunta);
                $preguntaUsuario->palabras_clave = json_encode($palabrasClaveEncontradas);

                $preguntasSimilares = PreguntaUsuario::buscarPreguntasSimilares($preguntaUsuario->pregunta);
                
                if (!empty($preguntasSimilares)) {
                    $preguntaExistente = array_shift($preguntasSimilares);
                    $preguntaExistente->frecuencia = $preguntaExistente->frecuencia + 1;

                    $umbralFrecuencia = 10; 
                    if ($preguntaExistente->frecuencia >= $umbralFrecuencia && $preguntaExistente->marcada_frecuente == 0) {
                        $preguntaExistente->marcada_frecuente = 1;
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
            $alertas = array_merge($alertas, Faq::getAlertas());
        }

        $layout = ($_SESSION['rol'] === 'vendedor') ? 'vendedor-layout' : 'layout';

        $router->render('paginas/faqs/index', [
            'titulo' => 'Preguntas Frecuentes',
            'categorias' => $categoriasFaq, // Pasar las nuevas categorías de FAQ
            'faqs' => $faqs,
            'preguntaUsuario' => $preguntaUsuario,
            'alertas' => $alertas
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
            'alertas' => $alertas
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
            'alertas' => $alertas
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
}