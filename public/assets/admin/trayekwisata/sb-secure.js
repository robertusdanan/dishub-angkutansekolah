/**
 * admin/assets/trayekwisata/sb-secure.js
 * Helper fetch ke admin/api/trayekwisata/db.php - pola sama dengan
 * admin/assets/angkutansekolah/sb-secure.js. Browser tidak pernah
 * menyentuh service_role key.
 */
const TW_API = '/admin/api/trayekwisata/db';

// CATATAN MIGRASI: helper baru (tidak ada di kode lama) - Laravel butuh
// CSRF token untuk request POST (termasuk yang di-override jadi PATCH/DELETE).
function _admCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.content : '';
}

async function twGet(table, qs = '') {
  const r = await fetch(`${TW_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(qs)}`);
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `${table} HTTP ${r.status}`);
  }
  return r.json();
}

async function twPost(table, body, prefer = 'return=representation') {
  const r = await fetch(`${TW_API}?table=${encodeURIComponent(table)}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-SB-Prefer': prefer, 'X-CSRF-TOKEN': _admCsrfToken() },
    body: JSON.stringify(body),
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `POST ${table} HTTP ${r.status}`);
  }
  return prefer === 'return=minimal' ? null : r.json();
}

async function twPatch(table, filterQs, body, prefer = 'return=representation') {
  const r = await fetch(`${TW_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(filterQs)}`, {
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
  return prefer === 'return=minimal' ? null : r.json();
}

async function twDelete(table, filterQs) {
  const r = await fetch(`${TW_API}?table=${encodeURIComponent(table)}&qs=${encodeURIComponent(filterQs)}`, {
    method: 'POST',
    headers: { 'X-HTTP-Method-Override': 'DELETE', 'X-CSRF-TOKEN': _admCsrfToken() },
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || e.message || `DELETE ${table} HTTP ${r.status}`);
  }
  return true;
}

function twEsc(s) {
  return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

let _twToastTimer;
function twToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast ${type} show`;
  clearTimeout(_twToastTimer);
  _twToastTimer = setTimeout(() => t.classList.remove('show'), 3200);
}
