(function () {
    var sidebar = document.getElementById('sidebar');
    var btn = document.getElementById('menuBtn');
    if (sidebar && btn) {
        btn.addEventListener('click', function () { sidebar.classList.toggle('open'); });
        document.querySelector('.main').addEventListener('click', function () { sidebar.classList.remove('open'); });
    }

    /* ---- search + select filters + tabs ---- */
    var form = document.querySelector('.filters');
    var seg = document.querySelector('.seg');
    var segVal = '';
    var q = form ? form.querySelector('input[type=search]') : null;
    var selects = form ? [].slice.call(form.querySelectorAll('select[data-attr]')) : [];
    var clear = form ? form.querySelector('.btn-ghost') : null;
    var empty = document.getElementById('emptyRow');

    function rows() { return [].slice.call(document.querySelectorAll('tbody tr[data-search]')); }

    function run() {
        if (!form && !seg) return;
        var t = q ? q.value.trim().toLowerCase() : '';
        var shown = 0;
        rows().forEach(function (r) {
            var ok = !t || r.dataset.search.indexOf(t) > -1;
            if (ok && seg && segVal) ok = r.dataset[seg.dataset.attr] === segVal;
            selects.forEach(function (s) { if (ok && s.value) ok = r.dataset[s.dataset.attr] === s.value; });
            r.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        if (empty) empty.style.display = shown ? 'none' : '';
        if (clear) {
            var active = t || selects.some(function (s) { return s.value; });
            clear.style.display = active ? '' : 'none';
        }
    }
    if (q) q.addEventListener('input', run);
    selects.forEach(function (s) { s.addEventListener('change', run); });
    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); run(); });
    if (clear) clear.addEventListener('click', function () {
        q.value = ''; selects.forEach(function (s) { s.value = ''; }); run();
    });
    if (seg) seg.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        [].forEach.call(seg.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
        b.classList.add('active'); segVal = b.dataset.val; run();
    });

    /* ---- suspend / activate (Users page) ---- */
    function recount() {
        if (!document.getElementById('tot-farmer')) return;
        var all = rows();
        ['farmer', 'buyer'].forEach(function (role) {
            var mine = all.filter(function (r) { return r.dataset.role === role; });
            function set(id, text) { var el = document.getElementById(id + '-' + role); if (el) el.textContent = text; }
            set('tot', mine.length);
            set('sus', mine.filter(function (r) { return r.dataset.status === 'suspended'; }).length + ' suspended');
            set('today', mine.filter(function (r) { return r.dataset.today === '1'; }).length);
            set('on', mine.filter(function (r) { return r.dataset.online === '1'; }).length + ' online now');
        });
        var p = document.querySelector('.topbar p');
        if (p) p.textContent = all.length + ' farmers and buyers';
    }

    /* ---- approve / reject (Approvals page) ---- */
    function setPending(n) {
        [].forEach.call(document.querySelectorAll('.js-pending'), function (el) {
            el.textContent = n;
            if (el.classList.contains('count')) el.style.display = n ? '' : 'none';
        });
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-act]'); if (!b) return;
        var row = b.closest('tr'); if (!row) return;
        var act = b.dataset.act;

        if (act === 'toggle') {
            var suspend = row.dataset.status === 'active';
            row.dataset.status = suspend ? 'suspended' : 'active';
            row.querySelector('.st').innerHTML = '<span class="badge badge-' + (suspend ? 'bad' : 'ok') + '">' + (suspend ? 'Suspended' : 'Active') + '</span>';
            b.textContent = suspend ? 'Activate' : 'Suspend';
            b.className = 'btn btn-sm' + (suspend ? '' : ' btn-warn');
            recount(); run();
        }

        if (act === 'delete') {
            if (!window.confirm('Delete ' + row.dataset.name + '\'s account? This cannot be undone.')) return;
            row.remove();
            recount(); run();
        }

        if (act === 'approve' || act === 'reject') {
            if (act === 'reject' && !window.confirm('Reject this registration?')) return;
            row.remove();
            setPending(rows().length);
            run();
            if (!rows().length && empty) empty.style.display = '';
        }
    });

    run();
})();