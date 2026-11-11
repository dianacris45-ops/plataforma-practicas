<?php
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;
use PHPUnit\Framework\TestCase;

/** CP-05 a CP-08: Módulo 1 - Registro de estudiantes y empresas. */
class RegistroServicioTest extends TestCase
{
    private PDO $db;
    private RegistroServicio $servicio;

    protected function setUp(): void
    {
        $this->db = baseDePruebas();
        $this->servicio = new RegistroServicio($this->db);
    }

    private function estudiante(array $cambios = []): array
    {
        return array_merge([
            'nombres' => 'Diana Cristina', 'apellidos' => 'Perafán Arias', 'documento' => '1061234567',
            'programa' => 'Ingeniería Informática', 'semestre' => 8,
            'correo' => 'diana@ucn.edu.co', 'clave' => 'Practica2026', 'telefono' => '3001234567',
        ], $cambios);
    }

    /** CP-05: registrar un estudiante con datos correctos crea el usuario y su perfil. */
    public function testRegistrarEstudianteValido(): void
    {
        $id = $this->servicio->registrarEstudiante($this->estudiante());

        $this->assertGreaterThan(0, $id);
        $fila = $this->db->query('SELECT rol, clave_hash FROM usuarios WHERE id = ' . $id)->fetch();
        $this->assertSame('estudiante', $fila['rol']);
        $this->assertNotSame('Practica2026', $fila['clave_hash'], 'La contraseña no debe guardarse en texto plano');
        $this->assertTrue(password_verify('Practica2026', $fila['clave_hash']));
        $this->assertSame(1, (int) $this->db->query('SELECT COUNT(*) FROM estudiantes')->fetchColumn());
    }

    /** CP-06: no se permiten dos cuentas con el mismo correo. */
    public function testCorreoDuplicadoSeRechaza(): void
    {
        $this->servicio->registrarEstudiante($this->estudiante());

        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('Ese correo ya está registrado');
        $this->servicio->registrarEstudiante($this->estudiante(['documento' => '1069999999']));
    }

    /** CP-07: un estudiante de 4.º semestre no puede registrarse para prácticas. */
    public function testSemestreMenorASeisSeRechaza(): void
    {
        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('entre 6.º y 10.º semestre');
        $this->servicio->registrarEstudiante($this->estudiante(['semestre' => 4]));
    }

    /** CP-08: registrar empresa e iniciar sesión (correcto e incorrecto). */
    public function testRegistrarEmpresaEIniciarSesion(): void
    {
        $this->servicio->registrarEmpresa([
            'razon_social' => 'Soluciones TIC del Norte S.A.S.', 'nit' => '900123456-7', 'sector' => 'Tecnología',
            'ciudad' => 'Medellín', 'contacto' => 'Laura Gómez', 'correo' => 'talento@ticnorte.co', 'clave' => 'Empresa2026',
        ]);

        $sesion = $this->servicio->iniciarSesion('TALENTO@ticnorte.co', 'Empresa2026');
        $this->assertNotNull($sesion);
        $this->assertSame('empresa', $sesion['rol']);
        $this->assertSame('Soluciones TIC del Norte S.A.S.', $sesion['nombre']);

        $this->assertNull($this->servicio->iniciarSesion('talento@ticnorte.co', 'claveEquivocada1'));
    }
}
