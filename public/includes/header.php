<?php $u = usuario(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Plataforma de Prácticas') ?> | Prácticas UCN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark barra">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">Prácticas Profesionales</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="vacantes.php">Vacantes</a></li>
                <?php if (!$u): ?>
                    <li class="nav-item"><a class="nav-link" href="registro_estudiante.php">Soy estudiante</a></li>
                    <li class="nav-item"><a class="nav-link" href="registro_empresa.php">Soy empresa</a></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 mt-1" href="login.php">Iniciar sesión</a></li>
                <?php else: ?>
                    <?php if ($u['rol'] === 'estudiante'): ?>
                        <li class="nav-item"><a class="nav-link" href="mis_postulaciones.php">Mis postulaciones</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="publicar_vacante.php">Publicar vacante</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="panel.php"><?= e($u['nombre']) ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Salir</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4 flex-grow-1">
<?php if (!empty($_SESSION['mensaje'])): $m = $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
    <div class="alert alert-<?= e($m['tipo']) ?> alert-dismissible fade show" role="alert">
        <?= e($m['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
