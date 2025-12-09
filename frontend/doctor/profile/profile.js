document.addEventListener("DOMContentLoaded", () => {
    if (!LOGGED_DOCTOR_ID) {
        console.error("No doctor ID found.");
        return;
    }

    fetch(`/WebDev_Prescription/backend/sql_handler/doctor_table.php?doctorID=${LOGGED_DOCTOR_ID}`)
        .then(res => res.json())
        .then(doc => {

            document.getElementById("p_name").textContent =
                `${doc.firstName} ${doc.lastName}`;

            document.getElementById("p_specialization").textContent =
                doc.specialization || "—";

            document.getElementById("p_license").textContent =
                doc.licenseNumber || "—";

            document.getElementById("p_clinic").textContent =
                doc.clinicAddress || "—";

            document.getElementById("p_email").textContent =
                doc.email || "—";

            document.getElementById("p_status").textContent =
                doc.status || "—";
        })
        .catch(err => {
            console.error("Error loading doctor profile:", err);
        });
});

document.getElementById("logoutBtn").addEventListener("click", () => {
    window.location.href = "../../../logout.php";
});
