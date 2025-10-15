<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login & Registro - Harper Ingenieros</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(120deg, #2980b9, #6dd5fa, #ffffff);
    }

    .container {
      position: relative;
      width: 100%;
      max-width: 900px;
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1fr;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .forms-container {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 50px;
    }

    form {
      width: 100%;
    }

    .title {
      font-size: 2rem;
      margin-bottom: 20px;
      color: #2980b9;
      font-weight: 600;
      text-align: center;
    }

    .input-field {
      position: relative;
      margin: 15px 0;
      width: 100%;
    }

    .input-field i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #2980b9;
    }

    .input-field input {
      width: 100%;
      padding: 12px 40px;
      border-radius: 25px;
      border: 1px solid #ccc;
      outline: none;
      transition: all 0.3s ease;
    }

    .input-field input:focus {
      border-color: #2980b9;
      box-shadow: 0 0 5px rgba(41, 128, 185, 0.5);
    }

    .btn {
      display: inline-block;
      width: 100%;
      padding: 12px;
      margin: 20px 0;
      border-radius: 25px;
      background: #2980b9;
      border: none;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn:hover {
      background: #1c5980;
    }

    .social-text {
      text-align: center;
      margin: 10px 0;
      font-size: 0.9rem;
      color: #666;
    }

    .social-media {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .social-icon {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid #2980b9;
      color: #2980b9;
      text-decoration: none;
      transition: 0.3s;
    }

    .social-icon:hover {
      background: #2980b9;
      color: #fff;
    }

    .panels-container {
      background: linear-gradient(120deg, #6dd5fa, #2980b9);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px;
      text-align: center;
    }

    .panels-container h3 {
      font-size: 1.5rem;
      margin-bottom: 10px;
    }

    .panels-container p {
      font-size: 0.9rem;
      margin-bottom: 20px;
    }

    .btn.transparent {
      background: transparent;
      border: 2px solid #fff;
      color: #fff;
    }

    .btn.transparent:hover {
      background: #fff;
      color: #2980b9;
    }

    .image {
      width: 80%;
      margin-top: 20px;
    }

    @media (max-width: 768px) {
      .container {
        grid-template-columns: 1fr;
      }
      .panels-container {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Formulario -->
    <div class="forms-container">
      <div class="signin-signup">
        <!-- LOGIN -->
        <form class="sign-in-form">
          <h2 class="title">Iniciar Sesión</h2>

          <div class="input-field">
            <i class="fas fa-user"></i>
            <input type="email" name="email" placeholder="Correo" required />
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Contraseña" required />
          </div>

          <button type="submit" class="btn">Ingresar</button>
        </form>

        <!-- REGISTRO -->
        <form class="sign-up-form" style="display:none;">
          <h2 class="title">Registrarse</h2>

          <div class="input-field">
            <i class="fas fa-user"></i>
            <input type="text" name="name" placeholder="Nombre completo" required />
          </div>

          <div class="input-field">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Correo" required />
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Contraseña" required />
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" required />
          </div>

          <button type="submit" class="btn">Crear Cuenta</button>
        </form>
      </div>
    </div>

    <!-- Panel derecho -->
    <div class="panels-container">
      <h3 id="panel-title">¿Nuevo aquí?</h3>
      <p id="panel-text">Servicios Energeticos HECAB S.A.C</p>
      <button class="btn transparent" id="toggle-btn">Registrarse</button>
      <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" class="image" alt="Login" />

    </div>
  </div>

<script>
  const toggleBtn = document.getElementById("toggle-btn");
  const signInForm = document.querySelector(".sign-in-form");
  const signUpForm = document.querySelector(".sign-up-form");
  const panelTitle = document.getElementById("panel-title");
  const panelText = document.getElementById("panel-text");
  const panelBtn = document.getElementById("toggle-btn");

  toggleBtn.addEventListener("click", () => {
    if (signInForm.style.display !== "none") {
      signInForm.style.display = "none";
      signUpForm.style.display = "block";
      panelTitle.innerText = "¿Ya tienes cuenta?";
      panelText.innerText = "Inicia sesión para acceder a tu cuenta.";
      panelBtn.innerText = "Iniciar Sesión";
    } else {
      signInForm.style.display = "block";
      signUpForm.style.display = "none";
      panelTitle.innerText = "¿Nuevo aquí?";
      panelText.innerText = "Regístrate y forma parte de Harper Ingenieros.";
      panelBtn.innerText = "Registrarse";
    }
  });

  // Loader
  function showLoader(msg = "Procesando...") {
    Swal.fire({
      title: msg,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });
  }

  function showMessage(type, title, text) {
    Swal.fire({ icon: type, title, text });
  }

  // LOGIN
  document.querySelector('.sign-in-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = this.email.value;
    const password = this.password.value;

    try {
      showLoader("Iniciando sesión...");
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      Swal.close();

    if (res.ok && data.access_token) {
    localStorage.setItem('token', data.access_token);
    localStorage.setItem('user', data.email ?? '');
    // opcional: si quieres un objeto usuario con más info, modifica tu API
    // localStorage.setItem('user', JSON.stringify({email: data.usuario}));
    window.location.href = '/';
      } else {
        showMessage('error', 'Error', data.error || data.message);
      }
    } catch (err) {
      Swal.close();
      showMessage('warning', 'Advertencia', 'Error al conectar con el servidor');
    }
  });

  // REGISTRO
  // REGISTRO
document.querySelector('.sign-up-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const name = this.name.value;
  const email = this.email.value;
  const password = this.password.value;
  const password_confirmation = this.password_confirmation.value;

  try {
    showLoader("Registrando usuario...");
    const res = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password, password_confirmation })
    });

    const data = await res.json();
    Swal.close();

    if (res.ok && data.access_token) {
      localStorage.setItem('token', data.access_token);
      localStorage.setItem('user', data.user.email ?? '');
      window.location.href = '/';
    } else if (res.status === 422 && data.errors) {
      // Mostrar errores de validación
      let errores = '';
      for (const key in data.errors) {
        if (data.errors.hasOwnProperty(key)) {
          errores += `${data.errors[key].join(' ')}\n`;
        }
      }
      showMessage('error', 'Error de validación', errores);
    } else {
      showMessage('error', 'Error', data.error || data.message);
    }
  } catch (err) {
    Swal.close();
    showMessage('warning', 'Advertencia', 'Error al conectar con el servidor');
  }
});
</script>
</body>
</html>
