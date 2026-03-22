<header class="top-navbar ">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Buscar usuarios, recetas, eventos...">
    </div>

    <div class="user-actions">
        <div class="notification-bell">
            <i class="bi bi-bell"></i>
            <span class="dot"></span>
        </div>
        <div class="user-details">
            <span class="user-name"><?php echo $userLogueado['username']; ?></span>
            <span class="user-rank"><?php echo strtoupper($userLogueado['role']); ?></span>
        </div>
        <div class="user-avatar"><?php echo strtoupper(substr($userLogueado['username'], 0, 2)) ?></div>
    </div>
</header>