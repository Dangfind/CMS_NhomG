/* Shared navigation and presentation-only newsletter. No email is transmitted. */
(function () {
    'use strict';

    var header = document.querySelector('.cmsng-header');
    if (header) {
        var toggle = header.querySelector('.cmsng-menu-toggle');
        var navigation = header.querySelector('.cmsng-header-navigation');
        var mobile = window.matchMedia('(max-width: 1024px)');

        function setOpen(open) {
            toggle.setAttribute('aria-expanded', String(open));
            navigation.hidden = mobile.matches && !open;
        }

        function syncViewport() {
            toggle.hidden = !mobile.matches;
            setOpen(false);
        }

        toggle.addEventListener('click', function () {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });
        header.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && mobile.matches && toggle.getAttribute('aria-expanded') === 'true') {
                setOpen(false);
                toggle.focus();
            }
        });
        document.addEventListener('click', function (event) {
            if (mobile.matches && !header.contains(event.target)) {
                var focusInside = navigation.contains(document.activeElement);
                setOpen(false);
                if (focusInside) toggle.focus();
            }
        });
        if (mobile.addEventListener) mobile.addEventListener('change', syncViewport);
        else mobile.addListener(syncViewport);
        syncViewport();
    }

    document.querySelectorAll('[data-cmsng-newsletter]').forEach(function (form) {
        var status = form.querySelector('[role="status"]');
        var email = form.querySelector('input[type="email"]');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            status.textContent = status.getAttribute('data-unavailable');
            status.hidden = false;
        });
        email.addEventListener('input', function () { status.hidden = true; });
        form.querySelector('button[type="submit"]').disabled = false;
    });
}());
