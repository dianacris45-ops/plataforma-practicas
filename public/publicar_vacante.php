<?php
require __DIR__ . '/includes/app.php';
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;
use App\Servicios\VacanteServicio;

$u = exigirRol('empresa');
$titulo = 'Publicar vacante';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    try {
        (new VacanteServicio(db()))->publicar($u['perfil_id'], $_POST);
        mensaje('success', 'La vacante fue publicada. Los estudiantes ya pueden verla.');
        redirigir('panel.php');
    } catch (ErrorValidacion $ex) {
        $error = $ex->getMessage();
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="card tarjeta form-card p-4">
    <h3 class="mb-1">Publicar vacante de práctica</h3>
    <p class="text-muted">Cuéntenles a los estudiantes qué buscan.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
        <?= campoCsrf() ?>
        <div class="row g-3">
            <div class="col-12"><label class="form-label">Título de la vacante</label><input class="form-control" name="titulo" value="<?= valor('titulo') ?>" placeholder="Practicante de desarrollo web" required></div>
            <div class="col-12"><label class="form-label">Descripción de funciones</label><textarea class="form-control" name="descripcion" rows="3" required><?= valor('descripcion') ?></textarea></div>
            <div class="col-12"><label class="form-label">Requisitos</label><textarea class="form-control" name="requisitos" rows="2"><?= valor('requisitos') ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Programa académico</label>
                <select class="form-select" name="programa" required>
                    <option value="">Selecciona…</option>
                    <?php foreach (RegistroServicio::PROGRAMAS as $p): ?>
                        <option <?= ($_POST['programa'] ?? '') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-6"><label class="form-label">Modalidad</label>
                <select class="form-select" name="modalidad" required>
                    <?php foreach (['presencial' => 'Presencial', 'remota' => 'Remota', 'hibrida' => 'Híbrida'] as $k => $t): ?>
                        <option value="<?= $k ?>" <?= ($_POST['modalidad'] ?? '') === $k ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-6"><label class="form-label">Cupos</label><input type="number" min="1" max="50" class="form-control" name="cupos" value="<?= valor('cupos') ?: '1' ?>"></div>
            <div class="col-md-6"><label class="form-label">Fecha de cierre</label><input type="date" class="form-control" name="fecha_cierre" value="<?= valor('fecha_cierre') ?>" min="<?= date('Y-m-d') ?>" required></div>
        </div>
        <button class="btn btn-verde mt-4 px-4">Publicar</button>
        <a href="panel.php" class="ms-3">Cancelar</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
