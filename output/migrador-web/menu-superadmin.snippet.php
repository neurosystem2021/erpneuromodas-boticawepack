<?php if (strcasecmp(trim((string)($_SESSION['user']??'')),'superadmin')===0): ?>
<li><a href="/views/migrador-historico.php">Migrador de compras y ventas</a></li>
<?php endif; ?>
