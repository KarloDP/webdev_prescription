// prescriptions.js

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded fired: prescriptions.js is running.');

    // --- Data Fetching and Table Rendering ---
    const refreshTables = () => {
        fetch('/webdev_prescription/backend/sql_handler/get_prescriptions_data.php')
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    renderPatientsTable(data.patients);
                    renderActivePrescriptionsTable(data.activePrescriptions);
                    renderHistoryTable(data.prescriptionHistory);
                    console.log('Prescriptions data refreshed successfully.');
                } catch (err) {
                    console.error('Invalid JSON from get_prescriptions_data.php', err);
                    console.log('Server response (raw):\n', text);
                    document.getElementById('patients-table-body').innerHTML =
                        '<tr><td colspan="4" class="error-cell">Failed to load data (see console)</td></tr>';
                    document.getElementById('active-prescriptions-body').innerHTML =
                        '<tr><td colspan="6" class="error-cell">Failed to load data (see console)</td></tr>';
                    document.getElementById('history-prescriptions-body').innerHTML =
                        '<tr><td colspan="5" class="error-cell">Failed to load data (see console)</td></tr>';
                }
            })
            .catch(err => {
                console.error('Network error fetching prescriptions data:', err);
            });
    };

    refreshTables(); // Initial data load

    // --- Modal Handling ---
    const modal = document.getElementById('prescription-modal');
    const openBtn = document.getElementById('add-prescription-btn');

    if (!modal) { console.error("Modal with ID 'prescription-modal' not found."); return; }
    if (!openBtn) { console.error("Open button with ID 'add-prescription-btn' not found."); return; }

    const closeBtn = modal.querySelector('.modal-close-btn');
    const cancelBtn = modal.querySelector('.modal-cancel-btn');
    const saveBtn = modal.querySelector('.modal-save-btn');
    const tabBtns = modal.querySelectorAll('.tab-btn');
    const tabContents = {
        existing: document.getElementById('tab-existing'),
        new: document.getElementById('tab-new')
    };

    // Form fields
    const searchPatientInput = document.getElementById('search-patient');
    const selectedPatientIdInput = document.getElementById('selected-patient-id');
    const patientSearchResults = document.getElementById('patient-search-results');

    const newPatientFullNameInput = document.getElementById('new-patient-full-name');
    const newPatientAgeInput = document.getElementById('new-patient-age');
    const newPatientGenderSelect = document.getElementById('new-patient-gender');
    const newPatientContactInput = document.getElementById('new-patient-contact');

    const medicineInput = document.getElementById('prescription-medicine');
    const dosageInput = document.getElementById('prescription-dosage');
    const frequencyInput = document.getElementById('prescription-frequency');
    const startDateInput = document.getElementById('prescription-start');
    const notesInput = document.getElementById('prescription-notes');

    // State for existing patient selection
    let selectedPatient = null; // Stores { id, name }

    // Open modal
    openBtn.addEventListener('click', () => {
        resetModalForm();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        console.log('Add Prescription button clicked. Opening modal.');
    });

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        console.log('Closing modal.');
        resetModalForm(); // Clear form on close
    }
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // Reset Modal Form
    function resetModalForm() {
        searchPatientInput.value = '';
        selectedPatientIdInput.value = '';
        patientSearchResults.innerHTML = '';
        selectedPatient = null;

        newPatientFullNameInput.value = '';
        newPatientAgeInput.value = '';
        newPatientGenderSelect.value = '';
        newPatientContactInput.value = '';

        medicineInput.value = '';
        dosageInput.value = '';
        frequencyInput.value = '';
        startDateInput.value = '';
        notesInput.value = '';

        // Reset to Existing Patient tab
        tabBtns.forEach(b => b.classList.remove('active'));
        tabBtns[0].classList.add('active'); // Assuming first tab is 'existing'
        tabContents.existing.style.display = '';
        tabContents.new.style.display = 'none';
        console.log('Modal form reset.');
    }

    // Tab switching
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            if (btn.dataset.tab === 'existing') {
                tabContents.existing.style.display = '';
                tabContents.new.style.display = 'none';
            } else {
                tabContents.existing.style.display = 'none';
                tabContents.new.style.display = '';
            }
            // Clear relevant fields when switching tabs
            if (btn.dataset.tab === 'existing') {
                newPatientFullNameInput.value = '';
                newPatientAgeInput.value = '';
                newPatientGenderSelect.value = '';
                newPatientContactInput.value = '';
            } else { // 'new' tab
                searchPatientInput.value = '';
                selectedPatientIdInput.value = '';
                patientSearchResults.innerHTML = '';
                selectedPatient = null;
            }
            console.log(`Tab switched to: ${btn.dataset.tab}`);
        });
    });

    // Patient Search (Existing Patient tab)
    let searchTimeout;
    searchPatientInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const query = searchPatientInput.value.trim();
        patientSearchResults.innerHTML = ''; // Clear previous results immediately
        if (query.length < 2) { // Only search if query is at least 2 characters
            selectedPatient = null;
            selectedPatientIdInput.value = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/webdev_prescription/backend/sql_handler/get_patients_list.php?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    patientSearchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const resultItem = document.createElement('div');
                            resultItem.classList.add('search-result-item'); // Add a class for styling
                            resultItem.dataset.patientId = p.id;
                            resultItem.dataset.patientName = `${p.firstName} ${p.lastName}`;
                            resultItem.innerHTML = `${escapeHTML(p.firstName)} ${escapeHTML(p.lastName)} <span style="color:#888; font-size:0.8em;">(ID: ${p.id})</span>`;
                            resultItem.addEventListener('click', () => {
                                selectPatient(p.id, `${p.firstName} ${p.lastName}`);
                            });
                            patientSearchResults.appendChild(resultItem);
                        });
                    } else {
                        patientSearchResults.innerHTML = '<div>No patients found.</div>';
                    }
                    console.log('Patient search results:', data);
                })
                .catch(err => {
                    console.error('Error fetching patient list:', err);
                    patientSearchResults.innerHTML = '<div>Error searching patients.</div>';
                });
        }, 300); // Debounce search
    });

    function selectPatient(id, name) {
        selectedPatient = { id: id, name: name };
        selectedPatientIdInput.value = id;
        searchPatientInput.value = name; // Populate input with selected name
        patientSearchResults.innerHTML = ''; // Clear results after selection
        console.log('Selected Patient:', selectedPatient);
    }

    // Save button logic
    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const activeTab = modal.querySelector('.tab-btn.active').dataset.tab;
            let response;

            try {
                if (activeTab === 'existing') {
                    response = await saveExistingPatientPrescription();
                } else { // activeTab === 'new'
                    response = await saveNewPatientPrescription();
                }

                if (response && response.success) {
                    alert('Prescription saved successfully!');
                    closeModal();
                    refreshTables(); // Refresh main page tables
                } else {
                    alert('Error: ' + (response ? response.message : 'Unknown error.'));
                }
            } catch (error) {
                console.error('Error during save operation:', error);
                alert('An unexpected error occurred: ' + error.message);
            }
        });
        console.log('Event listener attached to save button.');
    }

    async function saveExistingPatientPrescription() {
        if (!selectedPatient || !selectedPatient.id) {
            alert('Please select an existing patient.');
            return;
        }
        if (!medicineInput.value.trim() || !dosageInput.value.trim() || !frequencyInput.value.trim() || !startDateInput.value.trim()) {
            alert('Please fill in all prescription details.');
            return;
        }

        const formData = new FormData();
        formData.append('patientId', selectedPatient.id);
        formData.append('medicineName', medicineInput.value.trim());
        formData.append('dosage', dosageInput.value.trim());
        formData.append('frequency', frequencyInput.value.trim());
        formData.append('issueDate', startDateInput.value.trim());
        formData.append('notes', notesInput.value.trim());

        console.log('Submitting existing patient prescription:', Object.fromEntries(formData.entries()));

        const res = await fetch('/webdev_prescription/backend/sql_handler/add_prescription_existing_patient.php', {
            method: 'POST',
            body: formData
        });
        return res.json();
    }

    async function saveNewPatientPrescription() {
        const fullName = newPatientFullNameInput.value.trim();
        const age = newPatientAgeInput.value.trim();
        const gender = newPatientGenderSelect.value;
        const contact = newPatientContactInput.value.trim();

        if (!fullName || !age || !gender || !contact) {
            alert('Please fill in all new patient details.');
            return;
        }
        if (!medicineInput.value.trim() || !dosageInput.value.trim() || !frequencyInput.value.trim() || !startDateInput.value.trim()) {
            alert('Please fill in all prescription details.');
            return;
        }

        // Simple split for first and last name; consider more robust handling for complex names
        const nameParts = fullName.split(' ');
        const firstName = nameParts[0];
        const lastName = nameParts.slice(1).join(' ') || '';

        const formData = new FormData();
        formData.append('newPatientFirstName', firstName);
        formData.append('newPatientLastName', lastName);
        formData.append('newPatientAge', age);
        formData.append('newPatientGender', gender);
        formData.append('newPatientContact', contact); // Could be email or phone

        formData.append('medicineName', medicineInput.value.trim());
        formData.append('dosage', dosageInput.value.trim());
        formData.append('frequency', frequencyInput.value.trim());
        formData.append('issueDate', startDateInput.value.trim());
        formData.append('notes', notesInput.value.trim());

        console.log('Submitting new patient and prescription:', Object.fromEntries(formData.entries()));

        const res = await fetch('/webdev_prescription/backend/sql_handler/add_prescription_new_patient.php', {
            method: 'POST',
            body: formData
        });
        return res.json();
    }

    // Close modal when clicking outside panel
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            console.log('Clicked outside modal, closing.');
            closeModal();
        }
    });
    console.log('Event listener attached for clicking outside modal.');
});

// --- Helper Functions (remain unchanged, but included for completeness) ---
function formatDateISOtoLocal(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function displayMedicineName(item) {
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