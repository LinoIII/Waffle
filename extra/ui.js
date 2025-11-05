// Modalità dark/light e piccole UX per la dashboard
(function() {
    function setTheme(theme) {
        document.body.setAttribute("data-theme", theme);
        localStorage.setItem("theme", theme);
        document.querySelector(".btn-theme-toggle").innerText = (theme === "dark") ? "☀️ Light" : "🌙 Dark";
    }
    function toggleTheme() {
        const now = document.body.getAttribute("data-theme") === "dark" ? "light" : "dark";
        setTheme(now);
    }
    window.addEventListener("DOMContentLoaded", function() {
        let theme = localStorage.getItem("theme") || "light";
        setTheme(theme);
        document.querySelectorAll(".btn-theme-toggle").forEach(btn => {
            btn.addEventListener("click", toggleTheme);
        });
    });
    // Altre micro-interazioni:
    window.waffleNotify = function(msg, type="info") {
        let d = document.createElement("div");
        d.innerText = msg;
        d.className = "badge badge-"+type;
        d.style.position="fixed"; d.style.top="70px"; d.style.right="30px"; d.style.zIndex=3000;
        d.style.fontSize="1.08em"; d.style.padding="10px 18px"; d.style.minWidth="80px";
        d.style.opacity = 0.97;
        document.body.append(d);
        setTimeout(()=>d.remove(), 2000);
    }
})();