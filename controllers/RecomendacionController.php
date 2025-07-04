<?php

namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\PreferenciaUsuario;
use Model\HistorialInteraccion;

class RecomendacionController {

    public static function obtenerCategoriasRecomendadas(int $usuarioId): array {
        // 1. Verificar si el usuario cumple el umbral para recibir recomendaciones dinámicas
        $clicks = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'clic']);
        $busquedas = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'busqueda']);
        
        $categoriasInteres = [];

        // 2. Obtener preferencias explícitas del usuario y darles un peso inicial alto
        $preferencias = PreferenciaUsuario::where('usuarioId', $usuarioId);
        $categoriasIdsPref = $preferencias ? json_decode($preferencias->categorias, true) : [];

        if (!empty($categoriasIdsPref)) {
            foreach ($categoriasIdsPref as $catId) {
                // Asignamos un peso base alto por ser una preferencia explícita
                $categoriasInteres[intval($catId)] = 10; 
            }
        }

        // Solo procesar interacciones si el usuario ha alcanzado el umbral
        if ($clicks >= 10 || $busquedas >= 5) {
            $interacciones = HistorialInteraccion::whereField('usuarioId', $usuarioId);

            foreach ($interacciones as $interaccion) {
                $catId = null;
                $peso = self::obtenerPesoInteraccion($interaccion->tipo);

                if ($interaccion->productoId) {
                    $producto = Producto::find($interaccion->productoId);
                    if ($producto && $producto->categoriaId) {
                        $catId = $producto->categoriaId;
                    }
                } elseif ($interaccion->tipo === 'busqueda' && $interaccion->metadata) {
                    $metadata = json_decode($interaccion->metadata, true);
                    $terminoBusqueda = $metadata['termino'] ?? '';
                    
                    // Buscar categorías que coincidan con el término de búsqueda
                    $categoriasEncontradas = Categoria::whereArray(['nombre LIKE' => "%{$terminoBusqueda}%"]);
                    if (!empty($categoriasEncontradas)) {
                        // Por simplicidad, tomamos la primera categoría encontrada
                        $catId = $categoriasEncontradas[0]->id;
                    }
                }
                
                if ($catId) {
                    if (!isset($categoriasInteres[$catId])) {
                        $categoriasInteres[$catId] = 0;
                    }
                    $categoriasInteres[$catId] += $peso;
                }
            }

            // Ajuste adicional por umbral de favoritos
            $favoritos = HistorialInteraccion::totalArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
            if ($favoritos >= 3) {
                $productosFavoritos = HistorialInteraccion::whereArray(['usuarioId' => $usuarioId, 'tipo' => 'favorito']);
                foreach ($productosFavoritos as $fav) {
                    $producto = Producto::find($fav->productoId);
                    if ($producto && isset($categoriasInteres[$producto->categoriaId])) {
                        // Multiplicamos el peso para dar un gran impulso a las categorías de favoritos
                        $categoriasInteres[$producto->categoriaId] *= 1.5; 
                    }
                }
            }
        }
        
        // 3. Ordenar las categorías por el peso acumulado, de mayor a menor
        arsort($categoriasInteres);

        // 4. Devolver solo los IDs de las categorías ordenadas
        return array_keys($categoriasInteres);
    }


    private static function obtenerPesoInteraccion(string $tipo): int {
        switch ($tipo) {
            case 'compra': return 5;
            case 'favorito': return 3;
            case 'autocompletado_producto': return 2; // Clic en autocompletado de producto
            case 'autocompletado_categoria': return 2; // Clic en autocompletado de categoría
            case 'clic': return 1;
            case 'tiempo_en_pagina': return 1; // Se puede ajustar si tienes el dato
            case 'busqueda': return 1;
            default: return 0;
        }
    }
}