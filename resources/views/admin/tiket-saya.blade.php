<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Tiket Saya — Dishub Tulungagung</title>
<link rel="canonical" href="{{ url('/admin/tiket-saya') }}"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .tiket-grid { display: grid; gap: 14px; max-width: 760px; }
  .tiket-card { padding: 18px 20px; display: flex; justify-content: space-between; gap: 16px; align-items: center; }
  .tiket-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 600; }
  .tiket-badge.terkonfirmasi, .tiket-badge.terdaftar { background: #e6f4ea; color: #1a7f37; }
  .tiket-badge.menunggu   { background: #fff4e5; color: #b45309; }
  .tiket-badge.dibatalkan { background: #fde8e8; color: #b91c1c; }
  .tiket-empty { padding: 40px 20px; text-align: center; color: var(--text-4); }
  .tiket-section-title { font-size: 13px; font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: .04em; margin: 22px 0 10px; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'tiket_saya', 'currentModule' => null])
  <main class="adm-main">
    <div class="adm-content">
      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Tiket Saya</h1>
          <p class="adm-page-subtitle">Riwayat pemesanan atas nama {{ $namaAkun }}</p>
        </div>
      </div>

      <div id="tiketWrap">
        <p style="color:var(--text-4);">Memuat tiket…</p>
      </div>
    </div>
  </main>
</div>

<script>
(function () {
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
  const wrap = document.getElementById('tiketWrap');
  const badgeClass = (s) => {
    s = (s || '').toLowerCase();
    if (['terkonfirmasi','terdaftar','aktif'].includes(s)) return 'terkonfirmasi';
    if (s === 'dibatalkan') return 'dibatalkan';
    return 'menunggu';
  };
  const fmtTanggal = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) : '—';

  function renderSection(title, items, renderItem) {
    if (!items.length) return '';
    let html = `<div class="tiket-section-title">${title}</div><div class="tiket-grid">`;
    items.forEach(it => { html += renderItem(it); });
    return html + '</div>';
  }

  fetch('/admin/api/tiket-saya')
    .then(r => r.json())
    .then(data => {
      if (data.error) { wrap.innerHTML = `<p style="color:#b91c1c;">${data.error}</p>`; return; }

      let html = '';
      html += renderSection('Trayek Wisata', data.trayekwisata || [], (t) => `
        <div class="card tiket-card">
          <div>
            <div style="font-weight:600;">${t.nama_trayek}</div>
            <div style="font-size:13px;color:var(--text-4);">${fmtTanggal(t.tanggal)} ${t.jam ? '· ' + t.jam.slice(0,5) : ''} · ${t.jumlah_kursi || 1} kursi</div>
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="tiket-badge ${badgeClass(t.status)}">${t.status}</span>
            ${t.status === 'terkonfirmasi' && !t.checked_in ? `<button class="btn-batal" data-id="${t.id}" style="border:1px solid #fca5a5;color:#b91c1c;background:none;border-radius:8px;padding:5px 10px;font-size:12px;cursor:pointer;">Batalkan</button>` : ''}
          </div>
        </div>`);

      html += renderSection('Balik Gratis', data.balikgratis || [], (t) => `
        <div class="card tiket-card">
          <div>
            <div style="font-weight:600;">${t.nomor_tiket}</div>
            <div style="font-size:13px;color:var(--text-4);">Balik Gratis ${t.tahun} · ${t.kategori || ''}</div>
          </div>
          <span class="tiket-badge ${badgeClass(t.status)}">${t.status}</span>
        </div>`);

      if (!html) {
        html = `<div class="card tiket-empty">
          Belum ada tiket yang dipesan.<br/>
          <a href="/trayek-wisata" style="color:var(--accent);font-weight:600;">Pesan Trayek Wisata</a> ·
          <a href="/balikgratis" style="color:var(--accent);font-weight:600;">Daftar Balik Gratis</a>
        </div>`;
      }
      wrap.innerHTML = html;
      wrap.querySelectorAll('.btn-batal').forEach((btn) => {
        btn.addEventListener('click', async () => {
          if (!confirm('Batalkan pemesanan ini? Kursi akan dilepas kembali.')) return;
          btn.disabled = true; btn.textContent = 'Membatalkan…';
          try {
            const res = await fetch('/trayek-wisata/api/batalkan-pesanan', {
              method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
              body: JSON.stringify({ pemesanan_id: btn.dataset.id }),
            });
            const d = await res.json();
            if (!res.ok || d.error) throw new Error(d.error || 'Gagal membatalkan.');
            location.reload();
          } catch (e) {
            alert(e.message);
            btn.disabled = false; btn.textContent = 'Batalkan';
          }
        });
      });
    })
    .catch(() => { wrap.innerHTML = '<p style="color:#b91c1c;">Gagal memuat tiket. Coba muat ulang halaman.</p>'; });
})();
</script>
</body>
</html>
