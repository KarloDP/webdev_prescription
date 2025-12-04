document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('patients-table-body');
    let allPatients = []; // Cache for all patient data

    // Fetch initial data
    fetch('/WebDev_Prescription/backend/sql_handler/get_doctor_patients.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            allPatients = data; // Store the full list
            renderPatientsTable(allPatients); // Render the full list initially
        })
        .catch(error => {
            console.error('Error fetching patient data:', error);
            tableBody.innerHTML = `<tr><td colspan="5" class="error-cell">Failed to load patient data.</td></tr>`;
        });

    // --- NEW: Event listener for the search input ---
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase().trim();

        // Filter the cached patient list
        const filteredPatients = allPatients.filter(patient => {
            const fullName = `${patient.firstName} ${patient.lastName}`.toLowerCase();
            const prescriptionID = patient.lastPrescriptionID ? `rx-${patient.lastPrescriptionID}`.toLowerCase() : '';
            
            return fullName.includes(searchTerm) || prescriptionID.includes(searchTerm);
        });
        renderPatientsTable(filteredPatients);
    });
});

function renderPatientsTable(patients) {
    const tableBody = document.getElementById('patients-table-body');
    tableBody.innerHTML = ''; // Clear previous results

    if (!patients || patients.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="no-data-cell">No patients found.</td></tr>`;
        return;
    }

    patients.forEach(patient => {
        const age = patient.birthDate ? calculateAge(patient.birthDate) : 'N/A';
        const fullName = `${patient.firstName} ${patient.lastName}`;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHTML(fullName)}</td>
            <td>${age}</td>
            <td>${escapeHTML(patient.gender) || 'N/A'}</td>
            <td>${escapeHTML(patient.lastVisit) || 'No record'}</td>
            <td>${patient.lastPrescriptionID ? `RX-${escapeHTML(patient.lastPrescriptionID)}` : 'None'}</td>
        `;
        tableBody.appendChild(row);
    });
}

function calculateAge(dateString) {
    const birthDate = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDifference = today.getMonth() - birthDate.getMonth();
    if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}