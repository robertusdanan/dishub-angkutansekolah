<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Pemesanan — Admin Trayek Wisata</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }
  .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
  .filter-bar select, .filter-bar input { padding:8px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; }
  .data-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; }
  .data-table { width:100%; border-collapse:collapse; }
  .data-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
  .data-table th { padding:10px 14px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); }
  .data-table td { padding:10px 14px; font-size:12.5px; color:var(--text-1); border-bottom:1px solid var(--border); vertical-align:top; }
  .empty-state { text-align:center; padding:40px; color:var(--text-4); font-size:13px; }
  .status-pill { font-size:10px; padding:2px 8px; border-radius:20px; font-weight:700; text-transform:uppercase; }
  .status-terkonfirmasi { background:#dcfce7; color:#15803d; }
  .status-dibatalkan { background:#f1f5f9; color:#64748b; }
  .nik-list { display:flex; flex-direction:column; gap:2px; font-family:'DM Mono',monospace; font-size:11.5px; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_pemesanan', 'currentModule' => 'trayekwisata'])
  <main class="adm-main"><div class="adm-content">
    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Pemesanan</h1><p class="adm-page-subtitle">Data pemesanan kursi trayek wisata — otomatis terisi begitu website publik (login Google + pesan kursi) aktif.</p></div>
      <button class="btn btn-secondary btn-sm" onclick="exportPdf()">⬇ Export PDF</button>
    </div>

    <div class="filter-bar" style="justify-content:space-between">
      <select id="fManifesJadwal" style="min-width:280px"><option value="">— Pilih trayek untuk cetak manifes —</option></select>
      <button class="btn btn-primary btn-sm" onclick="cetakManifes()">🖨 Cetak Manifes Keberangkatan</button>
    </div>

    <div class="filter-bar">
      <input type="text" id="fCari" placeholder="Cari nama / email / NIK..." oninput="renderTable()" style="min-width:220px"/>
      <select id="fStatus" onchange="renderTable()">
        <option value="">Semua Status</option>
        <option value="terkonfirmasi">Terkonfirmasi</option>
        <option value="dibatalkan">Dibatalkan</option>
      </select>
    </div>

    <div class="data-card">
      <table class="data-table">
        <thead><tr><th>Trayek &amp; Tanggal</th><th>Pemesan</th><th>Penumpang (NIK)</th><th>Kursi</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody id="tblBody"><tr><td colspan="6" class="empty-state">Memuat data…</td></tr></tbody>
      </table>
    </div>
  </div></main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
let rows = [];
async function loadData() {
  try {
    const pemesanan = await twGet('trayekwisata_pemesanan', 'select=*,akun_publik(nama,email,nik,no_hp),trayekwisata_jadwal(tanggal,jam_berangkat,trayekwisata_trayek(nama))&order=created_at.desc&limit=200');
    const ids = pemesanan.map(p => p.id);
    let nikRows = [];
    if (ids.length) {
      nikRows = await twGet('trayekwisata_pemesanan_nik', `pemesanan_id=in.(${ids.join(',')})`);
    }
    rows = pemesanan.map(p => ({ ...p, _nik: nikRows.filter(n => n.pemesanan_id === p.id) }));
    renderTable();
    populateManifesDropdown();
  } catch (e) {
    document.getElementById('tblBody').innerHTML = `<tr><td colspan="6" class="empty-state" style="color:#ef4444">Belum ada data / gagal memuat: ${twEsc(e.message)}</td></tr>`;
  }
}

function populateManifesDropdown() {
  const seen = new Map();
  rows.filter(r => r.status === 'terkonfirmasi').forEach(r => {
    const j = r.trayekwisata_jadwal;
    if (!j) return;
    const label = `${j.trayekwisata_trayek?.nama || 'Trayek'} — ${new Date(j.tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})} ${ (j.jam_berangkat||'').slice(0,5) }`;
    seen.set(r.jadwal_id, label);
  });
  const sel = document.getElementById('fManifesJadwal');
  sel.innerHTML = '<option value="">— Pilih trayek untuk cetak manifes —</option>' +
    [...seen.entries()].map(([id, label]) => `<option value="${id}">${twEsc(label)}</option>`).join('');
}

async function cetakManifes() {
  const jadwalId = document.getElementById('fManifesJadwal').value;
  if (!jadwalId) { twToast('Pilih trayek terlebih dahulu', 'error'); return; }
  const list = rows.filter(r => r.jadwal_id === jadwalId && r.status === 'terkonfirmasi');
  if (!list.length) { twToast('Tidak ada penumpang terkonfirmasi untuk trayek ini', 'error'); return; }

  const jadwal = list[0].trayekwisata_jadwal;
  const trayekNama = jadwal?.trayekwisata_trayek?.nama || 'Trayek';
  const tglLabel = jadwal ? new Date(jadwal.tanggal).toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) : '—';

  const penumpang = [];
  list.forEach(r => {
    const paxList = (r._nik && r._nik.length) ? r._nik : [{ nama: r.akun_publik?.nama || '—', nik: r.akun_publik?.nik || '—' }];
    paxList.forEach(p => penumpang.push({ nama: p.nama, nik: p.nik, hp: r.akun_publik?.no_hp || '' }));
  });

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
  const pageW = doc.internal.pageSize.getWidth();

  doc.setFillColor(10, 31, 68);
  doc.rect(0, 0, pageW, 64, 'F');
  doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(14);
  doc.text('MANIFES KEBERANGKATAN', 36, 28);
  doc.setFont('helvetica','normal'); doc.setFontSize(10);
  doc.text('Dinas Perhubungan Kabupaten Tulungagung — Trayek Wisata Gratis', 36, 44);

  doc.setTextColor(15,30,56); doc.setFontSize(12); doc.setFont('helvetica','bold');
  doc.text(trayekNama, 36, 92);
  doc.setFont('helvetica','normal'); doc.setFontSize(10);
  doc.text(`${tglLabel}  ·  Berangkat ${(jadwal?.jam_berangkat||'').slice(0,5)}  ·  Total ${penumpang.length} penumpang`, 36, 108);

  doc.autoTable({
    startY: 126,
    head: [['No', 'Nama Penumpang', 'NIK', 'No. HP Pemesan', 'Hadir?']],
    body: penumpang.map((p, i) => [String(i+1), p.nama, p.nik, p.hp, '']),
    theme: 'grid',
    styles: { font: 'helvetica', fontSize: 10, cellPadding: 8, lineColor: [200,205,220], lineWidth: 0.6 },
    headStyles: { fillColor: [26,86,219], textColor: 255, fontStyle: 'bold' },
    columnStyles: { 0: { cellWidth: 30, halign:'center' }, 4: { cellWidth: 60, halign:'center' } },
  });

  const finalY = doc.lastAutoTable.finalY + 40;
  doc.setFontSize(10);
  doc.text('Petugas Keberangkatan,', 36, finalY);
  doc.text('_____________________________', 36, finalY + 50);

  doc.save(`manifes-${trayekNama.replace(/\s+/g,'-').toLowerCase()}-${jadwal?.tanggal || ''}.pdf`);
}

function renderTable() {
  const q = document.getElementById('fCari').value.toLowerCase().trim();
  const st = document.getElementById('fStatus').value;
  let filtered = rows;
  if (st) filtered = filtered.filter(r => r.status === st);
  if (q) filtered = filtered.filter(r => {
    const hay = `${r.akun_publik?.nama||''} ${r.akun_publik?.email||''} ${r.akun_publik?.nik||''} ${(r._nik||[]).map(n=>n.nik).join(' ')}`.toLowerCase();
    return hay.includes(q);
  });

  const tbody = document.getElementById('tblBody');
  if (!filtered.length) { tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Belum ada pemesanan.</td></tr>`; return; }

  tbody.innerHTML = filtered.map(r => `
    <tr>
      <td><strong>${twEsc(r.trayekwisata_jadwal?.trayekwisata_trayek?.nama || '—')}</strong><br/><span style="color:var(--text-3)">${twEsc(r.trayekwisata_jadwal?.tanggal||'')} · ${(r.trayekwisata_jadwal?.jam_berangkat||'').slice(0,5)}</span></td>
      <td>${twEsc(r.akun_publik?.nama||'—')}<br/><span style="color:var(--text-3)">${twEsc(r.akun_publik?.email||'')}</span><br/>
        <a href="/admin/api/trayekwisata/dokumen-view?profil_id=${r.profil_id}&jenis=ktp" target="_blank" style="font-size:11px;color:var(--accent)">Lihat KTP</a></td>
      <td><div class="nik-list">${(r._nik||[]).map(n => `${twEsc(n.nama)} — ${twEsc(n.nik)}`).join('<br/>') || '—'}</div></td>
      <td>${r.jumlah_kursi}</td>
      <td><span class="status-pill status-${r.status}">${r.status}</span></td>
      <td><button class="btn btn-secondary btn-sm" onclick="copySurveiLink('${r.id}')">Salin Link Survei</button></td>
    </tr>`).join('');
}

document.getElementById('fStatus');

function copySurveiLink(pemesananId) {
  const link = `${location.origin}/trayek-wisata/survei?pemesanan_id=${pemesananId}`;
  navigator.clipboard.writeText(link).then(
    () => twToast('✓ Link survei disalin — bisa dibagikan manual lewat WA'),
    () => prompt('Salin link ini secara manual:', link)
  );
}

async function exportPdf() {
  if (!rows.length) { twToast('Tidak ada data untuk diekspor', 'error'); return; }
  if (!window.jspdf) { twToast('Library PDF belum termuat, coba lagi sesaat lagi', 'error'); return; }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
  const pageW = doc.internal.pageSize.getWidth();
  const NAVY = [10, 31, 68], BLUE = [26, 86, 219], INK = [15, 30, 56], GRAY = [126, 138, 163];

  // ── Header band ─────────────────────────────────────────────────
  doc.setFillColor(...NAVY);
  doc.rect(0, 0, pageW, 72, 'F');

  const logoDataUrl = await loadImageAsDataUrl('/assets/dishub.png').catch(() => null);
  if (logoDataUrl) {
    try { doc.addImage(logoDataUrl, 'PNG', 36, 16, 40, 40); } catch (e) {}
  }
  doc.setTextColor(255, 255, 255);
  doc.setFont('helvetica', 'bold'); doc.setFontSize(15);
  doc.text('LAPORAN PEMESANAN TRAYEK WISATA GRATIS', logoDataUrl ? 86 : 36, 32);
  doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
  doc.text('Dinas Perhubungan Kabupaten Tulungagung', logoDataUrl ? 86 : 36, 48);

  const now = new Date();
  const statusLabel = document.getElementById('fStatus').value || 'Semua Status';
  doc.setFontSize(9);
  doc.text(`Dicetak: ${now.toLocaleString('id-ID')}`, pageW - 36, 30, { align: 'right' });
  doc.text(`Filter: ${statusLabel}  ·  Total: ${rows.length} pemesanan`, pageW - 36, 44, { align: 'right' });

  // ── Tabel ────────────────────────────────────────────────────────
  const body = rows.map(r => {
    const trayek = r.trayekwisata_jadwal?.trayekwisata_trayek?.nama || '—';
    const tanggal = r.trayekwisata_jadwal?.tanggal
      ? new Date(r.trayekwisata_jadwal.tanggal).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) : '—';
    const jam = (r.trayekwisata_jadwal?.jam_berangkat || '').slice(0,5) || '—';
    const pemesan = r.akun_publik?.nama || '—';
    const penumpang = (r._nik || []).map(n => n.nama).join(', ') || pemesan;
    const nik = (r._nik || []).map(n => n.nik).join(', ') || (r.akun_publik?.nik || '—');
    const status = r.status === 'terkonfirmasi' ? 'Terkonfirmasi' : r.status === 'dibatalkan' ? 'Dibatalkan' : r.status;
    return [trayek, tanggal, jam, pemesan, penumpang, nik, String(r.jumlah_kursi), status];
  });

  doc.autoTable({
    startY: 88,
    head: [['Trayek', 'Tanggal', 'Jam', 'Pemesan', 'Penumpang', 'NIK', 'Kursi', 'Status']],
    body,
    theme: 'plain',
    styles: { font: 'helvetica', fontSize: 8.5, textColor: INK, cellPadding: 7, lineColor: [230, 234, 244], lineWidth: 0.5 },
    headStyles: { fillColor: BLUE, textColor: 255, fontStyle: 'bold', fontSize: 8.5 },
    alternateRowStyles: { fillColor: [244, 247, 252] },
    columnStyles: { 6: { halign: 'center', cellWidth: 40 }, 2: { cellWidth: 45 } },
    didParseCell: (data) => {
      if (data.section === 'body' && data.column.index === 7) {
        if (data.cell.raw === 'Terkonfirmasi') data.cell.styles.textColor = [21, 128, 61];
        if (data.cell.raw === 'Dibatalkan') data.cell.styles.textColor = [185, 28, 28];
        data.cell.styles.fontStyle = 'bold';
      }
    },
    didDrawPage: () => {
      const pageCount = doc.internal.getNumberOfPages();
      const pageH = doc.internal.pageSize.getHeight();
      doc.setFontSize(8); doc.setTextColor(...GRAY); doc.setFont('helvetica', 'normal');
      doc.text('Dishub Kabupaten Tulungagung — Sistem Trayek Wisata Gratis', 36, pageH - 20);
      doc.text(`Halaman ${doc.internal.getCurrentPageInfo().pageNumber} / ${pageCount}`, pageW - 36, pageH - 20, { align: 'right' });
    },
  });

  doc.save(`pemesanan-trayekwisata-${now.toISOString().slice(0,10)}.pdf`);
}

function loadImageAsDataUrl(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = img.width; canvas.height = img.height;
      canvas.getContext('2d').drawImage(img, 0, 0);
      resolve(canvas.toDataURL('image/png'));
    };
    img.onerror = reject;
    img.src = src;
  });
}
loadData();
</script>
</body>
</html>
