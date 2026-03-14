<div class="bottom-nav">

<a href="/App/pages/feed"><span class="material-symbols-outlined blanco">
home
</span></a>
<a href="#"><span class="material-symbols-outlined blanco">
menu_book_2
</span></a>

<?php if(Auth::check()): ?>

<a href="/App/pages/perfil"><span class="material-symbols-outlined blanco">
account_circle
</span></a>

<?php else: ?>

<a href="/App/pages/login"><span class="material-symbols-outlined blanco">
account_circle
</span></a>

<?php endif; ?>

</div>


</body>

</html>