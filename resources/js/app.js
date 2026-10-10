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
