# Plataforma de Prácticas Profesionales — Prototipo (Unidad 3)

Prototipo funcional de la plataforma de prácticas profesionales del curso **Ingeniería de Software**
(Fundación Universitaria Católica del Norte). Continúa el análisis (Taller 1) y el diseño (Taller 2).

## Módulos incluidos

| Módulo | Qué hace | Requisitos |
|---|---|---|
| 1. Registro e inicio de sesión | Registro de estudiantes y empresas con validaciones, inicio y cierre de sesión por rol | RF-01, RF-02, RF-03 |
| 2. Vacantes y postulaciones | La empresa publica vacantes, ve postulantes, cambia su estado y cierra vacantes. El estudiante consulta, filtra y se postula, y sigue el estado de su postulación | RF-04 a RF-08 |

## Tecnologías

- **XAMPP** (Apache + PHP 8 + MySQL/MariaDB + phpMyAdmin)
- **PHP 8** con organización por capas (presentación, servicios y datos) y **PDO** con consultas preparadas
- **Bootstrap 5.3** (framework de interfaz, desde CDN)
- **Composer** + **PHPUnit 10** (pruebas unitarias)
- **Git + GitHub** (control de versiones)

## Cómo ejecutarlo en XAMPP

1. Copia la carpeta `practicas` dentro de `C:\xampp\htdocs\`.
2. Abre el **XAMPP Control Panel** y pulsa **Start** en **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin` → pestaña **Importar** → elige `database/plataforma_practicas.sql` → **Continuar**.
4. Abre `http://localhost/practicas/` en el navegador.

> Si tu MySQL tiene contraseña, cámbiala en `config/database.php`.

## Cómo correr las pruebas

```bash
cd C:\xampp\htdocs\practicas
composer install
vendor\bin\phpunit
```

Las pruebas usan una base de datos SQLite en memoria, así que **no modifican** tus datos de XAMPP.
Si PHPUnit dice que falta `pdo_sqlite`, abre `C:\xampp\php\php.ini`, quita el `;` de la línea
`extension=pdo_sqlite` y reinicia Apache.

## Estructura

```
practicas/
├── config/database.php        Conexión a MySQL
├── database/*.sql             Script de la base de datos
├── public/                    Páginas web (capa de presentación)
├── src/                       Lógica del negocio (servicios y validaciones)
├── tests/                     Pruebas unitarias PHPUnit
├── composer.json / phpunit.xml
└── README.md
```
