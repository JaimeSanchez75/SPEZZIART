<?php
$rutaActual = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

$enlaces = [
    ['href' => '/pages/administracion',             'icono' => 'bi-grid-fill',          'texto' => 'Dashboard'],
    ['href' => '/pages/administracion/usuarios',    'icono' => 'bi-people',             'texto' => 'Usuarios & Admins'],
    ['href' => '/pages/administracion/recetas',     'icono' => 'bi-journal-bookmark',   'texto' => 'Recetas'],
    ['href' => '/pages/administracion/ingredientes','icono' => 'bi-egg-fried',          'texto' => 'Ingredientes'],
    ['href' => '/pages/administracion/etiquetas',   'icono' => 'bi-bookmark-star',      'texto' => 'Etiquetas'],
    ['href' => '/pages/administracion/moderacion',  'icono' => 'bi-check-circle',       'texto' => 'Moderación Social'],
];

if (!function_exists('navegadorEsActivo')) {
    function navegadorEsActivo(string $href, string $rutaActual): bool {
        $hrefNormal = '/' . trim(parse_url($href, PHP_URL_PATH), '/');
        if ($hrefNormal === '/pages/administracion') {
            // Dashboard solo se marca activo en su ruta exacta
            return $rutaActual === $hrefNormal;
        }
        return $rutaActual === $hrefNormal || strncmp($rutaActual, $hrefNormal . '/', strlen($hrefNormal) + 1) === 0;
    }
}
?>
<div class="mt-4">
    <h1 class="titulo letraRomana fw-bold texto-rojo m-0 sidebar-titulo">SPEZZIART</h1>
    <p class="texto-gris textoPequeno ">ADMIN PANEL</p>
</div>
<nav class="d-flex flex-column nav pb-3 gap-2 mt-3">
    <?php foreach ($enlaces as $enlace) {
        $activo = navegadorEsActivo($enlace['href'], $rutaActual);
        $clases = 'nav-item py-2 px-3 rounded text-decoration-none texto fw-medium d-flex align-items-center gap-2 navegador';
        $clases .= $activo ? ' active bg-rojo text-white' : ' text-secondary';
    ?>
        <a href="<?= htmlspecialchars($enlace['href']) ?>" class="<?= $clases ?>">
            <i class="bi <?= htmlspecialchars($enlace['icono']) ?> textoMediano"></i> <?= htmlspecialchars($enlace['texto']) ?>
        </a>
    <?php } ?>
</nav>
<div class="border-top py-3 mt-auto">
    <a href="/auth/logout" class="nav-item text-decoration-none texto fw-medium text-secondary py-3 px-3 d-flex align-items-center gap-2 cerrarSesion">
        <i class="bi bi-box-arrow-left textoMediano me-1"></i> Cerrar Sesión
    </a>
</div>
