<?php
require __DIR__ . '/includes/app.php';
$_SESSION = [];
session_destroy();
session_start();
mensaje('info', 'Cerraste sesión. ¡Hasta pronto!');
redirigir('index.php');
