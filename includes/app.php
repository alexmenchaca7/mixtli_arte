<?php

require 'funciones.php';
require 'config/database.php';
require __DIR__ . '/../vendor/autoload.php';

// Conexion a la base de datos
$conexion = conectarDB();

// Importar la clase padre
use Model\ActiveRecord;

ActiveRecord::setDB($conexion);