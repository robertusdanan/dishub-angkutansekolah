<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Session;

/**
 * Port dari admin/roles.php - kumpulan pengecekan role MURNI (baca session,
 * tidak ada redirect/exit). Dipakai baik di controller halaman biasa
 * maupun controller API yang butuh respons JSON 401/403, bukan redirect.
 */
class AdminRoleService
{
    public function isSuperAdmin(): bool
    {
        return Session::get('admin_role') === 'superadmin';
    }

    public function isDishubta(): bool
    {
        return Session::get('admin_role') === 'dishubta';
    }

    public function isViewer(): bool
    {
        return Session::get('admin_role') === 'viewer';
    }

    /** Guest - akses read-only Data Absensi hari ini saja. */
    public function isGuest(): bool
    {
        return Session::get('admin_role') === 'guest';
    }

    /**
     * Pengguna - akun publik yang login lewat Google, HANYA boleh melihat
     * "Tiket Saya" & "Profil Saya". Lihat catatan jaminan keamanan yang
     * sama di admin/roles.php lama: role ini tidak pernah lewat admin_roles,
     * currentPermissions() akan selalu kosong untuknya.
     */
    public function isPenggunaPublik(): bool
    {
        return Session::get('admin_role') === 'pengguna';
    }

    public function currentRoleLevel(): int
    {
        return (int) Session::get('admin_role_level', 0);
    }

    /** @return array<string,mixed> */
    public function currentPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return [
                'menus' => ['*'],
                'akun' => ['view' => true, 'create' => true, 'edit' => true, 'deactivate' => true, 'delete' => true],
                'manajemen_role' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'listlink' => ['access' => true],
                'pengaturan' => ['cleanup' => true],
            ];
        }
        $p = Session::get('admin_permissions');

        return is_array($p) ? $p : [];
    }

    public function hasPermission(string $group, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $perms = $this->currentPermissions();

        return !empty($perms[$group][$action]);
    }

    public function canAccessMenu(string $menuId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $menus = $this->currentPermissions()['menus'] ?? [];

        return is_array($menus) && (in_array('*', $menus, true) || in_array($menuId, $menus, true));
    }

    /**
     * Boleh kelola akun/role level $targetLevel? Setara ATAU lebih rendah
     * dari level sendiri (dipakai utk operasi non-Manajemen-Role: ubah
     * nama, nonaktifkan, hapus, reset password).
     */
    public function canManageLevel(int $targetLevel): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->currentRoleLevel() >= $targetLevel;
    }

    /**
     * Versi KETAT - target HARUS level lebih rendah (bukan setara).
     * Dipakai KHUSUS urusan Manajemen Role & penetapan role.
     */
    public function canManageLevelStrict(int $targetLevel): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->currentRoleLevel() > $targetLevel;
    }

    /*
    |--------------------------------------------------------------------------
    | Guard methods (redirect-based) - port dari function requireX() di
    | admin/auth.php. Dipanggil di awal method controller:
    |   if ($r = $this->roles->requireSuperAdmin()) return $r;
    |--------------------------------------------------------------------------
    */

    public function requireSuperAdmin(): mixed
    {
        return $this->isSuperAdmin() ? null : redirect('/admin/');
    }

    /** Superadmin ATAU dishubta - halaman registrasi siswa, tambah absen foto. */
    public function requireAbsensiAccess(): mixed
    {
        return ($this->isSuperAdmin() || $this->isDishubta()) ? null : redirect('/admin/');
    }

    public function requireNonGuest(): mixed
    {
        return $this->isGuest() ? redirect('/admin/') : null;
    }

    /** Blokir role 'pengguna' dari semua halaman admin kecuali Tiket Saya. */
    public function requireNonPenggunaPublik(): mixed
    {
        return $this->isPenggunaPublik() ? redirect('/admin/tiket-saya') : null;
    }

    public function requireMenuAccess(string $menuId): mixed
    {
        return $this->canAccessMenu($menuId) ? null : redirect('/admin/');
    }

    public function requireAkunAccess(): mixed
    {
        return ($this->isSuperAdmin() || $this->hasPermission('akun', 'view')) ? null : redirect('/admin/');
    }

    public function requireRoleManagementAccess(): mixed
    {
        return ($this->isSuperAdmin() || $this->hasPermission('manajemen_role', 'view')) ? null : redirect('/admin/');
    }
}
