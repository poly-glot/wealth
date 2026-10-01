(() => {
    const navigation = document.querySelector(".site-nav");
    const toggle = navigation?.querySelector(".site-nav__toggle");

    if (!toggle) {
        return;
    }

    const list = navigation.querySelector(".site-nav__list");
    const isOpen = () => toggle.getAttribute("aria-expanded") === "true";
    const setOpen = (open) => toggle.setAttribute("aria-expanded", String(open));

    navigation.classList.add("site-nav--enhanced");
    toggle.hidden = false;

    toggle.addEventListener("click", () => setOpen(!isOpen()));

    list.addEventListener("click", (event) => {
        if (!event.target.closest("a")) {
            return;
        }

        setOpen(false);
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape" || !isOpen()) {
            return;
        }

        setOpen(false);
        toggle.focus();
    });
})();
