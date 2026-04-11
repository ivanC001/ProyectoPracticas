<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Acceso al Sistema HECAB</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        radial-gradient(circle at top right, rgba(109, 213, 250, .35), transparent 30%),
        linear-gradient(135deg, #0f172a 0%, #123b64 45%, #6dd5fa 100%);
      padding: 24px;
    }

    .shell {
      width: 100%;
      max-width: 980px;
      background: rgba(255, 255, 255, 0.98);
      border-radius: 28px;
      overflow: hidden;
      display: grid;
      grid-template-columns: 1.05fr .95fr;
      box-shadow: 0 30px 70px rgba(15, 23, 42, 0.35);
    }

    .panel-copy {
      padding: 54px 48px;
      background:
        linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)),
        linear-gradient(150deg, #0f172a 0%, #102a43 52%, #1d4ed8 100%);
      color: #fff;
      position: relative;
    }

    .panel-copy::after {
      content: "";
      position: absolute;
      width: 180px;
      height: 180px;
      right: -40px;
      bottom: -40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
    }

    .brand {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 22px;
    }

    .brand img {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      background: #fff;
      padding: 8px;
    }

    .brand h1 {
      font-size: 1.35rem;
      line-height: 1.1;
    }

    .brand span {
      display: block;
      font-size: .8rem;
      opacity: .78;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .panel-copy h2 {
      font-size: 2rem;
      margin-bottom: 14px;
    }

    .panel-copy p {
      color: rgba(255, 255, 255, .82);
      line-height: 1.7;
      margin-bottom: 24px;
    }

    .role-list {
      display: grid;
      gap: 12px;
    }

    .role-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 14px 16px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .role-item i {
      margin-top: 2px;
      color: #7dd3fc;
    }

    .role-item strong {
      display: block;
      margin-bottom: 4px;
    }

    .panel-form {
      padding: 54px 48px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-card {
      width: 100%;
      max-width: 380px;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #eff6ff;
      color: #1d4ed8;
      padding: 8px 14px;
      border-radius: 999px;
      font-size: .82rem;
      font-weight: 600;
      margin-bottom: 18px;
    }

    .form-card h3 {
      font-size: 2rem;
      color: #0f172a;
      margin-bottom: 10px;
    }

    .form-card p {
      color: #64748b;
      margin-bottom: 24px;
      line-height: 1.6;
    }

    .input-field {
      position: relative;
      margin-bottom: 16px;
    }

    .input-field i {
      position: absolute;
      top: 50%;
      left: 16px;
      transform: translateY(-50%);
      color: #3b82f6;
    }

    .input-field input {
      width: 100%;
      border: 1px solid #dbe3ef;
      border-radius: 16px;
      padding: 13px 16px 13px 44px;
      outline: none;
      transition: all .2s ease;
    }

    .input-field input:focus {
      border-color: #60a5fa;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
    }

    .btn {
      width: 100%;
      border: none;
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 700;
      cursor: pointer;
      transition: all .2s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
      color: #fff;
      box-shadow: 0 14px 26px rgba(37, 99, 235, .22);
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 30px rgba(37, 99, 235, .26);
    }

    .hint-card {
      margin-top: 18px;
      padding: 16px 18px;
      border-radius: 18px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #475569;
      line-height: 1.6;
      font-size: .94rem;
    }

    .hint-card strong {
      display: block;
      margin-bottom: 6px;
      color: #0f172a;
    }

    @media (max-width: 920px) {
      .shell {
        grid-template-columns: 1fr;
      }

      .panel-copy,
      .panel-form {
        padding: 38px 28px;
      }
    }
  </style>
</head>
@php
  $bootstrapMode = \App\Models\User::count() === 0;
  $roleDefinitions = config('roles.definitions', []);
@endphp
<body>
  <div class="shell">
    <section class="panel-copy">
      <div class="brand">
        <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="HECAB">
        <div>
          <h1>HECAB</h1>
          <span>Sistema de gestion</span>
        </div>
      </div>

      <h2>Acceso por usuarios y roles</h2>
      <p>Ahora cada cuenta puede entrar solo a las areas que le corresponden. Eso nos ayuda a ordenar comercial, operaciones y administracion desde un mismo panel.</p>

      <div class="role-list">
        <div class="role-item">
          <i class="fas fa-shield-alt"></i>
          <div>
            <strong>Administrador</strong>
            <span>Gestiona usuarios, roles y acceso total al sistema.</span>
          </div>
        </div>
        <div class="role-item">
          <i class="fas fa-briefcase"></i>
          <div>
            <strong>Comercial</strong>
            <span>Trabaja clientes, cotizaciones, ventas, productos y servicios.</span>
          </div>
        </div>
        <div class="role-item">
          <i class="fas fa-route"></i>
          <div>
            <strong>Operaciones</strong>
            <span>Gestiona conductores, unidades, rutas, gastos y reportes.</span>
          </div>
        </div>
      </div>
    </section>

    <section class="panel-form">
      <div class="form-card">
        <span class="eyebrow">
          <i class="fas fa-lock"></i>
          {{ $bootstrapMode ? 'Configuracion inicial' : 'Ingreso seguro' }}
        </span>

        <h3>{{ $bootstrapMode ? 'Crear usuario administrador' : 'Iniciar sesion' }}</h3>
        <p>
          {{ $bootstrapMode
              ? 'Como aun no existen usuarios, este primer registro se guardara automaticamente como administrador.'
              : 'Ingresa con tu cuenta asignada. Los nuevos usuarios se crean desde el modulo de Usuarios por un administrador.' }}
        </p>

        <form id="{{ $bootstrapMode ? 'bootstrapForm' : 'loginForm' }}">
          @if($bootstrapMode)
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="name" placeholder="Nombre completo" required />
            </div>
          @endif

          <div class="input-field">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Correo" required />
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Contrasena" required />
          </div>

          @if($bootstrapMode)
            <div class="input-field">
              <i class="fas fa-check-circle"></i>
              <input type="password" name="password_confirmation" placeholder="Confirmar contrasena" required />
            </div>
          @endif

          <button type="submit" class="btn btn-primary">
            {{ $bootstrapMode ? 'Crear administrador e ingresar' : 'Ingresar al sistema' }}
          </button>
        </form>

        <div class="hint-card">
          <strong>{{ $bootstrapMode ? 'Primer paso recomendado' : 'Importante' }}</strong>
          {{ $bootstrapMode
              ? 'Despues de entrar, crea los demas usuarios desde Usuarios y roles para asignarles su acceso correcto.'
              : 'Si necesitas una cuenta nueva o cambio de rol, debe hacerlo un administrador desde el sistema.' }}
        </div>
      </div>
    </section>
  </div>

  <script>
    const bootstrapMode = @json($bootstrapMode);
    const roleDefinitions = @json($roleDefinitions);

    function getRedirectPath(user) {
      const definition = roleDefinitions[user?.rol] || {};

      if (definition.default_path) {
        return definition.default_path;
      }

      const paths = definition.paths || [];

      if (!paths.length || paths.includes('*')) {
        return '/';
      }

      return paths.find((path) => path !== '/') || '/';
    }

    function showLoader(message = 'Procesando...') {
      Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    }

    function saveSession(data) {
      localStorage.setItem('token', data.access_token);
      localStorage.setItem('user', data.user?.email || data.email || '');
      localStorage.setItem('auth_user', JSON.stringify(data.user || {}));
    }

    async function submitJson(url, payload, loadingTitle) {
      showLoader(loadingTitle);

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      Swal.close();

      if (!response.ok) {
        if (response.status === 422 && data.errors) {
          const messages = Object.values(data.errors)
            .flat()
            .join('\n');

          throw new Error(messages || data.message || 'Error de validacion');
        }

        throw new Error(data.error || data.message || 'No se pudo completar la accion');
      }

      return data;
    }

    document.getElementById(bootstrapMode ? 'bootstrapForm' : 'loginForm').addEventListener('submit', async function (event) {
      event.preventDefault();
      const submitButton = this.querySelector('button[type="submit"]');

      if (submitButton?.dataset.submitting === '1') {
        return;
      }

      if (submitButton) {
        submitButton.dataset.submitting = '1';
        submitButton.disabled = true;
      }

      try {
        const payload = Object.fromEntries(new FormData(this).entries());
        const url = bootstrapMode ? '/api/register' : '/api/login';
        const loadingTitle = bootstrapMode ? 'Creando administrador...' : 'Iniciando sesion...';

        const data = await submitJson(url, payload, loadingTitle);

        saveSession(data);

        Swal.fire({
          icon: 'success',
          title: bootstrapMode ? 'Administrador creado' : 'Bienvenido',
          text: bootstrapMode ? 'La configuracion inicial quedo lista.' : 'Acceso concedido correctamente.',
          timer: 1400,
          showConfirmButton: false,
        }).then(() => {
          window.location.href = getRedirectPath(data.user);
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'No se pudo continuar',
          text: error.message || 'Ocurrio un problema al conectar con el servidor.',
        });
      } finally {
        if (submitButton) {
          submitButton.dataset.submitting = '0';
          submitButton.disabled = false;
        }
      }
    });
  </script>
</body>
</html>
