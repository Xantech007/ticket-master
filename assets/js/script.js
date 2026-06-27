document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.getElementById("menuToggle");
    const mobileMenu = document.getElementById("mobileMenu");

    if (!menuToggle || !mobileMenu) {
        console.log("Menu elements not found");
        return;
    }

    menuToggle.addEventListener("click", () => {
        mobileMenu.classList.toggle("active");

        const expanded = menuToggle.getAttribute("aria-expanded") === "true";
        menuToggle.setAttribute("aria-expanded", !expanded);
    });
});
