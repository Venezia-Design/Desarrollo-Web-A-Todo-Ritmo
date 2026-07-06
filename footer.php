<div class="footer__col">
  <p class="footer__col-titulo">Redes</p>
  <a class="footer__link" href="#">Instagram</a>
  <a class="footer__link" href="#">YouTube</a>
  <a class="footer__link" href="#">TikTok</a>
</div>
<div class="footer__col">
  <p class="footer__col-titulo">Staff</p>
  <?php if ($esAdmin): ?>
    <a class="footer__link" href="logout.php">Salir (admin)</a>
  <?php else: ?>
    <a class="footer__link" href="index.php">Acceso admin</a>
  <?php endif; ?>
</div>
