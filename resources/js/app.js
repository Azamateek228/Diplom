import './bootstrap';

const dismissToast = (toast) => {
    toast.classList.add('is-hiding');
    window.setTimeout(() => toast.remove(), 220);
};

document.querySelectorAll('[data-toast]').forEach((toast) => {
    const closeButton = toast.querySelector('[data-toast-close]');

    closeButton?.addEventListener('click', () => dismissToast(toast));
    window.setTimeout(() => dismissToast(toast), 5200);
});

document.querySelectorAll('form.vote-form').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"], .vote-btn');

        if (! button || button.disabled) {
            return;
        }

        button.dataset.originalText = button.textContent.trim();
        button.textContent = 'Отправляем голос…';
        button.disabled = true;
        button.classList.add('is-loading');
    });
});

document.querySelectorAll('[data-card-number]').forEach((input) => {
    input.addEventListener('input', () => {
        const digits = input.value.replace(/\D/g, '').slice(0, 19);
        input.value = digits.replace(/(.{4})/g, '$1 ').trim();
    });
});

document.querySelectorAll('[data-card-expiry]').forEach((input) => {
    input.addEventListener('input', () => {
        const digits = input.value.replace(/\D/g, '').slice(0, 6);
        const month = digits.slice(0, 2);
        const year = digits.slice(2);
        input.value = year ? `${month}/${year}` : month;
    });
});

document.querySelectorAll('[data-card-cvv]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 4);
    });
});

document.querySelectorAll('[data-ticket-quantity]').forEach((input) => {
    const totalTarget = document.querySelector(input.dataset.totalTarget);
    const unitPrice = Number(input.dataset.unitPrice || 0);

    const updateTotal = () => {
        if (! totalTarget) {
            return;
        }

        totalTarget.textContent = `${Number(input.value || 0) * unitPrice} ₽`;
    };

    input.addEventListener('input', updateTotal);
    updateTotal();
});

const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (mobileMenuToggle && mobileMenu) {
    const setMenuOpen = (isOpen) => {
        document.body.classList.toggle('mobile-menu-open', isOpen);
        mobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
        mobileMenuToggle.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
    };

    mobileMenuToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        setMenuOpen(! document.body.classList.contains('mobile-menu-open'));
    });

    mobileMenu.addEventListener('click', (event) => {
        if (event.target.closest('a, button[type="submit"]')) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (! document.body.classList.contains('mobile-menu-open')) {
            return;
        }

        if (! mobileMenu.contains(event.target) && ! mobileMenuToggle.contains(event.target)) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
        }
    });
}

document.querySelectorAll('.movie-admin-details-toggle[open]').forEach((details) => {
    const closeOnMobile = () => {
        if (window.matchMedia('(max-width: 720px)').matches) {
            details.removeAttribute('open');
        } else {
            details.setAttribute('open', 'open');
        }
    };

    closeOnMobile();
    window.addEventListener('resize', closeOnMobile, { passive: true });
});
