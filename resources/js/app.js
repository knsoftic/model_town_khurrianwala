import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import ajaxForm from './front/forms';
import calculator from './front/calculator';
import gallery from './front/gallery';
import booking from './front/booking';

Alpine.plugin(collapse);

Alpine.data('ajaxForm', ajaxForm);
Alpine.data('calculator', calculator);
Alpine.data('gallery', gallery);
Alpine.data('booking', booking);

Alpine.data('siteHeader', (transparent = false) => ({
    scrolled: false,
    menuOpen: false,
    transparent,

    init() {
        const chrome = document.querySelector('[data-site-chrome]');
        const update = () => (this.scrolled = window.scrollY > (chrome?.offsetHeight ?? 0) + 8);
        update();
        window.addEventListener('scroll', update, { passive: true });
        this.$watch('menuOpen', (open) => document.documentElement.classList.toggle('overflow-hidden', open));
    },

    get solid() {
        return !this.transparent || this.scrolled || this.menuOpen;
    },
}));

Alpine.data('carousel', () => ({
    atStart: true,
    atEnd: false,

    init() {
        this.$nextTick(() => this.update());
    },

    update() {
        const track = this.$refs.track;
        this.atStart = track.scrollLeft <= 4;
        this.atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
    },

    scroll(direction) {
        const track = this.$refs.track;
        const card = track.firstElementChild;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 24;
        track.scrollBy({ left: direction * ((card?.clientWidth ?? 320) + gap), behavior: 'smooth' });
    },
}));

Alpine.data('videoModal', () => ({
    open: false,
    src: '',
    type: 'embed',

    play(src, type = 'embed') {
        this.src = src;
        this.type = type;
        this.open = true;
    },

    close() {
        this.open = false;
        this.src = '';
    },
}));

Alpine.store('callback', {
    open: false,
    plot: null,
    show(plot = null) {
        this.plot = plot;
        this.open = true;
    },
});

/**
 * x-reveal: fades elements in when they scroll into view and sets the CSS
 * variable --reveal used by progress bars. x-count-up animates numbers.
 */
Alpine.directive('reveal', (el, { modifiers }) => {
    el.classList.add('opacity-0');
    el.style.setProperty('--reveal', '0');
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                el.classList.remove('opacity-0');
                el.classList.add(modifiers.includes('plain') ? 'transition-opacity' : 'animate-fade-up');
                el.style.setProperty('--reveal', '1');
                observer.unobserve(el);
            });
        },
        { threshold: 0.15 },
    );
    observer.observe(el);
});

Alpine.directive('count-up', (el) => {
    const target = Number(el.dataset.value ?? el.textContent.replace(/[^\d.]/g, ''));
    if (!Number.isFinite(target) || target <= 0) return;
    const decimals = (String(el.dataset.value).split('.')[1] ?? '').length;
    const format = new Intl.NumberFormat('en-US', { maximumFractionDigits: decimals, minimumFractionDigits: decimals });
    el.textContent = format.format(0);

    const observer = new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting) return;
        observer.disconnect();
        const start = performance.now();
        const duration = 1600;
        const tick = (now) => {
            const progress = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = format.format(target * eased);
            if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
    observer.observe(el);
});

/*
 * Homepage sections below the first, and blocks marked data-defer, start their
 * components only when they come near the screen, so the first load runs a
 * fraction of the JavaScript.
 */
if ('IntersectionObserver' in window) {
    const wake = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                wake.unobserve(entry.target);
                delete entry.target._x_ignore;
                Alpine.initTree(entry.target);
            });
        },
        { rootMargin: '600px 0px' },
    );

    document.querySelectorAll('.home-flow > section:not(:first-child), [data-defer]').forEach((section) => {
        section._x_ignore = true;
        wake.observe(section);
    });
}

window.Alpine = Alpine;
Alpine.start();
