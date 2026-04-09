<?php

namespace App\Http\Requests;

use App\Models\ProductosModel\Producto;
use App\Models\ProductosModel\Servicio;
use App\Services\SunatIgvCatalogService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'tipo_documento' => 'required|in:01,03',
            'fecha_emision' => 'required|date',
            'moneda' => 'required|in:PEN,USD',
            'forma_pago' => 'required|in:contado,credito',
            'observacion' => 'nullable|string|max:500',

            'cliente' => 'nullable|array',

            'credito' => 'nullable|array',
            'credito.cuotas' => 'required_if:forma_pago,credito|integer|min:1|max:36',
            'credito.fecha_vencimiento' => 'required_if:forma_pago,credito|date',
            'credito.monto_pendiente' => 'nullable|numeric|min:0',

            'detraccion' => 'nullable|array',
            'detraccion.aplica' => 'nullable|boolean',
            'detraccion.codigo' => 'nullable|string|size:3',
            'detraccion.porcentaje' => 'nullable|numeric|min:0.01|max:100',
            'detraccion.cuenta' => 'nullable|string|max:30',
            'detraccion.medio_pago' => 'nullable|string|size:3',
            'detraccion.monto' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.tipo_item' => 'required|in:producto,servicio',
            'items.*.item_id' => 'required|integer|min:1',
            'items.*.codigo' => 'required|string|max:50',
            'items.*.descripcion' => 'required|string|max:500',
            'items.*.unidad' => 'nullable|string|max:10',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.valor_unitario' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
            'items.*.tip_afe_igv' => [
                'required',
                Rule::in(array_keys(config('sunat_igv.catalog', []))),
            ],
        ];

        if ($this->tipo_documento === '01') {
            $rules['cliente'] = 'required|array';
            $rules['cliente.tipo_doc'] = 'required|in:6';
            $rules['cliente.num_doc'] = 'required|digits:11';
            $rules['cliente.razon_social'] = 'required|string|max:255';
        } else {
            $rules['cliente.tipo_doc'] = 'nullable|in:0,1,6';
            $rules['cliente.num_doc'] = 'nullable|string|max:15';
            $rules['cliente.razon_social'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'Debe seleccionar el tipo de comprobante',
            'tipo_documento.in' => 'El tipo de comprobante no es valido',

            'fecha_emision.required' => 'La fecha de emision es obligatoria',
            'fecha_emision.date' => 'La fecha de emision no tiene un formato valido',

            'moneda.required' => 'Debe seleccionar la moneda',
            'moneda.in' => 'La moneda no es valida',

            'forma_pago.required' => 'Debe seleccionar la forma de pago',
            'forma_pago.in' => 'La forma de pago no es valida',

            'credito.cuotas.required_if' => 'Debe indicar la cantidad de cuotas para venta al credito',
            'credito.cuotas.integer' => 'La cantidad de cuotas debe ser numerica',
            'credito.cuotas.min' => 'La cantidad de cuotas debe ser mayor a 0',
            'credito.cuotas.max' => 'La cantidad de cuotas no puede ser mayor a 36',
            'credito.fecha_vencimiento.required_if' => 'Debe indicar la fecha de vencimiento para venta al credito',
            'credito.fecha_vencimiento.date' => 'La fecha de vencimiento no tiene un formato valido',
            'credito.monto_pendiente.numeric' => 'El monto pendiente debe ser numerico',
            'credito.monto_pendiente.min' => 'El monto pendiente no puede ser negativo',

            'detraccion.aplica.boolean' => 'El indicador de detraccion no es valido',
            'detraccion.codigo.size' => 'El codigo de detraccion debe tener 3 digitos',
            'detraccion.porcentaje.numeric' => 'El porcentaje de detraccion debe ser numerico',
            'detraccion.porcentaje.min' => 'El porcentaje de detraccion debe ser mayor a 0',
            'detraccion.porcentaje.max' => 'El porcentaje de detraccion no puede superar 100',
            'detraccion.monto.numeric' => 'El monto de detraccion debe ser numerico',
            'detraccion.monto.min' => 'El monto de detraccion no puede ser negativo',
            'detraccion.medio_pago.size' => 'El medio de pago de detraccion debe tener 3 caracteres',
            'detraccion.cuenta.max' => 'La cuenta de detraccion es demasiado larga',

            'cliente.required' => 'El cliente es obligatorio para facturas',
            'cliente.array' => 'El formato del cliente no es valido',

            'cliente.tipo_doc.required' => 'El tipo de documento es obligatorio en factura',
            'cliente.tipo_doc.in' => 'El tipo de documento no es valido',

            'cliente.num_doc.required' => 'El numero de documento es obligatorio',
            'cliente.num_doc.digits' => 'El RUC debe tener exactamente 11 digitos',
            'cliente.num_doc.max' => 'El numero de documento es demasiado largo',

            'cliente.razon_social.required' => 'La razon social es obligatoria',
            'cliente.razon_social.max' => 'La razon social es demasiado larga',

            'items.required' => 'Debe agregar al menos un producto',
            'items.array' => 'Los productos deben enviarse en formato lista',
            'items.min' => 'Debe agregar al menos un producto',

            'items.*.tipo_item.required' => 'Debe indicar si el item es producto o servicio',
            'items.*.tipo_item.in' => 'El tipo de item debe ser producto o servicio',
            'items.*.item_id.required' => 'Debe seleccionar un producto o servicio valido',
            'items.*.item_id.integer' => 'El item seleccionado no es valido',
            'items.*.item_id.min' => 'El item seleccionado no es valido',

            'items.*.codigo.required' => 'El codigo del producto es obligatorio',
            'items.*.codigo.max' => 'El codigo del producto es demasiado largo',

            'items.*.cantidad.required' => 'La cantidad es obligatoria',
            'items.*.cantidad.numeric' => 'La cantidad debe ser numerica',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0',

            'items.*.descripcion.required' => 'La descripcion del producto es obligatoria',
            'items.*.descripcion.max' => 'La descripcion del producto es demasiado larga',

            'items.*.valor_unitario.required' => 'El valor unitario es obligatorio',
            'items.*.valor_unitario.numeric' => 'El valor unitario debe ser numerico',
            'items.*.valor_unitario.min' => 'El valor unitario no puede ser negativo',

            'items.*.descuento.numeric' => 'El descuento debe ser numerico',
            'items.*.descuento.min' => 'El descuento no puede ser negativo',

            'items.*.tip_afe_igv.required' => 'Debe seleccionar el tipo de afectacion IGV',
            'items.*.tip_afe_igv.in' => 'El tipo de afectacion IGV no pertenece al catalogo SUNAT',
        ];
    }

    public function withValidator(ValidationValidator $validator): void
    {
        $validator->after(function (ValidationValidator $validator) {
            $tipoComprobante = (string) $this->input('tipo_documento');
            $tipoDocCliente = (string) data_get($this->input('cliente', []), 'tipo_doc', '');
            $numDocCliente = trim((string) data_get($this->input('cliente', []), 'num_doc', ''));
            $soloDigitos = preg_replace('/\D+/', '', $numDocCliente) ?? '';
            $moneda = (string) $this->input('moneda', 'PEN');
            $formaPago = (string) $this->input('forma_pago', 'contado');

            $items = $this->input('items', []);
            $totales = (new SunatIgvCatalogService())->calculateTotals($items);
            $totalOperacion = (float) ($totales['total'] ?? 0);
            $boletaSinDniPermitida = $moneda === 'PEN' && $totalOperacion <= 500;
            $igvCatalogService = new SunatIgvCatalogService();
            $hasServiceItems = collect($items)->contains(function ($item) {
                return (string) data_get($item, 'tipo_item') === 'servicio';
            });
            $totalServicios = (float) collect($items)->reduce(function ($carry, $item) use ($igvCatalogService) {
                if ((string) data_get($item, 'tipo_item') !== 'servicio') {
                    return $carry;
                }

                $line = $igvCatalogService->calculateLine((array) $item);
                return $carry + (float) ($line['total'] ?? 0);
            }, 0.0);

            $montoMinimoDetraccionServicios = (float) config('sunat_detraccion.monto_minimo_servicios', 700);
            $requiereDetraccionServicios = $hasServiceItems && $totalServicios > $montoMinimoDetraccionServicios;
            $detraccionCatalog = config('sunat_detraccion.servicios', []);
            $detraccionData = (array) $this->input('detraccion', []);
            $detraccionAplica = $this->toBool(data_get($detraccionData, 'aplica', false));
            $detraccionCodigo = (string) data_get($detraccionData, 'codigo', '');
            $detraccionPorcentaje = (float) data_get($detraccionData, 'porcentaje', 0);
            $detraccionMonto = (float) data_get($detraccionData, 'monto', 0);
            $detraccionCuenta = trim((string) data_get($detraccionData, 'cuenta', ''));

            try {
                $fechaEmision = Carbon::parse((string) $this->input('fecha_emision'));
                $ahora = now();
                $fechaMinima = $ahora->copy()->subDays(2)->startOfDay();

                if ($fechaEmision->lt($fechaMinima) || $fechaEmision->gt($ahora)) {
                    $validator->errors()->add(
                        'fecha_emision',
                        'La fecha de emision solo puede estar entre hoy y como maximo 2 dias anteriores.'
                    );
                }
            } catch (\Throwable $e) {
                $validator->errors()->add(
                    'fecha_emision',
                    'La fecha de emision no tiene un formato valido.'
                );
            }

            if ($tipoComprobante === '01' && $tipoDocCliente !== '6') {
                $validator->errors()->add(
                    'cliente.tipo_doc',
                    'Para factura el tipo de documento del cliente debe ser RUC.'
                );
            }

            if ($tipoDocCliente === '1' && $numDocCliente !== '' && strlen($soloDigitos) !== 8) {
                $validator->errors()->add(
                    'cliente.num_doc',
                    'El DNI debe tener exactamente 8 digitos.'
                );
            }

            if ($tipoDocCliente === '6' && $numDocCliente !== '' && strlen($soloDigitos) !== 11) {
                $validator->errors()->add(
                    'cliente.num_doc',
                    'El RUC debe tener exactamente 11 digitos.'
                );
            }

            if ($tipoComprobante === '03') {
                if (!$boletaSinDniPermitida) {
                    if ($tipoDocCliente !== '1') {
                        $validator->errors()->add(
                            'cliente.tipo_doc',
                            'Para boletas mayores a S/ 500.00 debe registrar DNI del cliente.'
                        );
                    }

                    if (strlen($soloDigitos) !== 8) {
                        $validator->errors()->add(
                            'cliente.num_doc',
                            'Para boletas mayores a S/ 500.00 el DNI debe tener 8 digitos.'
                        );
                    }
                } else {
                    if ($tipoDocCliente === '6' && $numDocCliente !== '' && strlen($soloDigitos) !== 11) {
                        $validator->errors()->add(
                            'cliente.num_doc',
                            'Si ingresa RUC en boleta, debe tener 11 digitos.'
                        );
                    }

                    if ($tipoDocCliente === '1' && $numDocCliente !== '' && strlen($soloDigitos) !== 8) {
                        $validator->errors()->add(
                            'cliente.num_doc',
                            'Si ingresa DNI en boleta, debe tener 8 digitos.'
                        );
                    }
                }
            }

            if ($formaPago === 'credito') {
                try {
                    $fechaEmision = Carbon::parse((string) $this->input('fecha_emision'));
                    $fechaVencimientoCredito = data_get($this->input('credito', []), 'fecha_vencimiento');

                    if ($fechaVencimientoCredito) {
                        $fechaVencimiento = Carbon::parse((string) $fechaVencimientoCredito);

                        if ($fechaVencimiento->lt($fechaEmision->copy()->startOfDay())) {
                            $validator->errors()->add(
                                'credito.fecha_vencimiento',
                                'La fecha de vencimiento del credito no puede ser anterior a la fecha de emision.'
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    // Las reglas base de formato de fecha ya mostraran el error correspondiente.
                }
            }

            $productosIds = collect($items)
                ->filter(fn ($item) => (string) data_get($item, 'tipo_item') === 'producto')
                ->pluck('item_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $serviciosIds = collect($items)
                ->filter(fn ($item) => (string) data_get($item, 'tipo_item') === 'servicio')
                ->pluck('item_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $productosValidos = Producto::query()
                ->where('activo', 1)
                ->whereIn('id', $productosIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $serviciosValidos = Servicio::query()
                ->where('activo', true)
                ->whereIn('id', $serviciosIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($items as $idx => $item) {
                $tipoItem = (string) data_get($item, 'tipo_item');
                $itemId = (int) data_get($item, 'item_id', 0);

                if ($tipoItem === 'producto' && !in_array($itemId, $productosValidos, true)) {
                    $validator->errors()->add(
                        "items.$idx.item_id",
                        'El producto seleccionado no existe o esta inactivo.'
                    );
                }

                if ($tipoItem === 'servicio' && !in_array($itemId, $serviciosValidos, true)) {
                    $validator->errors()->add(
                        "items.$idx.item_id",
                        'El servicio seleccionado no existe o esta inactivo.'
                    );
                }
            }

            if ($tipoComprobante !== '01' && $detraccionAplica) {
                $validator->errors()->add(
                    'detraccion.aplica',
                    'La detraccion solo aplica para facturas.'
                );
            }

            if ($tipoComprobante === '01' && $requiereDetraccionServicios && !$detraccionAplica) {
                $validator->errors()->add(
                    'detraccion.aplica',
                    'La detraccion en servicios es obligatoria cuando el monto supera S/ 700.00.'
                );
            }

            if (!$hasServiceItems && $detraccionAplica) {
                $validator->errors()->add(
                    'detraccion.aplica',
                    'La detraccion solo aplica a servicios.'
                );
            }

            if ($hasServiceItems && !$requiereDetraccionServicios && $detraccionAplica) {
                $validator->errors()->add(
                    'detraccion.aplica',
                    'La detraccion en servicios aplica cuando el monto de servicios supera S/ 700.00.'
                );
            }

            if ($detraccionAplica) {
                if (!array_key_exists($detraccionCodigo, $detraccionCatalog)) {
                    $validator->errors()->add(
                        'detraccion.codigo',
                        'Debe seleccionar un codigo de detraccion SUNAT valido.'
                    );
                } else {
                    $porcentajeCatalogo = (float) data_get($detraccionCatalog[$detraccionCodigo], 'porcentaje', 0);

                    if (abs($detraccionPorcentaje - $porcentajeCatalogo) > 0.001) {
                        $validator->errors()->add(
                            'detraccion.porcentaje',
                            'El porcentaje de detraccion no coincide con el catalogo SUNAT para el codigo seleccionado.'
                        );
                    }
                }

                if ($detraccionCuenta === '') {
                    $validator->errors()->add(
                        'detraccion.cuenta',
                        'Debe indicar la cuenta de detraccion.'
                    );
                }

                if ($detraccionMonto <= 0) {
                    $validator->errors()->add(
                        'detraccion.monto',
                        'Debe indicar un monto de detraccion mayor a 0.'
                    );
                }

                if ($detraccionMonto > ($totalServicios + 0.01)) {
                    $validator->errors()->add(
                        'detraccion.monto',
                        'El monto de detraccion no puede ser mayor al total de servicios.'
                    );
                }
            }
        });
    }

    protected function toBool(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validacion',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
