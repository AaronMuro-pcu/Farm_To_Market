/* FarmLinked admin: one small script for every page.
   Save as: assets/js/admin.js
   (replaces admin.js, users.js, approvals.js, activity.js and reports.js) */
(function () {
    // Mobile menu
    var sidebar = document.getElementById('sidebar');
    var btn = document.getElementById('menuBtn');
    if (sidebar && btn) {
        btn.addEventListener('click', function () { sidebar.classList.toggle('open'); });
        var main = document.querySelector('.main');
        if (main) main.addEventListener('click', function () { sidebar.classList.remove('open'); });
    }

    // "Are you sure?" on buttons such as Delete and Reject
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    // Dropdown filters apply as soon as you change them
    var selects = document.querySelectorAll('select[data-autosubmit]');
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function () { this.form.submit(); });
    }
})();
