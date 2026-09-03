<footer class="footer" id="footer">
    <div class="footer__top">
      <span class="footer__brand">A Todo Ritmo</span>
    </div>
    <div class="footer__cols">
      <div class="footer__col">
        <p class="footer__col-titulo">Contacto</p>
        <a class="footer__link" href="mailto:hola@atodoritmo.com.ar">hola@atodoritmo.com.ar</a>
        <a class="footer__link" href="tel:+541100000000">+54 11 0000-0000</a>
      </div>
      <div class="footer__col">
        <p class="footer__col-titulo">Ubicación</p>
        <p class="footer__texto">Av. Corrientes 1234, piso 3<br>CABA, Argentina</p>
      </div>
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
    </div>
    <div class="footer__bottom">
      <p>© 2026 A Todo Ritmo — Escuela de música</p>
    </div>
  </footer>