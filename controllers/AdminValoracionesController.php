<?php

namespace Controllers;

use MVC\Router;
use Model\Producto;
use Model\Usuario;
use Model\Valoracion;

class AdminValoracionesController {
    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        // --- OBTENER VALORACIONES PENDIENTES (moderado = 0) ---
        $queryPendientes = "SELECT * FROM " . Valoracion::getTablaNombre() . " WHERE moderado = 0 AND comentario IS NOT NULL AND comentario != '' ORDER BY id DESC";
        $valoracionesPendientes = Valoracion::consultarSQL($queryPendientes);
        foreach($valoracionesPendientes as $valoracion) {
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->calificado = Usuario::find($valoracion->calificadoId);
            $valoracion->producto = Producto::find($valoracion->productoId);
        }
        
        // --- OBTENER VALORACIONES YA PROCESADAS (moderado = 1) ---
        $queryProcesadas = "SELECT * FROM " . Valoracion::getTablaNombre() . " WHERE moderado IN (1, 2) AND comentario IS NOT NULL AND comentario != '' ORDER BY id DESC";
        $valoracionesProcesadas = Valoracion::consultarSQL($queryProcesadas);
        foreach($valoracionesProcesadas as $valoracion) {
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->calificado = Usuario::find($valoracion->calificadoId);
            $valoracion->producto = Producto::find($valoracion->productoId);
        }

        $router->render('admin/valoraciones/index', [
            'titulo' => 'Moderar Valoraciones',
            'valoracionesPendientes' => $valoracionesPendientes,
            'valoracionesProcesadas' => $valoracionesProcesadas // Pasar el nuevo array a la vista
        ], 'admin-layout');
    }

    public static function aprobar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $valoracion = Valoracion::find($id);
            if ($valoracion) {
                $valoracion->moderado = 1; // 1 = Aprobado
                $valoracion->guardar();
            }
        }
        header('Location: /admin/valoraciones');
        exit();
    }

    public static function rechazar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }
        
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $valoracion = Valoracion::find($id);
            if ($valoracion) {
                $valoracion->moderado = 2; // 2 = Rechazado
                $valoracion->guardar();
            }
        }
        header('Location: /admin/valoraciones');
        exit();
    }
}