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


document.querySelectorAll('[data-nav-toggle]').forEach((toggle) => {
    const nav = toggle.closest('[data-app-nav]');
    const links = nav?.querySelector('[data-nav-links]');

    toggle.addEventListener('click', () => {
        const isOpen = nav?.classList.toggle('is-open') ?? false;
        toggle.setAttribute('aria-expanded', String(isOpen));
        links?.classList.toggle('is-open', isOpen);
    });
});
