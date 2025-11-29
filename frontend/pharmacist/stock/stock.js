
document.addEventListener("DOMContentLoaded", () => {
    console.log("stock.js loaded and ready.");

    // Attach listeners to all dynamic buttons
    initViewHistoryButtons();
    initButtonEffects();
});


function initViewHistoryButtons() {
    document.addEventListener("click", (event) => {
        const target = event.target;

        // Check if clicked element is a history button
        if (target.classList.contains("view-history")) {
            const medID = target.dataset.id;

            if (!medID) {
                console.warn("No medication ID found for this history button.");
                return;
            }

            // Placeholder behavior (to be replaced with AJAX or modal)
            alert(`📜 View history for medication #${medID} is not implemented yet.`);

            // DEBUG
            console.log("History button clicked for medication:", medID);
        }
    });
}

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

