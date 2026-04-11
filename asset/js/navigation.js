/* global document */
(function () {
    var openButton = document.getElementById("voir_menu");
    var closeButton = document.getElementById("js_close_mobile_menu");
    var mobileMenu = document.getElementById("mobile_menu");
    var mobileMenuOverlay = document.getElementById("mobile_menu_overlay");

    if (!openButton || !closeButton || !mobileMenu || !mobileMenuOverlay) {
        return;
    }

    function openMenu() {
        mobileMenu.classList.add("active");
        mobileMenuOverlay.classList.add("active");
        document.body.classList.add("mobile-menu-open");
    }

    function closeMenu() {
        mobileMenu.classList.remove("active");
        mobileMenuOverlay.classList.remove("active");
        document.body.classList.remove("mobile-menu-open");
    }

    // On ferme le panneau sur toutes les interactions extérieures utiles en mobile.
    function handleDocumentClick(event) {
        if (!mobileMenu.classList.contains("active")) {
            return;
        }

        if (mobileMenu.contains(event.target) || openButton.contains(event.target)) {
            return;
        }

        closeMenu();
    }

    openButton.addEventListener("click", function (event) {
        event.stopPropagation();
        openMenu();
    });

    closeButton.addEventListener("click", closeMenu);
    mobileMenuOverlay.addEventListener("click", closeMenu);

    mobileMenu.querySelectorAll("a").forEach(function (link) {
        link.addEventListener("click", closeMenu);
    });

    document.addEventListener("click", handleDocumentClick);
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeMenu();
        }
    });
})();
