<?php
namespace App\Servicios;

use App\ErrorValidacion;
use App\Validador;
use PDO;

/**
 * MÓDULO 2: Publicación de vacantes (empresa) y postulación (estudiante).
 * Requisitos que cubre: RF-04 (publicar vacante), RF-05 (consultar vacantes),
 * RF-06 (postularse), RF-07 (ver postulantes y cambiar estado), RF-08 (cerrar vacante).
 */
class VacanteServicio
{
    public const MODALIDADES = ['presencial', 'remota', 'hibrida'];
    public const ESTADOS_POSTULACION = ['enviada', 'en_revision', 'aceptada', 'rechazada'];

    public function __construct(private PDO $db)
    {
    }

    public function publicar(int $empresaId, array $d): int
    {
        $titulo      = Validador::requerido($d['titulo'] ?? null, 'título');
        $descripcion = Validador::requerido($d['descripcion'] ?? null, 'descripción');
        $programa    = Validador::requerido($d['programa'] ?? null, 'programa');
        $modalidad   = Validador::requerido($d['modalidad'] ?? null, 'modalidad');
        $fechaCierre = Validador::fechaFutura($d['fecha_cierre'] ?? null);
        $cupos       = (int) ($d['cupos'] ?? 0);

        if (!in_array($modalidad, self::MODALIDADES, true)) {
            throw new ErrorValidacion('La modalidad debe ser presencial, remota o híbrida.');
        }
        if ($cupos < 1 || $cupos > 50) {
            throw new ErrorValidacion('Los cupos deben estar entre 1 y 50.');
        }
        $st = $this->db->prepare('SELECT COUNT(*) FROM empresas WHERE id = ?');
        $st->execute([$empresaId]);
        if ((int) $st->fetchColumn() === 0) {
            throw new ErrorValidacion('Solo una empresa registrada puede publicar vacantes.');
        }

        $st = $this->db->prepare(
            'INSERT INTO vacantes (empresa_id, titulo, descripcion, requisitos, programa, modalidad, cupos, fecha_cierre, estado, creado_en)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$empresaId, $titulo, $descripcion, trim($d['requisitos'] ?? ''), $programa, $modalidad,
            $cupos, $fechaCierre, 'abierta', date('Y-m-d H:i:s')]);
        return (int) $this->db->lastInsertId();
    }

    /** Vacantes abiertas y con fecha vigente. Se puede filtrar por programa. */
    public function listarAbiertas(?string $programa = null): array
    {
        $sql = 'SELECT v.*, e.razon_social, e.ciudad,
                       (SELECT COUNT(*) FROM postulaciones p WHERE p.vacante_id = v.id) AS total_postulados
                FROM vacantes v JOIN empresas e ON e.id = v.empresa_id
                WHERE v.estado = ? AND v.fecha_cierre >= ?';
        $params = ['abierta', date('Y-m-d')];
        if ($programa) {
            $sql .= ' AND v.programa = ?';
            $params[] = $programa;
        }
        $sql .= ' ORDER BY v.creado_en DESC, v.id DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function listarDeEmpresa(int $empresaId): array
    {
        $st = $this->db->prepare(
            'SELECT v.*, (SELECT COUNT(*) FROM postulaciones p WHERE p.vacante_id = v.id) AS total_postulados
             FROM vacantes v WHERE v.empresa_id = ? ORDER BY v.id DESC'
        );
        $st->execute([$empresaId]);
        return $st->fetchAll();
    }

    public function buscar(int $vacanteId): ?array
    {
        $st = $this->db->prepare('SELECT v.*, e.razon_social FROM vacantes v JOIN empresas e ON e.id = v.empresa_id WHERE v.id = ?');
        $st->execute([$vacanteId]);
        return $st->fetch() ?: null;
    }

    public function postular(int $estudianteId, int $vacanteId): int
    {
        $vacante = $this->buscar($vacanteId);
        if (!$vacante) {
            throw new ErrorValidacion('La vacante no existe.');
        }
        if ($vacante['estado'] !== 'abierta' || $vacante['fecha_cierre'] < date('Y-m-d')) {
            throw new ErrorValidacion('Esta vacante ya está cerrada. No se reciben más postulaciones.');
        }
        $st = $this->db->prepare('SELECT COUNT(*) FROM postulaciones WHERE vacante_id = ? AND estudiante_id = ?');
        $st->execute([$vacanteId, $estudianteId]);
        if ((int) $st->fetchColumn() > 0) {
            throw new ErrorValidacion('Ya te postulaste a esta vacante.');
        }
        $st = $this->db->prepare('INSERT INTO postulaciones (vacante_id, estudiante_id, estado, fecha) VALUES (?, ?, ?, ?)');
        $st->execute([$vacanteId, $estudianteId, 'enviada', date('Y-m-d H:i:s')]);
        return (int) $this->db->lastInsertId();
    }

    public function postulacionesDeEstudiante(int $estudianteId): array
    {
        $st = $this->db->prepare(
            'SELECT p.*, v.titulo, v.modalidad, e.razon_social
             FROM postulaciones p JOIN vacantes v ON v.id = p.vacante_id JOIN empresas e ON e.id = v.empresa_id
             WHERE p.estudiante_id = ? ORDER BY p.fecha DESC, p.id DESC'
        );
        $st->execute([$estudianteId]);
        return $st->fetchAll();
    }

    public function postulantesDeVacante(int $vacanteId, int $empresaId): array
    {
        $this->verificarDueno($vacanteId, $empresaId);
        $st = $this->db->prepare(
            'SELECT p.id, p.estado, p.fecha, s.nombres, s.apellidos, s.programa, s.semestre, s.telefono, u.correo
             FROM postulaciones p JOIN estudiantes s ON s.id = p.estudiante_id JOIN usuarios u ON u.id = s.usuario_id
             WHERE p.vacante_id = ? ORDER BY p.fecha ASC, p.id ASC'
        );
        $st->execute([$vacanteId]);
        return $st->fetchAll();
    }

    public function cambiarEstadoPostulacion(int $postulacionId, int $empresaId, string $estado): void
    {
        if (!in_array($estado, self::ESTADOS_POSTULACION, true)) {
            throw new ErrorValidacion('Estado de postulación no válido.');
        }
        $st = $this->db->prepare('SELECT vacante_id FROM postulaciones WHERE id = ?');
        $st->execute([$postulacionId]);
        $vacanteId = $st->fetchColumn();
        if ($vacanteId === false) {
            throw new ErrorValidacion('La postulación no existe.');
        }
        $this->verificarDueno((int) $vacanteId, $empresaId);
        $this->db->prepare('UPDATE postulaciones SET estado = ? WHERE id = ?')->execute([$estado, $postulacionId]);
    }

    public function cerrar(int $vacanteId, int $empresaId): void
    {
        $this->verificarDueno($vacanteId, $empresaId);
        $this->db->prepare('UPDATE vacantes SET estado = ? WHERE id = ?')->execute(['cerrada', $vacanteId]);
    }

    private function verificarDueno(int $vacanteId, int $empresaId): void
    {
        $vacante = $this->buscar($vacanteId);
        if (!$vacante || (int) $vacante['empresa_id'] !== $empresaId) {
            throw new ErrorValidacion('No tienes permiso sobre esta vacante.');
        }
    }
}
