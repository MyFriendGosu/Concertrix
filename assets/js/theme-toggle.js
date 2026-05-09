// theme-toggle.js

document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("themeToggle");

    // Load saved theme
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
        document.body.classList.add(savedTheme);
    } else {
        document.body.classList.add("light"); // default theme
    }

    // Toggle theme
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (document.body.classList.contains("dark")) {
                document.body.classList.remove("dark");
                document.body.classList.add("light");
                localStorage.setItem("theme", "light");
            } else {
                document.body.classList.remove("light");
                document.body.classList.add("dark");
                localStorage.setItem("theme", "dark");
            }
        });
    }
});