<link rel="stylesheet" href="/global/styles/styles.css">
<link rel="stylesheet" href="/global/styles/navbar.css">
<link rel="stylesheet" href="/global/styles/global.css">
    <ul class="bottom-nav bg-rojo position-fixed bottom-0 mb-2 d-flex rounded-5 px-5 py-2 justify-content-between align-items-center text-white z-5 m-0 gap-5 ">
        <li class="nav-item d-flex align-items-center ms-5 justify-content-center">
            <a class="nav-link" href="/pages/feed">
                <span class="material-symbols-outlined text-white">home</span>
                <p class="textoPequeno">FEED</p>
            </a>
        </li>
        <li class="nav-item d-flex align-items-center justify-content-center">
            <?php if(Auth::check()): ?>
            <a href="/pages/individual" class="text-center">
                <span class="material-symbols-outlined text-white textoMediano">menu_book_2</span>
                <p class="textoPequeno fw-normal">INDIVIDUAL</p>
            </a>
            <?php else: ?>
            <a href="/pages/login" class="text-center">
                <span class="material-symbols-outlined text-white textoMediano">menu_book_2</span>
                <p class="textoPequeno fw-normal">INDIVIDUAL</p>
            </a>
            <?php endif; ?>
        </li>
        <?php if(Auth::check() && $_SESSION['user']['ModoFit'] == 1): ?>
        <li class="nav-item d-flex align-items-center justify-content-center">
            <a href="/pages/modofit" class="text-center">
                <span class="material-symbols-outlined text-white textoMediano">fitness_center</span>
                <p class="textoPequeno fw-normal">MODO FIT</p>
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item d-flex align-items-center justify-content-center">
            <a class="nav-link text-center" href="/pages/buscar" >
                <span class="material-symbols-outlined text-white textoMediano">search</span>
                <p class="textoPequeno fw-normal">BUSCAR</p>
            </a>
        </li>
        <li class="nav-item ms-auto d-flex align-items-center me-5 justify-content-center">
            <?php if(Auth::check()): ?>
            <a href="/pages/perfil" class="text-center">
                <span class="material-symbols-outlined  text-white textoMediano ">account_circle</span>
                <p class="textoPequeno fw-normal ">PERFIL</p>
            </a>
            <?php else: ?>
            <a href="/pages/login" class="text-center">
                <span class="material-symbols-outlined text-white textoMediano">account_circle</span>
                <p class="textoPequeno fw-normal">PERFIL</p>
            </a>
            <?php endif; ?>
        </li>
    </ul>
<script>
    (function () {
        const nav = document.querySelector('.bottom-nav');
        if (!nav) return;
        document.addEventListener('show.bs.modal',   function () { nav.style.display = 'none'; });
        document.addEventListener('hidden.bs.modal', function () { nav.style.display = '';     });
    })();
</script>
</body>
</html>