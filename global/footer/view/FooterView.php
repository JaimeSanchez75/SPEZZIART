<div class="bottom-nav">

<a href="/pages/feed"><span class="material-symbols-outlined blanco">
home
</span></a>
<a href="/pages/individual"><span class="material-symbols-outlined blanco">
menu_book_2
</span></a>

<?php if(Auth::check()): ?>

<a href="/pages/perfil"><span class="material-symbols-outlined blanco">
account_circle
</span></a>

<?php else: ?>

<a href="/pages/login"><span class="material-symbols-outlined blanco">
account_circle
</span></a>

<?php endif; ?>

</div>


</body>

</html>