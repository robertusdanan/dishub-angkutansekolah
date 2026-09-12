<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AuditLogService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Port dari admin/api/akun.php - satu-satunya tempat yang boleh menulis ke
 * tabel admin_accounts (lewat SupabaseClient dengan service key, TIDAK
 * PERNAH dikirim ke browser).
 *
 * Aturan hierarki (dicek di sini, bukan cuma UI - lihat komentar lengkap
 * di kode lama, dipertahankan 1:1):
 *   - Daftar akun: level SETARA tetap TERLIHAT (read-only); level LEBIH
 *     TINGGI disembunyikan total.
 *   - Aksi (ubah, nonaktifkan, hapus, reset password): HANYA ke akun
 *     dengan level LEBIH RENDAH (strict, bukan setara) - canManageLevelStrict().
 *   - Penetapan role (create/pindah role): role tujuan WAJIB level LEBIH
 *     RENDAH dari level akun yang login - canManageLevelStrict().
 *   - Superadmin selalu boleh (dicek via isSuperAdmin(), bukan angka level).
 *   - Akun tidak boleh menghapus/menonaktifkan dirinya sendiri.
 */
class AdminAkunApiController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected AuditLogService $audit,
    ) {
    }

    protected function requireAccess(): ?JsonResponse
    {
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'view')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya akses ke menu Akun.'], 403);
        }

        return null;
    }

    protected function myAccountId(): ?string
    {
        return Session::get('admin_account_id');
    }

    protected function getRoleLevel(string $roleId): ?int
    {
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): validasi di sini
        // (bukan di tiap pemanggil) supaya SEMUA jalur ke fungsi ini
        // otomatis terlindungi dari filter injection PostgREST.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $roleId)) {
            return null;
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_roles?id=eq.'.$roleId.'&select=id,level,is_active&limit=1');
        if ($code < 200 || $code >= 300 || empty($rows)) {
            return null;
        }

        return (int) $rows[0]['level'];
    }

    protected function getAccount(string $id): ?array
    {
        // PERBAIKAN KEAMANAN: defense-in-depth, lihat catatan di getRoleLevel().
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return null;
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$id
            .'&select=id,username,full_name,role_id,is_active,must_change_password,last_login_at,created_at,admin_roles(id,role_key,role_name,level)&limit=1');
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

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?select=id,username,full_name,role_id,is_active,must_change_password,last_login_at,created_at,admin_roles(id,role_key,role_name,level)&order=created_at.asc');
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengambil data akun: HTTP '.$code], 502);
        }

        $myLevel = $this->roles->currentRoleLevel();
        $myAccountId = $this->myAccountId();

        $filtered = array_values(array_filter((array) $rows, function ($r) use ($myLevel, $myAccountId) {
            if ($this->roles->isSuperAdmin()) {
                return true;
            }
            if (($r['id'] ?? null) === $myAccountId) {
                return true;
            }
            $lvl = (int) ($r['admin_roles']['level'] ?? 0);

            return $lvl <= $myLevel;
        }));

        return response()->json(['status' => 'ok', 'data' => $filtered]);
    }

    /** POST ?action=create */
    public function create(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'create')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin membuat akun.'], 403);
        }

        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');
        $fullName = trim((string) $request->input('full_name', ''));
        $roleId = trim((string) $request->input('role_id', ''));

        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
            return response()->json(['error' => 'Username tidak valid (3-50 karakter, huruf/angka/._- saja).'], 400);
        }
        if (strlen($password) < 8) {
            return response()->json(['error' => 'Password minimal 8 karakter.'], 400);
        }
        if ($roleId === '') {
            return response()->json(['error' => 'Role wajib dipilih.'], 400);
        }

        $targetLevel = $this->getRoleLevel($roleId);
        if ($targetLevel === null) {
            return response()->json(['error' => 'Role tidak ditemukan.'], 400);
        }
        if (!$this->roles->canManageLevelStrict($targetLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa membuat akun dengan role yang levelnya LEBIH RENDAH dari level Anda sendiri (tidak boleh setara).'], 403);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        [$code, $res] = $this->supabase->rawRequest('POST', 'admin_accounts', [
            'username' => $username,
            'password_hash' => $hash,
            'full_name' => $fullName !== '' ? $fullName : null,
            'role_id' => $roleId,
            'is_active' => true,
            'created_by' => $this->myAccountId(),
        ]);

        if ($code === 409 || (is_array($res) && !empty($res['code']) && $res['code'] === '23505')) {
            return response()->json(['error' => 'Username sudah dipakai.'], 409);
        }
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal membuat akun: HTTP '.$code], 502);
        }

        $this->audit->log('admin_akun.create', ['username' => $username, 'role_id' => $roleId, 'new_account_id' => $res[0]['id'] ?? null]);

        return response()->json(['status' => 'ok', 'data' => $res[0] ?? null]);
    }

    /** POST ?action=update */
    public function update(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'edit')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin mengubah akun.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): sebelumnya cuma
        // dicek "tidak kosong", padahal $id masuk mentah ke query string
        // (bukan lewat array filter yang auto-encode). Validasi format UUID
        // di sini mencegah filter injection PostgREST (mis. menyisipkan
        // "&"/"," untuk memperluas cakupan PATCH/DELETE di luar 1 baris).
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID akun tidak valid.'], 400);
        }

        $target = $this->getAccount($id);
        if (!$target) {
            return response()->json(['error' => 'Akun tidak ditemukan.'], 404);
        }

        $currentLevel = (int) ($target['admin_roles']['level'] ?? 0);
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa mengubah akun dengan role yang levelnya LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        $patch = [];
        if ($request->has('full_name')) {
            $patch['full_name'] = trim((string) $request->input('full_name')) ?: null;
        }
        if ($request->has('must_change_password')) {
            $patch['must_change_password'] = (bool) $request->input('must_change_password');
        }
        if ($request->filled('role_id')) {
            $newRoleId = (string) $request->input('role_id');
            $newLevel = $this->getRoleLevel($newRoleId);
            if ($newLevel === null) {
                return response()->json(['error' => 'Role baru tidak ditemukan.'], 400);
            }
            if (!$this->roles->canManageLevelStrict($newLevel)) {
                return response()->json(['error' => 'Forbidden: Anda hanya bisa memindahkan akun ke role yang levelnya LEBIH RENDAH dari level Anda sendiri (tidak boleh setara).'], 403);
            }
            if ($id === $this->myAccountId() && $newLevel < $this->roles->currentRoleLevel() && !$this->roles->isSuperAdmin()) {
                return response()->json(['error' => 'Tidak bisa menurunkan role akun Anda sendiri.'], 400);
            }
            $patch['role_id'] = $newRoleId;
        }

        if (empty($patch)) {
            return response()->json(['error' => 'Tidak ada perubahan yang dikirim.'], 400);
        }

        [$code, $res] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$id, $patch);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengubah akun: HTTP '.$code], 502);
        }

        $this->audit->log('admin_akun.update', ['target_account_id' => $id, 'fields' => array_keys($patch)]);

        return response()->json(['status' => 'ok', 'data' => $res[0] ?? null]);
    }

    /** POST ?action=toggle_active */
    public function toggleActive(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'deactivate')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin menonaktifkan akun.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        $isActive = (bool) $request->input('is_active', true);
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID akun tidak valid.'], 400);
        }
        if ($id === $this->myAccountId()) {
            return response()->json(['error' => 'Tidak bisa menonaktifkan akun Anda sendiri.'], 400);
        }

        $target = $this->getAccount($id);
        if (!$target) {
            return response()->json(['error' => 'Akun tidak ditemukan.'], 404);
        }

        $currentLevel = (int) ($target['admin_roles']['level'] ?? 0);
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa menonaktifkan/mengaktifkan akun dengan role yang levelnya LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        [$code, $res] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$id, ['is_active' => $isActive]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengubah status akun: HTTP '.$code], 502);
        }

        $this->audit->log('admin_akun.toggle_active', ['target_account_id' => $id, 'is_active' => $isActive]);

        return response()->json(['status' => 'ok', 'data' => $res[0] ?? null]);
    }

    /** POST ?action=reset_password */
    public function resetPassword(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'edit')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin mengubah password akun.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        $password = (string) $request->input('new_password', '');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID akun tidak valid.'], 400);
        }
        if (strlen($password) < 8) {
            return response()->json(['error' => 'Password minimal 8 karakter.'], 400);
        }

        $target = $this->getAccount($id);
        if (!$target) {
            return response()->json(['error' => 'Akun tidak ditemukan.'], 404);
        }

        $currentLevel = (int) ($target['admin_roles']['level'] ?? 0);
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa mereset password akun dengan role yang levelnya LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        [$code] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$id, [
            'password_hash' => $hash,
            'must_change_password' => false,
        ]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengubah password: HTTP '.$code], 502);
        }

        $this->audit->log('admin_akun.reset_password', ['target_account_id' => $id]);

        return response()->json(['status' => 'ok', 'data' => true]);
    }

    /** POST ?action=delete */
    public function delete(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('akun', 'delete')) {
            return response()->json(['error' => 'Forbidden: Anda tidak punya izin menghapus akun.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): sebelumnya cuma
        // dicek "tidak kosong", padahal $id masuk mentah ke query string
        // (bukan lewat array filter yang auto-encode). Validasi format UUID
        // di sini mencegah filter injection PostgREST (mis. menyisipkan
        // "&"/"," untuk memperluas cakupan PATCH/DELETE di luar 1 baris).
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID akun tidak valid.'], 400);
        }
        if ($id === $this->myAccountId()) {
            return response()->json(['error' => 'Tidak bisa menghapus akun Anda sendiri.'], 400);
        }

        $target = $this->getAccount($id);
        if (!$target) {
            return response()->json(['error' => 'Akun tidak ditemukan.'], 404);
        }

        $currentLevel = (int) ($target['admin_roles']['level'] ?? 0);
        if (!$this->roles->canManageLevelStrict($currentLevel)) {
            return response()->json(['error' => 'Forbidden: Anda hanya bisa menghapus akun dengan role yang levelnya LEBIH RENDAH dari level Anda (tidak boleh setara).'], 403);
        }

        // Safety net: jangan biarkan superadmin aktif terakhir terhapus.
        if (($target['admin_roles']['role_key'] ?? '') === 'superadmin') {
            [, $others] = $this->supabase->rawRequest('GET', 'admin_accounts?select=id,is_active,admin_roles!inner(role_key)&is_active=eq.true&admin_roles.role_key=eq.superadmin');
            if (is_array($others) && count($others) <= 1) {
                return response()->json(['error' => 'Tidak bisa menghapus superadmin aktif terakhir.'], 400);
            }
        }

        [$code] = $this->supabase->rawRequest('DELETE', 'admin_accounts?id=eq.'.$id);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal menghapus akun: HTTP '.$code], 502);
        }

        $this->audit->log('admin_akun.delete', ['target_account_id' => $id, 'target_username' => $target['username'] ?? null]);

        return response()->json(['status' => 'ok', 'data' => true]);
    }
}
