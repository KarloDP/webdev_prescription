document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("register-form");
  const roleSelect = document.getElementById("role");
  const dynamicFields = document.getElementById("dynamic-fields");
  const messageBox = document.getElementById("form-message");
  const submitBtn = document.getElementById("submit-btn");

  const roleFieldMap = {
    doctor: [
      { name: "firstName", label: "First Name", type: "text", required: true },
      { name: "lastName", label: "Last Name", type: "text", required: true },
      { name: "specialization", label: "Specialization", type: "text", required: true },
      { name: "licenseNumber", label: "License Number", type: "text", required: true },
      { name: "clinicAddress", label: "Clinic Address", type: "textarea", required: true }
    ],
    pharmacist: [
      { name: "name", label: "Pharmacy Name", type: "text", required: true },
      { name: "address", label: "Address", type: "textarea", required: true },
      { name: "contactNumber", label: "Contact Number", type: "tel", required: true },
      { name: "clinicAddress", label: "Clinic Address", type: "textarea", required: true }
    ],
    patient: [
      { name: "firstName", label: "First Name", type: "text", required: true },
      { name: "lastName", label: "Last Name", type: "text", required: true },
      { name: "birthDate", label: "Birth Date", type: "date", required: true },
      { name: "gender", label: "Gender", type: "select", required: true, options: ["Female","Male","Other"] },
      { name: "contactNumber", label: "Contact Number", type: "tel", required: true },
      { name: "address", label: "Address", type: "textarea", required: true }
    ]
  };

  roleSelect.addEventListener("change", renderDynamicFields);
  renderDynamicFields();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearMessage();

    if (!roleSelect.value) {
      return showMessage("Please select a role.", "error");
    }

    const email = form.email.value.trim();
    const password = form.password.value;
    const confirm = document.getElementById("confirm").value;

    if (password !== confirm) {
      return showMessage("Passwords do not match.", "error");
    }

    const payload = {
      role: roleSelect.value,
      email,
      password
    };

    const extras = collectDynamicValues();
    if (!extras.ok) {
      return showMessage(extras.error, "error");
    }
    Object.assign(payload, extras.data);

    try {
      toggleSubmitting(true);
      const res = await fetch("/backend/sql_handler/register.php", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const result = await res.json();
      if (!res.ok || result.error) {
        throw new Error(result.error || result.details || "Registration failed.");
      }

      showMessage("Registration successful. Redirecting to login...", "success");
      setTimeout(() => (window.location.href = "/login.php"), 1800);
    } catch (err) {
      console.error(err);
      showMessage(err.message || "Unable to register.", "error");
    } finally {
      toggleSubmitting(false);
    }
  });

  function renderDynamicFields() {
    dynamicFields.innerHTML = "";
    const fields = roleFieldMap[roleSelect.value] || [];
    fields.forEach(field => {
      const wrapper = document.createElement("div");
      const id = `field-${field.name}`;

      const label = document.createElement("label");
      label.setAttribute("for", id);
      label.textContent = field.label + (field.required ? " *" : "");
      wrapper.appendChild(label);

      let input;
      if (field.type === "textarea") {
        input = document.createElement("textarea");
        input.rows = 2;
      } else if (field.type === "select") {
        input = document.createElement("select");
        input.appendChild(new Option("Select...", ""));
        (field.options || []).forEach(opt => input.appendChild(new Option(opt, opt)));
      } else {
        input = document.createElement("input");
        input.type = field.type;
        if (field.type === "number") input.min = "0";
      }
      input.id = id;
      input.name = field.name;
      if (field.required) input.required = true;

      wrapper.appendChild(input);
      dynamicFields.appendChild(wrapper);
    });
  }

  function collectDynamicValues() {
    const fields = roleFieldMap[roleSelect.value] || [];
    const data = {};
    for (const field of fields) {
      const el = form.querySelector(`[name="${field.name}"]`);
      if (!el) continue;
      const value = field.type === "number" ? el.value.trim() : el.value.trim();
      if (field.required && !value) {
        return { ok: false, error: `Field "${field.label}" is required.` };
      }
      if (value !== "") {
        data[field.name] = field.type === "number" ? Number(value) : value;
      }
    }
    return { ok: true, data };
  }

  function showMessage(text, type) {
    messageBox.textContent = text;
    messageBox.className = `message ${type}`;
    messageBox.style.display = "block";
  }

  function clearMessage() {
    messageBox.textContent = "";
    messageBox.className = "message";
    messageBox.style.display = "none";
  }

  function toggleSubmitting(isSubmitting) {
    submitBtn.disabled = isSubmitting;
    submitBtn.textContent = isSubmitting ? "Please wait..." : "Register";
  }
});