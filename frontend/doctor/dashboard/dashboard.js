document.addEventListener('DOMContentLoaded', function () {

    // === 1️⃣ FIXED URL FOR DASHBOARD STATS ===
    fetch('/WebDev_Prescription/backend/sql_handler/get_dashboard_stats.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('patients-count').textContent = data.total_patients ?? '0';
            document.getElementById('active-prescriptions-count').textContent = data.active_prescriptions ?? '0';
            document.getElementById('meds-prescribed-count').textContent = data.medications_prescribed ?? '0';
        })
        .catch(err => {
            console.error("Stats error:", err);
            document.getElementById('patients-count').textContent = 'N/A';
            document.getElementById('active-prescriptions-count').textContent = 'N/A';
            document.getElementById('meds-prescribed-count').textContent = 'N/A';
        });



    // === 2️⃣ FIXED URL → MUST USE get_doctor_patients.php ===
    fetch('/WebDev_Prescription/backend/sql_handler/get_doctor_patients.php')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('patients-table-body');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No patients found.</td></tr>';
                return;
            }

            data.forEach(p => {
                const row = `
                    <tr>
                        <td>${p.patientID}</td>
                        <td>${p.firstName}</td>
                        <td>${p.lastName}</td>
                        <td>${(p.birthDate && p.birthDate !== "0000-00-00") ? p.birthDate : "—"}</td>
                    </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(err => {
            console.error("Patients error:", err);
            document.getElementById('patients-table-body').innerHTML =
                '<tr><td colspan="4">Error loading patients.</td></tr>';
        });

});
