
document.addEventListener("DOMContentLoaded", () => {
    console.log("stock.js loaded and ready.");

    // Attach listeners to all dynamic buttons
    initButtonEffects();
});

function initButtonEffects() {
    document.addEventListener("mousedown", (event) => {
        if (event.target.tagName === "BUTTON" || event.target.classList.contains("btn")) {
            event.target.classList.add("btn-active");
        }
    });

    document.addEventListener("mouseup", (event) => {
        if (event.target.tagName === "BUTTON" || event.target.classList.contains("btn")) {
            event.target.classList.remove("btn-active");
        }
    });
}

