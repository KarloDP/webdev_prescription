// Load profile info
fetch("../../../backend/sql_handler/doctor_profile.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "get_doctor_profile" })
})
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById("doctor_name").textContent = data.profile.name;
        }
    });

// Logout
document.getElementById("logoutBtn").addEventListener("click", () => {
    window.location.href = "../../../logout.php";
});
