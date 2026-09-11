<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;

/**
 * REKOMENDASI KEAMANAN — TIDAK ADA di kode PHP asli. Mencatat jejak aksi
 * admin sensitif ke channel log terpisah, supaya bisa ditelusuri kalau
 * perlu investigasi ("siapa yang menghapus akun X, kapan?").
 */
class AuditLogService
{
    public function log(string $action, array $context = []): void
    {
        Log::channel('audit')->info($action, array_merge([
            'actor' => session('admin_user') ?? session('listlink_user') ?? session('akun_publik_email') ?? 'guest',
            'actor_role' => session('admin_role') ?? session('listlink_role'),
            'ip' => request()?->ip(),
        ], $context));
    }
}
