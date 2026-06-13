<?php

namespace App\Models;

use App\Models\Concerns\UsesTunnelConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunnelStructure extends Model
{
    use UsesTunnelConnection;

    protected $table = 'm_tunnel_structures';

    protected $guarded = ['id'];

    protected $casts = [
        'tahun_rehabilitasi_terakhir' => 'integer',
    ];

    public function tunnel(): BelongsTo
    {
        return $this->belongsTo(Tunnel::class, 'tunnel_id', 'tunnel_id');
    }
}
