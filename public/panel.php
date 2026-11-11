<?php
require __DIR__ . '/includes/app.php';
use App\Servicios\VacanteServicio;

$u = usuario();
if (!$u) {
    redirigir('login.php');
}
$servicio = new VacanteServicio(db());
$titulo = 'Mi panel';
require __DIR__ . '/includes/header.php';
?>
<h3 class="mb-3">Mi panel <span class="badge etiqueta text-capitalize"><?= e($u['rol']) ?></span></h3>
<?php if ($u['rol'] === 'estudiante'):
    $mias = $servicio->postulacionesDeEstudiante($u['perfil_id']);
    $abiertas = $servicio->listarAbiertas(); ?>
    <div class="row g-3">
        <div class="col-md-6"><div class="card tarjeta p-4"><div class="text-muted">Vacantes abiertas</div><div class="display-6 fw-bold"><?= count($abiertas) ?></div><a href="vacantes.php" class="btn btn-verde mt-2 align-self-start">Ver vacantes</a></div></div>
        <div class="col-md-6"><div class="card tarjeta p-4"><div class="text-muted">Mis postulaciones</div><div class="display-6 fw-bold"><?= count($mias) ?></div><a href="mis_postulaciones.php" class="btn btn-outline-success mt-2 align-self-start">Ver estado</a></div></div>
    </div>
<?php else:
    $vacantes = $servicio->listarDeEmpresa($u['perfil_id']); ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">Estas son las vacantes que ha publicado su empresa.</p>
        <a href="publicar_vacante.php" class="btn btn-verde">Publicar vacante</a>
    </div>
    <div class="card tarjeta p-0">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Vacante</th><th>Cierre</th><th>Estado</th><th>Postulados</th><th></th></tr></thead>
            <tbody>
            <?php if (!$vacantes): ?><tr><td colspan="5" class="text-muted p-4">Aún no han publicado vacantes.</td></tr><?php endif; ?>
            <?php foreach ($vacantes as $v): ?>
                <tr>
                    <td class="fw-semibold"><?= e($v['titulo']) ?></td>
                    <td><?= e($v['fecha_cierre']) ?></td>
                    <td><span class="badge <?= $v['estado'] === 'abierta' ? 'bg-success' : 'bg-secondary' ?>"><?= e(etiqueta($v['estado'])) ?></span></td>
                    <td><?= (int) $v['total_postulados'] ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-success" href="postulantes.php?vacante=<?= (int) $v['id'] ?>">Ver postulantes</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
