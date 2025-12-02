// Load doctor profile
fetch("../../../backend/doctor/get_profile.php")
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById("doctor_name").textContent = data.profile.name;
        }
    });

// Logout
document.getElementById("logoutBtn").addEventListener("click", () => {
    fetch("../../../logout.php")
        .then(() => window.location.href = "../../../login.php")
        .catch(err => console.log(err));
});
