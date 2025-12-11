// prescriptions.js

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded fired: prescriptions.js is running.');

    // --- Data Fetching and Table Rendering ---
    const refreshTables = () => {
        fetch('/WebDev_Prescription/backend/sql_handler/get_prescriptions_data.php')
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
    const newPatientEmailInput = document.getElementById('new-patient-email');
    const newPatientContactInput = document.getElementById('new-patient-contact');

    const brandNameInput = document.getElementById('brandNameInput');
    const genericNameInput = document.getElementById('genericNameInput');
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
        newPatientEmailInput.value = '';
        newPatientContactInput.value = '';

        brandNameInput.value = '';
        genericNameInput.value = '';
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
                newPatientEmailInput.value = '';
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
            fetch(`/WebDev_Prescription/backend/sql_handler/get_patients_list.php?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    patientSearchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const pid = p.patientID ?? p.id; // use the actual key
                            const resultItem = document.createElement('div');
                            resultItem.classList.add('search-result-item');
                            resultItem.dataset.patientId = pid;
                            resultItem.dataset.patientName = `${p.firstName} ${p.lastName}`;
                            resultItem.innerHTML = `${escapeHTML(p.firstName)} ${escapeHTML(p.lastName)} <span style="color:#888; font-size:0.8em;">(ID: ${pid})</span>`;
                            resultItem.addEventListener('click', () => {
                                selectPatient(pid, `${p.firstName} ${p.lastName}`);
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

    async function postPrescription(payload) {
        const res = await fetch('/WebDev_Prescription/backend/sql_handler/add_prescription.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });

        const raw = await res.text();
        if (!res.ok) {
            console.error('Add prescription API error body:', raw);
            let msg = `HTTP ${res.status}`;
            try {
                const j = JSON.parse(raw);
                if (j.details) msg = j.details;
                else if (j.error) msg = j.error;
            } catch {}
            throw new Error(msg);
        }

        let data;
        try { data = JSON.parse(raw); }
        catch {
            console.error('Invalid JSON from server:', raw);
            throw new Error('Server returned invalid JSON');
        }
        if (data.error) throw new Error(data.details || data.message || 'Server error');
        return data;
    }

    async function saveExistingPatientPrescription() {
        if (!selectedPatient || !selectedPatient.id) {
            alert('Please select a patient.');
            return { error: true, message: 'No patient selected' };
        }
        const brand = brandNameInput.value.trim();
        const generic = genericNameInput.value.trim();
        if ((!brand && !generic) || !dosageInput.value.trim() || !frequencyInput.value.trim() || !startDateInput.value.trim()) {
            alert('Brand or generic name, dosage, frequency, and start date are required.');
            return { error: true, message: 'Missing fields' };
        }

        const payload = {
            mode: 'existing',
            patientId: Number(selectedPatient.id),
            brandName: brand,
            genericName: generic,
            dosage: dosageInput.value.trim(),
            frequency: frequencyInput.value.trim(),
            issueDate: startDateInput.value.trim(),
            notes: notesInput.value.trim()
        };
        return postPrescription(payload);
    }

    async function saveNewPatientPrescription() {
        const fullName = newPatientFullNameInput.value.trim();
        const age = newPatientAgeInput.value.trim();
        const gender = newPatientGenderSelect.value;
        const email = newPatientEmailInput.value.trim();
        const contact = newPatientContactInput.value.trim();

        if (!fullName || !age || !gender || !contact) {
            alert('Fill all required patient fields (name, age, gender, contact number).');
            return { error: true, message: 'Missing patient fields' };
        }
        const brand = brandNameInput.value.trim();
        const generic = genericNameInput.value.trim();
        if ((!brand && !generic) || !dosageInput.value.trim() || !frequencyInput.value.trim() || !startDateInput.value.trim()) {
            alert('Brand or generic name, dosage, frequency, and start date are required.');
            return { error: true, message: 'Missing prescription fields' };
        }

        const nameParts = fullName.split(' ');
        const firstName = nameParts[0];
        const lastName = nameParts.slice(1).join(' ') || '';

        const payload = {
            mode: 'new',
            firstName,
            lastName,
            age: Number(age),
            gender,
            email,
            contact,
            brandName: brand,
            genericName: generic,
            dosage: dosageInput.value.trim(),
            frequency: frequencyInput.value.trim(),
            issueDate: startDateInput.value.trim(),
            notes: notesInput.value.trim()
        };
        return postPrescription(payload);
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
        const pid   = p.patientID ?? p.patientId ?? p.id ?? '—';
        const first = p.firstName ?? p.firstname ?? p.first_name ?? '';
        const last  = p.lastName  ?? p.lastname  ?? p.last_name  ?? '';
        const dob   = p.birthDate ?? p.birthdate ?? p.dateOfBirth ?? p.dob ?? '';
        const row = document.createElement('tr');
        row.innerHTML = `
            <td data-label="ID">${escapeHTML(pid)}</td>
            <td data-label="First Name">${escapeHTML(first)}</td>
            <td data-label="Last Name">${escapeHTML(last)}</td>
            <td data-label="Date of Birth">${escapeHTML(dob)}</td>
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