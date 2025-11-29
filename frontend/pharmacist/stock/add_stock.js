
document.addEventListener("DOMContentLoaded", () => {
    console.log("add_stock.js loaded");

    const form = document.querySelector(".edit-stock-form");
    const submitBtn = form.querySelector(".btn-save");
    const cancelBtn = document.querySelector(".btn-cancel");
    const backBtn = document.querySelector(".btn-back-outline");

    const fields = {
        genericName: form.querySelector("input[name='genericName']"),
        brandName: form.querySelector("input[name='brandName']"),
        formType: form.querySelector("select[name='form']"),
        strength: form.querySelector("input[name='strength']"),
        manufacturer: form.querySelector("input[name='manufacturer']"),
        stock: form.querySelector("input[name='stock']")
    };

    const resetButton = () => {
        submitBtn.disabled = false;
        submitBtn.textContent = "Add Medication";
    };

    const showError = (msg) => {
        alert(msg);
        console.error(msg);
    };

    const validateFields = () => {
        if (fields.genericName.value.trim() === "") {
            showError("Generic Name cannot be empty.");
            return false;
        }
        if (fields.brandName.value.trim() === "") {
            showError("Brand Name cannot be empty.");
            return false;
        }
        if (fields.formType.value.trim() === "") {
            showError("Please select a form.");
            return false;
        }
        if (fields.strength.value.trim() === "" || isNaN(fields.strength.value) || Number(fields.strength.value) < 1) {
            showError("Strength must be a valid number greater than 0.");
            return false;
        }
        if (fields.manufacturer.value.trim() === "") {
            showError("Manufacturer cannot be empty.");
            return false;
        }
        if (
            fields.stock.value.trim() === "" ||
            isNaN(fields.stock.value) ||
            Number(fields.stock.value) < 0
        ) {
            showError("Stock must be a whole number equal to or greater than 0.");
            return false;
        }

        return true;
    };

    submitBtn.addEventListener("click", (event) => {
        event.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = "Processing...";

        if (!validateFields()) {
            resetButton();
            return;
        }

        console.log("Validation passed. Submitting form...");
        form.submit();
    });

    cancelBtn.addEventListener("click", (event) => {
        event.preventDefault();
        const confirmCancel = confirm("Are you sure you want to cancel?");
        if (confirmCancel) {
            window.location.href = "stock.php";
        }
    });

    if (backBtn) {
        backBtn.addEventListener("click", (event) => {
            event.preventDefault();
            window.location.href = "stock.php";
        });
    }
});
