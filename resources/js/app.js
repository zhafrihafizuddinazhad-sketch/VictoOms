import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const initializeVictoInteractions = () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (document.body.classList.contains('dashboard-page') && !reducedMotion && 'IntersectionObserver' in window) {
        const revealTargets = document.querySelectorAll('.content-header, .content .small-box, .content .card, .content .alert, .content .dev-hero, .content .card .border-bottom.p-3, .content table tbody tr');
        if (revealTargets.length) {
            document.body.classList.add('dashboard-reveal-ready');
            revealTargets.forEach((element, index) => {
                element.classList.add('dashboard-reveal');
                element.style.setProperty('--dashboard-reveal-delay', `${(index % 5) * 55}ms`);
            });

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.06, rootMargin: '0px 0px -4% 0px' });
            revealTargets.forEach((element) => revealObserver.observe(element));
        }
    }

    document.querySelectorAll('.victo-toast').forEach((toast) => {
        document.body.append(toast);
        if (toast.classList.contains('victo-toast--success')) {
            window.setTimeout(() => {
                if (toast.isConnected) toast.remove();
            }, 6500);
        }
    });

    document.querySelectorAll('[data-count-up]').forEach((element) => {
        const target = Number(element.dataset.countUp);
        if (!Number.isFinite(target) || target < 0) return;
        if (reducedMotion || !('IntersectionObserver' in window)) {
            element.textContent = new Intl.NumberFormat().format(target);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            observer.disconnect();
            const duration = 650;
            const started = performance.now();
            const tick = (now) => {
                const progress = Math.min((now - started) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                element.textContent = new Intl.NumberFormat().format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }, { threshold: 0.35 });
        observer.observe(element);
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            if (form.dataset.loading === 'off' || form.dataset.submitting === 'true') {
                if (form.dataset.submitting === 'true') event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');
            const button = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.dataset.originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1" aria-hidden="true"></i><span>Processing…</span>';
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeVictoInteractions, { once: true });
} else {
    initializeVictoInteractions();
}
