@php
    $empresa = $empresa ?? config('empresa');
    $anchorId = $anchorId ?? 'contacto';
    $emails = (array) data_get($empresa, 'emails', []);
    $telefono = (string) data_get($empresa, 'telefono', '');
    $whatsapp = preg_replace('/\D+/', '', $telefono);
    if (strlen($whatsapp) === 9) {
        $whatsapp = '51' . $whatsapp;
    }
@endphp

<section class="section-block contact-wrap" id="{{ $anchorId }}">
    <div class="container">
        <h2 class="section-title">Contacto comercial</h2>
        <p class="section-lead">Si necesitas una cotizacion o coordinacion tecnica, puedes escribirnos por correo, telefono o WhatsApp.</p>
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="contact-card">
                    <div class="contact-line">
                        <i class="fa-solid fa-building"></i>
                        <div>
                            <strong>{{ data_get($empresa, 'razon_social') }}</strong><br>
                            RUC: {{ data_get($empresa, 'ruc') }}
                        </div>
                    </div>
                    <div class="contact-line">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>{{ data_get($empresa, 'direccion') }}</div>
                    </div>
                    <div class="contact-line">
                        <i class="fa-solid fa-phone"></i>
                        <div><a href="tel:{{ preg_replace('/\D+/', '', $telefono) }}">{{ $telefono }}</a></div>
                    </div>
                    @foreach($emails as $email)
                        <div class="contact-line mb-0">
                            <i class="fa-solid fa-envelope"></i>
                            <div><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="contact-card">
                    <h5 class="mb-3">Canales de atencion</h5>
                    <p class="mb-3">Escribenos para cotizaciones, soporte tecnico y coordinaciones comerciales.</p>
                    <div class="contact-line">
                        <i class="fa-solid fa-user-tie"></i>
                        <div>
                            <strong>Responsable:</strong><br>
                            {{ data_get($empresa, 'gerente_nombre') }}
                        </div>
                    </div>
                    <div class="d-flex flex-wrap">
                        @if(!empty($whatsapp))
                            <a class="btn btn-hecab mr-2 mb-2" target="_blank" href="https://wa.me/{{ $whatsapp }}">WhatsApp</a>
                        @endif
                        @if(!empty($emails))
                            <a class="btn btn-outline-primary mb-2 mr-2" href="mailto:{{ $emails[0] }}">Correo</a>
                        @endif
                        <a class="btn btn-outline-primary mb-2" href="/login">Portal interno</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
