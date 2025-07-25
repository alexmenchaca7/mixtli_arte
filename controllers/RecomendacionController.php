<?php

namespace Controllers;

use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Categoria;
use Model\PreferenciaUsuario;
use Model\HistorialInteraccion;
use Model\ProductoNoInteresado;

class RecomendacionController {

    // MÉTODO PÚBLICO Y UNIFICADO
    public static function obtenerRecomendacionesUnificadas(int $usuarioId, int $limiteProductosPorCategoria = 15): array {
        $log = "";
        
        // 1. Obtener recomendaciones por similitud de usuarios (alta prioridad)
        $resultadoSimilitud = self::obtenerRecomendacionesPorSimilitud($usuarioId, $log);
        $idsPorSimilitud = $resultadoSimilitud['ids']; // Extraemos solo el array de IDs
        
        // 2. Obtener categorías recomendadas por interacciones y preferencias
        $categoriasRecomendadas = self::obtenerCategoriasRecomendadas($usuarioId);

        $idsPorCategorias = [];
        if (!empty($categoriasRecomendadas)) {
            $log .= "Categorías recomendadas por interacción (ordenadas por peso): " . implode(', ', $categoriasRecomendadas) . "\n";
            $idsCategoriasString = implode(',', $categoriasRecomendadas);
            
            foreach ($categoriasRecomendadas as $catId) {
                $query = "SELECT id FROM productos WHERE categoriaId = {$catId} AND estado != 'agotado' LIMIT {$limiteProductosPorCategoria}";
                $productosDeCategoria = Producto::consultarSQL($query);
                $idsDeCategoria = array_column($productosDeCategoria, 'id');
                $idsPorCategorias = array_merge($idsPorCategorias, $idsDeCategoria);
            }
            
            $log .= "Se obtuvieron " . count($idsPorCategorias) . " productos de las categorías recomendadas.\n";
        } else {
            $log .= "No se encontraron categorías recomendadas por interacción.\n";
        }

        // 3. Unificar todas las recomendaciones
        // Ahora $idsPorSimilitud es un array plano y 'array_merge' funciona correctamente.
        $todosLosIds = array_unique(array_merge($idsPorSimilitud, $idsPorCategorias));
        
        // 4. Filtrar productos que al usuario no le interesan
        $productosNoInteresados = ProductoNoInteresado::whereField('usuarioId', $usuarioId);
        $idsNoInteresados = array_column($productosNoInteresados, 'productoId');

        // Obtener productos que el usuario ya compró
        $compras = HistorialInteraccion::whereArray(['usuarioId' => $usuarioId, 'tipo' => 'compra']);
        $idsComprados = array_column($compras, 'productoId');

        // Obtener productos que el usuario ya tiene en favoritos
        $favoritos = Favorito::whereField('usuarioId', $usuarioId);
        $idsFavoritos = array_column($favoritos, 'productoId');

        // Unificar todos los IDs a excluir
        $idsParaExcluir = array_unique(array_merge($idsNoInteresados, $idsComprados, $idsFavoritos));
        
        if(!empty($idsParaExcluir)) {
            $log .= "Filtrando " . count($idsParaExcluir) . " productos (no interesados, comprados o favoritos).\n";
            $todosLosIds = array_diff($todosLosIds, $idsParaExcluir);
        }

        return [
            'ids' => array_values($todosLosIds),
            'log' => $log
        ];
    }

    public static function obtenerCategoriasRecomendadas(int $usuarioId): array {
        // Verificar si el usuario cumple el umbral para recibir recomendaciones dinámicas
        $clicks = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'clic']);
        $busquedas = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'busqueda']);
        
        // Estructura unificada para guardar los pesos y el desglose de cada categoría
        $categoriasInteres = [];

        // Penalizar categorías de productos "No me interesa"
        $productosNoInteresados = ProductoNoInteresado::whereField('usuarioId', $usuarioId);
        if (!empty($productosNoInteresados)) {
            foreach ($productosNoInteresados as $item) {
                $producto = Producto::find($item->productoId);
                if ($producto && $producto->categoriaId) {
                    $catId = $producto->categoriaId;
                    if (!isset($categoriasInteres[$catId])) {
                        $categoriasInteres[$catId] = ['total' => 0, 'breakdown' => []];
                    }
                    $penalizacion = -1000;
                    $categoriasInteres[$catId]['total'] += $penalizacion;
                    $categoriasInteres[$catId]['breakdown'][] = "Penalización por 'No me interesa' (Producto ID {$producto->id}): {$penalizacion}";
                }
            }
        }

        // Obtener preferencias explícitas del usuario y darles un peso inicial alto (10)
        $preferencias = PreferenciaUsuario::where('usuarioId', $usuarioId);
        $categoriasIdsPref = $preferencias ? json_decode($preferencias->categorias, true) : [];

        if (!empty($categoriasIdsPref)) {
            foreach ($categoriasIdsPref as $catId) {
                $catId = intval($catId);
                // Aseguramos que desde el inicio se use la estructura correcta
                if (!isset($categoriasInteres[$catId])) {
                    $categoriasInteres[$catId] = ['total' => 0, 'breakdown' => []];
                }
                $categoriasInteres[$catId]['total'] += 10; // Sumamos al total
                $categoriasInteres[$catId]['breakdown'][] = 'Preferencia Explícita: +10';
            }
        }

        // Solo procesar interacciones si el usuario ha alcanzado el umbral (10 clics o 10 busquedas minimo)
        if ($clicks >= 10 || $busquedas >= 5) {
            $interacciones = HistorialInteraccion::whereField('usuarioId', $usuarioId);

            foreach ($interacciones as $interaccion) {
                $catId = null;
                $peso = 0; // Inicializamos el peso
                $descripcionInteraccion = ''; // Para describir la interacción en el log

                // --- Cálculo de Peso y Descripción ---
                if ($interaccion->tipo === 'tiempo_en_pagina' && $interaccion->metadata) {
                    $metadata = json_decode($interaccion->metadata, true);
                    $segundos = $metadata['segundos'] ?? 0;
                    // Fórmula para dar más peso a mayor tiempo, con un límite para no desbalancear.
                    // 1 punto por cada 20 segundos, con un máximo de 5 puntos.
                    $peso = min(floor($segundos / 20), 5); 
                    $descripcionInteraccion = "Tiempo en Página ({$segundos}s) en producto ID {$interaccion->productoId}: +{$peso}";
                } else {
                    $peso = self::obtenerPesoInteraccion($interaccion->tipo);
                    // Crea una descripción basada en el tipo de interacción
                    switch ($interaccion->tipo) {
                        case 'compra':
                            $descripcionInteraccion = "Compra de producto ID {$interaccion->productoId}: +{$peso}";
                            break;
                        case 'favorito':
                            $descripcionInteraccion = "Agregó a favoritos producto ID {$interaccion->productoId}: +{$peso}";
                            break;
                        default:
                            $descripcionInteraccion = "Interacción '{$interaccion->tipo}' en producto ID {$interaccion->productoId}: +{$peso}";
                            break;
                    }
                }

                // --- Búsqueda de Categoría ID ---
                if ($interaccion->productoId) {
                    $producto = Producto::find($interaccion->productoId);
                    if ($producto && $producto->categoriaId) {
                        $catId = $producto->categoriaId;
                    }
                } elseif ($interaccion->tipo === 'busqueda' || strpos($interaccion->tipo, 'autocompletado') === 0) {
                    $metadata = json_decode($interaccion->metadata, true);
                    $terminoBusqueda = $metadata['termino'] ?? '';
                    
                    if (!empty($terminoBusqueda)) {
                        // Buscar la categoría por nombre
                        $categoriaEncontrada = Categoria::where('nombre', $terminoBusqueda);
                        if ($categoriaEncontrada) {
                            $catId = $categoriaEncontrada->id;
                            $descripcionInteraccion .= " para el término '{$terminoBusqueda}'";
                        }
                    }
                }
                
                // --- Acumulación de Pesos y Desglose ---
                if ($catId && $peso > 0) { // Solo sumar si el peso es mayor a cero
                    if (!isset($categoriasInteres[$catId])) {
                        // Inicializa la estructura para esta categoría si es la primera vez que la vemos
                        $categoriasInteres[$catId] = ['total' => 0, 'breakdown' => []];
                    }
                    $categoriasInteres[$catId]['total'] += $peso;
                    $categoriasInteres[$catId]['breakdown'][] = $descripcionInteraccion;
                }
            }

            // Ajuste adicional por umbral de favoritos
            $favoritos = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
            if ($favoritos >= 3) {
                $productosFavoritos = HistorialInteraccion::whereArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
                foreach ($productosFavoritos as $fav) {
                    $producto = Producto::find($fav->productoId);
                    if ($producto && isset($categoriasInteres[$producto->categoriaId])) {
                        // Solo aplicar el multiplicador si el peso actual de la categoría es POSITIVO.
                        if ($categoriasInteres[$producto->categoriaId]['total'] > 0) {
                            $pesoAntiguo = $categoriasInteres[$producto->categoriaId]['total'];

                            // Multiplicamos el peso para dar un gran impulso a las categorías de favoritos
                            $categoriasInteres[$producto->categoriaId]['total'] *= 1.5;
                            
                            // Redondear para evitar decimales largos en el log
                            $categoriasInteres[$producto->categoriaId]['total'] = round($categoriasInteres[$producto->categoriaId]['total'], 2);

                            $pesoNuevo = $categoriasInteres[$producto->categoriaId]['total'];
                            $categoriasInteres[$producto->categoriaId]['breakdown'][] = "Multiplicador por favoritos (x1.5): {$pesoAntiguo} -> {$pesoNuevo}";
                        }
                    }
                }
            }
        }
        
        // FILTRAR para quedarnos solo con lo relevante
        $categoriasPositivas = array_filter($categoriasInteres, function($categoria) {
            return $categoria['total'] > 0;
        });

        // ORDENAR únicamente las categorías que tienen un peso positivo
        uasort($categoriasPositivas, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });


        // --- INICIO DEL CÓDIGO DE LOGGING ---

        // Preparar el contenido del log
        $logContent = "-------------------------------------------------\n";
        $logContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "Usuario ID: " . $usuarioId . "\n\n";
        $logContent .= "Fórmula de Pesos y Penalizaciones:\n";
        $logContent .= " - Penalización 'No me interesa': -1000\n";
        $logContent .= " - Compra: 15\n";
        $logContent .= " - Preferencia Explícita: 10\n";
        $logContent .= " - Favorito: 8\n";
        $logContent .= " - Clic en Autocompletado: 5\n";
        $logContent .= " - Búsqueda: 3\n";
        $logContent .= " - Clic: 1\n";
        $logContent .= " - Tiempo en Página: 1 punto por cada 20s (máx 5)\n\n";

        // Ordenamos el array de intereses COMPLETO (incluyendo negativos) solo para el log
        uasort($categoriasInteres, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        $logContent .= "Cálculo Detallado de Intereses por Categoría (Ordenado por Peso):\n";
        if (empty($categoriasInteres)) {
            $logContent .= "No se calcularon intereses para este usuario.\n";
        } else {
            foreach ($categoriasInteres as $id => $data) {
                $nombreCategoria = Categoria::find($id)->nombre ?? 'Desconocida';
                $logContent .= "\n[Categoría: {$nombreCategoria} (ID: {$id})] - Peso Total: {$data['total']}\n";
                $logContent .= "   Desglose del cálculo:\n";
                foreach ($data['breakdown'] as $linea) {
                    $logContent .= "     - " . $linea . "\n";
                }
            }
        }

        $categoriasFinalesParaUsuario = array_keys($categoriasPositivas);
        $logContent .= "\nOrden Final de Categorías Recomendadas (Peso > 0):\n";
        $logContent .= empty($categoriasFinalesParaUsuario) ? "Ninguna" : implode(', ', $categoriasFinalesParaUsuario);
        $logContent .= "\n-------------------------------------------------\n\n";

        $logFilePath = __DIR__ . '/../recomendaciones.log';
        file_put_contents($logFilePath, $logContent, FILE_APPEND);
        // --- FIN DEL CÓDIGO DE LOGGING ---


        // 3. DEVOLVER el array final: filtrado y ordenado.
        return $categoriasFinalesParaUsuario;
    }

    // Obtiene recomendaciones de productos basadas en la similitud con otros usuarios (Filtro Colaborativo).
    public static function obtenerRecomendacionesPorSimilitud(int $usuarioId, string &$log): array {
        $idsProductosUsuarioActual = [];
        // Solo consideramos interacciones fuertes para la similitud
        $interacciones = HistorialInteraccion::consultarSQL("SELECT productoId FROM historial_interacciones WHERE usuarioId = {$usuarioId} AND (tipo = 'favorito' OR tipo = 'compra') AND productoId IS NOT NULL");
        
        if (!empty($interacciones)) {
            $idsProductosUsuarioActual = array_unique(array_column($interacciones, 'productoId'));
        }

        if (empty($idsProductosUsuarioActual)) {
            $log .= "Similitud: Usuario no tiene interacciones significativas (favoritos/compras). No se pueden generar recomendaciones por similitud.\n";
            return ['ids' => [], 'log' => ''];
        }
        
        // Pasamos el log por referencia para que la función de búsqueda pueda escribir en él
        $usuariosSimilares = self::encontrarUsuariosSimilares($usuarioId, $idsProductosUsuarioActual, $log);
        
        if (empty($usuariosSimilares)) {
            $log .= "Similitud: No se encontraron usuarios suficientemente similares.\n";
            return ['ids' => [], 'log' => ''];
        }

        $idsUsuariosSimilares = array_keys($usuariosSimilares);
        // Obtener los nombres de los usuarios similares para un log más legible
        $nombresUsuariosSimilares = [];
        foreach($idsUsuariosSimilares as $idSimilar) {
            $usuario = Usuario::find($idSimilar);
            $nombresUsuariosSimilares[] = $usuario ? "{$usuario->nombre} (ID: {$idSimilar})" : "Usuario ID: {$idSimilar}";
        }
        $log .= "Similitud: Usuarios similares encontrados (ordenados por relevancia): " . implode(', ', $nombresUsuariosSimilares) . ".\n";

        // Buscar productos que a los usuarios similares les gustaron
        $interaccionesSimilares = HistorialInteraccion::consultarSQL(
            "SELECT DISTINCT productoId FROM historial_interacciones WHERE usuarioId IN (" . implode(',', $idsUsuariosSimilares) . ") AND (tipo = 'favorito' OR tipo = 'compra') AND productoId IS NOT NULL"
        );
        
        $idsRecomendadosBruto = array_column($interaccionesSimilares, 'productoId');
        
        // Excluir productos que el usuario actual ya ha interactuado
        $idsRecomendadosFinal = array_diff($idsRecomendadosBruto, $idsProductosUsuarioActual);
        
        if (empty($idsRecomendadosFinal)) {
            $log .= "Similitud: Los usuarios similares no tienen productos nuevos que recomendar.\n";
        } else {
            $log .= "Similitud: Se encontraron " . count($idsRecomendadosFinal) . " productos recomendados desde usuarios similares: [" . implode(', ', $idsRecomendadosFinal) . "]\n";
        }

        return [
            'ids' => array_values(array_unique($idsRecomendadosFinal)),
            'log' => '' // El log ya se pasó por referencia
        ];
    }

    // Encuentra usuarios con gustos similares basados en el índice de Jaccard.
    private static function encontrarUsuariosSimilares(int $idUsuarioActual, array $idsProductosUsuarioActual, string &$log): array {
        $interaccionesOtrosUsuarios = HistorialInteraccion::consultarSQL(
            "SELECT usuarioId, productoId FROM historial_interacciones WHERE usuarioId != {$idUsuarioActual} AND (tipo = 'favorito' OR tipo = 'compra') AND productoId IS NOT NULL"
        );

        $mapaUsuarioProducto = [];
        foreach ($interaccionesOtrosUsuarios as $interaccion) {
            // Nos aseguramos que cada producto solo cuente una vez por usuario
            if (!isset($mapaUsuarioProducto[$interaccion->usuarioId]) || !in_array($interaccion->productoId, $mapaUsuarioProducto[$interaccion->usuarioId])) {
                $mapaUsuarioProducto[$interaccion->usuarioId][] = $interaccion->productoId;
            }
        }

        $similitudes = [];
        $log .= "Similitud: Calculando índice de Jaccard contra otros usuarios...\n";
        foreach ($mapaUsuarioProducto as $idUsuario => $idsProductos) {
            $interseccion = count(array_intersect($idsProductosUsuarioActual, $idsProductos));
            $union = count(array_unique(array_merge($idsProductosUsuarioActual, $idsProductos)));
            
            if ($union > 0) {
                $indiceJaccard = $interseccion / $union;
                // Solo registramos a los que tienen alguna similitud para no saturar el log
                if ($indiceJaccard > 0) { 
                    $log .= "  - vs Usuario ID {$idUsuario}: Intersección={$interseccion}, Unión={$union}, Similitud=" . number_format($indiceJaccard, 2) . "\n";
                    // Aplicamos el umbral para considerarlo "similar"
                    if ($indiceJaccard > 0.1) {
                        $similitudes[$idUsuario] = $indiceJaccard;
                    }
                }
            }
        }
        arsort($similitudes); // Ordenar por puntuación de similitud
        return array_slice($similitudes, 0, 10, true); // Devolver los 10 usuarios más similares
    }


    private static function obtenerPesoInteraccion(string $tipo): int {
        switch ($tipo) {
            // Interacciones de alta intención
            case 'compra':
                return 15; // Un usuario que compra algo está muy interesado
            case 'favorito':
                return 8;  // Marcar como favorito es una señal fuerte


            // Interacciones de búsqueda (media-alta intención)
            case 'autocompletado_categoria':
            case 'autocompletado_producto':
                return 5; // El usuario seleccionó una sugerencia, es una búsqueda dirigida
            case 'busqueda':
                return 3;  // Una búsqueda normal también es una señal de interés


            // Interacciones de descubrimiento (baja intención)
            case 'clic':
                return 1;
            case 'tiempo_en_pagina': 
                return 1;
            default:
                return 0;
        }
    }
}