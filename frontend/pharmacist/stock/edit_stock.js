// edit_stock.js

document.addEventListener("DOMContentLoaded", () => {
    console.log("edit_stock.js loaded");

    const form = document.querySelector(".edit-stock-form");
    const stockInput = form.querySelector("input[name='stock']");

    let errorSpan = null;

    // Create error message container
    const showError = (msg) => {
        if (!errorSpan) {
            errorSpan = document.createElement("span");
            errorSpan.className = "error-text";
            errorSpan.style.color = "#a40000";
            errorSpan.style.fontSize = "13px";
            errorSpan.style.marginLeft = "5px";
            errorSpan.style.display = "block";
            errorSpan.style.marginTop = "4px";
            stockInput.parentNode.appendChild(errorSpan);
        }
        errorSpan.textContent = msg;
        stockInput.style.border = "1px solid #a40000";
    };

    const clearError = () => {
        if (errorSpan) {
            errorSpan.textContent = "";
        }
        stockInput.style.border = "1px solid #ccc";
    };

    // Validation function
    const validateStock = () => {
        const value = stockInput.value.trim();

        if (value === "") {
            showError("Stock cannot be empty.");
            return false;
        }

        const number = Number(value);

        if (isNaN(number)) {
            showError("Stock must be a number.");
            return false;
        }

        if (!Number.isInteger(number)) {
            showError("Stock must be a whole number.");
            return false;
        }

        if (number < 0) {
            showError("Stock cannot be negative.");
            return false;
        }

        clearError();
        return true;
    };

    // Validate on input changes
    stockInput.addEventListener("input", () => {
        validateStock();
    });

    // Form submission listener
    form.addEventListener("submit", (event) => {
        clearError();

        if (!validateStock()) {
            event.preventDefault();
            return;
        }

        // Confirmation dialog
        const confirmSubmit = confirm("Are you sure you want to update this stock?");
        if (!confirmSubmit) {
            event.preventDefault();
            return;
        }

        console.log("Stock update submitted.");
    });
});
