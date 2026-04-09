<?php

namespace App\Models\GuiasModel;

use Illuminate\Database\Eloquent\Model;

class GuiaRemisionDetalle extends Model
{
    protected $table = 'guia_remision_detalles';

    protected $fillable = [
        'guia_remision_id',
        'tipo_item',
        'item_id',
        'codigo',
        'descripcion',
        'unidad',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'item_id' => 'integer',
    ];

    public function guia()
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }
}

