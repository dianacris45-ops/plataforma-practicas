<?php
require __DIR__ . '/includes/app.php';
$titulo = 'Inicio';
$vacantes = (new App\Servicios\VacanteServicio(db()))->listarAbiertas();
require __DIR__ . '/includes/header.php';
?>
<section class="hero p-4 p-md-5 mb-4">
    <h1 class="fw-bold">Encuentra tu práctica profesional</h1>
    <p class="lead mb-4">Un solo lugar para que estudiantes, empresas y la universidad gestionen las prácticas.</p>
    <a href="registro_estudiante.php" class="btn btn-light btn-lg me-2 mb-2">Soy estudiante</a>
    <a href="registro_empresa.php" class="btn btn-outline-light btn-lg mb-2">Soy empresa</a>
</section>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card tarjeta p-3 h-100"><h5 class="mt-2">1. Regístrate</h5><p class="mb-0 text-muted">Crea tu cuenta como estudiante o como empresa.</p></div></div>
    <div class="col-md-4"><div class="card tarjeta p-3 h-100"><h5 class="mt-2">2. Publica o busca</h5><p class="mb-0 text-muted">Las empresas publican vacantes; los estudiantes las consultan.</p></div></div>
    <div class="col-md-4"><div class="card tarjeta p-3 h-100"><h5 class="mt-2">3. Postúlate</h5><p class="mb-0 text-muted">Envía tu postulación con un clic y sigue su estado.</p></div></div>
</div>
<h4 class="mb-3">Vacantes abiertas <span class="badge etiqueta"><?= count($vacantes) ?></span></h4>
<?php if (!$vacantes): ?>
    <p class="text-muted">Aún no hay vacantes publicadas.</p>
<?php endif; ?>
<div class="row g-3">
<?php foreach (array_slice($vacantes, 0, 3) as $v): ?>
    <div class="col-md-4"><div class="card tarjeta p-3 h-100">
        <h6 class="fw-bold mb-1"><?= e($v['titulo']) ?></h6>
        <div class="text-muted small mb-2"><?= e($v['razon_social']) ?> · <?= e($v['ciudad']) ?></div>
        <span class="badge etiqueta align-self-start"><?= e(etiqueta($v['modalidad'])) ?></span>
        <a class="stretched-link" href="vacantes.php"></a>
    </div></div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
