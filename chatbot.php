<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';
$pageTitle='Wandi - AI Travel Chat';
$extraHead='<link rel="stylesheet" href="'.SITE_URL.'/assets/css/chatbot.css">';
require_once 'includes/header.php'; ?>

<div class="chat-shell page-container section">
  <div style="max-width:860px;margin:0 auto">

    <?php if(!isLoggedIn()): ?>
    <div class="guest-banner" style="margin-bottom:24px">
      <i class="fas fa-robot"></i>
      <p><strong>Preview mode.</strong> You can explore the chat interface. Sign up free to start chatting with Wandi, your AI travel companion.</p>
      <a href="signup.php" class="btn-primary btn-sm"><i class="fas fa-user-plus"></i> Sign Up Free</a>
    </div>
    <?php endif; ?>

    <!-- INTRO BANNER -->
    <div style="background:linear-gradient(135deg,rgba(0,50,98,.08),rgba(0,112,187,.06));border:1px solid rgba(0,112,187,.15);border-radius:var(--radius);padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap">
      <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--primary-dark),var(--primary));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0">🧭</div>
      <div>
        <h3 style="margin-bottom:4px;color:var(--text)">Meet Wandi — Your AI Travel Companion</h3>
        <p style="font-size:.88rem;color:var(--muted);margin:0">Powered by Google Gemini. Ask anything about Indian travel — destinations, culture, food, tips, safety, budgets.</p>
      </div>
    </div>

    <div class="chat-card">
      <div class="chat-wandi-header">
        <div class="chat-wandi-avatar">🧭</div>
        <div class="chat-wandi-info">
          <h3>Wandi</h3>
          <span><span class="chat-online-dot"></span>Online — Ready to help you explore India</span>
        </div>
        <div style="margin-left:auto">
          <span style="background:rgba(255,255,255,.15);padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.85)"><i class="fas fa-bolt"></i> AI Powered</span>
        </div>
      </div>

      <div class="chat-body">
        <div class="chips" style="margin-top:16px">
          <button class="chip" data-q="Best time to visit Goa?"><i class="fas fa-sun"></i> Goa timing?</button>
          <button class="chip" data-q="Plan a 5-day budget trip to Rajasthan"><i class="fas fa-route"></i> Rajasthan on budget</button>
          <button class="chip" data-q="What to pack for a hill station trip in India?"><i class="fas fa-suitcase-rolling"></i> Packing for hills</button>
          <button class="chip" data-q="Safest solo travel destinations in India for women?"><i class="fas fa-shield-alt"></i> Solo travel tips</button>
          <button class="chip" data-q="Best street food to try in India"><i class="fas fa-utensils"></i> Street food</button>
          <button class="chip" data-q="Top hidden gems in India not many tourists know about"><i class="fas fa-gem"></i> Hidden gems</button>
        </div>

        <div id="chat-messages" class="chat-container" style="margin-top:0">
          <div class="msg-ai">
            🙏 <strong>Namaste!</strong> I'm Wandi, your personal India travel guide.<br><br>
            Ask me anything — best destinations, local food, hidden gems, budgeting tips, what to pack, travel safety, or help planning your next adventure across India!
          </div>
        </div>

        <div class="chat-typing" id="chat-typing">
          <div class="typing-dots"><span></span><span></span><span></span></div>
          <span>Wandi is thinking...</span>
        </div>
      </div>

      <div class="chat-footer-bar">
        <form id="chat-form" class="chat-input-row">
          <input id="chat-input" class="form-input" placeholder="Ask about Indian travel, destinations, food, tips..." autocomplete="off">
          <button type="submit" class="btn-primary" id="send-btn"><i class="fas fa-paper-plane"></i> Send</button>
        </form>
        <p style="font-size:.75rem;color:var(--muted);text-align:center;margin-top:10px">
          <i class="fas fa-info-circle"></i> Wandi uses AI — verify critical travel info independently.<?= isLoggedIn() ? ' Limited to 10 messages/hour.' : '' ?>
        </p>
      </div>
    </div>
  </div>
</div>

<script>
const isLoggedIn = <?= isLoggedIn() ? 'true' : 'false' ?>;
<?php if(isLoggedIn()): ?>
// Full chatbot functionality for logged-in users
<?php endif; ?>
// Intercept for guests
document.getElementById('chat-form')?.addEventListener('submit', function(e) {
  if (!isLoggedIn) { e.preventDefault(); e.stopImmediatePropagation(); showLoginModal(); return; }
}, true);
document.querySelectorAll('.chip[data-q]').forEach(chip => {
  chip.addEventListener('click', function(e) {
    if (!isLoggedIn) { e.stopImmediatePropagation(); showLoginModal(); return; }
  }, true);
});
</script>
<?php $extraScripts='<script src="'.SITE_URL.'/assets/js/chatbot.js"></script>'; require_once 'includes/footer.php'; ?>
