<?php
    function conectarDB() : mysqli { // Esta función retorna una conexión de mysqli
        $host = 'localhost';
        $user = 'root';
        $pass = 'root';
        $db = 'mixtli_arte';

        $conexion = new mysqli($host, $user, $pass, $db);

        if(!$conexion) {
           die('Error de conexión: ' . mysqli_connect_error());
        }

        return $conexion;
    }

?>