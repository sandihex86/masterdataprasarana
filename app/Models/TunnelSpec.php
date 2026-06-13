<?php

namespace App\Models;

use App\Models\Concerns\UsesTunnelConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunnelSpec extends Model
{
    use UsesTunnelConnection;

    protected $table = 'm_tunnel_specs';

    protected $guarded = ['id'];

    protected $casts = [
        'jumlah_jalur' => 'integer',
        'gauge_m' => 'decimal:3',
        'lebar_bersih_m' => 'decimal:2',
        'tinggi_bersih_m' => 'decimal:2',
        'clearance_horizontal_mm' => 'integer',
        'clearance_vertikal_mm' => 'integer',
        'gradien_persen' => 'decimal:2',
        'radius_lengkung_m' => 'decimal:2',
    ];

    public function tunnel(): BelongsTo
    {
        return $this->belongsTo(Tunnel::class, 'tunnel_id', 'tunnel_id');
    }
}
