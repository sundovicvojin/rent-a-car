// Mobilni meni: bez biblioteka, radi i bez JavaScript-a kao običan niz linkova.
const toggle = document.querySelector('[data-nav-toggle]');
const menu = document.querySelector('[data-nav-menu]');

if (toggle && menu) {
    const set = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        menu.classList.toggle('hidden', !open);
    };
    toggle.addEventListener('click', () => set(toggle.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            set(false);
            toggle.focus();
        }
    });
}

// Galerija na detalju vozila: klik na sličicu menja glavnu sliku.
document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const main = gallery.querySelector('[data-gallery-main]');
    gallery.querySelectorAll('[data-gallery-thumb]').forEach((btn) => {
        btn.addEventListener('click', () => {
            main.src = btn.dataset.large;
            main.srcset = '';
            main.alt = btn.dataset.alt;
            gallery.querySelectorAll('[data-gallery-thumb]').forEach((b) => b.setAttribute('aria-current', String(b === btn)));
        });
    });
});


// Zaglavlje: senka posle skrola.
const header = document.querySelector('.site-header');
if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

// Otkrivanje elemenata pri skrolu (IntersectionObserver). Preskače se kod smanjenog kretanja.
if (document.documentElement.classList.contains('js-reveal') && 'IntersectionObserver' in window) {
    const items = [...document.querySelectorAll('[data-reveal]')];
    const siblingsSeen = new Map();
    items.forEach((el) => {
        // Stagger: svaki sledeći element istog roditelja kasni 80 ms (najviše 400 ms).
        const n = siblingsSeen.get(el.parentElement) ?? 0;
        siblingsSeen.set(el.parentElement, n + 1);
        el.style.setProperty('--reveal-delay', `${Math.min(n, 5) * 80}ms`);
    });

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                el.classList.add('is-visible');
                io.unobserve(el);
                // Posle otkrivanja skidamo reveal tranziciju da ne usporava hover i druge efekte.
                el.addEventListener('transitionend', function done(e) {
                    if (e.propertyName === 'opacity') {
                        el.removeAttribute('data-reveal');
                        el.style.removeProperty('--reveal-delay');
                        el.removeEventListener('transitionend', done);
                    }
                });
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    items.forEach((el) => io.observe(el));
}
