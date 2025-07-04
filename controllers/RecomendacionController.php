<?php

namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\PreferenciaUsuario;
use Model\HistorialInteraccion;

class RecomendacionController {

    public static function obtenerCategoriasRecomendadas(int $usuarioId): array {
        // Verificar si el usuario cumple el umbral para recibir recomendaciones dinámicas
        $clicks = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'clic']);
        $busquedas = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'busqueda']);
        
        // Estructura unificada para guardar los pesos y el desglose de cada categoría
        $categoriasInteres = [];

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
                        $catIdsEncontradas[] = $producto->categoriaId;
                        $descripcionInteraccion .= " en producto ID {$interaccion->productoId}";
                    }
                } elseif ($interaccion->tipo === 'autocompletado_categoria') {
                    $metadata = json_decode($interaccion->metadata, true);
                    $termino = $metadata['termino'] ?? '';
                    if ($termino) {
                        $categoria = Categoria::where('nombre', $termino);
                        if ($categoria) $catIdsEncontradas[] = $categoria->id;
                        $descripcionInteraccion .= " para el término '{$termino}'";
                    }
                } elseif ($interaccion->tipo === 'busqueda') {
                    $metadata = json_decode($interaccion->metadata, true);
                    $termino = $metadata['termino'] ?? null;
                    $descripcionInteraccion .= " para el término '{$termino}'";

                    if ($termino) {
                        // Prioridad 1: Búsqueda de categoría exacta.
                        $categoriaExacta = Categoria::where('nombre', $termino);
                        if ($categoriaExacta) {
                            $catIdsEncontradas[] = $categoriaExacta->id;
                        } else {
                            // Prioridad 2: Búsqueda de producto exacto.
                            $productoExacto = Producto::where('nombre', $termino);
                            if ($productoExacto && $productoExacto->categoriaId) {
                                $catIdsEncontradas[] = $productoExacto->categoriaId;
                            } else {
                                // Prioridad 3: Búsqueda aproximada (LIKE).
                                $idsPorLike = self::buscarCategoriasPorTerminoAproximado($termino);
                                $catIdsEncontradas = array_merge($catIdsEncontradas, $idsPorLike);
                            }
                        }
                    }
                }
                
                // --- Acumulación de Pesos ---
                if (!empty($catIdsEncontradas) && $peso > 0) {
                    $catIdsUnicas = array_unique($catIdsEncontradas);
                    foreach ($catIdsUnicas as $catId) {
                        if (!isset($categoriasInteres[$catId])) {
                            $categoriasInteres[$catId] = ['total' => 0, 'breakdown' => []];
                        }
                        $categoriasInteres[$catId]['total'] += $peso;
                        $categoriasInteres[$catId]['breakdown'][] = "{$descripcionInteraccion}: +{$peso}";
                    }
                }
            }

            // Ajuste adicional por umbral de favoritos
            $favoritos = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
            if ($favoritos >= 3) {
                $productosFavoritos = HistorialInteraccion::whereArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
                foreach ($productosFavoritos as $fav) {
                    $producto = Producto::find($fav->productoId);
                    if ($producto && isset($categoriasInteres[$producto->categoriaId])) {
                        $pesoAntiguo = $categoriasInteres[$producto->categoriaId]['total'];

                        // Multiplicamos el peso para dar un gran impulso a las categorías de favoritos
                        $categoriasInteres[$producto->categoriaId]['total'] *= 1.5;
                        $pesoNuevo = $categoriasInteres[$producto->categoriaId]['total'];
                        $categoriasInteres[$producto->categoriaId]['breakdown'][] = "Multiplicador por favoritos (x1.5): {$pesoAntiguo} -> {$pesoNuevo}";
                    }
                }
            }
        }
        
        // --- Ordenar las categorías según el peso total ---
        uasort($categoriasInteres, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });


        // --- INICIO DEL CÓDIGO DE LOGGING ---

        // Preparar el contenido del log
        $logContent = "-------------------------------------------------\n";
        $logContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "Usuario ID: " . $usuarioId . "\n\n";
        $logContent .= "Fórmula de Pesos por Interacción:\n";
        $logContent .= " - Preferencia Explícita: 10\n";
        $logContent .= " - Compra: 5\n";
        $logContent .= " - Favorito: 3\n";
        $logContent .= " - Clic en Autocompletado: 2\n";
        $logContent .= " - Clic, Búsqueda: 1\n";
        $logContent .= " - Tiempo en Página: 1 punto por cada 20s (máx 5)\n\n";

        $logContent .= "Cálculo Detallado de Intereses por Categoría:\n";
        if (empty($categoriasInteres)) {
            $logContent .= "No se calcularon intereses para este usuario.\n";
        } else {
            foreach ($categoriasInteres as $id => $data) {
                $nombreCategoria = Categoria::find($id)->nombre ?? 'Desconocida';
                $logContent .= "\n[Categoría: {$nombreCategoria} (ID: {$id})] - Peso Total: {$data['total']}\n";
                $logContent .= "  Desglose del cálculo:\n";
                foreach ($data['breakdown'] as $linea) {
                    $logContent .= "    - " . $linea . "\n";
                }
            }
        }

        $categoriasOrdenadas = array_keys($categoriasInteres);
        $logContent .= "\nOrden Final de Categorías Recomendadas (por ID):\n";
        $logContent .= empty($categoriasOrdenadas) ? "Ninguna" : implode(', ', $categoriasOrdenadas);
        $logContent .= "\n-------------------------------------------------\n\n";

        $logFilePath = __DIR__ . '/../recomendaciones.log';
        file_put_contents($logFilePath, $logContent, FILE_APPEND);
        // --- FIN DEL CÓDIGO DE LOGGING ---


        // 4. Devolver solo los IDs de las categorías ordenadas
        return array_keys($categoriasInteres);
    }


    // Busca categorías basándose en un término de búsqueda aproximado.
    private static function buscarCategoriasPorTerminoAproximado(string $termino): array {
        $catIds = [];
        // Asume que tienes un método searchByTerm en tus modelos
        $productos = Producto::searchByTerm($termino); 
        foreach ($productos as $producto) {
            if ($producto->categoriaId) $catIds[] = $producto->categoriaId;
        }
        $categorias = Categoria::searchByTerm($termino);
        foreach ($categorias as $categoria) {
            $catIds[] = $categoria->id;
        }
        return array_unique($catIds);
    }
    

    private static function obtenerPesoInteraccion(string $tipo): int
    {
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