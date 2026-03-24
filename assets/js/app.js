// ====== NAVBAR SCROLL ======
const nav = document.getElementById('main-navbar');
if (nav) {
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 10);
  });
}

// ====== HAMBURGER MENU ======
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');
if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => {
    navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
  });
}

// ====== TRIP TYPE CARDS (multi-select) ======
document.querySelectorAll('.type-card').forEach(card => {
  card.addEventListener('click', function() {
    this.classList.toggle('selected');
    const cb = this.querySelector('input[type="checkbox"]');
    if (cb) cb.checked = !cb.checked;
  });
});

// ====== PLAN TRIP WIZARD ======
const wizardNext = document.querySelectorAll('.wizard-next');
const wizardBack = document.querySelectorAll('.wizard-back');
const wizardPanes = document.querySelectorAll('.wizard-pane');
const wizardSteps = document.querySelectorAll('.wizard-step');

let currentStep = 0;

function goToStep(step) {
  wizardPanes.forEach((p, i) => p.classList.toggle('active', i === step));
  wizardSteps.forEach((s, i) => {
    s.classList.remove('active', 'done');
    if (i === step) s.classList.add('active');
    if (i < step) s.classList.add('done');
  });
  currentStep = step;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

if (wizardPanes.length > 0) {
  goToStep(0);
  wizardNext.forEach(btn => {
    btn.addEventListener('click', () => {
      if (currentStep < wizardPanes.length - 1) goToStep(currentStep + 1);
    });
  });
  wizardBack.forEach(btn => {
    btn.addEventListener('click', () => {
      if (currentStep > 0) goToStep(currentStep - 1);
    });
  });
}

// ====== DURATION DISPLAY ======
function updateDuration() {
  const startEl = document.getElementById('start_date');
  const endEl = document.getElementById('end_date');
  const note = document.getElementById('duration-note');
  if (!startEl || !endEl || !note) return;
  if (startEl.value && endEl.value) {
    const a = new Date(startEl.value), b = new Date(endEl.value);
    const days = Math.round((b - a) / 86400000) + 1;
    if (days > 0) {
      note.innerHTML = `<span class="badge badge-saffron"><i class="fas fa-sun"></i> ${days} day${days > 1 ? 's' : ''} of adventure</span>`;
    } else {
      note.textContent = 'End date must be after start date';
    }
  }
}
document.getElementById('start_date')?.addEventListener('change', updateDuration);
document.getElementById('end_date')?.addEventListener('change', updateDuration);

// ====== BUDGET DISPLAY ======
const budgetInput = document.getElementById('budget_amount');
const budgetDisplay = document.getElementById('budget-display');
if (budgetInput && budgetDisplay) {
  budgetInput.addEventListener('input', () => {
    const v = parseInt(budgetInput.value) || 0;
    budgetDisplay.textContent = '₹' + v.toLocaleString('en-IN');
  });
}

// ====== PACKING LIST CHECK ======
document.querySelectorAll('.pack-item input[type="checkbox"]').forEach(cb => {
  cb.addEventListener('change', function() {
    this.closest('.pack-item')?.classList.toggle('done', this.checked);
  });
});

// ====== PRINT ======
window.printPage = () => window.print();

// ====== PASSWORD STRENGTH ======
const pwInput = document.getElementById('pw');
const pwStrength = document.getElementById('pw-strength');
if (pwInput && pwStrength) {
  pwInput.addEventListener('input', () => {
    const len = pwInput.value.length;
    const colors = { Weak: '#dc2626', Good: '#d97706', Strong: '#16a34a' };
    const level = len < 8 ? 'Weak' : len < 12 ? 'Good' : 'Strong';
    pwStrength.textContent = len > 0 ? `Password strength: ${level}` : '';
    pwStrength.style.color = colors[level] || '';
  });
}
