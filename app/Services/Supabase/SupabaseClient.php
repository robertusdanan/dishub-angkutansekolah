<?php

namespace App\Services\Supabase;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

const LOCAL_TABLES = [
    'akun_publik' => 'akun_publik', 'akun_publik_keluarga' => 'akun_publik_keluarga',
    'balikgratis_pemesanan' => 'balikgratis_pemesanan', 'balikgratis_pengaturan' => 'balikgratis_pengaturan',
    'trayekwisata_trayek' => 'trayekwisata_trayek', 'trayekwisata_titik' => 'trayekwisata_titik',
    'trayekwisata_titik_galeri' => 'trayekwisata_titik_galeri', 'trayekwisata_bus' => 'trayekwisata_bus',
    'trayekwisata_jadwal' => 'trayekwisata_jadwal', 'trayekwisata_pemesanan' => 'trayekwisata_pemesanan',
    'trayekwisata_pemesanan_nik' => 'trayekwisata_pemesanan_nik', 'trayekwisata_waitlist' => 'trayekwisata_waitlist',
    'trayekwisata_hero' => 'trayekwisata_hero', 'list_tambangan' => 'asdp_lokasi',
];

const LOCAL_COLUMNS = [
    'akun_publik' => ['id','google_sub','email','nama','jenis_kelamin','nik','no_kk','foto_ktp_url','foto_kk_url','alamat_kabupaten','alamat_kecamatan','alamat_kode_pos','alamat_lengkap','no_hp','nama_sesuai_ktp','created_at','updated_at'],
    'akun_publik_keluarga' => ['id','akun_id','nik','nama','jenis_kelamin','created_at','updated_at'],
    'balikgratis_pengaturan' => ['id','tahun','kuota','status','dibuka_at','dibuka_oleh','ditutup_at','ditutup_oleh','created_at','updated_at'],
    'balikgratis_pemesanan' => ['id','akun_id','pemesanan_grup_id','tahun','nomor_tiket','qr_token','nama','nik','kategori','jenis_kelamin','alamat','status','dokumen_filename','no_hp','no_kk','ip_pendaftar','verifikasi_at','created_at','updated_at'],
    'trayekwisata_trayek' => ['id','nama','deskripsi','warna','aktif','created_at','updated_at'],
    'trayekwisata_titik' => ['id','nama','jenis','deskripsi','lat','lng','jumlah_kunjungan','urutan','aktif','created_at','updated_at'],
    'trayekwisata_titik_galeri' => ['id','titik_id','tipe','url','urutan','created_at'],
    'trayekwisata_bus' => ['id','nama','plat_nomor','kapasitas','aktif','created_at','updated_at'],
    'trayekwisata_jadwal' => ['id','trayek_id','bus_id','tanggal','hari','jam_berangkat','jam_pulang','kuota_total','kuota_terisi','status','created_at','updated_at'],
    'trayekwisata_pemesanan' => ['id','jadwal_id','profil_id','jumlah_kursi','status','checked_in_at','catatan_admin','created_at','updated_at'],
    'trayekwisata_pemesanan_nik' => ['id','pemesanan_id','jadwal_id','nik','nama','tanggal_trayek','created_at','updated_at'],
    'trayekwisata_waitlist' => ['id','jadwal_id','profil_id','created_at','updated_at'],
    'trayekwisata_hero' => ['id','tipe','url','urutan','aktif','created_at','updated_at'],
    'list_tambangan' => ['id','name','description','lat','lng','image','created','updated'],
];

class SupabaseClient
{
    protected string $url;
    protected string $anonKey;
    protected string $serviceKey;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.supabase.url'), '/');
        $this->anonKey = (string) config('services.supabase.anon_key');
        $this->serviceKey = (string) config('services.supabase.service_key');
    }

    protected function key(bool $useService): string { return $useService ? $this->serviceKey : $this->anonKey; }

    public function request(string $method, string $endpoint, ?array $body = null, array $params = [], bool $useService = true, bool $minimal = false): array
    {
        if (str_starts_with($endpoint, '/rest/v1/')) {
            $localPath = substr($endpoint, 9);
            [$localTable] = explode('?', $localPath, 2);
            if (isset(LOCAL_TABLES[$localTable])) return $this->rawRequest($method, $localPath, $body, $useService);
        }
        $key = $this->key($useService);
        $url = $this->url.$endpoint;
        if ($params) {
            $parts = [];
            foreach ($params as $k => $v) $parts[] = rawurlencode((string) $k).'='.rawurlencode((string) $v);
            $url .= '?'.implode('&', $parts);
        }
        $headers = ['Content-Type' => 'application/json', 'apikey' => $key, 'Authorization' => 'Bearer '.$key];
        if (in_array($method, ['POST', 'PATCH'], true)) $headers['Prefer'] = $minimal ? 'return=minimal' : 'return=representation';
        try {
            $response = Http::withHeaders($headers)->timeout(15)->send($method, $url, $body !== null ? ['json' => $body] : []);
            return [$response->status(), $response->json()];
        } catch (\Throwable $e) {
            Log::error('SupabaseClient request gagal: '.$e->getMessage(), ['method' => $method, 'endpoint' => $endpoint]);
            return [0, null];
        }
    }

    protected function validateColumns(string $table, array $row): void
    {
        foreach (array_keys($row) as $column) {
            if (!in_array($column, LOCAL_COLUMNS[$table] ?? [], true)) throw new \InvalidArgumentException('Invalid local column: '.$column);
        }
    }

    protected function localQuery(string $table, array $params): \Illuminate\Database\Query\Builder
    {
        $q = DB::table(LOCAL_TABLES[$table]);
        $allowed = LOCAL_COLUMNS[$table] ?? [];
        foreach ($params as $key => $value) {
            if (in_array($key, ['select','order','limit','offset'], true)) continue;
            if (!in_array($key, $allowed, true)) throw new \InvalidArgumentException('Invalid local filter: '.$key);
            [$operator, $operand] = array_pad(explode('.', (string) $value, 2), 2, null);
            $operand = rawurldecode((string) $operand);
            if ($operator === 'eq') $q->where($key, '=', $operand);
            elseif ($operator === 'neq') $q->where($key, '!=', $operand);
            elseif ($operator === 'gte') $q->where($key, '>=', $operand);
            elseif ($operator === 'gt') $q->where($key, '>', $operand);
            elseif ($operator === 'lte') $q->where($key, '<=', $operand);
            elseif ($operator === 'lt') $q->where($key, '<', $operand);
            elseif ($operator === 'is' && $operand === 'null') $q->whereNull($key);
            elseif ($operator === 'in') $q->whereIn($key, array_filter(explode(',', trim($operand, '()'))));
            else throw new \InvalidArgumentException('Invalid local operator');
        }
        if (!empty($params['order'])) {
            foreach (explode(',', $params['order']) as $order) {
                [$column, $direction] = array_pad(explode('.', trim($order), 2), 2, 'asc');
                if (!in_array($column, $allowed, true) || !in_array(strtolower($direction), ['asc','desc'], true)) throw new \InvalidArgumentException('Invalid local order');
                $q->orderBy($column, strtolower($direction));
            }
        }
        if (isset($params['offset']) && filter_var($params['offset'], FILTER_VALIDATE_INT) === false) throw new \InvalidArgumentException('Invalid local offset');
        if (isset($params['limit']) && filter_var($params['limit'], FILTER_VALIDATE_INT) === false) throw new \InvalidArgumentException('Invalid local limit');
        if (isset($params['offset'])) $q->offset((int) $params['offset']);
        if (isset($params['limit'])) $q->limit((int) $params['limit']);
        return $q;
    }

    public function rawRequest(string $method, string $pathWithQuery, mixed $body = null, bool $useService = true): array
    {
        [$table, $query] = array_pad(explode('?', $pathWithQuery, 2), 2, '');
        if (!isset(LOCAL_TABLES[$table])) return $this->request($method, '/rest/v1/'.$pathWithQuery, $body, [], $useService);
        try {
            parse_str($query, $params);
            $q = $this->localQuery($table, $params);
            if ($method === 'GET') {
                $rows = array_map(fn ($r) => (array) $r, $q->get()->all());
                if ($table === 'trayekwisata_pemesanan' && isset($params['select']) && str_contains($params['select'], 'trayekwisata_jadwal')) {
                    foreach ($rows as &$row) {
                        $jadwal = DB::table('trayekwisata_jadwal')->where('id', $row['jadwal_id'] ?? '')->first();
                        $jadwal = $jadwal ? (array) $jadwal : [];
                        if (preg_match('/trayekwisata_jadwal\(([^)]*)\)/', $params['select'], $match)) {
                            $fields = array_filter(array_map('trim', explode(',', $match[1])));
                            $jadwal = array_intersect_key($jadwal, array_flip(array_filter($fields, fn ($f) => !str_contains($f, '('))));
                            if (str_contains($match[1], 'trayekwisata_trayek')) {
                                $trayek = DB::table('trayekwisata_trayek')->where('id', $jadwal['trayek_id'] ?? '')->first();
                                $jadwal['trayekwisata_trayek'] = $trayek ? (array) $trayek : null;
                            }
                        }
                        $row['trayekwisata_jadwal'] = $jadwal;
                    }
                    unset($row);
                }
                return [200, $rows];
            }
            if (!is_array($body)) return [400, ['message' => 'Request body must be object or array']];
            if ($method === 'POST') {
                $rows = array_is_list($body) ? $body : [$body];
                foreach ($rows as &$row) { if (!is_array($row)) return [400, ['message' => 'Invalid request row']]; $row['id'] ??= (string) Str::uuid(); $this->validateColumns($table, $row); $q->insert($row); }
                return [201, $rows];
            }
            if ($method === 'PATCH') { $this->validateColumns($table, $body); $count = $q->update($body); return [200, $count ? [$body] : []]; }
            if ($method === 'DELETE') { $q->delete(); return [204, []]; }
            return [405, ['message' => 'Method not allowed']];
        } catch (\InvalidArgumentException $e) {
            return [400, ['message' => $e->getMessage()]];
        } catch (QueryException $e) {
            Log::error('Local SQLite request gagal', ['table' => $table, 'error' => $e->getMessage()]);
            return [500, ['message' => 'Local database error']];
        }
    }

    public function select(string $table, array $filters = [], ?int $limit = null, ?string $order = null, bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) { $query = $filters; if ($limit) $query['limit'] = $limit; if ($order) $query['order'] = $order; return $this->rawRequest('GET', $table.'?'.http_build_query($query), null, $useService); }
        $params = $filters; if ($limit) $params['limit'] = $limit; if ($order) $params['order'] = $order; $params['select'] = '*';
        return $this->request('GET', "/rest/v1/{$table}", null, $params, $useService);
    }

    public function insert(string $table, array $data, bool $useService = true): array
    { return isset(LOCAL_TABLES[$table]) ? $this->rawRequest('POST', $table, $data, $useService) : $this->request('POST', "/rest/v1/{$table}", $data, [], $useService); }

    public function upsert(string $table, array $data, string $onConflict = 'id', bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) {
            if (!in_array($onConflict, LOCAL_COLUMNS[$table] ?? [], true)) return [400, ['message' => 'Invalid conflict column']];
            $rows = array_is_list($data) ? $data : [$data]; $out = [];
            foreach ($rows as $row) {
                if (!is_array($row)) return [400, ['message' => 'Invalid request row']];
                $row['id'] ??= (string) Str::uuid(); $this->validateColumns($table, $row);
                $existing = DB::table(LOCAL_TABLES[$table])->where($onConflict, $row[$onConflict] ?? null)->first();
                if ($existing) DB::table(LOCAL_TABLES[$table])->where($onConflict, $row[$onConflict])->update($row); else DB::table(LOCAL_TABLES[$table])->insert($row);
                $out[] = $row;
            }
            return [200, $out];
        }
        $key = $this->key($useService); $url = "{$this->url}/rest/v1/{$table}?on_conflict=".rawurlencode($onConflict);
        try { $response = Http::withHeaders(['Content-Type'=>'application/json','apikey'=>$key,'Authorization'=>'Bearer '.$key,'Prefer'=>'resolution=merge-duplicates,return=representation'])->timeout(15)->post($url, $data); return [$response->status(), $response->json()]; }
        catch (\Throwable $e) { Log::error('SupabaseClient upsert gagal: '.$e->getMessage(), ['table'=>$table]); return [0, null]; }
    }

    public function update(string $table, array $data, array $filters, bool $minimal = false, bool $useService = true): array
    { return isset(LOCAL_TABLES[$table]) ? $this->rawRequest('PATCH', $table.'?'.http_build_query($filters), $data, $useService) : $this->request('PATCH', "/rest/v1/{$table}", $data, $filters, $useService, $minimal); }

    public function delete(string $table, array $filters, bool $useService = true): array
    { return isset(LOCAL_TABLES[$table]) ? $this->rawRequest('DELETE', $table.'?'.http_build_query($filters), null, $useService) : $this->request('DELETE', "/rest/v1/{$table}", null, $filters, $useService); }
}
