import './bootstrap';
import Alpine from 'alpinejs';

// Alpine.js global store for notifications
Alpine.store('notifications', {
    items: [],

    add(message, type = 'info', duration = 4000) {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });

        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }
    },

    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
    },

    success(message, duration = 4000) {
        this.add(message, 'success', duration);
    },

    error(message, duration = 5000) {
        this.add(message, 'error', duration);
    },

    warning(message, duration = 4000) {
        this.add(message, 'warning', duration);
    },
});

// Alpine.js global store for UI state
Alpine.store('ui', {
    sidebarOpen: false,
    darkMode: localStorage.getItem('darkMode') === 'true',

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        document.documentElement.classList.toggle('dark', this.darkMode);
    },

    init() {
        document.documentElement.classList.toggle('dark', this.darkMode);
    },
});

// Alpine.js component: Dropdown
Alpine.data('dropdown', (defaultOpen = false) => ({
    open: defaultOpen,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
}));

// Alpine.js component: Modal
Alpine.data('modal', (defaultOpen = false) => ({
    open: defaultOpen,

    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },

    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },
}));

// Alpine.js component: Form with loading state
Alpine.data('asyncForm', () => ({
    loading: false,
    success: false,
    errors: {},

    async submit(event) {
        this.loading = true;
        this.errors = {};
        this.success = false;

        try {
            const form = event.target;
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                this.success = true;
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else if (response.status === 422) {
                this.errors = data.errors || {};
            }
        } catch (error) {
            console.error('Form submission error:', error);
        } finally {
            this.loading = false;
        }
    },
}));

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();
