/**
 * Espace organisateur — comportements de l'éditeur de festival.
 *   [data-repeater]        listes de lignes ajoutables (paragraphes, créneaux)
 *   [data-design-form]     aperçu en direct de la palette et des polices
 *   [data-image-input]     aperçu des images avant envoi
 */
(() => {
    'use strict';

    /* ---------- Répéteur : <template> contenant __INDEX__ ---------- */
    document.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const list = repeater.querySelector('[data-repeater-list]');
        const template = repeater.querySelector('[data-repeater-template]');
        const addBtn = repeater.querySelector('[data-repeater-add]');
        const max = Number(repeater.dataset.max || Infinity);
        const copyFields = (repeater.dataset.copyLast || '').split(',').filter(Boolean);
        let next = Date.now(); // index unique : l'ordre réel est celui du DOM

        const rows = () => list.querySelectorAll(':scope > [data-repeater-row]');
        const refresh = () => {
            if (addBtn) addBtn.disabled = rows().length >= max;
            const empty = repeater.querySelector('[data-repeater-empty]');
            if (empty) empty.hidden = rows().length > 0;
        };

        addBtn?.addEventListener('click', () => {
            if (rows().length >= max) return;
            const html = template.innerHTML.replaceAll('__INDEX__', String(next++));
            const holder = document.createElement('div');
            holder.innerHTML = html.trim();
            const row = holder.firstElementChild;
            // Reprend la journée et la scène de la ligne précédente : saisie plus rapide
            const last = rows()[rows().length - 1];
            copyFields.forEach((name) => {
                const from = last?.querySelector(`[data-copy="${name}"]`);
                const to = row.querySelector(`[data-copy="${name}"]`);
                if (from && to) to.value = from.value;
            });
            list.appendChild(row);
            refresh();
            row.querySelector('input:not([type=hidden]), textarea, select')?.focus();
        });

        list.addEventListener('click', (e) => {
            const row = e.target.closest('[data-repeater-row]');
            if (!row) return;
            if (e.target.closest('[data-repeater-remove]')) {
                row.remove();
                refresh();
            } else if (e.target.closest('[data-repeater-up]') && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            } else if (e.target.closest('[data-repeater-down]') && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
            }
        });
        refresh();
    });

    /* ---------- Aperçu de l'apparence ---------- */
    const form = document.querySelector('[data-design-form]');
    if (form) {
        const preview = form.querySelector('[data-design-preview]');
        const modes = JSON.parse(preview.dataset.modes);
        const custom = form.querySelector('[data-custom-colors]');

        const readable = (hex) => {
            const [r, g, b] = [1, 3, 5].map((i) => {
                const c = parseInt(hex.slice(i, i + 2), 16) / 255;
                return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
            });
            return 0.2126 * r + 0.7152 * g + 0.0722 * b > 0.36 ? '#17132b' : '#ffffff';
        };

        const update = () => {
            const chosen = form.querySelector('input[name=palette]:checked');
            const isCustom = chosen?.value === 'custom';
            custom.hidden = !isCustom;
            const primary = isCustom ? form.elements.primary.value : chosen.dataset.primary;
            const secondary = isCustom ? form.elements.secondary.value : chosen.dataset.secondary;
            const mode = modes[isCustom ? form.elements.mode.value : chosen.dataset.mode] || modes.light;
            const heading = form.elements.heading_font.value;
            const body = form.elements.body_font.value;

            Object.entries({
                '--color-primary': primary,
                '--color-on-primary': readable(primary),
                '--color-accent': secondary,
                '--color-bg': mode.bg,
                '--color-surface': mode.surface,
                '--color-text': mode.text,
                '--color-muted': mode.muted,
                '--color-line': mode.line,
                '--font-display': `"${heading}", sans-serif`,
                '--font-body': `"${body}", sans-serif`,
            }).forEach(([k, v]) => preview.style.setProperty(k, v));

            const hue = form.querySelector('[data-hue-input]');
            preview.querySelectorAll('.cover').forEach((c) => c.style.setProperty('--h', hue.value));
        };

        form.addEventListener('input', update);
        form.addEventListener('change', update);
        update();
    }

    /* ---------- Aperçu des images choisies ---------- */
    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const files = [...input.files].filter((f) => f.type.startsWith('image/'));
            if (input.dataset.multiplePreview) {
                const target = document.querySelector(input.dataset.multiplePreview);
                target.innerHTML = '';
                files.forEach((file) => {
                    const li = document.createElement('li');
                    li.className = 'photo-item is-new';
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = '';
                    const label = document.createElement('span');
                    label.textContent = 'Nouvelle — enregistrez pour l\'ajouter';
                    li.append(img, label);
                    target.appendChild(li);
                });
                document.querySelector('[data-photos-empty]')?.remove();
                return;
            }
            const box = input.closest('.media-card')?.querySelector('[data-image-preview]');
            if (box && files[0]) {
                box.innerHTML = '';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(files[0]);
                img.alt = 'Nouvelle image (non enregistrée)';
                box.appendChild(img);
                box.classList.add('is-new');
            }
        });
    });
})();
