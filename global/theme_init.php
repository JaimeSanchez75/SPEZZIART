<?php // Inicialización del tema para pasar el tema del usuario a JavaScript - Es un Helper.
$userTheme = 'sistema';
if (Auth::check()) {$userTheme = Auth::user()['tema'] ?? 'sistema';}?>
<script>window.__USER_THEME__ = <?= json_encode($userTheme) ?>;</script>