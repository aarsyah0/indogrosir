import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
Chart.defaults.animation.duration = motionPreference.matches ? 0 : 250;
motionPreference.addEventListener?.('change', event => {
    Chart.defaults.animation.duration = event.matches ? 0 : 250;
});

Alpine.data('searchableSelect', ({ options = [], value = '', required = false } = {}) => ({
    options,
    selected: value === null || value === undefined ? '' : String(value),
    query: '',
    open: false,
    activeIndex: 0,
    menuTop: 0,
    menuLeft: 8,
    menuWidth: 240,
    required,
    get filteredOptions() {
        const query = this.query.trim().toLocaleLowerCase('id-ID');
        if (!query) return this.options;
        return this.options.filter(option => option.label.toLocaleLowerCase('id-ID').includes(query));
    },
    get selectedLabel() {
        return this.options.find(option => String(option.value) === this.selected)?.label ?? '';
    },
    init() {
        this.$watch('selected', () => this.validate());
        this.$watch('open', open => {
            if (open) this.$nextTick(() => this.positionMenu());
        });
        this.$nextTick(() => this.validate());
    },
    positionMenu() {
        if (!this.open || !this.$refs.search) return;
        const rect = this.$refs.search.getBoundingClientRect();
        const width = Math.min(rect.width, window.innerWidth - 16);
        const maxTop = window.innerHeight - 264;
        this.menuTop = rect.bottom + 4 <= maxTop
            ? rect.bottom + 4
            : Math.max(8, rect.top - 264);
        this.menuLeft = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8));
        this.menuWidth = width;
    },
    startSearch() {
        this.query = '';
        this.open = true;
        this.activeIndex = 0;
    },
    search(event) {
        this.query = event.target.value;
        this.selected = '';
        this.open = true;
        this.activeIndex = 0;
        this.$dispatch('searchable-change', { value: '' });
    },
    toggle() {
        if (this.open) {
            this.close();
            return;
        }
        this.startSearch();
        this.$nextTick(() => this.$refs.search.focus());
    },
    choose(option) {
        this.selected = option.value;
        this.query = '';
        this.open = false;
        this.validate();
        this.$dispatch('searchable-change', { value: this.selected });
    },
    clear() {
        this.selected = '';
        this.query = '';
        this.open = false;
        this.validate();
        this.$dispatch('searchable-change', { value: '' });
    },
    close() {
        this.open = false;
        this.query = '';
        this.validate();
    },
    moveActive(direction) {
        this.open = true;
        const count = this.filteredOptions.length;
        if (!count) return;
        this.activeIndex = (this.activeIndex + direction + count) % count;
    },
    chooseActive() {
        if (!this.open) {
            this.startSearch();
            return;
        }
        const option = this.filteredOptions[this.activeIndex];
        if (option) this.choose(option);
    },
    validate() {
        if (this.$refs.search) {
            this.$refs.search.setCustomValidity(this.required && !this.selected ? 'Pilih satu opsi dari daftar.' : '');
        }
    },
}));

Alpine.start();
