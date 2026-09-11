<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * TIDAK dipakai untuk data bisnis aplikasi (user & admin datanya ada di
 * Supabase, diakses lewat App\Services\Supabase\SupabaseClient). Model ini
 * hanya placeholder supaya konfigurasi auth bawaan Laravel valid.
 */
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];
}
