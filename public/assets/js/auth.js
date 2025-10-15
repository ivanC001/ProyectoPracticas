// URL base de la API (ajusta si usas subcarpeta o dominio distinto)
const API_URL = "http://127.0.0.1:8000/api";

// LOGIN
document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = e.target;
    const data = {
        email: form.email.value,
        password: form.password.value,
    };

    try {
        const res = await fetch(`${API_URL}/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (res.ok) {
            localStorage.setItem("token", result.access_token);
            alert("✅ Inicio de sesión exitoso");
            // Redirigir a tu dashboard
            window.location.href = "/dashboard";
        } else {
            document.getElementById("loginError").innerText = result.message || "Error en login";
            document.getElementById("loginError").style.display = "block";
        }
    } catch (error) {
        console.error("Error login:", error);
    }
});

// REGISTRO
document.getElementById("registerForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = e.target;
    const data = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value,
        password_confirmation: form.password_confirmation.value,
    };

    try {
        const res = await fetch(`${API_URL}/register`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (res.ok) {
            alert("✅ Usuario registrado correctamente");
            // opcional: autologin después de registrar
            form.reset();
        } else {
            document.getElementById("registerError").innerText = JSON.stringify(result.errors || result.message);
            document.getElementById("registerError").style.display = "block";
        }
    } catch (error) {
        console.error("Error registro:", error);
    }
});
