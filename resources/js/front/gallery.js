/**
 * Gallery with category filters and a keyboard-friendly lightbox that supports zoom.
 * Images are read from the [data-gallery-item] elements inside the component.
 */
export default () => ({
    filter: 'all',
    open: false,
    index: 0,
    zoom: 1,
    items: [],

    init() {
        this.items = [...this.$el.querySelectorAll('[data-gallery-item]')].map((el) => ({
            src: el.dataset.src,
            title: el.dataset.title ?? '',
            caption: el.dataset.caption ?? '',
            album: el.dataset.album ?? '',
        }));
    },

    visible(album) {
        return this.filter === 'all' || this.filter === album;
    },

    get visibleItems() {
        return this.items.map((item, i) => ({ ...item, i })).filter((item) => this.visible(item.album));
    },

    get current() {
        return this.items[this.index] ?? null;
    },

    show(i) {
        this.index = i;
        this.zoom = 1;
        this.open = true;
        document.documentElement.classList.add('overflow-hidden');
    },

    close() {
        this.open = false;
        document.documentElement.classList.remove('overflow-hidden');
    },

    step(direction) {
        const list = this.visibleItems;
        if (!list.length) return;
        const position = list.findIndex((item) => item.i === this.index);
        const next = list[(position + direction + list.length) % list.length];
        this.index = next.i;
        this.zoom = 1;
    },

    toggleZoom() {
        this.zoom = this.zoom > 1 ? 1 : 2;
    },
});
