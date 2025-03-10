<?php

namespace Model;

// CLASE PADRE
class ActiveRecord {

    // BASE DE DATOS
    protected static $conexion; // Static porque no require una nueva instancia, siempre son las mismas credenciales
    protected static $columnasDB = [];
    protected static $tabla = '';

    // ALERTAS Y MENSAJES
    protected static $alertas = []; 

    // Definir la conexion a la base de datos
    public static function setDB($database) {
        self::$conexion = $database; // Self hace referencia a los atributos estaticos de esta misma clase
    }

    // Setear un tipo de Alerta
    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Obtener las alertas
    public static function getAlertas() {
        return static::$alertas;
    }

    // Validación que se hereda en modelos
    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria (Active Record)
    public static function consultarSQL($query) {
        // Consultar la BD
        $resultado = self::$conexion->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // Liberar la memoria
        $resultado->free();

        // Retornar los resultados
        return $array;
    }

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro) {
        // Creando objeto de la clase actual
        $objeto = new static;

        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) {  // Revisar de un objeto que una propiedad exista (ya sea la llave o el valor)
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];

        // Recorriendo el arreglo columnasDB 
        foreach (static::$columnasDB as $columna) {
            if($columna == 'id') continue; // Ignoramos el campo de ID ya que se agrega automatico
            $atributos[$columna] = $this->$columna;
        }
        
        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];

        // Recorriendo el arreglo como un arreglo asociativo (tanto llave como valor)
        foreach($atributos as $key => $value) {

            // Verificamos si el valor es null o vacío y lo reemplazamos por null
            if ($value === NULL || $value === '') {
                $sanitizado[$key] = NULL;  // Reemplazamos con NULL
            } else {
                $sanitizado[$key] = self::$conexion->escape_string($value);
            }
        }

        return $sanitizado;
    }

    // Sincroniza el objeto en memoria con los cambios realizados por el usuario
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {  // Revisar de un objeto que una propiedad exista
                $this->$key = $value;
            }
        }
    }

    // Registros - CRUD
    public function guardar() {
        $resultado = '';
        if(!is_null($this->id)){ // is_null: Determina si una variable es null
            // Actualizar registro
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    // Obtener todos los registros
    public static function all($orden = 'ASC') {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id $orden";
        $resultado = self::consultarSQL($query);
        return $resultado; 
    }

    // Busca un registro por su ID
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = $id";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado); // Retorna el primer objeto del arreglo de objetos
    }

    // Obtener determinado numero de registros
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT $limite"; 
        $resultado = self::consultarSQL($query);
        return $resultado; // Retorna un arreglo de objetos 
    }

    // Paginar los registros
    public static function paginar($por_pagina, $offset) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT $por_pagina OFFSET $offset" ;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busqueda Where con Columna 
    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE $columna = '$valor'";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    // Busqueda Where con Múltiples opciones
    public static function whereArray($array = []) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            if($key == array_key_last($array)) {
                $query .= " $key = '$value'";
            } else {
                $query .= " $key = '$value' AND ";
            }
        }
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Retornar los registros por un orden
    public static function ordenar($columna, $orden) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY $columna $orden"; 
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Retornar por orden y con un limite
    public static function ordenarLimite($columna, $orden, $limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY $columna $orden LIMIT $limite"; 
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Traer un total de registros
    public static function total($columna = '', $valor = '') {
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        if($columna) {
            $query .= " WHERE $columna = $valor";
        }
        $resultado = self::$conexion->query($query);
        $total = $resultado->fetch_array();

        return array_shift($total);
    }

    // Total de Registros con un Array Where
    public static function totalArray($array = []) {
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            if($key == array_key_last($array)) {
                $query .= " $key = '$value' ";
            } else {
                $query .= " $key = '$value' AND ";
            }
        }
        $resultado = self::$conexion->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    // Crear un nuevo registro
    public function crear() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $columnas = join(', ', array_keys($atributos)); // Crear un string a partir de las llaves del arreglo
        $filas = [];

        // Reemplazar los valores NULL por la palabra 'NULL' en la consulta
        foreach (array_values($atributos) as $value) {
            if ($value === null) {
                $filas[] = 'NULL'; // Si el valor es NULL, se agrega 'NULL' a la consulta
            } else {
                $filas[] = "'" . self::$conexion->escape_string($value) . "'"; // Si no es NULL, escapamos y agregamos comillas
            }
        }

        $filas = join(", ", $filas); // Convertir el array a un string de valores

        // Reemplazar las comillas adicionales antes de insertar en la consulta
        $query = "INSERT INTO " . static::$tabla . " ($columnas) VALUES ($filas)";

        // Ejecutar la consulta
        $resultado = self::$conexion->query($query); 
        return [
            'resultado' =>  $resultado,
            'id' => self::$conexion->insert_id
        ];
    }

    // Actualizar un registro
    public function actualizar(){
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "$key = '$value'";
        }

        // Consulta SQL
        $filas =  join(', ', $valores); // Crear un string a partir de las llaves y valores del arreglo
        $id_sanitizado = self::$conexion->escape_string($this->id); // Escapar el id para evitar inyección SQL

        $query = "UPDATE " . static::$tabla . " SET $filas WHERE id = $id_sanitizado LIMIT 1";

        // Actualizar BD
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    // Eliminar un registro por su ID
    public function eliminar() {
        // Escapar el id para evitar inyección SQL
        $id_sanitizado = self::$conexion->escape_string($this->id);

        $query = "DELETE FROM " . static::$tabla . " WHERE id = $id_sanitizado LIMIT 1";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    // Subida de archivos
    public function setImagen($imagen) {
        // Verificar si la propiedad ya tiene un ID (indica que es una actualización)
        if(!is_null($this->id)) {
            $this->borrarImagen();
        }

        // Asignar el nuevo nombre de la imagen
        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    // Elimina el archivo
    public function borrarImagen() {
        // Verificar si el archivo existe y es un archivo real 
        $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
        if($existeArchivo) {
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }
}