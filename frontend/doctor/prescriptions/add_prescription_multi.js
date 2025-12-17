document.addEventListener('DOMContentLoaded', () => {

    const baseUrl = window.APP_BASE_URL || '';

    const patientSelect = document.getElementById('patient-select');
    const medsBody  = document.getElementById('medications-body');
    const addRowBtn = document.getElementById('add-med-row');
    const saveBtn   = document.getElementById('save-prescription');

    let medications = [];

    /* ============================
       LOAD PATIENTS (ALL)
    ============================ */
    fetch(`${baseUrl}/backend/sql_handler/patient_table.php?scope=all`, {
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            patientSelect.innerHTML = '<option value="">Select patient</option>';

            (Array.isArray(data) ? data : []).forEach(p => {
                if (!p.patientID) return;
                const opt = document.createElement('option');
                opt.value = p.patientID;
                opt.textContent = `${p.lastName}, ${p.firstName}`;
                patientSelect.appendChild(opt);
            });
        })
        .catch(err => {
            console.error('Failed to load patients:', err);
            alert('Failed to load patients.');
        });

    /* ============================
       LOAD MEDICATIONS
    ============================ */
    fetch(`${baseUrl}/backend/sql_handler/medication_table.php`, {
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            medications = Array.isArray(data) ? data : [];
            addMedicationRow();
        })
        .catch(err => {
            console.error('Failed to load medications:', err);
            alert('Failed to load medications.');
        });

    /* ============================
       ADD MEDICATION ROW
    ============================ */
    function addMedicationRow() {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <select class="med-select" style="width:100%;">
                    <option value="">Select medication</option>
                </select>
            </td>
            <td><input type="text" class="dosage" placeholder="500 mg"></td>
            <td><input type="text" class="frequency" placeholder="twice daily"></td>
            <td><input type="text" class="duration" placeholder="7 days"></td>
            <td><input type="number" class="prescribed_amount" min="0"></td>
            <td><input type="date" class="refillInterval"></td>
            <td><input type="text" class="instructions"></td>
            <td><button type="button" class="remove">✕</button></td>
        `;

        const select = tr.querySelector('.med-select');

        medications.forEach(m => {
            const brand = m.brandName?.trim() || '';
            const generic = m.medicine?.trim() || '';   // backend sends "medicine"
            const label = brand && generic
                ? `${brand} (${generic})`
                : (brand || generic || 'Unnamed medication');

            const opt = document.createElement('option');
            opt.value = m.medicationID;
            opt.textContent = label;
            select.appendChild(opt);
        });

        tr.querySelector('.remove').onclick = () => tr.remove();
        medsBody.appendChild(tr);
    }

    addRowBtn.addEventListener('click', addMedicationRow);

    /* ============================
       SAVE PRESCRIPTION
    ============================ */
    saveBtn.addEventListener('click', async () => {

        const payload = {
            mode: 'multi',
            patientID: Number(patientSelect.value),
            issueDate: document.getElementById('issue-date').value,
            expirationDate: document.getElementById('expiration-date').value,
            medications: []
        };

        [...medsBody.children].forEach(row => {
            const medicationID = Number(row.querySelector('.med-select').value);
            const dosage = row.querySelector('.dosage').value.trim();
            const frequency = row.querySelector('.frequency').value.trim();
            const duration = row.querySelector('.duration').value.trim();

            if (!medicationID || !dosage || !frequency) return;

            payload.medications.push({
                medicationID,
                dosage,
                frequency,
                duration,
                prescribed_amount: Number(row.querySelector('.prescribed_amount').value || 0),
                refillInterval: row.querySelector('.refillInterval').value || null,
                instructions: row.querySelector('.instructions').value.trim()
            });
        });

        if (!payload.patientID || !payload.issueDate || !payload.expirationDate || payload.medications.length === 0) {
            alert('Please complete all required fields.');
            return;
        }

        const res = await fetch(`${baseUrl}/backend/sql_handler/prescription_table.php`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            window.location.href = 'prescriptions.php';
        } else {
            alert(data.details || data.error || 'Failed to save prescription');
        }
    });

});