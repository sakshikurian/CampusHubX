if (localStorage.getItem("darkMode") === "on") {
    document.body.classList.add("dark-mode");
}

document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("darkModeToggle");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "on");
            } else {
                localStorage.setItem("darkMode", "off");
            }
        });
    }
});