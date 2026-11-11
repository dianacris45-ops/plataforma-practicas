<?php
namespace App\Servicios;

use App\ErrorValidacion;
use App\Validador;
use PDO;

/**
 * MÓDULO 1: Registro de estudiantes y empresas + inicio de sesión.
 * Requisitos que cubre: RF-01 (registrar estudiante), RF-02 (registrar empresa), RF-03 (iniciar sesión).
 */
class RegistroServicio
{
    public const PROGRAMAS = [
        'Ingeniería Informática',
        'Ingeniería Industrial',
        'Administración de Empresas',
        'Contaduría Pública',
        'Psicología',
    ];

    public function __construct(private PDO $db)
    {
    }

    /** Registra un estudiante y devuelve el id de usuario creado. */
    public function registrarEstudiante(array $d): int
    {
        $correo    = Validador::correo($d['correo'] ?? null);
        $clave     = Validador::clave($d['clave'] ?? null);
        $nombres   = Validador::requerido($d['nombres'] ?? null, 'nombres');
        $apellidos = Validador::requerido($d['apellidos'] ?? null, 'apellidos');
        $documento = Validador::documento($d['documento'] ?? null);
        $programa  = Validador::requerido($d['programa'] ?? null, 'programa');
        $semestre  = (int) ($d['semestre'] ?? 0);

        if (!in_array($programa, self::PROGRAMAS, true)) {
            throw new ErrorValidacion('Selecciona un programa académico de la lista.');
        }
        if ($semestre < 6 || $semestre > 10) {
            throw new ErrorValidacion('Para hacer prácticas debes estar entre 6.º y 10.º semestre.');
        }
        $this->verificarCorreoLibre($correo);
        $this->verificarUnico('estudiantes', 'documento', $documento, 'Ya existe un estudiante con ese documento.');

        $this->db->beginTransaction();
        try {
            $usuarioId = $this->crearUsuario($correo, $clave, 'estudiante');
            $st = $this->db->prepare(
                'INSERT INTO estudiantes (usuario_id, nombres, apellidos, documento, programa, semestre, telefono)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([$usuarioId, $nombres, $apellidos, $documento, $programa, $semestre, trim($d['telefono'] ?? '')]);
            $this->db->commit();
            return $usuarioId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Registra una empresa y devuelve el id de usuario creado. */
    public function registrarEmpresa(array $d): int
    {
        $correo      = Validador::correo($d['correo'] ?? null);
        $clave       = Validador::clave($d['clave'] ?? null);
        $razonSocial = Validador::requerido($d['razon_social'] ?? null, 'razón social');
        $nit         = Validador::nit($d['nit'] ?? null);
        $sector      = Validador::requerido($d['sector'] ?? null, 'sector');
        $ciudad      = Validador::requerido($d['ciudad'] ?? null, 'ciudad');
        $contacto    = Validador::requerido($d['contacto'] ?? null, 'persona de contacto');

        $this->verificarCorreoLibre($correo);
        $this->verificarUnico('empresas', 'nit', $nit, 'Ya existe una empresa registrada con ese NIT.');

        $this->db->beginTransaction();
        try {
            $usuarioId = $this->crearUsuario($correo, $clave, 'empresa');
            $st = $this->db->prepare(
                'INSERT INTO empresas (usuario_id, razon_social, nit, sector, ciudad, contacto, telefono)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([$usuarioId, $razonSocial, $nit, $sector, $ciudad, $contacto, trim($d['telefono'] ?? '')]);
            $this->db->commit();
            return $usuarioId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Revisa correo y contraseña. Si son correctos devuelve los datos del usuario
     * (id, correo, rol, perfil_id, nombre). Si no, devuelve null.
     */
    public function iniciarSesion(string $correo, string $clave): ?array
    {
        $st = $this->db->prepare('SELECT * FROM usuarios WHERE correo = ?');
        $st->execute([strtolower(trim($correo))]);
        $u = $st->fetch();
        if (!$u || !password_verify($clave, $u['clave_hash'])) {
            return null;
        }
        if ($u['rol'] === 'estudiante') {
            $p = $this->db->prepare('SELECT id, nombres, apellidos FROM estudiantes WHERE usuario_id = ?');
        } else {
            $p = $this->db->prepare('SELECT id, razon_social FROM empresas WHERE usuario_id = ?');
        }
        $p->execute([$u['id']]);
        $fila = $p->fetch();
        $perfil = ['id' => null, 'nombre' => $u['correo']];
        if ($fila) {
            $perfil['id'] = $fila['id'];
            $perfil['nombre'] = $u['rol'] === 'estudiante'
                ? $fila['nombres'] . ' ' . $fila['apellidos']
                : $fila['razon_social'];
        }

        return [
            'id'        => (int) $u['id'],
            'correo'    => $u['correo'],
            'rol'       => $u['rol'],
            'perfil_id' => $perfil['id'] !== null ? (int) $perfil['id'] : null,
            'nombre'    => $perfil['nombre'],
        ];
    }

    private function crearUsuario(string $correo, string $clave, string $rol): int
    {
        $st = $this->db->prepare('INSERT INTO usuarios (correo, clave_hash, rol, creado_en) VALUES (?, ?, ?, ?)');
        $st->execute([$correo, password_hash($clave, PASSWORD_DEFAULT), $rol, date('Y-m-d H:i:s')]);
        return (int) $this->db->lastInsertId();
    }

    private function verificarCorreoLibre(string $correo): void
    {
        $this->verificarUnico('usuarios', 'correo', $correo, 'Ese correo ya está registrado. Intenta iniciar sesión.');
    }

    private function verificarUnico(string $tabla, string $columna, string $valor, string $mensaje): void
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = ?");
        $st->execute([$valor]);
        if ((int) $st->fetchColumn() > 0) {
            throw new ErrorValidacion($mensaje);
        }
    }
}
