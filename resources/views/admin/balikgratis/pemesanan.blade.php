<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Data Pemesanan — Admin Balik Gratis</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }
  .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
  .filter-bar select, .filter-bar input { padding:8px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; }
  .data-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow-x:auto; -webkit-overflow-scrolling:touch; overflow-x:auto; }
  .data-table { width:100%; border-collapse:collapse; min-width:900px; }
  .data-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
  .data-table th { padding:10px 14px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); white-space:nowrap; }
  .data-table td { padding:10px 14px; font-size:12.5px; color:var(--text-1); border-bottom:1px solid var(--border); vertical-align:top; }
  .empty-state { text-align:center; padding:40px; color:var(--text-4); font-size:13px; }
  .status-pill { font-size:10px; padding:2px 8px; border-radius:20px; font-weight:700; text-transform:uppercase; }
  .status-terdaftar { background:#e0f2fe; color:#0369a1; }
  .status-hadir { background:#dcfce7; color:#15803d; }
  .status-dibatalkan { background:#f1f5f9; color:#64748b; }
  .mono { font-family:'DM Mono',monospace; font-size:11.5px; }
  .link-btn { font-size:11px; color:var(--accent); background:none; border:none; cursor:pointer; padding:0; text-decoration:underline; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'balikgratis_pemesanan', 'currentModule' => 'balikgratis'])
  <main class="adm-main"><div class="adm-content">
    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Data Pemesanan</h1><p class="adm-page-subtitle">Data pendaftar Balik Gratis, terpisah otomatis per tahun (tabel Supabase tetap sama, difilter kolom tahun).</p></div>
      <button class="btn btn-secondary btn-sm" onclick="exportPdf()">⬇ Export PDF</button>
    </div>

    <div class="filter-bar">
      <select id="fTahun" onchange="loadData()"></select>
      <input type="text" id="fCari" placeholder="Cari nama / NIK / no. tiket..." oninput="renderTable()" style="min-width:220px"/>
      <select id="fStatus" onchange="renderTable()">
        <option value="">Semua Status</option>
        <option value="terdaftar">Terdaftar</option>
        <option value="hadir">Hadir</option>
        <option value="dibatalkan">Dibatalkan</option>
      </select>
    </div>

    <div class="data-card">
      <table class="data-table">
        <thead><tr><th>No. Tiket</th><th>Nama</th><th>NIK / No. HP</th><th>Kategori</th><th>Alamat</th><th>Dokumen</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody id="tblBody"><tr><td colspan="9" class="empty-state">Memuat data…</td></tr></tbody>
      </table>
    </div>
  </div></main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="/assets/admin/balikgratis/sb-secure.js"></script>
<script>
let rows = [];

function initTahun() {
  const y = new Date().getFullYear();
  const sel = document.getElementById('fTahun');
  sel.innerHTML = [y-2,y-1,y,y+1].map(v => `<option value="${v}" ${v===y?'selected':''}>${v}${v===y?' (tahun ini)':''}</option>`).join('');
}

async function loadData() {
  const tahun = document.getElementById('fTahun').value;
  document.getElementById('tblBody').innerHTML = `<tr><td colspan="9" class="empty-state">Memuat data…</td></tr>`;
  try {
    rows = await bgGet('balikgratis_pemesanan', `tahun=eq.${tahun}&select=*&order=created_at.desc&limit=1000`);
    renderTable();
  } catch (e) {
    document.getElementById('tblBody').innerHTML = `<tr><td colspan="9" class="empty-state" style="color:#ef4444">Gagal memuat: ${bgEscAdm(e.message)}</td></tr>`;
  }
}

function renderTable() {
  const q = document.getElementById('fCari').value.toLowerCase().trim();
  const st = document.getElementById('fStatus').value;
  let filtered = rows;
  if (st) filtered = filtered.filter(r => r.status === st);
  if (q) filtered = filtered.filter(r => `${r.nama} ${r.nik} ${r.nomor_tiket}`.toLowerCase().includes(q));

  const tbody = document.getElementById('tblBody');
  if (!filtered.length) { tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Belum ada pendaftar untuk tahun ini.</td></tr>`; return; }

  tbody.innerHTML = filtered.map(r => `
    <tr>
      <td class="mono">${bgEscAdm(r.nomor_tiket)}</td>
      <td><strong>${bgEscAdm(r.nama)}</strong><br/><span style="color:var(--text-3)">${bgEscAdm(r.jenis_kelamin)}</span></td>
      <td class="mono">${bgEscAdm(r.nik)}<br/>${bgEscAdm(r.no_hp)}</td>
      <td>${bgEscAdm(r.kategori)}</td>
      <td style="max-width:200px">${bgEscAdm(r.alamat)}</td>
      <td>${r.dokumen_filename ? `<a class="link-btn" href="/admin/api/balikgratis/dokumen-view?id=${r.id}" target="_blank">Lihat Foto</a>` : '—'}</td>
      <td><span class="status-pill status-${r.status}">${r.status}</span></td>
      <td>${new Date(r.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})}</td>
      <td>
        ${r.status === 'terdaftar' ? `<button class="link-btn" onclick="batalkan('${r.id}')">Batalkan</button>` : ''}
      </td>
    </tr>`).join('');
}

async function batalkan(id) {
  if (!confirm('Batalkan tiket ini? Kuota akan otomatis kembali tersedia.')) return;
  try {
    await bgPatch('balikgratis_pemesanan', `id=eq.${id}`, { status: 'dibatalkan', updated_at: new Date().toISOString() });
    bgAdmToast('Tiket dibatalkan.', 'success');
    loadData();
  } catch (e) { bgAdmToast(e.message, 'error'); }
}

async function exportPdf() {
  if (!rows.length) { bgAdmToast('Tidak ada data untuk diekspor', 'error'); return; }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
  const pageW = doc.internal.pageSize.getWidth();
  const NAVY = [10, 31, 68], BLUE = [26, 86, 219], INK = [15, 30, 56], GRAY = [126, 138, 163];
  const tahun = document.getElementById('fTahun').value;

  doc.setFillColor(...NAVY);
  doc.rect(0, 0, pageW, 72, 'F');
  const logoDataUrl = await loadImageAsDataUrl('/assets/dishub.png').catch(() => null);
  if (logoDataUrl) { try { doc.addImage(logoDataUrl, 'PNG', 36, 16, 40, 40); } catch (e) {} }
  doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(15);
  doc.text(`LAPORAN PENDAFTARAN BALIK GRATIS ${tahun}`, logoDataUrl ? 86 : 36, 32);
  doc.setFont('helvetica','normal'); doc.setFontSize(10);
  doc.text('Dinas Perhubungan Kabupaten Tulungagung', logoDataUrl ? 86 : 36, 48);

  const now = new Date();
  doc.setFontSize(9);
  doc.text(`Dicetak: ${now.toLocaleString('id-ID')}`, pageW - 36, 30, { align: 'right' });
  doc.text(`Total: ${rows.length} pendaftar`, pageW - 36, 44, { align: 'right' });

  const body = rows.map(r => [r.nomor_tiket, r.nama, r.nik, r.no_hp, r.kategori, r.jenis_kelamin, r.status]);
  doc.autoTable({
    startY: 88,
    head: [['No. Tiket', 'Nama', 'NIK', 'No. HP', 'Kategori', 'Jenis Kelamin', 'Status']],
    body,
    theme: 'plain',
    styles: { font: 'helvetica', fontSize: 8.5, textColor: INK, cellPadding: 7, lineColor: [230, 234, 244], lineWidth: 0.5 },
    headStyles: { fillColor: BLUE, textColor: 255, fontStyle: 'bold', fontSize: 8.5 },
    alternateRowStyles: { fillColor: [244, 247, 252] },
    didDrawPage: () => {
      const pageCount = doc.internal.getNumberOfPages();
      const pageH = doc.internal.pageSize.getHeight();
      doc.setFontSize(8); doc.setTextColor(...GRAY); doc.setFont('helvetica', 'normal');
      doc.text('Dishub Kabupaten Tulungagung — Sistem Balik Gratis', 36, pageH - 20);
      doc.text(`Halaman ${doc.internal.getCurrentPageInfo().pageNumber} / ${pageCount}`, pageW - 36, pageH - 20, { align: 'right' });
    },
  });

  doc.save(`pendaftaran-balikgratis-${tahun}-${now.toISOString().slice(0,10)}.pdf`);
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

initTahun();
loadData();
</script>
</body>
</html>
