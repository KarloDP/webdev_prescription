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
    fetch('../../../../backend/sql_handler/patient_table.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        patients = Array.isArray(data) ? data : [];
        renderPatients(patients);
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
    fetch('../../../../backend/sql_handler/medication_table.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        medications = Array.isArray(data) ? data : [];
        addMedicationRow();
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
            <td><input type="text" class="dosage"></td>
            <td><input type="text" class="frequency"></td>
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
            notes: document.getElementById('notes').value,
            medications: [...medsBody.children].map(row => ({
                medicationID: Number(row.querySelector('.med-select').value),
                dosage: row.querySelector('.dosage').value.trim(),
                frequency: row.querySelector('.frequency').value.trim()
            })).filter(m => m.medicationID && m.dosage && m.frequency)
        };

        if (!payload.patientID || !payload.issueDate || payload.medications.length === 0) {
            alert('Please complete all required fields.');
            return;
        }

        const res = await fetch(
            '../../../../backend/sql_handler/prescription_table.php',
            {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }
        );

        const data = await res.json();

        if (data.success) {
            window.location.href = 'prescriptions.php';
        } else {
            alert(data.details || data.error || 'Failed to save prescription');
        }
    };
});