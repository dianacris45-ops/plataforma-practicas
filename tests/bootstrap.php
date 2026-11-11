<?php
/**
 * Arranque de las pruebas: carga las clases y ofrece una base de datos
 * SQLite en memoria (se crea limpia en cada prueba, así no se daña la de XAMPP).
 */
date_default_timezone_set('America/Bogota');

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function (string $clase): void {
        if (str_starts_with($clase, 'App\\')) {
            require __DIR__ . '/../src/' . str_replace('\\', '/', substr($clase, 4)) . '.php';
        }
    });
}

function baseDePruebas(): PDO
{
    $db = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec(file_get_contents(__DIR__ . '/schema_sqlite.sql'));
    return $db;
}
