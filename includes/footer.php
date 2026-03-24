<footer class="site-footer">
  <div class="page-container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand"><i class="fas fa-compass"></i> WanderWise</div>
        <p class="footer-desc">India's most loved AI travel companion. Plan smarter trips, find travel buddies, and cherish every memory.</p>
        <div class="footer-social">
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
          <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <a href="<?= SITE_URL ?>/index.php">Home</a>
        <a href="<?= SITE_URL ?>/stories.php">Travel Stories</a>
        <a href="<?= SITE_URL ?>/festivals.php">Indian Festivals</a>
        <a href="<?= SITE_URL ?>/safety.php">Safety Tips</a>
      </div>
      <div class="footer-col">
        <h4>Plan</h4>
        <a href="<?= SITE_URL ?>/plan-trip.php">Plan a Trip</a>
        <a href="<?= SITE_URL ?>/travel-buddy.php">Find a Buddy</a>
        <a href="<?= SITE_URL ?>/chatbot.php">AI Travel Chat</a>
        <a href="<?= SITE_URL ?>/weather.php">Weather</a>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a>
        <a href="<?= SITE_URL ?>/storybook.php">My Storybook</a>
        <a href="<?= SITE_URL ?>/budget.php">Budget Tracker</a>
        <a href="<?= SITE_URL ?>/profile.php">Profile</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> WanderWise &mdash; Crafted with ❤️ for Indian Explorers</p>
      <div class="footer-links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Contact</a>
      </div>
    </div>
  </div>
</footer>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<?php if (!empty($extraScripts)): echo $extraScripts; endif; ?>
</body>
</html>
