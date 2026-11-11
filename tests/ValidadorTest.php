<?php
use App\ErrorValidacion;
use App\Validador;
use PHPUnit\Framework\TestCase;

/** CP-01 a CP-03: reglas de validación de datos. */
class ValidadorTest extends TestCase
{
    /** CP-01: un correo bien escrito se acepta y se guarda en minúsculas. */
    public function testCorreoValidoSeAceptaEnMinusculas(): void
    {
        $this->assertSame('diana.perafan@ucn.edu.co', Validador::correo('  Diana.Perafan@UCN.edu.co '));
    }

    /** CP-02: un correo sin @ se rechaza. */
    public function testCorreoInvalidoSeRechaza(): void
    {
        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('El correo no tiene un formato válido.');
        Validador::correo('diana.ucn.edu.co');
    }

    /** CP-03: contraseñas débiles se rechazan y una segura se acepta. */
    public function testClaveDebeTenerOchoCaracteresLetrasYNumeros(): void
    {
        $this->assertSame('Practica2026', Validador::clave('Practica2026'));
        foreach (['abc12', 'solamenteletras', '1234567890'] as $debil) {
            try {
                Validador::clave($debil);
                $this->fail("La contraseña '$debil' debió ser rechazada");
            } catch (ErrorValidacion $e) {
                $this->assertStringContainsString('mínimo 8 caracteres', $e->getMessage());
            }
        }
    }

    /** CP-04: el NIT acepta 9 dígitos con o sin dígito de verificación. */
    public function testNitConYSinDigitoDeVerificacion(): void
    {
        $this->assertSame('900123456-7', Validador::nit('900123456-7'));
        $this->assertSame('900123456', Validador::nit('900123456'));
        $this->expectException(ErrorValidacion::class);
        Validador::nit('90012-ABC');
    }
}
