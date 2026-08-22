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

// Alpine.js component: comprobador de codigo postal (carrito y checkout)
const postalChecker = (endpoint, cpInicial = '') => ({
    endpoint,
    cp: cpInicial,
    estado: null,       // null | 'ok' | 'fuera' | 'invalido' | 'error'
    cargando: false,
    gastos: null,       // importe formateado devuelto por el servidor
    gastosValor: null,  // importe numerico, para calcular el total
    zona: null,
    diasReparto: null,  // "jueves", "de lunes a sabado"...
    proximaEntrega: null,
    motivoRetraso: null, // festivo o cierre que ha desplazado la entrega

    init() {
        if (this.cp) {
            this.comprobar();
        }
    },

    async comprobar() {
        if (! /^[0-9]{5}$/.test(this.cp)) {
            this.estado = this.cp ? 'invalido' : null;
            this.gastos = null;
            this.gastosValor = null;
            this.diasReparto = null;
            this.proximaEntrega = null;
            this.motivoRetraso = null;
            return;
        }

        this.cargando = true;

        try {
            const url = `${this.endpoint}?codigo_postal=${encodeURIComponent(this.cp)}`;
            const response = await fetch(url, { headers: { Accept: 'application/json' } });

            if (! response.ok) {
                throw new Error('Respuesta no valida');
            }

            const data = await response.json();

            this.estado = data.cubierto ? 'ok' : 'fuera';
            this.gastos = data.gastos_envio_formateado;
            this.gastosValor = data.gastos_envio;
            this.zona = data.zona;
            this.diasReparto = data.dias_reparto;
            this.proximaEntrega = data.proxima_entrega_texto;
            this.motivoRetraso = data.motivo_retraso;
        } catch (error) {
            this.estado = 'error';
            this.gastos = null;
            this.gastosValor = null;
            this.diasReparto = null;
            this.proximaEntrega = null;
            this.motivoRetraso = null;
        } finally {
            this.cargando = false;
        }
    },
});

Alpine.data('postalChecker', postalChecker);

// Alpine.js component: formulario de checkout (codigo postal + total en vivo)
Alpine.data('checkoutForm', (endpoint, cpInicial = '') => ({
    ...postalChecker(endpoint, cpInicial),
    enviando: false,

    totalFormateado(subtotal) {
        const total = Number(subtotal) + Number(this.gastosValor ?? 0);

        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR',
        }).format(total);
    },
}));

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();
