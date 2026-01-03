(function () {
  function buildButton(id) {
    const btn = document.createElement('button');
    btn.className = 'menu-toggle';
    btn.type = 'button';
    btn.setAttribute('aria-controls', id);
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Toggle navigation menu');

    // simple accessible icon (3 bars)
    btn.innerHTML = '<span class="bar" aria-hidden="true"></span>' +
                    '<span class="bar" aria-hidden="true"></span>' +
                    '<span class="bar" aria-hidden="true"></span>';
    return btn;
  }

  function closeAllExcept(navToKeepOpen) {
    document.querySelectorAll('header nav.mobile-nav.open').forEach(nav => {
      if (nav !== navToKeepOpen) {
        nav.classList.remove('open');
        nav.setAttribute('aria-hidden', 'true');
        const ctrl = document.querySelector(
          'button.menu-toggle[aria-controls="' + nav.id + '"]'
        );
        if (ctrl) ctrl.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function setupHeaderNavs() {
    document.querySelectorAll('header').forEach((header, i) => {
      const nav = header.querySelector('nav');
      if (!nav) return;

      // Avoid touching administrative sidebars or secondary navs:
      // only work on navs that are direct children or part of the header container.
      // (You can refine selector if needed)
      // Give nav an id if it doesn't have one
      if (!nav.id) nav.id = 'mobile-nav-' + i;

      // Add mobile-nav class to enable our CSS behavior
      nav.classList.add('mobile-nav');
      nav.setAttribute('aria-hidden', 'true');

      // If a toggle button already exists inside header, reuse it
      let toggle = header.querySelector('.menu-toggle');
      if (!toggle) {
        // Insert the button before nav where space makes sense.
        toggle = buildButton(nav.id);
        // Prefer placing toggle at start of header's .header-container or nav's parent
        const container = header.querySelector('.header-container, .nav-container') || header;
        container.insertBefore(toggle, nav);
      } else {
        // ensure it points to this nav
        toggle.setAttribute('aria-controls', nav.id);
        toggle.setAttribute('aria-expanded', 'false');
      }

      // Toggle behavior
      toggle.addEventListener('click', function (e) {
        const isOpen = nav.classList.toggle('open');
        nav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        // close other open menus
        if (isOpen) closeAllExcept(nav);
      });

      // Close menu when a link is clicked (useful for single-page or anchor links)
      nav.addEventListener('click', function (event) {
        const target = event.target;
        if (target && target.tagName === 'A') {
          nav.classList.remove('open');
          nav.setAttribute('aria-hidden', 'true');
          const ctrl = header.querySelector('.menu-toggle');
          if (ctrl) ctrl.setAttribute('aria-expanded', 'false');
        }
      });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function (e) {
      const clickedInsideHeader = !!e.target.closest('header');
      if (!clickedInsideHeader) {
        closeAllExcept(null);
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        closeAllExcept(null);
      }
    });

    // Optional: close on window resize up to desktop to avoid stuck open states
    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) {
        // ensure all mobile menus are closed on desktop
        closeAllExcept(null);
      }
    });
  }

  // Run on DOM ready (defer script also works)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupHeaderNavs);
  } else {
    setupHeaderNavs();
  }
})();
