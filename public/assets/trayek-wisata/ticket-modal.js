
let _ticketOverlay = null;

function showTicketModal(data) {
  // data: { pemesananId, trayekNama, warna, tanggal, hari, jamBerangkat, jamPulang, penumpang, status, checkedIn }
  if (!_ticketOverlay) {
    _ticketOverlay = document.createElement('div');
    _ticketOverlay.className = 'ticket-modal-overlay';
    _ticketOverlay.setAttribute('role', 'dialog');
    _ticketOverlay.setAttribute('aria-modal', 'true');
    _ticketOverlay.setAttribute('aria-label', 'E-tiket trayek wisata');
    _ticketOverlay.onclick = (e) => { if (e.target === _ticketOverlay) closeTicketModal(); };
    document.body.appendChild(_ticketOverlay);
  }

  const tglLabel = data.tanggal
    ? new Date(data.tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    : '';
  const namaList = (data.penumpang || []).map(p => `<div class="ticket-pax-row"><span>${escTicket(p.nama)}</span><span class="ticket-pax-nik">${escTicket(p.nik)}</span></div>`).join('');

  const isBatal = data.status === 'dibatalkan';
  const isHadir = !!data.checkedIn;

  _ticketOverlay.innerHTML = `
    <div class="ticket-modal">
      <button class="ticket-modal-close" onclick="closeTicketModal()" aria-label="Tutup e-tiket">✕</button>
      <div class="ticket-card ${isBatal ? 'ticket-batal' : ''}">
        <div class="ticket-head" style="background:${data.warna || '#1A56DB'}">
          <div class="ticket-brand">🚌 E-TIKET TRAYEK WISATA</div>
          <div class="ticket-trayek">${escTicket(data.trayekNama || 'Trayek')}</div>
        </div>
        <div class="ticket-body">
          ${isBatal ? '<div class="ticket-flag flag-batal">DIBATALKAN</div>' : (isHadir ? '<div class="ticket-flag flag-hadir">✓ SUDAH CHECK-IN</div>' : '')}
          <div class="ticket-row"><span class="ticket-label">Tanggal</span><span class="ticket-val">${tglLabel}</span></div>
          <div class="ticket-row"><span class="ticket-label">Berangkat</span><span class="ticket-val">${(data.jamBerangkat||'').slice(0,5)} ${data.jamPulang ? '· Pulang '+data.jamPulang.slice(0,5) : ''}</span></div>
          <div class="ticket-divider"></div>
          <div class="ticket-label" style="margin-bottom:8px">Penumpang</div>
          <div class="ticket-pax-list">${namaList || '<div class="ticket-pax-row"><span>-</span></div>'}</div>
          <div class="ticket-divider"></div>
          <div class="ticket-qr-wrap">
            <div id="ticketQrCanvas"></div>
            <div class="ticket-qr-hint">Tunjukkan QR ini ke petugas di titik keberangkatan</div>
          </div>
          <div class="ticket-id">ID: ${escTicket(data.pemesananId || '').slice(0, 8).toUpperCase()}</div>
        </div>
      </div>
      <div class="ticket-actions">
        <button class="btn btn-ghost btn-sm" onclick="window.print()">🖨 Cetak</button>
        <button class="btn btn-solid btn-sm" onclick="closeTicketModal()">Tutup</button>
      </div>
    </div>`;

  _ticketOverlay.classList.add('open');
  document.body.style.overflow = 'hidden';

  if (!isBatal && window.QRCode) {
    new QRCode(document.getElementById('ticketQrCanvas'), {
      text: 'TW:' + (data.pemesananId || ''),
      width: 160, height: 160,
      colorDark: '#0F1E38', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
  }
}

function closeTicketModal() {
  if (_ticketOverlay) _ticketOverlay.classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && _ticketOverlay && _ticketOverlay.classList.contains('open')) closeTicketModal();
});

function escTicket(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
