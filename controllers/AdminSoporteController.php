<?php

namespace Controllers;

use MVC\Router;
use Model\Soporte;
use Classes\Paginacion;
use Classes\Email;

class AdminSoporteController {
    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $estado_filtro = $_GET['estado'] ?? '';
        $busqueda = $_GET['busqueda'] ?? '';

        if ($pagina_actual < 1) {
            header('Location: /admin/soporte?page=1');
            exit();
        }

        $registros_por_pagina = 10;
        $condiciones = [];

        if (!empty($estado_filtro)) {
            $condiciones[] = "estado = '" . Soporte::$conexion->escape_string($estado_filtro) . "'";
        }
        if (!empty($busqueda)) {
            $busquedaEscaped = Soporte::$conexion->escape_string("%{$busqueda}%");
            $condiciones[] = "(asunto LIKE '{$busquedaEscaped}' OR mensaje LIKE '{$busquedaEscaped}' OR email LIKE '{$busquedaEscaped}' OR numero_caso LIKE '{$busquedaEscaped}')";
        }

        $total = Soporte::totalCondiciones($condiciones);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/soporte?page=1');
            exit();
        }

        $params = [
            'condiciones' => $condiciones,
            'orden' => 'creado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $consultas = Soporte::metodoSQL($params);

        $router->render('admin/soporte/index', [
            'titulo' => 'Consultas de Soporte',
            'consultas' => $consultas,
            'paginacion' => $paginacion->paginacion(),
            'estado_filtro' => $estado_filtro,
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function ver(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/soporte');
            exit();
        }

        $consulta = Soporte::find($id);
        if (!$consulta) {
            header('Location: /admin/soporte');
            exit();
        }

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'actualizar_estado';

            // TEMPORAL: Depurar los IDs para entender el problema
            // debuguear([
            //     'ID_from_GET' => $id, // ID de la consulta cargada
            //     'ID_from_Form_Post' => ($_POST['consulta_id'] ?? 'N/A'), // ID del campo oculto en el formulario
            //     'Action_from_Form' => $action,
            //     'Raw_POST_data' => $_POST
            // ]);


            if ($action === 'responder_consulta') {
                $respuestaMensaje = trim($_POST['respuesta_mensaje'] ?? '');
                $consultaIdEnFormulario = filter_var($_POST['consulta_id'] ?? '', FILTER_VALIDATE_INT);

                // La comparación estricta (`!==`) es correcta aquí.
                // Si esto sigue fallando, la diferencia de tipo o valor es real.
                if ($consultaIdEnFormulario !== $consulta->id) {
                    Soporte::setAlerta('error', 'Error de seguridad: ID de consulta no coincide.');
                } elseif (empty($respuestaMensaje)) {
                    Soporte::setAlerta('error', 'El mensaje de respuesta no puede estar vacío.');
                } else {
                    $adminEmail = $_ENV['EMAIL_ADMIN_SUPPORT'];
                    $adminName = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] . ' (Soporte MixtliArte)';
                    
                    $email = new Email($consulta->email, 'Usuario MixtliArte', $consulta->numero_caso);
                    $resultadoEnvio = $email->enviarRespuestaSoporte(
                        $consulta->email,
                        $consulta->numero_caso,
                        $consulta->asunto,
                        $respuestaMensaje,
                        $adminName
                    );

                    if ($resultadoEnvio) {
                        Soporte::setAlerta('exito', 'Respuesta enviada al usuario correctamente.');
                        if ($consulta->estado === 'pendiente' || $consulta->estado === 'en_proceso') {
                             $consulta->estado = 'resuelto';
                             $consulta->actualizado = date('Y-m-d H:i:s');
                             if(is_null($consulta->fecha_resolucion)) {
                                 $consulta->fecha_resolucion = date('Y-m-d H:i:s');
                             }
                             $consulta->guardar();
                        }
                    } else {
                        Soporte::setAlerta('error', 'Hubo un error al enviar la respuesta por correo.');
                    }
                }
            } elseif ($action === 'actualizar_estado') {
                $nuevoEstado = $_POST['estado'] ?? '';
                $estadosValidos = ['pendiente', 'en_proceso', 'resuelto', 'cerrado'];

                if (in_array($nuevoEstado, $estadosValidos)) {
                    $consulta->estado = $nuevoEstado;
                    $consulta->actualizado = date('Y-m-d H:i:s');
                    
                    if (in_array($nuevoEstado, ['resuelto', 'cerrado']) && is_null($consulta->fecha_resolucion)) {
                        $consulta->fecha_resolucion = date('Y-m-d H:i:s');
                    } 
                    else if (!in_array($nuevoEstado, ['resuelto', 'cerrado'])) {
                        $consulta->fecha_resolucion = null;
                    }

                    $resultado = $consulta->guardar();

                    if ($resultado) {
                        Soporte::setAlerta('exito', 'Estado de la consulta actualizado correctamente.');
                    } else {
                        Soporte::setAlerta('error', 'Hubo un error al actualizar el estado.');
                    }
                } else {
                    Soporte::setAlerta('error', 'Estado no válido.');
                }
            }
            $alertas = array_merge($alertas, Soporte::getAlertas());
        }

        $router->render('admin/soporte/ver', [
            'titulo' => 'Ver Consulta de Soporte',
            'consulta' => $consulta,
            'alertas' => $alertas
        ], 'admin-layout');
    }

    public static function eliminar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $consulta = Soporte::find($id);
            if ($consulta) {
                $consulta->eliminar();
                Soporte::setAlerta('exito', 'Consulta eliminada correctamente.');
            } else {
                Soporte::setAlerta('error', 'Consulta no encontrada.');
            }
        } else {
            Soporte::setAlerta('error', 'ID de consulta no válido.');
        }
        header('Location: /admin/soporte');
        exit();
    }
}