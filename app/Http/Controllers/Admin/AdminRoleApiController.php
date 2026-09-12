<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AuditLogService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/roles.php - satu-satunya tempat yang boleh menulis
 * ke tabel admin_roles. Lihat komentar lengkap aturan hierarki & anti-
 * eskalasi izin di kode lama, dipertahankan 1:1 di sini.
 */
class AdminRoleApiController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected AuditLogService $audit,
    ) {
    }

    protected function requireAccess(): ?JsonResponse
    {
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('manajemen_role', 'view')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya akses ke menu Manajemen Role.'], 403);
        }

        return null;
    }

    /** @return string[] */
    protected function validMenuIds(): array
    {
        $catalog = config('admin_menu_catalog.grup', []);
        $ids = ['*'];
        foreach ($catalog as $items) {
            $ids = array_merge($ids, array_keys($items));
        }

        return $ids;
    }

    protected function normalizePermissions(array $raw, array $validMenuIds): array
    {
        $menus = array_values(array_intersect((array) ($raw['menus'] ?? []), $validMenuIds));
        $bool = fn ($v) => (bool) $v;
        $akun = $raw['akun'] ?? [];
        $roleMgmt = $raw['manajemen_role'] ?? [];
        $listlink = $raw['listlink'] ?? [];

        return [
            'menus' => $menus,
            'akun' => [
                'view' => $bool($akun['view'] ?? false),
                'create' => $bool($akun['create'] ?? false),
                'edit' => $bool($akun['edit'] ?? false),
                'deactivate' => $bool($akun['deactivate'] ?? false),
                'delete' => $bool($akun['delete'] ?? false),
            ],
            'manajemen_role' => [
                'view' => $bool($roleMgmt['view'] ?? false),
                'create' => $bool($roleMgmt['create'] ?? false),
                'edit' => $bool($roleMgmt['edit'] ?? false),
                'delete' => $bool($roleMgmt['delete'] ?? false),
            ],
            'pengaturan' => [
                'cleanup' => $bool(($raw['pengaturan'] ?? [])['cleanup'] ?? false),
            ],
            'listlink' => [
                'access' => $bool($listlink['access'] ?? false),
            ],
        ];
    }

    /**
     * Coret izin yang TIDAK dimiliki akun yang sedang login dari payload
     * izin yang mau disimpan ke sebuah role (defense in depth). Superadmin
     * bebas dari batasan ini.
     */
    protected function clampPermissionsToOwn(array $perms): array
    {
        if ($this->roles->isSuperAdmin()) {
            return $perms;
        }

        $mine = $this->roles->currentPermissions();
        $myMenus = (array) ($mine['menus'] ?? []);
        $iHaveAllMenus = in_array('*', $myMenus, true);

        if (!$iHaveAllMenus) {
            $perms['menus'] = array_values(array_intersect((array) $perms['menus'], $myMenus));
        }

        foreach (['akun', 'manajemen_role', 'pengaturan'] as $grp) {
            foreach ((array) ($perms[$grp] ?? []) as $k => $v) {
                if ($v && empty($mine[$grp][$k])) {
                    $perms[$grp][$k] = false;
                }
            }
        }
        if (!empty($perms['listlink']['access']) && empty($mine['listlink']['access'])) {
            $perms['listlink']['access'] = false;
        }

        return $perms;
    }

    protected function getRole(string $id): ?array
    {
        // PERBAIKAN KEAMANAN: defense-in-depth, lihat catatan di AdminAkunApiController.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return null;
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_roles?id=eq.'.$id.'&select=*&limit=1');
        if ($code < 200 || $code >= 300 || empty($rows)) {
            return null;
        }

        return $rows[0];
    }

    /** GET ?action=list */
    public function list(): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_roles?select=*&order=level.desc');
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengambil data role: HTTP '.$code], 502);
        }

        $myLevel = $this->roles->currentRoleLevel();
        $myRoleKey = (string) session('admin_role', '');

        $filtered = array_values(array_filter((array) $rows, function ($r) use ($myLevel, $myRoleKey) {
            if (($r['role_key'] ?? '') === 'guest') {
                return false;
            }
            if ($this->roles->isSuperAdmin()) {
                return true;
            }
            if (($r['role_key'] ?? '') === $myRoleKey) {
                return true;
            }

            return (int) $r['level'] < $myLevel;
        }));

        foreach ($filtered as &$r) {
            $r['is_mine'] = !$this->roles->isSuperAdmin() && (($r['role_key'] ?? '') === $myRoleKey);
        }
        unset($r);

        return response()->json(['status' => 'ok', 'data' => $filtered]);
    }

    /** POST ?action=create */
    public function create(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('manajemen_role', 'create')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin membuat role.'], 403);
        }

        $roleKey = strtolower(trim((string) $request->input('role_key', '')));
        $roleName = trim((string) $request->input('role_name', ''));
        $desc = trim((string) $request->input('description', ''));
        $level = (int) $request->input('level', 0);
        $perms = $this->clampPermissionsToOwn($this->normalizePermissions((array) $request->input('permissions', []), $this->validMenuIds()));

        if (!preg_match('/^[a-z0-9_]{3,40}$/', $roleKey)) {
            return response()->json(['error' => 'Role key tidak valid (huruf kecil/angka/underscore, 3-40 karakter).'], 400);
        }
        if ($roleName === '') {
            return response()->json(['error' => 'Nama role wajib diisi.'], 400);
        }
        if ($level < 0) {
            return response()->json(['error' => 'Level tidak boleh negatif.'], 400);
        }
        if (!$this->roles->canManageLevelStrict($level)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa membuat role dengan level LEBIH RENDAH dari level Anda sendiri (tidak boleh setara).'], 403);
        }

        [$code, $res] = $this->supabase->rawRequest('POST', 'admin_roles', [
            'role_key' => $roleKey,
            'role_name' => $roleName,
            'description' => $desc !== '' ? $desc : null,
            'level' => $level,
            'permissions' => $perms,
            'is_system' => false,
        ]);

        if ($code === 409 || (is_array($res) && !empty($res['code']) && $res['code'] === '23505')) {
            return response()->json(['error' => 'Role key sudah dipakai.'], 409);
        }
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal membuat role: HTTP '.$code], 502);
        }

        $this->audit->log('admin_role.create', ['role_key' => $roleKey, 'level' => $level, 'new_role_id' => $res[0]['id'] ?? null]);

        return response()->json(['status' => 'ok', 'data' => $res[0] ?? null]);
    }

    /** POST ?action=update */
    public function update(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('manajemen_role', 'edit')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin mengubah role.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): validasi format
        // UUID, bukan cuma "tidak kosong" - lihat catatan sama di
        // AdminAkunApiController.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID role tidak valid.'], 400);
        }

        $target = $this->getRole($id);
        if (!$target) {
            return response()->json(['error' => 'Role tidak ditemukan.'], 404);
        }

        $currentLevel = (int) $target['level'];
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa mengubah role dengan level LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        $patch = [];
        if ($request->has('role_name')) {
            $name = trim((string) $request->input('role_name'));
            if ($name === '') {
                return response()->json(['error' => 'Nama role tidak boleh kosong.'], 400);
            }
            $patch['role_name'] = $name;
        }
        if ($request->has('description')) {
            $patch['description'] = trim((string) $request->input('description')) ?: null;
        }
        if ($request->has('permissions')) {
            $patch['permissions'] = $this->clampPermissionsToOwn($this->normalizePermissions((array) $request->input('permissions'), $this->validMenuIds()));
        }
        if ($request->has('level')) {
            if (!empty($target['is_system'])) {
                return response()->json(['error' => 'Level role bawaan sistem ('.$target['role_key'].') tidak bisa diubah.'], 400);
            }
            $newLevel = (int) $request->input('level');
            if ($newLevel < 0) {
                return response()->json(['error' => 'Level tidak boleh negatif.'], 400);
            }
            if (!$this->roles->canManageLevelStrict($newLevel)) {
                return response()->json(['error' => 'Forbidden: Anda hanya bisa mengatur level role ke yang LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
            }
            $patch['level'] = $newLevel;
        }
        if ($request->has('is_active')) {
            if (!empty($target['is_system'])) {
                return response()->json(['error' => 'Role bawaan sistem tidak bisa dinonaktifkan.'], 400);
            }
            $patch['is_active'] = (bool) $request->input('is_active');
        }

        if (empty($patch)) {
            return response()->json(['error' => 'Tidak ada perubahan yang dikirim.'], 400);
        }

        [$code, $res] = $this->supabase->rawRequest('PATCH', 'admin_roles?id=eq.'.$id, $patch);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengubah role: HTTP '.$code], 502);
        }

        $this->audit->log('admin_role.update', ['target_role_id' => $id, 'role_key' => $target['role_key'] ?? null, 'fields' => array_keys($patch)]);

        return response()->json(['status' => 'ok', 'data' => $res[0] ?? null]);
    }

    /** POST ?action=delete */
    public function delete(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('manajemen_role', 'delete')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin menghapus role.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): validasi format
        // UUID, bukan cuma "tidak kosong" - lihat catatan sama di
        // AdminAkunApiController.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID role tidak valid.'], 400);
        }

        $target = $this->getRole($id);
        if (!$target) {
            return response()->json(['error' => 'Role tidak ditemukan.'], 404);
        }
        if (!empty($target['is_system'])) {
            return response()->json(['error' => 'Role bawaan sistem tidak bisa dihapus.'], 400);
        }

        $currentLevel = (int) $target['level'];
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa menghapus role dengan level LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        [, $usedBy] = $this->supabase->rawRequest('GET', 'admin_accounts?role_id=eq.'.$id.'&select=id&limit=1');
        if (is_array($usedBy) && count($usedBy) > 0) {
            return response()->json(['error' => 'Role masih dipakai oleh akun aktif - pindahkan akun tersebut ke role lain dulu.'], 400);
        }

        [$code] = $this->supabase->rawRequest('DELETE', 'admin_roles?id=eq.'.$id);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal menghapus role: HTTP '.$code], 502);
        }

        $this->audit->log('admin_role.delete', ['target_role_id' => $id, 'role_key' => $target['role_key'] ?? null]);

        return response()->json(['status' => 'ok', 'data' => true]);
    }
}
