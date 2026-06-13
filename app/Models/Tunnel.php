<?php

namespace App\Models;

use App\Models\Concerns\UsesTunnelConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tunnel extends Model
{
    use SoftDeletes;
    use UsesTunnelConnection;

    protected $table = 'm_tunnels';

    protected $guarded = ['id'];

    protected $casts = [
        'panjang_m' => 'decimal:2',
        'tahun_bangunan' => 'integer',
        'tahun_operasi' => 'integer',
        'umur_tahun' => 'integer',
        'lat' => 'decimal:7',
        'long' => 'decimal:7',
        'tgl_inspeksi_terakhir' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tunnel $tunnel): void {
            if (! $tunnel->tunnel_id) {
                $tunnel->tunnel_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'tunnel_id';
    }

    public function structure(): HasOne
    {
        return $this->hasOne(TunnelStructure::class, 'tunnel_id', 'tunnel_id');
    }

    public function specs(): HasOne
    {
        return $this->hasOne(TunnelSpec::class, 'tunnel_id', 'tunnel_id');
    }

    public function docs(): HasOne
    {
        return $this->hasOne(TunnelDoc::class, 'tunnel_id', 'tunnel_id');
    }
}
