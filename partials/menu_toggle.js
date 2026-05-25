window.addEventListener("DOMContentLoaded", () => {

    const menuButton = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebarMenu");

    if (!menuButton || !sidebar) {
        return;
    }

    let overlay = document.querySelector(".sidebar-overlay");

    if (!overlay) {

        overlay = document.createElement("div");
        overlay.classList.add("sidebar-overlay");

        document.body.appendChild(overlay);
    }

    function openMenu() {

        sidebar.classList.add("active");
        overlay.classList.add("active");

        document.body.style.overflow = "hidden";
    }

    function closeMenu() {

        sidebar.classList.remove("active");
        overlay.classList.remove("active");

        document.body.style.overflow = "";
    }

    menuButton.addEventListener("click", () => {

        if (sidebar.classList.contains("active")) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    overlay.addEventListener("click", closeMenu);

    window.addEventListener("resize", () => {

        if (window.innerWidth > 991) {
            closeMenu();
        }
    });
});