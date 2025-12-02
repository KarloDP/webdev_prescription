// Load details of a single patient
fetch(`../../../backend/doctor/get_patient_details.php?id=${id}`)
    .then(res => res.json())
    .then(p => {
        document.getElementById("details").innerHTML = `
            <h3>${p.full_name}</h3>
            <p><strong>Age:</strong> ${p.age}</p>
            <p><strong>Gender:</strong> ${p.gender}</p>
            <p><strong>Address:</strong> ${p.address}</p>
            <p><strong>Contact:</strong> ${p.contact}</p>
        `;
    })
    .catch(err => console.log(err));
