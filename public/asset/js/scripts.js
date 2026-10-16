/*!
    * Start Bootstrap - SB Admin Pro v2.0.1 (https://shop.startbootstrap.com/product/sb-admin-pro)
    * Copyright 2013-2021 Start Bootstrap
    * Licensed under SEE_LICENSE (https://github.com/StartBootstrap/sb-admin-pro/blob/master/LICENSE)
    */
    window.addEventListener('DOMContentLoaded', event => {
    // Activate feather
    feather.replace();

    // Enable tooltips globally
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enable popovers globally
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Activate Bootstrap scrollspy for the sticky nav component
    const stickyNav = document.body.querySelector('#stickyNav');
    if (stickyNav) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#stickyNav',
            offset: 82,
        });
    }

    // Toggle the side navigation
    // Desktop: sidenav-toggled = icon-only mini rail (see layout-sidenav.css)
    // Mobile: sidenav-toggled = drawer open (SB Admin default)
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Restore preferred desktop mini state
        if (window.innerWidth >= 992 && localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sidenav-toggled');
        }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sidenav-toggled');
            if (window.innerWidth >= 992) {
                localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sidenav-toggled'));
            }
        });
        sidebarToggle.setAttribute('title', 'Réduire / étendre le menu');
    }

    // Desktop mini mode: clicking a collapse parent expands the rail so labels appear
    document.querySelectorAll('#layoutSidenav_nav .nav-link[data-bs-toggle="collapse"]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth >= 992 && document.body.classList.contains('sidenav-toggled')) {
                document.body.classList.remove('sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', 'false');
            }
        });
    });

    // Close side navigation when width < LG (mobile overlay only)
    const sidenavContent = document.body.querySelector('#layoutSidenav_content');
    if (sidenavContent) {
        sidenavContent.addEventListener('click', event => {
            if (window.innerWidth >= 992) {
                return;
            }
            if (document.body.classList.contains("sidenav-toggled")) {
                document.body.classList.remove("sidenav-toggled");
            }
        });
    }

    // Add active state to sidbar nav links
    let activatedPath = window.location.pathname.match(/([\w-]+\.html)/, '$1');

    if (activatedPath) {
        activatedPath = activatedPath[0];
    } else {
        activatedPath = 'index.html';
    }

    const targetAnchors = document.body.querySelectorAll('[href="' + activatedPath + '"].nav-link');

    targetAnchors.forEach(targetAnchor => {
        let parentNode = targetAnchor.parentNode;
        while (parentNode !== null && parentNode !== document.documentElement) {
            if (parentNode.classList.contains('collapse')) {
                parentNode.classList.add('show');
                const parentNavLink = document.body.querySelector(
                    '[data-bs-target="#' + parentNode.id + '"]'
                );
                parentNavLink.classList.remove('collapsed');
                parentNavLink.classList.add('active');
            }
            parentNode = parentNode.parentNode;
        }
        targetAnchor.classList.add('active');
    });
});
