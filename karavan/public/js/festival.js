/**
 * Page d'un festival : carrousel, journées du calendrier, vue liste et infobulle des créneaux.
 */
(() => {
    'use strict';

    /* ---------- Carrousel ---------- */
    const carousel = document.querySelector('[data-carousel]');
    if (carousel) {
        const step = (dir) => {
            const item = carousel.querySelector('li');
            const width = item ? item.getBoundingClientRect().width + 12 : carousel.clientWidth;
            const atEnd = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 4;
            if (dir > 0 && atEnd) carousel.scrollTo({ left: 0 });
            else if (dir < 0 && carousel.scrollLeft <= 4) carousel.scrollTo({ left: carousel.scrollWidth });
            else carousel.scrollBy({ left: dir * width });
        };
        document.querySelector('[data-carousel-prev]')?.addEventListener('click', () => step(-1));
        document.querySelector('[data-carousel-next]')?.addEventListener('click', () => step(1));
        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); step(-1); }
        });
    }

    const timetable = document.querySelector('[data-timetable]');
    if (!timetable) return;

    /* ---------- Journées (onglets, flèches du clavier) ---------- */
    const tabs = [...timetable.querySelectorAll('.tt-days [role="tab"]')];
    const select = (tab) => {
        tabs.forEach((t) => {
            const on = t === tab;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
            document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
        });
        hideTip();
    };
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', (e) => {
            const next = e.key === 'ArrowRight' ? tabs[(i + 1) % tabs.length] : e.key === 'ArrowLeft' ? tabs[(i - 1 + tabs.length) % tabs.length] : null;
            if (next) { e.preventDefault(); next.focus(); select(next); }
        });
    });

    /* ---------- Calendrier / liste (choix mémorisé dans ce navigateur) ---------- */
    const viewButtons = document.querySelectorAll('[data-tt-view]');
    const setView = (view) => {
        timetable.classList.toggle('is-list', view === 'list');
        viewButtons.forEach((b) => {
            const on = b.dataset.ttView === view;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-pressed', String(on));
        });
        hideTip();
    };
    viewButtons.forEach((b) => b.addEventListener('click', () => {
        setView(b.dataset.ttView);
        try { localStorage.setItem('karavan.timetable', b.dataset.ttView); } catch (e) { /* stockage indisponible */ }
    }));
    try {
        const saved = localStorage.getItem('karavan.timetable');
        if (saved) setView(saved);
    } catch (e) { /* stockage indisponible */ }

    /* ---------- Infobulle des créneaux ---------- */
    const tip = document.getElementById('tt-tip');
    let current = null;

    const row = (label, value) => {
        const dt = document.createElement('dt');
        dt.textContent = label;
        const dd = document.createElement('dd');
        dd.textContent = value;
        return [dt, dd];
    };

    function showTip(slot) {
        if (current === slot) return;
        current?.classList.remove('is-active');
        current = slot;
        slot.classList.add('is-active');

        const d = slot.dataset;
        const title = document.createElement('strong');
        title.textContent = d.artist;
        const list = document.createElement('dl');
        list.append(...row('Scène', d.stage), ...row('Jour', d.day), ...row('Horaire', d.time), ...row('Durée', d.duration));
        if (d.capacity) list.append(...row('Capacité', `${d.capacity} personnes`));  // masquée si non renseignée
        tip.replaceChildren(title, list);
        tip.hidden = false;
        slot.setAttribute('aria-describedby', 'tt-tip');
        place(slot);
    }

    // Placement : au-dessus du créneau, ou en dessous s'il n'y a pas la place
    function place(slot) {
        const r = slot.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) { hideTip(); return; }
        const t = tip.getBoundingClientRect();
        const margin = 12;
        const centre = Math.min(Math.max(r.left + r.width / 2, margin), window.innerWidth - margin);
        const left = Math.min(Math.max(centre - t.width / 2, margin), window.innerWidth - t.width - margin);
        const above = r.top - t.height - 12 > 8;
        const top = above ? r.top - t.height - 12 : Math.min(r.bottom + 12, window.innerHeight - t.height - 8);
        tip.style.left = `${left}px`;
        tip.style.top = `${Math.max(8, top)}px`;
        tip.style.setProperty('--arrow-x', `${Math.min(Math.max(centre - left, 16), t.width - 16)}px`);
        tip.classList.toggle('is-above', above);
        tip.classList.toggle('is-below', !above);
    }

    function hideTip() {
        if (!current) return;
        current.classList.remove('is-active');
        current.removeAttribute('aria-describedby');
        current = null;
        tip.hidden = true;
    }

    timetable.addEventListener('pointerover', (e) => {
        const slot = e.target.closest('.tt-slot');
        if (slot && e.pointerType === 'mouse') showTip(slot);
    });
    timetable.addEventListener('pointerout', (e) => {
        const slot = e.target.closest('.tt-slot');
        if (slot && e.pointerType === 'mouse' && !slot.contains(e.relatedTarget)) hideTip();
    });
    timetable.addEventListener('focusin', (e) => {
        const slot = e.target.closest('.tt-slot');
        slot ? showTip(slot) : hideTip();
    });
    timetable.addEventListener('focusout', (e) => {
        if (!e.relatedTarget?.closest?.('.tt-slot')) hideTip();
    });
    // Écran tactile : un toucher affiche, un second toucher (ou ailleurs) masque
    timetable.addEventListener('click', (e) => {
        const slot = e.target.closest('.tt-slot');
        if (!slot) return;
        if (current === slot && document.activeElement !== slot) hideTip();
        else showTip(slot);
    });
    document.addEventListener('pointerdown', (e) => { if (!e.target.closest('.tt-slot')) hideTip(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideTip(); });
    // Au défilement, l'infobulle suit son créneau
    const follow = () => { if (current) place(current); };
    window.addEventListener('scroll', follow, { passive: true });
    window.addEventListener('resize', follow);
    timetable.querySelectorAll('.tt-scroll').forEach((s) => s.addEventListener('scroll', follow, { passive: true }));
})();
