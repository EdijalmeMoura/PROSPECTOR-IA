// Confirmações acessíveis e submissão de formulários de progresso.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('submit', (e) => {
            if (!window.confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
    document.querySelectorAll('[data-disable-on-submit]').forEach((form) => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('[type=submit]');
            if (btn) { btn.disabled = true; btn.dataset.originalText = btn.textContent; btn.textContent = 'Processando...'; }
        });
    });
});
