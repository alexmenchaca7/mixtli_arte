<?php

namespace Controllers;

use Model\Producto;
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
                if ($interaccion->productoId) {
                    $producto = Producto::find($interaccion->productoId);
                    if ($producto && $producto->categoriaId) {
                        $catId = $producto->categoriaId;
                        if (!isset($categoriasInteres[$catId])) {
                            $categoriasInteres[$catId] = 0;
                        }
                        // Sumar peso según el tipo de interacción
                        $peso = self::obtenerPesoInteraccion($interaccion->tipo);
                        $categoriasInteres[$catId] += $peso;
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
            case 'clic': return 1;
            case 'tiempo_en_pagina': return 1; // Se puede ajustar si tienes el dato
            // Las búsquedas se manejan por separado, pero se podrían integrar aquí si se asocian a categorías
            default: return 0;
        }
    }
}