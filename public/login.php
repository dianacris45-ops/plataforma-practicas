<?php
require __DIR__ . '/includes/app.php';
use App\Servicios\RegistroServicio;

$titulo = 'Iniciar sesión';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $u = (new RegistroServicio(db()))->iniciarSesion((string) ($_POST['correo'] ?? ''), (string) ($_POST['clave'] ?? ''));
    if ($u) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $u;
        mensaje('success', 'Hola, ' . $u['nombre'] . '. Bienvenido(a).');
        redirigir('panel.php');
    }
    $error = 'Correo o contraseña incorrectos.';
}
require __DIR__ . '/includes/header.php';
?>
<div class="card tarjeta p-4 mx-auto" style="max-width:420px">
    <h3 class="mb-3 text-center">Iniciar sesión</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= campoCsrf() ?>
        <div class="mb-3"><label class="form-label">Correo</label><input type="email" class="form-control" name="correo" value="<?= valor('correo') ?>" required></div>
        <div class="mb-3"><label class="form-label">Contraseña</label><input type="password" class="form-control" name="clave" required></div>
        <button class="btn btn-verde w-100">Entrar</button>
    </form>
    <p class="text-center mt-3 mb-0 small">¿No tienes cuenta? <a href="registro_estudiante.php">Estudiante</a> · <a href="registro_empresa.php">Empresa</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
