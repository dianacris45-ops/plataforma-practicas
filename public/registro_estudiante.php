<?php
require __DIR__ . '/includes/app.php';
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;

$titulo = 'Registro de estudiante';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    try {
        (new RegistroServicio(db()))->registrarEstudiante($_POST);
        mensaje('success', '¡Listo! Tu cuenta de estudiante fue creada. Ya puedes iniciar sesión.');
        redirigir('login.php');
    } catch (ErrorValidacion $ex) {
        $error = $ex->getMessage();
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="card tarjeta form-card p-4">
    <h3 class="mb-1">Registro de estudiante</h3>
    <p class="text-muted">Completa tus datos para buscar y postularte a prácticas.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
        <?= campoCsrf() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nombres</label><input class="form-control" name="nombres" value="<?= valor('nombres') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Apellidos</label><input class="form-control" name="apellidos" value="<?= valor('apellidos') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Documento de identidad</label><input class="form-control" name="documento" value="<?= valor('documento') ?>" inputmode="numeric" required></div>
            <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?= valor('telefono') ?>"></div>
            <div class="col-md-8"><label class="form-label">Programa académico</label>
                <select class="form-select" name="programa" required>
                    <option value="">Selecciona…</option>
                    <?php foreach (RegistroServicio::PROGRAMAS as $p): ?>
                        <option <?= ($_POST['programa'] ?? '') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-4"><label class="form-label">Semestre</label>
                <select class="form-select" name="semestre" required>
                    <?php for ($s = 6; $s <= 10; $s++): ?>
                        <option value="<?= $s ?>" <?= (int) ($_POST['semestre'] ?? 0) === $s ? 'selected' : '' ?>><?= $s ?>.º</option>
                    <?php endfor; ?>
                </select></div>
            <div class="col-md-6"><label class="form-label">Correo institucional</label><input type="email" class="form-control" name="correo" value="<?= valor('correo') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" class="form-control" name="clave" required>
                <div class="form-text">Mínimo 8 caracteres, con letras y números.</div></div>
        </div>
        <button class="btn btn-verde mt-4 px-4">Crear mi cuenta</button>
        <a href="login.php" class="ms-3">Ya tengo cuenta</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
