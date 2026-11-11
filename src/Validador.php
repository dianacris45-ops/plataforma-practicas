<?php
namespace App;

/**
 * Reglas de validación reutilizables (se prueban con PHPUnit).
 */
class Validador
{
    public static function requerido(?string $valor, string $campo): string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            throw new ErrorValidacion("El campo {$campo} es obligatorio.");
        }
        return $valor;
    }

    public static function correo(?string $correo): string
    {
        $correo = strtolower(self::requerido($correo, 'correo'));
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new ErrorValidacion('El correo no tiene un formato válido.');
        }
        return $correo;
    }

    public static function clave(?string $clave): string
    {
        $clave = (string) $clave;
        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
            throw new ErrorValidacion('La contraseña debe tener mínimo 8 caracteres, con letras y números.');
        }
        return $clave;
    }

    public static function documento(?string $documento): string
    {
        $documento = self::requerido($documento, 'documento');
        if (!preg_match('/^\d{6,12}$/', $documento)) {
            throw new ErrorValidacion('El documento debe tener solo números (entre 6 y 12 dígitos).');
        }
        return $documento;
    }

    /** NIT colombiano: 9 dígitos, opcionalmente con guion y dígito de verificación (900123456-7). */
    public static function nit(?string $nit): string
    {
        $nit = self::requerido($nit, 'NIT');
        if (!preg_match('/^\d{9}(-\d)?$/', $nit)) {
            throw new ErrorValidacion('El NIT debe tener 9 dígitos y, si quieres, el dígito de verificación (ej: 900123456-7).');
        }
        return $nit;
    }

    public static function fechaFutura(?string $fecha, string $campo = 'fecha de cierre'): string
    {
        $fecha = self::requerido($fecha, $campo);
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            throw new ErrorValidacion("La {$campo} no es una fecha válida.");
        }
        if ($fecha < date('Y-m-d')) {
            throw new ErrorValidacion("La {$campo} no puede estar en el pasado.");
        }
        return $fecha;
    }
}
