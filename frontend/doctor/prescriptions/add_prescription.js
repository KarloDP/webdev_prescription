// Add new medicine row
document.getElementById("addRowBtn").addEventListener("click", () => {
    const list = document.getElementById("medicineList");

    const row = document.createElement("div");
    row.classList.add("medicine-row");

    row.innerHTML = `
        <input type="text" class="medicine" placeholder="Medicine" required>
        <input type="text" class="dosage" placeholder="Dosage" required>
        <input type="text" class="frequency" placeholder="Frequency" required>
        <input type="text" class="notes" placeholder="Notes">
        <button type="button" class="removeRow">X</button>
    `;

    list.appendChild(row);
});

// Remove row
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("removeRow")) {
        e.target.parentElement.remove();
    }
});

document.getElementById("addPrescriptionForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const patientID = document.getElementById("patientID").value;
    const startDate = document.getElementById("startDate").value;

    const rows = document.querySelectorAll(".medicine-row");
    const medicines = [];

    rows.forEach(row => {
        medicines.push({
            medicine: row.querySelector(".medicine").value,
            dosage: row.querySelector(".dosage").value,
            frequency: row.querySelector(".frequency").value,
            notes: row.querySelector(".notes").value
        });
    });

    const data = {
        action: "add_prescription",
        patientID,
        doctorID: LOGGED_DOCTOR_ID,
        startDate,
        medicines
    };

    fetch("../../../../backend/sql_handler/prescription_table.php", {

        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(res => {
            const msg = document.getElementById("result");

            if (res.success) {
                msg.style.color = "green";
                msg.textContent = "Prescription added successfully!";
                setTimeout(() => window.location.href = "./prescriptions.php", 1200);
            } else {
                msg.style.color = "red";
                msg.textContent = res.message || "Error adding prescription.";
            }
        });
});
