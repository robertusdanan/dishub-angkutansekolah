/**
 * admin/assets/angkutansekolah/sb-secure.js
 * ─────────────────────────────────────────────────────────────────
 * Pengganti SB_URL + SB_KEY (service_role) yang dulu tertanam
 * langsung di HTML tiap halaman admin. Sekarang browser TIDAK
 * pernah menyimpan service key - semua request ditembakkan ke
 * admin/api/angkutansekolah/db.php (server kita sendiri), yang baru di sisi server
 * menempelkan service key dari private/config.php.
 *
 * Signature sengaja dibuat sama persis dengan fungsi lama
 * (sbGet/sbPost/sbPatch/sbDelete/sbGetAll) supaya kode tiap halaman
 * tidak perlu ditulis ulang total.
 * ─────────────────────────────────────────────────────────────────
 */
const SB_API = '/admin/api/angkutansekolah/db';

// CATATAN MIGRASI: helper baru (tidak ada di kode lama) - Laravel butuh
// CSRF token untuk request POST (termasuk yang di-override jadi PATCH/DELETE).
function _admCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.content : '';
}

// ── Invalidasi cache proxy di server (supabase_proxy.php) ─────────────────
// Dipanggil best-effort (TIDAK di-await, TIDAK boleh menggagalkan alur
// simpan) setiap kali sbPost/sbPatch/sbDelete sukses, supaya cache tabel
// referensi (driver_bus, driver_mpu, sekolah, domisili, trayek_*, rute_*,
// map) langsung segar di halaman lain (operasional.php, absensifoto.php,
// rfidwriter.php, registrasi.php, dll) - tanpa menunggu webhook Supabase
// (yang bisa telat/gagal kalau trigger belum terpasang atau jaringan
// bermasalah). admin/api/invalidate_cache.php sendiri yang menentukan
// apakah tabel ini termasuk tabel referensi yang di-cache atau bukan,
// jadi aman dipanggil untuk tabel APA SAJA (tabel non-referensi otomatis
// diabaikan di sisi server).
function _invalidateServerCache(table) {
  return fetch('/admin/api/angkutansekolah/invalidate-cache', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ table }),
  }).catch((e) => {
    // best-effort - jangan sampai ganggu alur simpan, tapi tetap jejak di console
    console.warn('Gagal invalidasi cache untuk tabel', table, e);
  });
}

async function sbGet(table, qs = '') {
  const r = await fetch(`${SB_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(qs)}`);
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `${table} HTTP ${r.status}`);
  }
  return r.json();
}

// Ambil satu halaman (Range) - dipakai untuk paging manual bila perlu.
async function sbGetRange(table, qs, from, to) {
  const r = await fetch(`${SB_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(qs)}`, {
    headers: { 'X-SB-Range': `${from}-${to}` },
  });
  if (!r.ok) throw new Error(`${table} HTTP ${r.status}`);
  return r.json();
}

// Ambil semua baris (>1000) dengan auto-paging, menggantikan pola lama.
async function sbGetAll(table, queryParts = []) {
  const qs = Array.isArray(queryParts) ? queryParts.join('&') : String(queryParts || '');
  const all = [];
  let from = 0;
  const PAGE = 1000;
  while (true) {
    const rows = await sbGetRange(table, qs, from, from + PAGE - 1);
    all.push(...rows);
    if (!Array.isArray(rows) || rows.length < PAGE) break;
    from += PAGE;
  }
  return all;
}

async function sbPost(table, body, prefer = 'return=representation') {
  const r = await fetch(`${SB_API}?table=${encodeURIComponent(table)}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-SB-Prefer': prefer, 'X-CSRF-TOKEN': _admCsrfToken() },
    body: JSON.stringify(body),
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `POST ${table} HTTP ${r.status}`);
  }
  await _invalidateServerCache(table);
  return prefer === 'return=minimal' ? null : r.json();
}

async function sbPatch(table, filterQs, body, prefer = 'return=representation') {
  const r = await fetch(`${SB_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(filterQs)}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-SB-Prefer': prefer,
      'X-HTTP-Method-Override': 'PATCH',
      'X-CSRF-TOKEN': _admCsrfToken(),
    },
    body: JSON.stringify(body),
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `PATCH ${table} HTTP ${r.status}`);
  }
  await _invalidateServerCache(table);
  return prefer === 'return=minimal' ? null : r.json();
}

async function sbDelete(table, filterQs) {
  const r = await fetch(`${SB_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(filterQs)}`, {
    method: 'POST',
    headers: { 'X-HTTP-Method-Override': 'DELETE', 'X-CSRF-TOKEN': _admCsrfToken() },
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `DELETE ${table} HTTP ${r.status}`);
  }
  await _invalidateServerCache(table);
  return true;
}

/**
 * ── Cache SERVER bersama (Tier A) untuk tabel referensi ───────────
 * Baca lewat api/supabase_proxy.php (anon key publik, BUKAN service_role)
 * yang sudah punya cache file permanen di server (TTL aman 24 jam,
 * dihapus otomatis begitu ada sbPost/sbPatch/sbDelete ke tabel yang sama
 * lewat _invalidateServerCache() di atas - lihat core/cache_helper.php).
 *
 * Beda dengan sbGetCached() (cache localStorage per-browser, TIDAK
 * dibagi antar admin): cache di sini SATU FILE dipakai bersama oleh
 * SEMUA admin yang buka halaman yang sama, bahkan dipakai bersama
 * halaman publik (mis. peta ASDP, tampilan rute). Jadi kalau ada
 * puluhan admin buka halaman yang sama lalu 1 admin menyimpan
 * perubahan, cuma admin PERTAMA yang reload setelah itu yang benar2
 * menembak Supabase - sisanya kena cache hit (nol request tambahan).
 *
 * Dipakai untuk tabel yang memang sudah terdaftar di $allowed_tables
 * pada api/supabase_proxy.php (trayek_bus/mpu, map, driver_bus/mpu,
 * user_RFID, domisili, sekolah, list_tambangan).
 * Tabel di luar itu akan ditolak (HTTP 400) oleh proxy - pakai sbGet()
 * biasa untuk tabel yang memang tidak cocok di-cache (mis. data yang
 * terus berubah tiap detik/menit seperti absensi harian).
 */
async function sbGetShared(table, qs = '') {
  const url = `/api/supabase-proxy?table=${encodeURIComponent(table)}${qs ? '&' + qs : ''}`;
  const r = await fetch(url);
  const body = await r.json().catch(() => ({}));
  if (!r.ok) {
    throw new Error(body.error || body.message || `${table} HTTP ${r.status}`);
  }
  return body;
}

/**
 * ── Cache lokal (localStorage) untuk tabel REFERENSI ──────────────
 * Ditujukan untuk tabel kecil yang jarang berubah tapi dibaca ulang di
 * banyak halaman admin (sekolah, trayek, armada bus/mpu, rute, peta,
 * domisili) - BUKAN untuk tabel besar yang terus tumbuh (absensi, siswa).
 *
 * CATATAN: cache ini per-BROWSER (tidak dibagi antar admin lain), jadi
 * untuk tabel yang sudah didukung sbGetShared() di atas, pakai itu saja
 * (lebih hemat egress lintas-admin).
 *
 * Cache otomatis dianggap basi setelah SB_CACHE_TTL_MS (jaga-jaga kalau
 * event realtime sempat terlewat, mis. tab sempat offline), dan dihapus
 * langsung begitu ada perubahan nyata lewat sbCacheInvalidate() yang
 * dipanggil dari handler realtime tiap halaman.
 */
const SB_CACHE_TTL_MS = 10 * 60 * 1000; // 10 menit
const SB_CACHE_PREFIX = 'sbcache:';

function _sbCacheKey(table, qs) {
  return SB_CACHE_PREFIX + table + ':' + (qs || '');
}

async function sbGetCached(table, qs = '', ttlMs = SB_CACHE_TTL_MS) {
  const key = _sbCacheKey(table, qs);
  try {
    const raw = localStorage.getItem(key);
    if (raw) {
      const { data, ts } = JSON.parse(raw);
      if (Date.now() - ts < ttlMs) return data; // cache hit - nol request ke server
    }
  } catch (_) { /* localStorage penuh/nonaktif - lanjut fetch biasa, tidak fatal */ }

  const data = await sbGet(table, qs);
  try {
    localStorage.setItem(key, JSON.stringify({ data, ts: Date.now() }));
  } catch (_) { /* gagal simpan (kuota penuh dsb) - tidak fatal, data tetap valid */ }
  return data;
}

// Hapus semua entri cache milik satu tabel - dipanggil dari handler
// realtime tiap halaman begitu ada INSERT/UPDATE/DELETE nyata, supaya
// halaman lain (atau kunjungan berikutnya) tidak menampilkan data basi.
function sbCacheInvalidate(table) {
  try {
    const prefix = SB_CACHE_PREFIX + table + ':';
    Object.keys(localStorage)
      .filter(k => k.startsWith(prefix))
      .forEach(k => localStorage.removeItem(k));
  } catch (_) {}
}

/**
 * ── Modal konfirmasi hapus (pengganti confirm() bawaan browser) ──
 * Kartu elegan dengan peringatan tambahan bahwa aksi hapus bersifat
 * permanen dan datanya tidak bisa dikembalikan/di-backup.
 *
 * Pemakaian (di dalam fungsi async):
 *   if (!(await confirmDangerModal(`Hapus "${nama}"?`))) return;
 *
 * Mengembalikan Promise<boolean> - true jika user menekan "Ya, Hapus".
 */
function _ensureConfirmModalEl() {
  if (document.getElementById('confirmModalOverlay')) return;
  const wrap = document.createElement('div');
  wrap.innerHTML = `
    <div class="confirm-modal-overlay" id="confirmModalOverlay">
      <div class="confirm-modal-box">
        <div class="confirm-modal-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
        </div>
        <div class="confirm-modal-title" id="confirmModalTitle">Hapus data ini?</div>
        <div class="confirm-modal-sub" id="confirmModalSub"></div>
        <div class="confirm-modal-warn">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Tindakan ini <strong>permanen</strong> - data yang sudah dihapus <strong>tidak bisa dikembalikan atau di-backup</strong>.</span>
        </div>
        <div class="confirm-modal-actions">
          <button type="button" class="confirm-modal-cancel" id="confirmModalCancel">Batal</button>
          <button type="button" class="confirm-modal-danger" id="confirmModalOk">Ya, Hapus</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(wrap.firstElementChild);
}

function confirmDangerModal(message, title = 'Hapus data ini?') {
  _ensureConfirmModalEl();
  const overlay = document.getElementById('confirmModalOverlay');
  document.getElementById('confirmModalTitle').textContent = title;
  document.getElementById('confirmModalSub').textContent   = message;
  overlay.classList.add('open');

  return new Promise((resolve) => {
    const okBtn     = document.getElementById('confirmModalOk');
    const cancelBtn = document.getElementById('confirmModalCancel');

    const cleanup = (result) => {
      overlay.classList.remove('open');
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      overlay.removeEventListener('click', onOverlay);
      document.removeEventListener('keydown', onKey);
      resolve(result);
    };
    const onOk      = () => cleanup(true);
    const onCancel  = () => cleanup(false);
    const onOverlay = (e) => { if (e.target === overlay) cleanup(false); };
    const onKey     = (e) => {
      if (e.key === 'Escape') cleanup(false);
      if (e.key === 'Enter')  cleanup(true);
    };

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    overlay.addEventListener('click', onOverlay);
    document.addEventListener('keydown', onKey);
  });
}
