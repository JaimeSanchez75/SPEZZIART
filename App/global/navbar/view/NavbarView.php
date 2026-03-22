<link rel="stylesheet" href="/App/global/styles/navbar.css">
<link rel="stylesheet" href="/App/global/styles/global.css">

<div class="bottom-nav">

<a href="/App/pages/feed"><span class="material-symbols-outlined blanco">
home
</span></a>
<<<<<<< HEAD

=======
>>>>>>> 49580534d0fc63b333604ae3ae75884320b9d992
<a href="/App/pages/individual"><span class="material-symbols-outlined blanco">
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