<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anggota extends Model
{
    /** @use HasFactory<\Database\Factories\AnggotaFactory> */
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'user_id',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'tanggal_registrasi',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_registrasi' => 'date',
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
