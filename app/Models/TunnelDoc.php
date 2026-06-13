<?php

namespace App\Models;

use App\Models\Concerns\UsesTunnelConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunnelDoc extends Model
{
    use UsesTunnelConnection;

    protected $table = 'm_tunnel_docs';

    protected $guarded = ['id'];

    protected $casts = [
        'ded_bed_kajian_teknis' => 'array',
        'spesifikasi_teknis' => 'array',
        'shop_drawing' => 'array',
        'as_built_drawing' => 'array',
        'dok_hasil_uji' => 'array',
    ];

    public function tunnel(): BelongsTo
    {
        return $this->belongsTo(Tunnel::class, 'tunnel_id', 'tunnel_id');
    }
}
