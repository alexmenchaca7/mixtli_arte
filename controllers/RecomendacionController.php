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
        
        // Obtener categorías recomendadas por interacciones y preferencias
        $categoriasRecomendadas = self::obtenerCategoriasRecomendadas($usuarioId);

        $idsPorCategorias = [];
        if (!empty($categoriasRecomendadas)) {
            $log .= "Categorías recomendadas por interacción (ordenadas por peso): " . implode(', ', $categoriasRecomendadas) . "\n";
            
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

        // Unificar todas las recomendaciones
        $todosLosIds = array_unique($idsPorCategorias);
        
        // Filtrar productos que al usuario no le interesan
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