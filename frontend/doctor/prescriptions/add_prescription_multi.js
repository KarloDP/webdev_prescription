document.addEventListener('DOMContentLoaded', () => {

    const patientSearch = document.getElementById('patient-search');
    const patientSelect = document.getElementById('patient-select');

    const medsBody  = document.getElementById('medications-body');
    const addRowBtn = document.getElementById('add-med-row');
    const saveBtn   = document.getElementById('save-prescription');

    let patients = [];
    let medications = [];

    /* ============================
       LOAD PATIENTS
    ============================ */
    const baseUrl = window.APP_BASE_URL || '';
    fetch(`${baseUrl}/backend/sql_handler/patient_table.php`, {
        credentials: 'include'
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            patients = Array.isArray(data) ? data : [];
            renderPatients(patients);
        })
        .catch(err => {
            console.error('Error loading patients:', err);
            alert('Failed to load patients. Please refresh the page.');
        });

    function renderPatients(list) {
        patientSelect.innerHTML = '<option value="">Select patient</option>';
        list.forEach(p => {
            if (!p.patientID) return;
            const opt = document.createElement('option');
            opt.value = p.patientID;
            opt.textContent = `${p.lastName}, ${p.firstName}`;
            patientSelect.appendChild(opt);
        });
    }

    patientSearch.addEventListener('input', () => {
        const q = patientSearch.value.toLowerCase();
        renderPatients(
            patients.filter(p =>
                `${p.firstName} ${p.lastName}`.toLowerCase().includes(q)
            )
        );
    });

    /* ============================
       LOAD MEDICATIONS
    ============================ */
    fetch(`${baseUrl}/backend/sql_handler/medication_table.php`, {
        credentials: 'include'
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            medications = Array.isArray(data) ? data : [];
            addMedicationRow();
        })
        .catch(err => {
            console.error('Error loading medications:', err);
            alert('Failed to load medications. Please refresh the page.');
        });

    function addMedicationRow() {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <input type="text" class="med-search" placeholder="Search medication...">
                <select class="med-select">
                    <option value="">Select medication</option>
                </select>
            </td>
            <td><input type="text" class="dosage" placeholder="500 mg"></td>
            <td><input type="text" class="frequency" placeholder="twice daily"></td>
            <td><input type="text" class="duration" placeholder="7 days"></td>
            <td><input type="number" class="prescribed_amount" min="0"></td>
            <td><input type="date" class="refillInterval"></td>
            <td><input type="text" class="instructions" placeholder="Take after meals"></td>
            <td><button type="button" class="remove">✕</button></td>
        `;

        const search = tr.querySelector('.med-search');
        const select = tr.querySelector('.med-select');

        function renderMeds(list) {
            select.innerHTML = '<option value="">Select medication</option>';
            list.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.medicationID;
                opt.textContent = `${m.genericName} (${m.brandName})`;
                select.appendChild(opt);
            });
        }

        renderMeds(medications);

        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            renderMeds(
                medications.filter(m =>
                    `${m.genericName} ${m.brandName}`.toLowerCase().includes(q)
                )
            );
        });

        tr.querySelector('.remove').onclick = () => tr.remove();
        medsBody.appendChild(tr);
    }

    addRowBtn.onclick = addMedicationRow;

    /* ============================
       SAVE PRESCRIPTION
    ============================ */
    saveBtn.onclick = async () => {

        const payload = {
            mode: 'multi',
            patientID: Number(patientSelect.value),
            issueDate: document.getElementById('issue-date').value,
            expirationDate: document.getElementById('expiration-date').value,
            notes: document.getElementById('notes').value,
            medications: []
        };

        [...medsBody.children].forEach(row => {
            const medicationID = Number(row.querySelector('.med-select').value);
            const dosage = row.querySelector('.dosage').value.trim();
            const frequency = row.querySelector('.frequency').value.trim();
            const duration = row.querySelector('.duration').value.trim();

            if (!medicationID || !dosage || !frequency || !duration) return;

            const refillDate = row.querySelector('.refillInterval').value.trim();
            payload.medications.push({
                medicationID,
                dosage,
                frequency,
                duration,
                prescribed_amount: Number(row.querySelector('.prescribed_amount').value || 0),
                refillInterval: refillDate || null, // Send null if empty, backend will handle default
                instructions: row.querySelector('.instructions').value.trim()
            });
        });

        if (!payload.patientID || !payload.issueDate || !payload.expirationDate || payload.medications.length === 0) {
            alert('Please complete all required fields.');
            return;
        }

        const res = await fetch(
            `${baseUrl}/backend/sql_handler/prescription_table.php`,
            {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }
        );

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Prescription save failed:', errorText);
            let errorMsg = `HTTP ${res.status}: Failed to save prescription`;
            try {
                const errorJson = JSON.parse(errorText);
                errorMsg = errorJson.details || errorJson.error || errorMsg;
            } catch (e) {
                // Not JSON, use default message
            }
            alert(errorMsg);
            return;
        }

        const data = await res.json();

        if (data.success) {
            window.location.href = 'prescriptions.php';
        } else {
            alert(data.details || data.error || 'Failed to save prescription');
        }
    };
});
