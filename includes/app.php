<?php 

date_default_timezone_set('America/Mexico_City');

// INICIA LA SESIÓN EN TODA LA APLICACIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Dotenv\Dotenv;
use Model\ActiveRecord;
require __DIR__ . '/../vendor/autoload.php';

// Añadir Dotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
ActiveRecord::setDB($db);