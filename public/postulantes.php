<?php
require __DIR__ . '/includes/app.php';
use App\ErrorValidacion;
use App\Servicios\VacanteServicio;

$u = exigirRol('empresa');
$servicio = new VacanteServicio(db());
$vacanteId = (int) ($_GET['vacante'] ?? $_POST['vacante_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    try {
        if (($_POST['accion'] ?? '') === 'cerrar') {
            $servicio->cerrar($vacanteId, $u['perfil_id']);
            mensaje('info', 'La vacante quedó cerrada. Ya no recibe postulaciones.');
        } else {
            $servicio->cambiarEstadoPostulacion((int) $_POST['postulacion_id'], $u['perfil_id'], (string) $_POST['estado']);
            mensaje('success', 'Estado de la postulación actualizado.');
        }
    } catch (ErrorValidacion $ex) {
        mensaje('danger', $ex->getMessage());
    }
    redirigir('postulantes.php?vacante=' . $vacanteId);
}

try {
    $postulantes = $servicio->postulantesDeVacante($vacanteId, $u['perfil_id']);
} catch (ErrorValidacion $ex) {
    mensaje('danger', $ex->getMessage());
    redirigir('panel.php');
}
$vacante = $servicio->buscar($vacanteId);
$titulo = 'Postulantes';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Postulantes: <?= e($vacante['titulo']) ?></h3>
        <span class="badge <?= $vacante['estado'] === 'abierta' ? 'bg-success' : 'bg-secondary' ?>"><?= e(etiqueta($vacante['estado'])) ?></span>
    </div>
    <?php if ($vacante['estado'] === 'abierta'): ?>
    <form method="post"><?= campoCsrf() ?><input type="hidden" name="vacante_id" value="<?= $vacanteId ?>">
        <button class="btn btn-outline-danger" name="accion" value="cerrar">Cerrar vacante</button></form>
    <?php endif; ?>
</div>
<div class="card tarjeta p-0">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Estudiante</th><th>Programa</th><th>Sem.</th><th>Correo</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if (!$postulantes): ?><tr><td colspan="5" class="text-muted p-4">Aún no hay postulantes.</td></tr><?php endif; ?>
        <?php foreach ($postulantes as $p): ?>
            <tr>
                <td class="fw-semibold"><?= e($p['nombres'] . ' ' . $p['apellidos']) ?></td>
                <td><?= e($p['programa']) ?></td>
                <td><?= (int) $p['semestre'] ?></td>
                <td><?= e($p['correo']) ?></td>
                <td>
                    <form method="post" class="d-flex gap-1"><?= campoCsrf() ?>
                        <input type="hidden" name="vacante_id" value="<?= $vacanteId ?>">
                        <input type="hidden" name="postulacion_id" value="<?= (int) $p['id'] ?>">
                        <select name="estado" class="form-select form-select-sm">
                            <?php foreach (VacanteServicio::ESTADOS_POSTULACION as $est): ?>
                                <option value="<?= $est ?>" <?= $p['estado'] === $est ? 'selected' : '' ?>><?= etiqueta($est) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-verde">Guardar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
