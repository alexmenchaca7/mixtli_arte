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

        // Build conditions using the model's capabilities
        if (!empty($estado_filtro)) {
            // No need to escape here, metodoSQL will handle it for specific fields
            $condiciones[] = "estado = '{$estado_filtro}'";
        }
        if (!empty($busqueda)) {
            // Use Soporte::buscar which is configured to use $buscarColumns and handles escaping
            $buscarCondiciones = Soporte::buscar($busqueda);
            if (!empty($buscarCondiciones)) {
                $condiciones[] = "(" . implode(' OR ', $buscarCondiciones) . ")"; // Combine search conditions with OR
            }
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

        if ($consulta) { // Ensure $consulta exists before trying to cast its property
            $consulta->id = (int) $consulta->id; // Explicitly cast the ID from string to integer
        }

        if (!$consulta) {
            header('Location: /admin/soporte');
            exit();
        }

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'actualizar_estado';

            // Define consultaIdEnFormulario unconditionally here
            // This variable is needed for comparison regardless of the action
            // It defaults to 0 if 'consulta_id' is not set in POST (e.g., for 'actualizar_estado' where it might not be explicitly passed)
            $consultaIdEnFormulario = (int) filter_var($_POST['consulta_id'] ?? 0, FILTER_VALIDATE_INT); 

            // TEMPORAL: Depurar los IDs para entender el problema
            // debuguear([
            //     'ID_from_GET (consulta->id)' => $consulta->id, // ID de la consulta cargada del DB (int)
            //     'Raw_POST_consulta_id' => ($_POST['consulta_id'] ?? 'N/A'), // Valor directo de $_POST
            //     'Valor_despues_filter_var_y_cast' => $consultaIdEnFormulario, // Valor después de la validation and cast
            //     'Tipo_despues_filter_var_y_cast' => gettype($consultaIdEnFormulario), // Tipo después de the validation and cast
            //     'Action_from_Form' => $action,
            //     'Raw_POST_data' => $_POST
            // ]);


            if ($action === 'responder_consulta') {
                $respuestaMensaje = trim($_POST['respuesta_mensaje'] ?? '');
                
                // The strict comparison ( !== ) is correct here.
                // With the explicit conversion, types should match if values are the same.
                if ($consultaIdEnFormulario !== $consulta->id) { // This condition will now correctly compare types and values
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

                // Since $consultaIdEnFormulario is now defined earlier,
                // we only need to compare it if a specific ID check is relevant for 'actualizar_estado'
                // Otherwise, the comparison above (if ($consultaIdEnFormulario !== $consulta->id))
                // would already handle cases where consulta_id is passed but doesn't match the current view.
                
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