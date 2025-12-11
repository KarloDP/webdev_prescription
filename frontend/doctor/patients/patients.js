// frontend/doctor/patients/patients.js
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('patients-table-body');

    if (!window.LOGGED_DOCTOR_ID || window.LOGGED_DOCTOR_ID === 0) {
        console.error('No logged doctor id available (LOGGED_DOCTOR_ID).');
        tableBody.innerHTML = `<tr><td colspan="5">No doctor ID — cannot load patients.</td></tr>`;
        return;
    }

    // Utility: escape text to prevent XSS
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Utility: format birthDate to age (years)
    function calculateAgeFromDateString(dateStr) {
        if (!dateStr || dateStr === '0000-00-00') return '—';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '—';
        const today = new Date();
        let age = today.getFullYear() - d.getFullYear();
        const m = today.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < d.getDate())) {
            age--;
        }
        return age >= 0 ? age : '—';
    }

    // Fetch patients for the current doctor
    function loadPatients() {
        tableBody.innerHTML = `<tr><td colspan="5">Loading patient data...</td></tr>`;
        const url = `/WebDev_Prescription/backend/sql_handler/get_doctor_patients.php?doctorID=${encodeURIComponent(LOGGED_DOCTOR_ID)}`;
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5">No patients found.</td></tr>`;
                    return;
                }
                // Cache full list for local filtering
                window._patientsCache = data;
                renderPatientsTable(data);
            })
            .catch(err => {
                console.error('Error fetching patients:', err);
                tableBody.innerHTML = `<tr><td colspan="5">Failed to load patient data. See console.</td></tr>`;
            });
    }

    // Render rows
    function renderPatientsTable(patients) {
        if (!patients || patients.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5">No patients found.</td></tr>`;
            return;
        }

        tableBody.innerHTML = '';
        patients.forEach(patient => {
            const fullName = `${patient.firstName ?? ''} ${patient.lastName ?? ''}`.trim();
            const age = calculateAgeFromDateString(patient.birthDate);
            const lastVisit = patient.lastVisit ?? patient.last_visit ?? 'No record';
            const lastPrescription = patient.lastPrescriptionID ? `RX-${escapeHTML(patient.lastPrescriptionID)}` : 'None';

            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${escapeHTML(fullName)}</td>
        <td>${escapeHTML(age)}</td>
        <td>${escapeHTML(patient.gender ?? 'N/A')}</td>
        <td>${escapeHTML(lastVisit)}</td>
        <td>${escapeHTML(lastPrescription)}</td>
      `;
            tableBody.appendChild(tr);
        });
    }

    // Local search/filtering
    searchInput.addEventListener('input', () => {
        const q = (searchInput.value || '').toLowerCase().trim();
        const all = window._patientsCache || [];
        if (!q) {
            renderPatientsTable(all);
            return;
        }
        const filtered = all.filter(p => {
            const fullName = `${p.firstName ?? ''} ${p.lastName ?? ''}`.toLowerCase();
            const rx = (p.lastPrescriptionID ? `rx-${p.lastPrescriptionID}` : '').toLowerCase();
            return fullName.includes(q) || rx.includes(q);
        });
        renderPatientsTable(filtered);
    });

    // initial load
    loadPatients();
});
