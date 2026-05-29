if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
}

document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("darkModeToggle");

    if (toggleBtn) {
        // Set initial button text
        if (document.body.classList.contains("dark-mode")) {
            toggleBtn.textContent = '☀️';
        } else {
            toggleBtn.textContent = '🌙';
        }

        toggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "enabled");
                toggleBtn.textContent = '☀️';
            } else {
                localStorage.setItem("darkMode", "disabled");
                toggleBtn.textContent = '🌙';
            }
        });
    }
});