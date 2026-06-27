const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");

if (!menuToggle || !mobileMenu) {
    console.log("Menu elements not found");
} else {
    menuToggle.addEventListener("click", () => {
        mobileMenu.classList.toggle("active");

        menuToggle.setAttribute(
            "aria-expanded",
            mobileMenu.classList.contains("active")
        );
    });
}
