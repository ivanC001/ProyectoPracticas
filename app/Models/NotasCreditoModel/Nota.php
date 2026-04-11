<?php
namespace App\Models\NotasCreditoModel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\VentasModel\Venta;

class Nota extends Model
{
    protected $table = 'notasCredito';

    protected $fillable = [

        'venta_id',
        'emisor_user_id',

        'tipo_documento', // 07 | 08
        'serie',
        'correlativo',
        'numero_comprobante',
        'fecha_emision',

        'tipDocAfectado',
        'numDocAfectado',

        'codMotivo',
        'desMotivo',

        'total',

        'sunat_enviado',
        'fecha_envio_sunat',
        'estado_envio',

        'codigo_respuesta_sunat',
        'descripcion_respuesta_sunat',
        'mensaje_error',

        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'archivo_cdr'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');

    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_user_id');
    }
}
