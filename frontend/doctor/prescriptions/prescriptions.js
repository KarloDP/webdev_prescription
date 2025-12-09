document.addEventListener('DOMContentLoaded', function () {
    fetch(`/webdev_prescription/backend/sql_handler/get_prescriptions_data.php?doctorID=${LOGGED_DOCTOR_ID}`)

        .then(res => res.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                renderPatientsTable(data.patients);
                renderActivePrescriptionsTable(data.activePrescriptions);
                renderHistoryTable(data.prescriptionHistory);
            } catch (err) {
                console.error('Invalid JSON from get_prescriptions_data.php', err);
                console.log('Server response (raw):\n', text);
                document.getElementById('patients-table-body').innerHTML = '<tr><td colspan="4" class="error-cell">Failed to load data (see console)</td></tr>';
                document.getElementById('active-prescriptions-body').innerHTML = '<tr><td colspan="6" class="error-cell">Failed to load data (see console)</td></tr>';
                document.getElementById('history-prescriptions-body').innerHTML = '<tr><td colspan="5" class="error-cell">Failed to load data (see console)</td></tr>';
            }
        })
        .catch(err => {
            console.error('Network error fetching prescriptions data:', err);
        });
});

function formatDateISOtoLocal(iso) {
    if (!iso) return '';
    // Ensure only date portion if datetime present
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function displayMedicineName(item) {
    // Accept item that may already have medicineName or generic/brand fields
    if (!item) return '';
    if (item.medicineName) return escapeHTML(item.medicineName);
    const brand = item.brandName || item.brand || '';
    const generic = item.genericName || item.generic || '';
    return escapeHTML(brand || generic || '').trim();
}

function renderPatientsTable(patients) {
    const tableBody = document.getElementById('patients-table-body');
    tableBody.innerHTML = '';
    if (!patients || patients.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="no-data-cell">No patients found.</td></tr>';
        return;
    }
    patients.forEach(p => {
        const age = p.birthDate ? calculateAge(p.birthDate) : '—';
        const name = `${escapeHTML(p.firstName)} ${escapeHTML(p.lastName)}`;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td data-label="Name">${name}</td>
            <td data-label="Age">${age}</td>
            <td data-label="Gender">${escapeHTML(p.gender || '—')}</td>
            <td data-label="Contact">${escapeHTML(p.email || '—')}</td>
        `;
        tableBody.appendChild(row);
    });
}

function renderActivePrescriptionsTable(prescriptions) {
    const tableBody = document.getElementById('active-prescriptions-body');
    tableBody.innerHTML = '';
    if (!prescriptions || prescriptions.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="no-data-cell">No active prescriptions.</td></tr>';
        return;
    }
    prescriptions.forEach(p => {
        const patientName = `${escapeHTML(p.firstName)} ${escapeHTML(p.lastName)}`;
        const med = displayMedicineName(p);
        const start = formatDateISOtoLocal(p.issueDate);
        const row = document.createElement('tr');
        row.innerHTML = `
    <td data-label="Patient Name">${patientName}</td>
    <td data-label="Medicine">${med}</td>
    <td data-label="Dosage">${escapeHTML(p.dosage || '—')}</td>
    <td data-label="Frequency">${escapeHTML(p.frequency || '—')}</td>
    <td data-label="Start Date">${start}</td>
    <td data-label="Notes">${escapeHTML(p.notes || '')}</td>
    <td>
        <button class="invalidate-btn" data-id="${p.prescriptionID}">
          Invalidate
        </button>
    </td>
`;

        tableBody.appendChild(row);
    });
}

function renderHistoryTable(history) {
    const tableBody = document.getElementById('history-prescriptions-body');
    tableBody.innerHTML = '';
    if (!history || history.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="no-data-cell">No prescription history.</td></tr>';
        return;
    }
    history.forEach(h => {
        const date = formatDateISOtoLocal(h.issueDate);
        const med = displayMedicineName(h);
        const statusClass = `status-${(h.status || '').toString().toLowerCase()}`;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td data-label="Date">${date}</td>
            <td data-label="Medicine">${med}</td>
            <td data-label="Dosage">${escapeHTML(h.dosage || '—')}</td>
            <td data-label="Status"><span class="status-badge ${statusClass}">${escapeHTML(h.status || 'unknown')}</span></td>
            <td data-label="Notes">${escapeHTML(h.notes || '')}</td>
        `;
        tableBody.appendChild(row);
    });
}

// --- Helper Functions ---
function calculateAge(dateString) {
    const birthDate = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return str.toString().replace(/[&<>"']/g, function(match) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
    });
}
document.getElementById("add-prescription-btn").addEventListener("click", () => {
    window.location.href = "./add_prescription.php";
});

// Handle invalidate button click
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("invalidate-btn")) {

        const id = e.target.getAttribute("data-id");

        if (!confirm("Invalidate this prescription?")) return;

        fetch("/webdev_prescription/backend/sql_handler/prescription_table.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "invalidate_prescription",
                prescriptionID: id
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Prescription invalidated.");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
});
