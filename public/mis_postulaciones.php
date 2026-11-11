<?php
require __DIR__ . '/includes/app.php';
use App\Servicios\VacanteServicio;

$u = exigirRol('estudiante');
$postulaciones = (new VacanteServicio(db()))->postulacionesDeEstudiante($u['perfil_id']);
$colores = ['enviada' => 'bg-info text-dark', 'en_revision' => 'bg-warning text-dark', 'aceptada' => 'bg-success', 'rechazada' => 'bg-danger'];
$titulo = 'Mis postulaciones';
require __DIR__ . '/includes/header.php';
?>
<h3 class="mb-3">Mis postulaciones</h3>
<div class="card tarjeta p-0">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Vacante</th><th>Empresa</th><th>Modalidad</th><th>Fecha</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if (!$postulaciones): ?><tr><td colspan="5" class="text-muted p-4">Todavía no te has postulado. <a href="vacantes.php">Mira las vacantes</a>.</td></tr><?php endif; ?>
        <?php foreach ($postulaciones as $p): ?>
            <tr>
                <td class="fw-semibold"><?= e($p['titulo']) ?></td>
                <td><?= e($p['razon_social']) ?></td>
                <td class="text-capitalize"><?= e(etiqueta($p['modalidad'])) ?></td>
                <td><?= e(substr($p['fecha'], 0, 16)) ?></td>
                <td><span class="badge <?= $colores[$p['estado']] ?? 'bg-secondary' ?>"><?= e(etiqueta($p['estado'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
