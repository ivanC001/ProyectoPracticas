@extends('admin.main')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script> --}}
@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Registro de conductor 
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroConductor">
                                <i class="fas fa-file"></i> Nuevo
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="" method="get">
                                {{-- <form action="{{ route('Conductor.index') }}" method="get"> --}}
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" value="">
                                    {{-- <input name="texto" type="text" class="form-control" value="--<{{ $texto }}> --{}"> --}}
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>                      
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="mt-2">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th width="20%">Opciones</th>
                                            <th width="5%">ID</th>
                                            <th width="20%">Nombre</th>
                                            <th width="20%">apellidos</th>
                                            <th width="20%">Licencia</th>
                                            <th width="15%">estado</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- @foreach($registros as $reg)
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm editar" onclick="editar({{ $reg->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar({{ $reg->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $reg->id }}</td>
                                                <td>{{ $reg->nombre }}</td>
                                                <td>{{ $reg->apellido }}</td>
                                                <td>{{ $reg->licencia }}</td>
                                                <td>{{ $reg->activo }}</td>
                                            </tr>
                                        @endforeach --}}
                                    </tbody>
                                </table>
                                {{-- {{ $registros->appends(['texto' => $texto]) }} --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('vista_conductor.registro')

@endsection
{{-- 
@push('scripts')
<script>
    $(document).ready(function() {
        $('#formRegistroConductor').on('submit', function(event) {
            event.preventDefault();

            $.ajax({
                url: "{{ route('Conductor.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#modalRegistroConductor').modal('hide');
                        alert('Registro creado satisfactoriamente');
                        location.reload();
                    } else {
                        alert('Error al crear el registro');
                    }
                },
                error: function(response) {
                    alert('Error al crear el registro');
                }
            });
        });
    });


    function eliminar(id) {
      Swal.fire({
            title: 'Eliminar registro',
            text: "¿Esta seguro de querer eliminar el registro?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si',
            cancelButtonText: 'No'
          }).then((result) => {
              if (result.isConfirmed) {
                $.ajax({
                    method: 'DELETE',
                    url: `{{url('Conductor')}}/${id}`,
                    headers:{
                      'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res){
                      window.location.reload();
                      Swal.fire({
                          icon: res.status,
                          title: res.message,
                          showConfirmButton: false,
                          timer: 1500
                      });
                    },
                    error: function (res){

                    }
                });
                
              }
          })
        
      }
</script>
@endpush --}}
