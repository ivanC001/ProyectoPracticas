<?php
namespace App\Models\VentasModel;

use App\Models\User;
use App\Models\NotasCreditoModel\Nota;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [

        // 📄 Documento
        'tipo_documento',
        'tipo_operacion',
        'serie',
        'correlativo',
        'numero_comprobante',
        'fecha_emision',
        'moneda',
        'forma_pago',
        'credito_total_cuotas',
        'credito_monto_pendiente',
        'credito_fecha_vencimiento',
        'detraccion_aplica',
        'detraccion_codigo',
        'detraccion_porcentaje',
        'detraccion_monto',
        'detraccion_cuenta',
        'detraccion_medio_pago',
        'observacion',

        // 👤 Cliente
        'tipo_documento_cliente',
        'numero_documento_cliente',
        'nombre_cliente',
        'emisor_user_id',

        // 💰 Totales
        'total_venta',
        'total_impuestos',

        // 🚀 SUNAT
        'sunat_enviado',
        'fecha_envio_sunat',
        'estado_envio',
        'codigo_respuesta_sunat',
        'descripcion_respuesta_sunat',
        'mensaje_error',

        // 📂 Archivos
        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'archivo_cdr',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'credito_fecha_vencimiento' => 'date',
        'sunat_enviado' => 'boolean',
        'detraccion_aplica' => 'boolean',
        'total_venta' => 'decimal:2',
        'total_impuestos' => 'decimal:2',
        'credito_monto_pendiente' => 'decimal:2',
        'detraccion_porcentaje' => 'decimal:2',
        'detraccion_monto' => 'decimal:2',
    ];

    /*
    |-----------------------------------------
    | RELACIONES
    |-----------------------------------------
    */

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_user_id');
    }

    public function notasCredito()
    {
        return $this->hasMany(Nota::class, 'venta_id');
    }

    /*
    |-----------------------------------------
    | ACCESSOR
    |-----------------------------------------
    */

    public function getNumeroCompletoAttribute()
    {
        return $this->serie . '-' . str_pad($this->correlativo, 8, '0', STR_PAD_LEFT);
    }
}
