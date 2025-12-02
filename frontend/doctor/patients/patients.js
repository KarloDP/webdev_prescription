// Load all patients
fetch("../../../backend/doctor/get_patients.php")
    .then(res => res.json())
    .then(data => {
        let tbody = document.querySelector("#patientsTable tbody");
        tbody.innerHTML = "";

        data.forEach(p => {
            tbody.innerHTML += `
                <tr>
                    <td>${p.patient_id}</td>
                    <td>${p.full_name}</td>
                    <td>${p.age}</td>
                    <td>${p.gender}</td>
                    <td><a href="patients_details.php?id=${p.patient_id}">View</a></td>
                </tr>
            `;
        });
    })
    .catch(err => console.log(err));
