/*
Template Name: ShopGrids - Bootstrap 5 eCommerce HTML Template.
Author: GrayGrids
*/

(function () {
    //===== Prealoder

    function hidePreloader() {
        window.setTimeout(fadeout, 500);
    }

    function fadeout() {
        var preloader = document.querySelector('.preloader');

        if (!preloader) {
            return;
        }

        preloader.style.opacity = '0';
        preloader.style.display = 'none';
    }

    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        window.addEventListener('load', hidePreloader);
    }

    document.addEventListener('turbo:load', hidePreloader);

    /*=====================================
    Sticky
    ======================================= */
    window.onscroll = function () {
        var header_navbar = document.querySelector(".navbar-area");
        var backToTo = document.querySelector(".scroll-top");

        if (!header_navbar || !backToTo) {
            return;
        }

        // show or hide the back-top-top button
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            backToTo.style.display = "flex";
        } else {
            backToTo.style.display = "none";
        }
    };

    //===== mobile-menu-btn
    function initNavbarToggler() {
        let navbarToggler = document.querySelector(".mobile-menu-btn");

        if (!navbarToggler || navbarToggler.dataset.clientMenuInitialized === 'true') {
            return;
        }

        navbarToggler.dataset.clientMenuInitialized = 'true';
        navbarToggler.addEventListener('click', function () {
            navbarToggler.classList.toggle("active");
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavbarToggler);
    } else {
        initNavbarToggler();
    }

    document.addEventListener('turbo:load', initNavbarToggler);


})();
