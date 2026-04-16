// ═══════════════════════════════════════════
// SHARED NAV COMPONENT — Kenya Portal
// Include this script in every page
// ═══════════════════════════════════════════

(function() {
  // ── THEME ──
  const themeBtn = document.getElementById('themeToggle');
  const themeIcon = themeBtn ? themeBtn.querySelector('i') : null;
  const saved = localStorage.getItem('ktp-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  
  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    if (themeIcon) themeIcon.className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('ktp-theme', t);
  }
  applyTheme(saved);
  if (themeBtn) themeBtn.addEventListener('click', () => applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));

  // ── HAMBURGER ──
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');
  
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', (e) => {
      e.stopPropagation();
      hamburger.classList.toggle('open');
      mobileNav.classList.toggle('open');
      document.body.style.overflow = mobileNav.classList.contains('open') ? 'hidden' : '';
    });
    document.addEventListener('click', (e) => {
      if (!mobileNav.contains(e.target) && !hamburger.contains(e.target)) {
        hamburger.classList.remove('open');
        mobileNav.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  }

  // ── MARK ACTIVE NAV ──
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.desk-nav a, .mob-nav-link').forEach(a => {
    if (a.getAttribute('href') === page) a.classList.add('active');
  });

  // ── BACK TO TOP ──
  const bt = document.getElementById('backTop');
  if (bt) {
    window.addEventListener('scroll', () => bt.classList.toggle('show', window.scrollY > 400));
    bt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ── TABS ──
  document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('[data-tab-group]') || document;
      group.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
      group.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const pane = document.getElementById(btn.dataset.tab);
      if (pane) {
        pane.classList.add('active');
        animateBars(pane);
      }
    });
  });

  // ── PROGRESS BARS ──
  function animateBars(ctx) {
    (ctx || document).querySelectorAll('.p-fill[data-w]').forEach(b => {
      b.style.width = b.getAttribute('data-w') + '%';
    });
  }
  window.addEventListener('load', () => setTimeout(() => animateBars(), 200));

  // ── REACTIONS ──
  let liked = false, disliked = false;
  window.react = function(type) {
    const lc = document.getElementById('likeCount'), dc = document.getElementById('dislikeCount');
    const lb = document.getElementById('likeBtn'), db = document.getElementById('dislikeBtn');
    if (!lc || !dc) return;
    if (type === 'like') {
      if (!liked) { lc.textContent = +lc.textContent + 1; lb && lb.classList.add('al'); if (disliked) { dc.textContent = +dc.textContent - 1; db && db.classList.remove('adl'); disliked = false; } liked = true; }
      else { lc.textContent = +lc.textContent - 1; lb && lb.classList.remove('al'); liked = false; }
    } else {
      if (!disliked) { dc.textContent = +dc.textContent + 1; db && db.classList.add('adl'); if (liked) { lc.textContent = +lc.textContent - 1; lb && lb.classList.remove('al'); liked = false; } disliked = true; }
      else { dc.textContent = +dc.textContent - 1; db && db.classList.remove('adl'); disliked = false; }
    }
  };

  // ── COMMENT ──
  window.postComment = function() {
    const txt = document.getElementById('cTxt')?.value.trim();
    const name = document.getElementById('cName')?.value.trim() || 'Anonymous';
    if (!txt) { window.showToast('Please write a comment first', 'error'); return; }
    const el = document.createElement('div'); el.className = 'comment';
    el.innerHTML = `<div class="c-hdr"><div class="c-av">${name[0].toUpperCase()}</div><div><div class="c-name">${name}</div><div class="c-date">Just now</div></div></div><div class="c-body">${txt}</div><button class="clk" onclick="likeComment(this)"><i class="fas fa-thumbs-up"></i> 0</button>`;
    document.getElementById('commentList')?.prepend(el);
    if(document.getElementById('cTxt')) document.getElementById('cTxt').value = '';
    window.showToast('Comment posted!');
  };
  window.likeComment = function(btn) {
    const c = parseInt(btn.innerHTML.split('</i> ')[1] || '0');
    const l = btn.classList.toggle('liked');
    btn.innerHTML = `<i class="fas fa-thumbs-up"></i> ${l ? c + 1 : c - 1}`;
  };

  // ── SHARE ──
  window.doShare = function(p) {
    const u = encodeURIComponent(location.href);
    const t = encodeURIComponent(document.title);
    const m = { twitter: `https://twitter.com/intent/tweet?url=${u}&text=${t}`, facebook: `https://www.facebook.com/sharer/sharer.php?u=${u}`, whatsapp: `https://wa.me/?text=${t}%20${u}`, linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${u}` };
    if (m[p]) window.open(m[p], '_blank', 'width=600,height=400');
  };
  window.copyLink = function() { navigator.clipboard.writeText(location.href).then(() => window.showToast('Link copied!')); };

  // ── TOAST ──
  window.showToast = function(msg, type = 'success') {
    const t = document.getElementById('toast'); if (!t) return;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    t.style.background = type === 'success' ? 'var(--g)' : 'var(--re)';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3800);
  };
})();
