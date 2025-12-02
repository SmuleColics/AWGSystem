document.addEventListener("DOMContentLoaded", function () {
  // --- Scroll Animation (adds .animated when in view) ---
  const animatedItems = document.querySelectorAll('.animate-on-scroll, .animate-left-on-scroll, .animate-right-on-scroll');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
      }
    });
  }, { threshold: 0.2 });
  animatedItems.forEach(item => observer.observe(item));

  // --- Navbar Active Section Tracking ---
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  const sections = Array.from(navLinks)
    .map(link => {
      const section = document.querySelector(link.getAttribute('href'));
      return section;
    })
    .filter(Boolean);

  function activateNav() {
    let currentSectionIdx = 0;
    let scrollPosition = window.scrollY + 100; // offset for fixed header (may need adjustment)
    sections.forEach((section, idx) => {
      // This offset handles sections slightly above/below the header for the active state
      if (section.offsetTop - 120 <= scrollPosition) {
        currentSectionIdx = idx;
      }
    });

    navLinks.forEach((link, idx) => {
      if (idx === currentSectionIdx) {
        link.classList.add('green-text');
      } else {
        link.classList.remove('green-text');
      }
    });
  }

  window.addEventListener('scroll', activateNav);
  window.addEventListener('resize', activateNav);
  activateNav(); // initialize on load
});