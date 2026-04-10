<?php

namespace App\Models\GuiasModel;

use App\Models\VentasModel\Venta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuiaRemision extends Model
{
    use SoftDeletes;

    protected $table = 'guias_remision';

    protected $fillable = [
        'tipo_documento',
        'serie',
        'correlativo',
        'numero_guia',
        'fecha_emision',
        'fecha_traslado',
        'motivo_traslado_codigo',
        'motivo_traslado_descripcion',
        'modalidad_transporte',
        'peso_total',
        'unidad_peso',
        'numero_bultos',
        'observacion',
        'destinatario_tipo_doc',
        'destinatario_num_doc',
        'destinatario_razon_social',
        'partida_ubigeo',
        'partida_direccion',
        'llegada_ubigeo',
        'llegada_direccion',
        'transportista_tipo_doc',
        'transportista_num_doc',
        'transportista_razon_social',
        'transportista_reg_mtc',
        'conductor_tipo_doc',
        'conductor_num_doc',
        'conductor_nombres',
        'conductor_licencia',
        'vehiculo_placa',
        'vehiculo_secundario_placa',
        'venta_id',
        'guia_remitente_id',
        'documento_rel_tipo',
        'documento_rel_numero',
        'documento_rel_emisor',
        'sunat_enviado',
        'fecha_envio_sunat',
        'estado_envio',
        'codigo_respuesta_sunat',
        'descripcion_respuesta_sunat',
        'mensaje_error',
        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'archivo_cdr',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_traslado' => 'date',
        'sunat_enviado' => 'boolean',
        'peso_total' => 'decimal:3',
        'numero_bultos' => 'integer',
    ];

    public function detalles()
    {
        return $this->hasMany(GuiaRemisionDetalle::class, 'guia_remision_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function guiaRemitente()
    {
        return $this->belongsTo(self::class, 'guia_remitente_id');
    }
}
