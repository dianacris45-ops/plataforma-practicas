<?php
/**
 * Configuración de la conexión a la base de datos.
 * Valores por defecto pensados para XAMPP (usuario "root" sin contraseña).
 * Si cambias la contraseña de MySQL en XAMPP, cámbiala aquí.
 */
return [
    'dsn'      => getenv('DB_DSN') ?: 'mysql:host=localhost;port=3306;dbname=plataforma_practicas;charset=utf8mb4',
    'usuario'  => getenv('DB_USER') ?: 'root',
    'clave'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
];
