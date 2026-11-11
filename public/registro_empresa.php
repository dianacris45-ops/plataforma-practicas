<?php
require __DIR__ . '/includes/app.php';
use App\ErrorValidacion;
use App\Servicios\RegistroServicio;

$titulo = 'Registro de empresa';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    try {
        (new RegistroServicio(db()))->registrarEmpresa($_POST);
        mensaje('success', '¡Bienvenidos! La empresa quedó registrada. Inicien sesión para publicar vacantes.');
        redirigir('login.php');
    } catch (ErrorValidacion $ex) {
        $error = $ex->getMessage();
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="card tarjeta form-card p-4">
    <h3 class="mb-1">Registro de empresa</h3>
    <p class="text-muted">Registren su empresa para publicar vacantes de práctica.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
        <?= campoCsrf() ?>
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Razón social</label><input class="form-control" name="razon_social" value="<?= valor('razon_social') ?>" required></div>
            <div class="col-md-4"><label class="form-label">NIT</label><input class="form-control" name="nit" value="<?= valor('nit') ?>" placeholder="900123456-7" required></div>
            <div class="col-md-6"><label class="form-label">Sector</label><input class="form-control" name="sector" value="<?= valor('sector') ?>" placeholder="Tecnología, salud, comercio…" required></div>
            <div class="col-md-6"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad" value="<?= valor('ciudad') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Persona de contacto</label><input class="form-control" name="contacto" value="<?= valor('contacto') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?= valor('telefono') ?>"></div>
            <div class="col-md-6"><label class="form-label">Correo de la empresa</label><input type="email" class="form-control" name="correo" value="<?= valor('correo') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" class="form-control" name="clave" required>
                <div class="form-text">Mínimo 8 caracteres, con letras y números.</div></div>
        </div>
        <button class="btn btn-verde mt-4 px-4">Registrar empresa</button>
        <a href="login.php" class="ms-3">Ya tenemos cuenta</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
