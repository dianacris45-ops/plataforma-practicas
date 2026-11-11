<?php
require __DIR__ . '/includes/app.php';
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;
use App\Servicios\VacanteServicio;

$servicio = new VacanteServicio(db());
$u = usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $u = exigirRol('estudiante');
    try {
        $servicio->postular($u['perfil_id'], (int) ($_POST['vacante_id'] ?? 0));
        mensaje('success', '¡Postulación enviada! Puedes ver su estado en "Mis postulaciones".');
    } catch (ErrorValidacion $ex) {
        mensaje('danger', $ex->getMessage());
    }
    redirigir('vacantes.php');
}

$programa = $_GET['programa'] ?? '';
$vacantes = $servicio->listarAbiertas($programa ?: null);
$yaPostulado = [];
if ($u && $u['rol'] === 'estudiante') {
    foreach ($servicio->postulacionesDeEstudiante($u['perfil_id']) as $p) {
        $yaPostulado[(int) $p['vacante_id']] = true;
    }
}
$titulo = 'Vacantes';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h3 class="mb-0">Vacantes de práctica</h3>
    <form class="d-flex gap-2" method="get">
        <select class="form-select" name="programa">
            <option value="">Todos los programas</option>
            <?php foreach (RegistroServicio::PROGRAMAS as $p): ?>
                <option <?= $programa === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-success">Filtrar</button>
    </form>
</div>
<?php if (!$vacantes): ?><p class="text-muted">No hay vacantes abiertas con ese filtro.</p><?php endif; ?>
<div class="row g-3">
<?php foreach ($vacantes as $v): ?>
    <div class="col-md-6"><div class="card tarjeta p-4 h-100">
        <div class="d-flex justify-content-between">
            <h5 class="fw-bold mb-1"><?= e($v['titulo']) ?></h5>
            <span class="badge etiqueta align-self-start text-capitalize"><?= e(etiqueta($v['modalidad'])) ?></span>
        </div>
        <div class="text-muted small mb-2"><?= e($v['razon_social']) ?> · <?= e($v['ciudad']) ?></div>
        <p class="mb-2"><?= e($v['descripcion']) ?></p>
        <?php if ($v['requisitos']): ?><p class="small text-muted mb-2"><strong>Requisitos:</strong> <?= e($v['requisitos']) ?></p><?php endif; ?>
        <div class="small mb-3"><?= e($v['programa']) ?> · <?= (int) $v['cupos'] ?> cupo(s) · Cierra: <?= e($v['fecha_cierre']) ?></div>
        <div class="mt-auto">
        <?php if ($u && $u['rol'] === 'estudiante'): ?>
            <?php if (isset($yaPostulado[(int) $v['id']])): ?>
                <button class="btn btn-secondary" disabled>Ya te postulaste</button>
            <?php else: ?>
                <form method="post" class="d-inline"><?= campoCsrf() ?>
                    <input type="hidden" name="vacante_id" value="<?= (int) $v['id'] ?>">
                    <button class="btn btn-verde">Postularme</button>
                </form>
            <?php endif; ?>
        <?php elseif (!$u): ?>
            <a href="login.php" class="btn btn-outline-success">Inicia sesión para postularte</a>
        <?php endif; ?>
        </div>
    </div></div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
