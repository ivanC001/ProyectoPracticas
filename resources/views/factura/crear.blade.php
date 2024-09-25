<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Comprobante</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Registro de Comprobante</h2>

        <!-- Mostrar mensajes de éxito o error -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('factura.store') }}" method="POST" class="shadow p-4 bg-white rounded">
            @csrf <!-- Protección contra CSRF -->

            <div class="mb-3">
                <label for="codComp" class="form-label">Código de Comprobante:</label>
                <input type="text" class="form-control" id="codComp" name="codComp" value="01" required>
            </div>

            <div class="mb-3">
                <label for="fechaEmision" class="form-label">Fecha de Emisión:</label>
                <input type="text" class="form-control" id="fechaEmision" name="fechaEmision" value="10/08/2024" required>
            </div>

            <div class="mb-3">
                <label for="monto" class="form-label">Monto:</label>
                <input type="text" class="form-control" id="monto" name="monto" value="320.00" required>
            </div>

            <div class="mb-3">
                <label for="numRuc" class="form-label">Número de RUC:</label>
                <input type="text" class="form-control" id="numRuc" name="numRuc" value="123456789012" required>
            </div>

            <div class="mb-3">
                <label for="numero" class="form-label">Número:</label>
                <input type="text" class="form-control" id="numero" name="numero" value="2" required>
            </div>

            <div class="mb-3">
                <label for="numeroSerie" class="form-label">Número de Serie:</label>
                <input type="text" class="form-control" id="numeroSerie" name="numeroSerie" value="F02" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary w-50">Guardar</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
