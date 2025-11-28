// frontend/patient/profile/profile.js

document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("updated") === "1") {
    const root = document.getElementById("profile-root");
    const msg = document.createElement("p");
    msg.className = "success";
    msg.textContent = "Profile updated successfully!";
    root.prepend(msg);
  }
});
