<?php

namespace MVC;

class Router {

    public $rutasGET = []; // Arreglo para almacenar rutas que responden a solicitudes GET
    public $rutasPOST = []; // Arreglo para almacenar rutas que responden a solicitudes POST

    // Método para registrar rutas GET
    public function get($url, $fn) {
        $this->rutasGET[$url] = $fn; // Asocia la URL con una función a ejecutar
    }

    // Método para registrar rutas POST
    public function post($url, $fn) {
        $this->rutasPOST[$url] = $fn; // Asocia la URL con una función a ejecutar
    }

    // Método para comprobar qué ruta se ha solicitado y ejecutar la función asociada
    public function comprobarRutas() {
        $urlProtegida = filter_var($_GET['url'] ?? '', FILTER_SANITIZE_URL); // Obtiene la URL solicitada y la sanitiza para evitar inyecciones de código
        $urlActual = ($urlProtegida === '') ? '/' : '/' . $urlProtegida; // Si la URL está vacía, se establece como raíz, de lo contrario se agrega una barra al inicio

        $metodo = $_SERVER['REQUEST_METHOD']; // Obtiene el método HTTP de la solicitud (GET o POST)

        if($metodo === 'GET') {
            $fn = $this->rutasGET[$urlActual] ?? null; // Busca la función en el arreglo de rutas GET
        } else {
            $fn = $this->rutasPOST[$urlActual] ?? null; // Busca la función en el arreglo de rutas POST
        }

        if($fn) {
            // Si existe una función asociada a la ruta, se ejecuta
            call_user_func($fn, $this); // Llama a la función almacenada, pasándole la instancia del Router
        } else {
            // Si la ruta no existe, muestra un mensaje de error
            echo "Pagina No Encontrada";
        }
    }

    // Método para renderizar vistas
    public function render($view, $datos = [], $layout = 'layout') {

        // Extrae los datos enviados para usarlos en la vista
        foreach($datos as $key => $value) {
            $$key = $value; // Convierte los elementos del array asociativo en variables con el mismo nombre de la clave
        }

        ob_start(); // Inicia el almacenamiento en memoria para capturar la salida del buffer
        include __DIR__ . "/views/$view.php"; // Incluye la vista específica

        $contenido = ob_get_clean(); // Obtiene el contenido del buffer y limpia el almacenamiento

        include __DIR__ . "/views/$layout.php"; // Incluye la plantilla base y pasa el contenido de la vista
    }
}