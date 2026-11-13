/**
 * Karavan — interactions légères.
 * Amélioration progressive : toutes les fonctions du site marchent aussi sans JavaScript.
 * Chaque bloc s'active seulement si les éléments concernés (data-*) sont présents dans la page.
 */
(() => {
    document.documentElement.classList.add('js');

    /* Messages flash : fermeture au clic, disparition auto des succès/infos */
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.flash-close');
        if (btn) btn.closest('.flash').remove();
    });
    setTimeout(() => document.querySelectorAll('.flash-success, .flash-info').forEach((f) => f.remove()), 6000);

    /* Menus déroulants <details> : fermeture au clic extérieur */
    document.addEventListener('click', (e) => {
        document.querySelectorAll('details.dropdown[open]').forEach((d) => {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });

    /* Confirmation avant les actions sensibles : <form data-confirm="Supprimer ?"> */
    document.addEventListener('submit', (e) => {
        const msg = e.target.dataset.confirm;
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    /* Filtres : envoi automatique au changement — <form data-autosubmit> */
    document.querySelectorAll('form[data-autosubmit]').forEach((form) => {
        let timer;
        form.addEventListener('change', (e) => {
            if (e.target.matches('select')) form.requestSubmit();
        });
        form.addEventListener('input', (e) => {
            if (!e.target.matches('input[type="search"]')) return;
            clearTimeout(timer);
            timer = setTimeout(() => form.requestSubmit(), 600);
        });
    });

    /* Onglets accessibles — [data-tabs] > [role=tab][aria-controls] */
    document.querySelectorAll('[data-tabs]').forEach((tabs) => {
        const buttons = tabs.querySelectorAll('[role="tab"]');
        buttons.forEach((btn) => btn.addEventListener('click', () => {
            buttons.forEach((b) => {
                const selected = b === btn;
                b.setAttribute('aria-selected', String(selected));
                document.getElementById(b.getAttribute('aria-controls')).hidden = !selected;
            });
        }));
    });

    /* Total d'une réservation recalculé en direct — <form data-price="12"> + [data-qty] + [data-total] */
    const euros = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
    document.querySelectorAll('form[data-price]').forEach((form) => {
        const price = parseFloat(form.dataset.price);
        const qty = form.querySelector('[data-qty]');
        const total = form.querySelector('[data-total]');
        if (!qty || !total) return;
        const update = () => { total.textContent = euros.format(price * parseInt(qty.value || '1', 10)); };
        qty.addEventListener('change', update);
        update();
    });

    /* Admin : aperçu en direct de la couverture d'un festival */
    const preview = document.querySelector('[data-cover-preview] .cover');
    if (preview) {
        const hue = document.querySelector('[data-hue]');
        const name = document.querySelector('[data-cover-name]');
        const word = preview.querySelector('.cover-word');
        hue?.addEventListener('input', () => preview.style.setProperty('--h', hue.value));
        name?.addEventListener('input', () => { word.textContent = name.value || 'Nom du festival'; });
    }
})();
