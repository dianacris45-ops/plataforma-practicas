<?php
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;
use App\Servicios\VacanteServicio;
use PHPUnit\Framework\TestCase;

/** CP-09 a CP-13: Módulo 2 - Publicación y postulación a vacantes. */
class VacanteServicioTest extends TestCase
{
    private PDO $db;
    private VacanteServicio $vacantes;
    private int $empresaId;
    private int $estudianteId;

    protected function setUp(): void
    {
        $this->db = baseDePruebas();
        $registro = new RegistroServicio($this->db);
        $registro->registrarEmpresa([
            'razon_social' => 'Soluciones TIC del Norte S.A.S.', 'nit' => '900123456-7', 'sector' => 'Tecnología',
            'ciudad' => 'Medellín', 'contacto' => 'Laura Gómez', 'correo' => 'talento@ticnorte.co', 'clave' => 'Empresa2026',
        ]);
        $registro->registrarEstudiante([
            'nombres' => 'Diana Cristina', 'apellidos' => 'Perafán Arias', 'documento' => '1061234567',
            'programa' => 'Ingeniería Informática', 'semestre' => 8, 'correo' => 'diana@ucn.edu.co', 'clave' => 'Practica2026',
        ]);
        $this->empresaId = (int) $this->db->query('SELECT id FROM empresas')->fetchColumn();
        $this->estudianteId = (int) $this->db->query('SELECT id FROM estudiantes')->fetchColumn();
        $this->vacantes = new VacanteServicio($this->db);
    }

    private function datosVacante(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Practicante de desarrollo web', 'descripcion' => 'Apoyo en el desarrollo de módulos PHP.',
            'requisitos' => 'Conocimientos en PHP y MySQL', 'programa' => 'Ingeniería Informática',
            'modalidad' => 'hibrida', 'cupos' => 2, 'fecha_cierre' => date('Y-m-d', strtotime('+30 days')),
        ], $cambios);
    }

    /** CP-09: la empresa publica una vacante y aparece en la lista de abiertas. */
    public function testPublicarVacanteValida(): void
    {
        $id = $this->vacantes->publicar($this->empresaId, $this->datosVacante());

        $abiertas = $this->vacantes->listarAbiertas();
        $this->assertCount(1, $abiertas);
        $this->assertSame($id, (int) $abiertas[0]['id']);
        $this->assertSame('Soluciones TIC del Norte S.A.S.', $abiertas[0]['razon_social']);
    }

    /** CP-10: no se puede publicar una vacante con fecha de cierre en el pasado. */
    public function testVacanteConFechaPasadaSeRechaza(): void
    {
        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('no puede estar en el pasado');
        $this->vacantes->publicar($this->empresaId, $this->datosVacante(['fecha_cierre' => '2020-01-01']));
    }

    /** CP-11: el estudiante se postula y la empresa lo ve en su lista. */
    public function testEstudianteSePostulaYEmpresaLoVe(): void
    {
        $vacanteId = $this->vacantes->publicar($this->empresaId, $this->datosVacante());

        $this->vacantes->postular($this->estudianteId, $vacanteId);

        $mias = $this->vacantes->postulacionesDeEstudiante($this->estudianteId);
        $this->assertCount(1, $mias);
        $this->assertSame('enviada', $mias[0]['estado']);
        $postulantes = $this->vacantes->postulantesDeVacante($vacanteId, $this->empresaId);
        $this->assertSame('diana@ucn.edu.co', $postulantes[0]['correo']);
    }

    /** CP-12: el mismo estudiante no puede postularse dos veces a la misma vacante. */
    public function testNoSePermitePostulacionDoble(): void
    {
        $vacanteId = $this->vacantes->publicar($this->empresaId, $this->datosVacante());
        $this->vacantes->postular($this->estudianteId, $vacanteId);

        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('Ya te postulaste');
        $this->vacantes->postular($this->estudianteId, $vacanteId);
    }

    /** CP-13: una vacante cerrada no recibe postulaciones y desaparece de la lista. */
    public function testVacanteCerradaNoRecibePostulaciones(): void
    {
        $vacanteId = $this->vacantes->publicar($this->empresaId, $this->datosVacante());
        $this->vacantes->cerrar($vacanteId, $this->empresaId);

        $this->assertCount(0, $this->vacantes->listarAbiertas());
        $this->expectException(ErrorValidacion::class);
        $this->expectExceptionMessage('ya está cerrada');
        $this->vacantes->postular($this->estudianteId, $vacanteId);
    }
}
