<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrayekWisataHero extends Model
{
    protected $table = 'trayekwisata_hero';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tipe', 'url', 'urutan', 'aktif'];
    protected $casts = ['aktif' => 'boolean', 'urutan' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $hero) {
            $hero->id ??= (string) Str::uuid();
        });
    }
}
