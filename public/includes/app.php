<?php
/**
 * Archivo que cargan todas las páginas: inicia la sesión, carga las clases
 * y trae funciones de ayuda pequeñas.
 */
declare(strict_types=1);
date_default_timezone_set('America/Bogota');
session_start();

// Carga automática de clases (sin necesidad de Composer para la parte web).
spl_autoload_register(function (string $clase): void {
    if (str_starts_with($clase, 'App\\')) {
        $ruta = __DIR__ . '/../../src/' . str_replace('\\', '/', substr($clase, 4)) . '.php';
        if (is_file($ruta)) {
            require $ruta;
        }
    }
});

use App\Database;

function db(): PDO
{
    return Database::conectar();
}

/** Escapa texto antes de mostrarlo en HTML (evita ataques XSS). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirigir(string $pagina): never
{
    header('Location: ' . $pagina);
    exit;
}

function mensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

function usuario(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Solo deja pasar a usuarios con el rol indicado. */
function exigirRol(string $rol): array
{
    $u = usuario();
    if (!$u || $u['rol'] !== $rol) {
        mensaje('warning', 'Debes iniciar sesión con una cuenta de ' . $rol . ' para entrar a esa página.');
        redirigir('login.php');
    }
    return $u;
}

/** Token anti-CSRF para los formularios. */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . tokenCsrf() . '">';
}

function verificarCsrf(): void
{
    if (!hash_equals(tokenCsrf(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Solicitud no válida. Vuelve a cargar la página.');
    }
}

function valor(string $campo): string
{
    return e($_POST[$campo] ?? '');
}

/** Muestra con tildes los valores que se guardan sin ellas en la base de datos. */
function etiqueta(string $valor): string
{
    $mapa = ['hibrida' => 'Híbrida', 'remota' => 'Remota', 'presencial' => 'Presencial',
             'en_revision' => 'En revisión', 'enviada' => 'Enviada', 'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada',
             'abierta' => 'Abierta', 'cerrada' => 'Cerrada'];
    return $mapa[$valor] ?? $valor;
}
