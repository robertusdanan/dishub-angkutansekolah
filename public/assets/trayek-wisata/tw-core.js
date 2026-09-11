
const TW_PROXY = '/api/supabase-proxy';

// CATATAN MIGRASI: helper baru (tidak ada di kode lama) — Laravel butuh
// CSRF token untuk request POST/DELETE. Meta tag <meta name="csrf-token">
// disediakan di setiap Blade halaman Trayek Wisata yang meng-include
// partials.trayek-wisata.nav / footer.
function twCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.content : '';
}


async function twFetch(table, params = {}) {
  const qs = new URLSearchParams({ table, select: params.select || '*' });
  if (params.order) qs.set('order', params.order);
  if (params.limit) qs.set('limit', params.limit);
  (params.filters || []).forEach(([col, val]) => { qs.append('col[]', col); qs.append('val[]', val); });
  const res = await fetch(`${TW_PROXY}?${qs.toString()}`);
  if (!res.ok) throw new Error(`Gagal memuat ${table} (HTTP ${res.status})`);
  const data = await res.json();
  if (data && data.error) throw new Error(data.error);
  return Array.isArray(data) ? data : [];
}

function twEsc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let _twToastTimer;
function twToast(msg) {
  const el = document.getElementById('twToastEl');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(_twToastTimer);
  _twToastTimer = setTimeout(() => el.classList.remove('show'), 3400);
}

function twMoreLink(href, label) {
  return `<a href="${href}" class="tw-more-link">${label} <span>→</span></a>`;
}

// ── Nav glass on scroll ─────────────────────────────────────────────
const twNav = document.getElementById('twNav');
if (twNav) {
  window.addEventListener('scroll', () => {
    twNav.classList.toggle('scrolled', window.scrollY > 40);
  }, { passive: true });
}

// ── Reveal on scroll (IntersectionObserver, ringan tanpa GSAP wajib) ─
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); revealObserver.unobserve(e.target); } });
}, { threshold: 0.15 });
function twObserveReveals() { document.querySelectorAll('.reveal:not(.in)').forEach(el => revealObserver.observe(el)); }
twObserveReveals();

// ── FAQ Accordion ────────────────────────────────────────────────────
function toggleFaq(qEl) {
  const item = qEl.closest('.faq-item');
  const wasOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i => { i.classList.remove('open'); i.querySelector('.faq-a').style.maxHeight = null; i.querySelector('.faq-q').setAttribute('aria-expanded', 'false'); });
  if (!wasOpen) {
    item.classList.add('open');
    const a = item.querySelector('.faq-a');
    a.style.maxHeight = a.scrollHeight + 'px';
    qEl.setAttribute('aria-expanded', 'true');
  }
}

// ── Smooth scroll (Lenis) ────────────────────────────────────────────
let _lenis = null;
if (window.Lenis) {
  _lenis = new Lenis({ duration: 1.05, easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)), smoothWheel: true });
  function _lenisRaf(time) { _lenis.raf(time); requestAnimationFrame(_lenisRaf); }
  requestAnimationFrame(_lenisRaf);
}
function twScrollTo(target) {
  const el = typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) { if (typeof target === 'string' && !target.startsWith('#')) window.location.href = target; return; }
  if (_lenis) _lenis.scrollTo(el, { offset: -70, duration: 1.1 });
  else el.scrollIntoView({ behavior: 'smooth' });
}
document.addEventListener('click', (e) => {
  const a = e.target.closest('a[href^="#"]');
  if (!a) return;
  const id = a.getAttribute('href');
  if (id.length < 2) return;
  const el = document.querySelector(id);
  if (!el) return;
  e.preventDefault();
  twScrollTo(el);
});

// ── Micro-interaction: tombol magnetik ──────────────────────────────
function twInitMagneticButtons() {
  document.querySelectorAll('.btn-solid, .btn-sunset').forEach(btn => {
    if (btn._twMagnetic) return;
    btn._twMagnetic = true;
    btn.addEventListener('mousemove', (e) => {
      const r = btn.getBoundingClientRect();
      const x = e.clientX - r.left - r.width / 2, y = e.clientY - r.top - r.height / 2;
      btn.style.transform = `translate(${x * 0.18}px, ${y * 0.3}px)`;
    });
    btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
  });
}
twInitMagneticButtons();

// ── Floating CTA ──────────────────────────────────────────────────
(function initFloatingCta() {
  const fab = document.getElementById('floatingCta');
  if (!fab) return;
  const jadwalSection = document.getElementById('jadwal');
  let jadwalVisible = false;
  if (jadwalSection) {
    const io = new IntersectionObserver(([entry]) => { jadwalVisible = entry.isIntersecting; updateFab(); }, { threshold: 0.15 });
    io.observe(jadwalSection);
  }
  // Sembunyikan tombol begitu footer (bagian bawah, termasuk baris
  // credit) mulai kelihatan, supaya tombol yang position:fixed ini
  // tidak pernah menutupi teks credit saat halaman di-scroll sampai
  // dasar. rootMargin negatif di bawah supaya baru dianggap "kelihatan"
  // saat footer sudah cukup masuk ke layar (bukan langsung begitu
  // ujung atasnya nongol).
  let footerVisible = false;
  const footerEl = document.querySelector('.tw-footer');
  if (footerEl) {
    const footerIo = new IntersectionObserver(
      ([entry]) => { footerVisible = entry.isIntersecting; updateFab(); },
      { rootMargin: '0px 0px -40% 0px', threshold: 0 }
    );
    footerIo.observe(footerEl);
  }
  function anyModalOpen() {
    return document.querySelector('.modal-overlay.open, .ticket-modal-overlay.open, .wisata-modal-overlay.open') !== null;
  }
  function updateFab() {
    const past = window.scrollY > (document.querySelector('.hero, .page-header')?.offsetHeight || 400) * 0.75;
    fab.classList.toggle('show', past && !jadwalVisible && !footerVisible && !anyModalOpen());
  }
  window.addEventListener('scroll', updateFab, { passive: true });
})();

// ═══════════════════════════════════════════════════════════════════
// STATE GLOBAL
// ═══════════════════════════════════════════════════════════════════
let TW_TITIK = [], TW_TRAYEK = [], TW_TRAYEK_TITIK = [], TW_GALERI = {}, TW_JADWAL = [];
let TW_AUTH = { logged_in: false };
let TW_WAITLIST = [];

async function twLoadTitikTrayek() {
  [TW_TITIK, TW_TRAYEK, TW_TRAYEK_TITIK] = await Promise.all([
    twFetch('trayekwisata_titik', { select: '*', order: 'urutan.asc' }),
    twFetch('trayekwisata_trayek', { select: '*', order: 'nama.asc' }),
    twFetch('trayekwisata_trayek_titik', { select: '*', order: 'urutan.asc' }),
  ]);
  const galeriRows = await twFetch('trayekwisata_titik_galeri', { select: '*', order: 'urutan.asc' });
  TW_GALERI = {};
  galeriRows.forEach(g => { (TW_GALERI[g.titik_id] ||= []).push(g); });
}
async function twLoadJadwalData() {
  TW_JADWAL = await twFetch('trayekwisata_jadwal', { select: '*', order: 'tanggal.asc,jam_berangkat.asc' });
}

function titikById(id) { return TW_TITIK.find(t => t.id === id); }
function coverFor(titikId) {
  const g = (TW_GALERI[titikId] || []).find(x => x.tipe === 'foto');
  return g ? g.url : null;
}

// ═══════════════════════════════════════════════════════════════════
// STORY PERJALANAN — dipakai di beranda (ringkas) & /rute (bagian atas)
// ═══════════════════════════════════════════════════════════════════
function renderStory(opts = {}) {
  const wrap = document.getElementById('storyItems');
  if (!wrap) return;
  let items = TW_TITIK.slice().sort((a,b) => a.urutan - b.urutan);
  const total = items.length;
  if (opts.limit) items = items.slice(0, opts.limit);

  if (!total) { wrap.innerHTML = `<p style="color:var(--ink-3);font-size:13.5px">Titik lokasi belum diatur admin.</p>`; return; }

  wrap.innerHTML = items.map((t, i) => `
    <div class="story-item" data-idx="${i}">
      <div class="story-node">${String(i+1).padStart(2,'0')}</div>
      <div class="story-kicker">${t.jenis === 'terminal' ? 'Titik Keberangkatan' : t.jenis === 'transit' ? 'Titik Transit' : 'Destinasi Wisata'}</div>
      <div class="story-name">${twEsc(t.nama)}</div>
      <div class="story-desc">${twEsc(t.deskripsi || '')}</div>
      ${t.lat && t.lng ? `<div class="story-meta"><span>${t.lat.toFixed?t.lat.toFixed(4):t.lat}, ${t.lng.toFixed?t.lng.toFixed(4):t.lng}</span></div>` : ''}
    </div>`).join('');

  if (opts.limit && total > opts.limit && opts.moreLink) {
    wrap.insertAdjacentHTML('beforeend', `<div class="story-item story-item-more">${twMoreLink(opts.moreLink, `Lihat rute lengkap (${total} titik)`)}</div>`);
  }

  const nodes = [...wrap.querySelectorAll('.story-item:not(.story-item-more)')];
  const spineFill = document.getElementById('storySpineFill');
  const track = document.querySelector('.story-track');
  if (!spineFill || !track) return;

  function onScrollStory() {
    const rect = track.getBoundingClientRect();
    const vh = window.innerHeight;
    const progress = Math.min(1, Math.max(0, (vh * 0.6 - rect.top) / rect.height));
    spineFill.style.height = (progress * 100) + '%';
    nodes.forEach(n => {
      const r = n.getBoundingClientRect();
      n.classList.toggle('active', r.top < vh * 0.62 && r.bottom > vh * 0.2);
    });
  }
  window.addEventListener('scroll', onScrollStory, { passive: true });
  onScrollStory();
}

// ═══════════════════════════════════════════════════════════════════
// INTERACTIVE ROUTE EXPLORER — dipakai penuh di /rute
// ═══════════════════════════════════════════════════════════════════
let twMap, twRouteLayer, twMarkersLayer, twVehicleMarker, twAnimFrame;

function initTwMap() {
  if (typeof L === 'undefined' || typeof L.map !== 'function') {
    console.error('Leaflet belum termuat — peta rute tidak bisa ditampilkan.');
    const stage = document.getElementById('twMap');
    if (stage) stage.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--ink-3);font-size:13px;padding:20px;text-align:center">Peta gagal dimuat. Coba muat ulang halaman.</div>';
    return;
  }
  twMap = L.map('twMap', {
    zoomControl: false, attributionControl: false,
    scrollWheelZoom: false, dragging: false, touchZoom: false, tap: false, doubleClickZoom: false,
  }).setView([-8.15, 111.95], 11);
  activateMapOnInteract(twMap, document.getElementById('twMap'));
  L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
  }).addTo(twMap);
  L.control.zoom({ position: 'bottomright' }).addTo(twMap);
  twRouteLayer = L.layerGroup().addTo(twMap);
  twMarkersLayer = L.layerGroup().addTo(twMap);
}

function activateMapOnInteract(map, container) {
  const hint = document.createElement('div');
  hint.className = 'map-activate-hint';
  hint.innerHTML = window.matchMedia('(pointer:coarse)').matches
    ? '👆 Ketuk peta untuk mulai berinteraksi'
    : '🖱️ Klik peta untuk zoom &amp; geser — scroll halaman tetap bebas';
  container.appendChild(hint);

  function activate() {
    map.scrollWheelZoom.enable(); map.dragging.enable(); map.touchZoom.enable();
    map.tap && map.tap.enable(); map.doubleClickZoom.enable();
    hint.classList.add('hide');
  }
  function deactivate() {
    map.scrollWheelZoom.disable(); map.dragging.disable(); map.touchZoom.disable();
    map.tap && map.tap.disable(); map.doubleClickZoom.disable();
    hint.classList.remove('hide');
  }
  container.addEventListener('click', activate);
  container.addEventListener('touchstart', activate, { passive: true });
  container.addEventListener('mouseleave', deactivate);
}

function renderExplorerChips() {
  const wrap = document.getElementById('explorerChips');
  if (!wrap) return;
  if (!TW_TRAYEK.length) { wrap.innerHTML = `<span style="color:var(--ink-3);font-size:13px">Belum ada trayek diatur admin.</span>`; return; }
  wrap.innerHTML = TW_TRAYEK.map((t, i) => `<div class="explorer-chip ${i===0?'active':''}" data-id="${t.id}" onclick="selectTrayek('${t.id}', this)">${twEsc(t.nama)}</div>`).join('');
  if (!twMap) initTwMap();
  selectTrayek(TW_TRAYEK[0].id);
}

function selectTrayek(trayekId, chipEl) {
  if (!twMap) return;
  document.querySelectorAll('.explorer-chip').forEach(c => c.classList.remove('active'));
  if (chipEl) chipEl.classList.add('active'); else document.querySelector(`.explorer-chip[data-id="${trayekId}"]`)?.classList.add('active');

  const trayek = TW_TRAYEK.find(t => t.id === trayekId);
  const rel = TW_TRAYEK_TITIK.filter(r => r.trayek_id === trayekId).sort((a,b) => a.urutan - b.urutan);
  const points = rel.map(r => titikById(r.titik_id)).filter(t => t && t.lat && t.lng);

  twRouteLayer.clearLayers();
  twMarkersLayer.clearLayers();
  if (twAnimFrame) cancelAnimationFrame(twAnimFrame);

  if (!points.length) {
    document.querySelector('.explorer-hint').textContent = 'Trayek ini belum punya titik lokasi dengan koordinat.';
    return;
  }

  const latlngs = points.map(p => [p.lat, p.lng]);
  const color = trayek?.warna || '#1A56DB';

  L.polyline(latlngs, { color, weight: 5, opacity: .85, lineJoin: 'round' }).addTo(twRouteLayer);
  L.polyline(latlngs, { color: '#fff', weight: 9, opacity: .5 }).addTo(twRouteLayer);
  L.polyline(latlngs, { color, weight: 5, opacity: .95, lineJoin: 'round' }).addTo(twRouteLayer);

  points.forEach((p, i) => {
    const rel_i = rel[i];
    const icon = L.divIcon({
      className: '',
      html: `<div style="width:30px;height:30px;border-radius:50% 50% 50% 0;background:${color};transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,.35);border:2px solid #fff">
               <span style="transform:rotate(45deg);color:#fff;font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700">${i+1}</span>
             </div>`,
      iconSize: [30,30], iconAnchor: [15,29],
    });
    const marker = L.marker([p.lat, p.lng], { icon }).addTo(twMarkersLayer);
    marker.on('click', () => openPanel(p, rel_i));
  });

  twMap.fitBounds(latlngs, { padding: [60,60] });
  document.querySelector('.explorer-hint').textContent = `🖱️ ${points.length} titik pada trayek ini — ketuk marker untuk detail`;

  animateVehicle(latlngs, color);
}

function animateVehicle(latlngs, color) {
  const icon = L.divIcon({
    className: '', iconSize: [22,22], iconAnchor: [11,11],
    html: `<div style="width:22px;height:22px;border-radius:50%;background:#fff;border:3px solid ${color};box-shadow:0 3px 10px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center">
             <div style="width:7px;height:7px;border-radius:50%;background:${color}"></div>
           </div>`,
  });
  if (twVehicleMarker) twMap.removeLayer(twVehicleMarker);
  twVehicleMarker = L.marker(latlngs[0], { icon, zIndexOffset: 900 }).addTo(twMap);

  const durationPerLeg = 2600;
  let leg = 0, start = null;
  function step(ts) {
    if (!start) start = ts;
    const t = Math.min(1, (ts - start) / durationPerLeg);
    const a = latlngs[leg], b = latlngs[(leg+1) % latlngs.length];
    const lat = a[0] + (b[0]-a[0]) * t;
    const lng = a[1] + (b[1]-a[1]) * t;
    twVehicleMarker.setLatLng([lat, lng]);
    if (t >= 1) { leg = (leg + 1) % (latlngs.length - 1 || 1); start = null; if (latlngs.length < 2) return; }
    twAnimFrame = requestAnimationFrame(step);
  }
  if (latlngs.length > 1) twAnimFrame = requestAnimationFrame(step);
}

function openPanel(titik, rel) {
  const panel = document.getElementById('explorerPanel');
  if (!panel) return;
  const media = document.getElementById('panelMedia');
  const cover = coverFor(titik.id);
  const galVideo = (TW_GALERI[titik.id] || []).find(g => g.tipe === 'video');

  if (galVideo) {
    media.innerHTML = `<video src="${twEsc(galVideo.url)}" muted loop autoplay playsinline></video>`;
  } else if (cover) {
    media.style.backgroundImage = `url('${cover}')`;
    media.innerHTML = '';
  } else {
    media.style.backgroundImage = '';
    media.innerHTML = `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:12px">Belum ada foto</div>`;
  }

  document.getElementById('panelName').textContent = titik.nama;
  document.getElementById('panelDesc').textContent = titik.deskripsi || '—';
  document.getElementById('panelEta').textContent = rel?.jam_perkiraan_tiba ? rel.jam_perkiraan_tiba.slice(0,5) : '—';
  document.getElementById('panelDur').textContent = rel?.estimasi_menit_dari_sebelumnya ? `${rel.estimasi_menit_dari_sebelumnya} mnt` : '—';
  document.getElementById('panelVisit').textContent = titik.jumlah_kunjungan ?? 0;
  document.getElementById('panelGmaps').href = `https://www.google.com/maps/dir/?api=1&destination=${titik.lat},${titik.lng}`;

  panel.classList.add('open');
}
function closePanel() { document.getElementById('explorerPanel')?.classList.remove('open'); }

// ═══════════════════════════════════════════════════════════════════
// GALERI DESTINASI — dipakai di beranda (ringkas) & /destinasi (penuh)
// ═══════════════════════════════════════════════════════════════════
function renderGallery(opts = {}) {
  const wrap = document.getElementById('galleryGrid');
  if (!wrap) return;
  let wisata = TW_TITIK.filter(t => t.jenis === 'wisata');
  const total = wisata.length;
  if (opts.limit) wisata = wisata.slice(0, opts.limit);

  if (!total) { wrap.innerHTML = `<p style="color:var(--ink-3);font-size:13.5px">Belum ada destinasi wisata diatur admin.</p>`; return; }

  wrap.innerHTML = wisata.map(t => {
    const cover = coverFor(t.id);
    const mediaCount = (TW_GALERI[t.id] || []).length;
    return `
    <div class="gallery-card" onclick="openWisataModal('${t.id}')">
      ${cover ? `<img src="${cover}" loading="lazy" alt="${twEsc(t.nama)}" class="img-fade" onload="this.classList.add('loaded')"/>` : ``}
      ${mediaCount > 1 ? `<span class="gallery-card-count">🖼 ${mediaCount}</span>` : ''}
      <div class="gallery-card-overlay">
        <div class="gallery-card-tag">Pantai Selatan</div>
        <div class="gallery-card-name">${twEsc(t.nama)}</div>
        <div class="gallery-card-visit">${t.jumlah_kunjungan ?? 0} kunjungan tercatat</div>
      </div>
    </div>`;
  }).join('');

  if (opts.limit && total > opts.limit && opts.moreLink) {
    wrap.insertAdjacentHTML('beforeend', `<a href="${opts.moreLink}" class="gallery-card gallery-card-more"><span>Lihat Semua<br/>${total} Destinasi<br/>→</span></a>`);
  }
}

// ── Modal Galeri Wisata ──────────────────────────────────────────────
let _wmSlides = [], _wmIndex = 0, _wmTimer = null;

function openWisataModal(titikId) {
  const titik = titikById(titikId);
  if (!titik) return;
  _wmSlides = TW_GALERI[titikId] || [];
  _wmIndex = 0;

  document.getElementById('wisataModalName').textContent = titik.nama;
  document.getElementById('wisataModalDesc').textContent = titik.deskripsi || 'Belum ada deskripsi untuk titik ini.';
  document.getElementById('wisataModalVisit').textContent = `${titik.jumlah_kunjungan ?? 0} kunjungan tercatat`;

  renderWisataModalSlides();
  document.getElementById('wisataModalOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  startWisataAutoSlide();
}

function renderWisataModalSlides() {
  const media = document.getElementById('wisataModalMedia');
  const dots = document.getElementById('wisataModalDots');
  media.querySelectorAll('.wisata-slide').forEach(el => el.remove());

  if (!_wmSlides.length) {
    const empty = document.createElement('div');
    empty.className = 'wisata-slide active';
    empty.style.cssText = 'display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.5);font-size:13px';
    empty.textContent = 'Belum ada foto/video untuk titik ini';
    media.prepend(empty);
    dots.innerHTML = '';
    return;
  }

  _wmSlides.forEach((s, i) => {
    const el = document.createElement('div');
    el.className = 'wisata-slide' + (i === _wmIndex ? ' active' : '');
    el.innerHTML = s.tipe === 'video'
      ? `<video src="${twEsc(s.url)}" muted loop playsinline ${i === _wmIndex ? 'autoplay' : ''}></video>`
      : `<img src="${twEsc(s.url)}" loading="lazy" class="img-fade" onload="this.classList.add('loaded')"/>`;
    media.prepend(el);
  });

  dots.innerHTML = _wmSlides.map((_, i) => `<span class="${i === _wmIndex ? 'active' : ''}" onclick="wisataModalGoto(${i})"></span>`).join('');

  let startX = null;
  media.ontouchstart = e => { startX = e.touches[0].clientX; };
  media.ontouchend = e => {
    if (startX === null) return;
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 40) wisataModalNav(dx < 0 ? 1 : -1);
    startX = null;
  };
}

function wisataModalGoto(i) {
  if (!_wmSlides.length) return;
  _wmIndex = (i + _wmSlides.length) % _wmSlides.length;
  renderWisataModalSlides();
  restartWisataAutoSlide();
}
function wisataModalNav(dir) { wisataModalGoto(_wmIndex + dir); }
function startWisataAutoSlide() {
  clearInterval(_wmTimer);
  if (_wmSlides.length > 1) _wmTimer = setInterval(() => wisataModalGoto(_wmIndex + 1), 4200);
}
function restartWisataAutoSlide() { startWisataAutoSlide(); }
function closeWisataModal() {
  document.getElementById('wisataModalOverlay').classList.remove('open');
  document.body.style.overflow = '';
  clearInterval(_wmTimer);
}
document.addEventListener('keydown', e => {
  const ov = document.getElementById('wisataModalOverlay');
  if (!ov || !ov.classList.contains('open')) return;
  if (e.key === 'Escape') closeWisataModal();
  if (e.key === 'ArrowRight') wisataModalNav(1);
  if (e.key === 'ArrowLeft') wisataModalNav(-1);
});

// ═══════════════════════════════════════════════════════════════════
// JADWAL — dipakai di beranda (ringkas) & /jadwal (penuh + tab)
// ═══════════════════════════════════════════════════════════════════
function switchJadwalTab(el, hari) {
  document.querySelectorAll('.jadwal-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  renderJadwal(hari, window._twJadwalOpts || {});
}

function statusKuota(terisi, total) {
  if (!total) return 'hijau';
  const r = terisi / total;
  if (r >= 1) return 'merah';
  if (r >= .7) return 'kuning';
  return 'hijau';
}

function renderJadwal(hariFilter, opts = {}) {
  window._twJadwalOpts = opts; // dipakai ulang saat ganti tab
  const wrap = document.getElementById('jadwalScroll');
  if (!wrap) return;
  let rows = TW_JADWAL.filter(j => j.status !== 'dibatalkan');
  if (hariFilter) rows = rows.filter(j => j.hari === hariFilter);
  const total = rows.length;
  if (opts.limit) rows = rows.slice(0, opts.limit);

  if (!total) { wrap.innerHTML = `<div class="jadwal-empty">Belum ada jadwal trayek untuk 4 minggu ke depan. Cek kembali nanti.</div>`; return; }

  wrap.innerHTML = rows.map(j => {
    const trayek = TW_TRAYEK.find(t => t.id === j.trayek_id);
    const st = statusKuota(j.kuota_terisi, j.kuota_total);
    const pct = j.kuota_total ? Math.min(100, Math.round(j.kuota_terisi / j.kuota_total * 100)) : 0;
    const tgl = new Date(j.tanggal).toLocaleDateString('id-ID', { day:'numeric', month:'long' });
    return `
    <div class="jadwal-card">
      <span class="jadwal-card-status status-${st}"></span>
      <div class="jadwal-card-day">${j.hari === 'SABTU' ? 'Sabtu' : 'Minggu'} · ${tgl}</div>
      <div class="jadwal-card-name">${twEsc(trayek?.nama || 'Trayek')}</div>
      <div class="jadwal-card-time"><span>🚌 ${j.jam_berangkat?.slice(0,5)}</span><span>↩ ${j.jam_pulang ? j.jam_pulang.slice(0,5) : '—'}</span></div>
      <div class="jadwal-card-meta">${j.kuota_terisi}/${j.kuota_total} kursi terisi</div>
      <div class="jadwal-card-bar"><div class="jadwal-card-bar-fill" style="width:${pct}%"></div></div>
      <div class="jadwal-card-actions">
        <button class="btn btn-solid btn-sm" style="flex:1;justify-content:center" ${st==='merah'?`onclick="joinWaitlist('${j.id}', this)"`:`onclick="startBooking('${j.id}')"`}>${st==='merah' ? (TW_WAITLIST.includes(j.id) ? '✓ Terdaftar di Daftar Tunggu' : '🔔 Beri Tahu Saya') : 'Pesan Kursi'}</button>
        <button class="jadwal-share-btn" title="Ajak teman lewat WhatsApp" aria-label="Ajak teman ikut lewat WhatsApp" onclick="shareJadwalWA('${j.id}')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.9-2-1-.3-.1-.5-.1-.7.1s-.8 1-.9 1.2c-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5.1-.1.2-.3.3-.4.1-.2 0-.4 0-.5-.1-.1-.7-1.7-.9-2.3-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1.1 2.8 1.2 3c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.1-1.4-.1-.1-.3-.2-.6-.3z"/><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.5A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .9.9-2.9-.2-.3A8 8 0 1 1 12 20z"/></svg>
        </button>
      </div>
    </div>`;
  }).join('') + (opts.limit && total > opts.limit && opts.moreLink ? `
    <div class="jadwal-card jadwal-card-more">
      <a href="${opts.moreLink}">Lihat Semua<br/>${total} Jadwal<br/><span>→</span></a>
    </div>` : '');
}

function shareJadwalWA(jadwalId) {
  const j = TW_JADWAL.find(x => x.id === jadwalId);
  const trayek = TW_TRAYEK.find(t => t.id === j?.trayek_id);
  const tgl = j ? new Date(j.tanggal).toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long' }) : '';
  const text = `🚌 Yuk ikut *${trayek?.nama || 'Trayek Wisata Gratis'}*!\n${tgl}, berangkat ${j?.jam_berangkat?.slice(0,5)} — GRATIS dari Dishub Kabupaten Tulungagung.\n\nPesan kursi di sini: ${location.origin}/trayek-wisata/jadwal`;
  window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}

async function joinWaitlist(jadwalId, btn) {
  if (!TW_AUTH.logged_in) {
    window.location.href = '/akun/masuk?next=' + encodeURIComponent('/trayek-wisata/jadwal');
    return;
  }
  if (TW_WAITLIST.includes(jadwalId)) return;
  btn.disabled = true;
  try {
    const res = await fetch('/trayek-wisata/api/waitlist', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': twCsrfToken() }, body: JSON.stringify({ jadwal_id: jadwalId }),
    });
    if (!res.ok) throw new Error((await res.json()).error || 'Gagal mendaftar');
    TW_WAITLIST.push(jadwalId);
    btn.textContent = '✓ Terdaftar di Daftar Tunggu';
    twToast('✓ Anda terdaftar — kami akan hubungi lewat nomor HP di profil kalau ada kursi kosong');
  } catch (e) {
    twToast('Gagal: ' + e.message);
  } finally {
    btn.disabled = false;
  }
}

// ═══════════════════════════════════════════════════════════════════
// KUOTA MINGGU INI — dipakai di beranda & /jadwal
// ═══════════════════════════════════════════════════════════════════
function renderKuota() {
  const fill = document.getElementById('kuotaRingFill');
  if (!fill) return;
  const now = new Date(); const in7 = new Date(now.getTime() + 8*86400000);
  const upcoming = TW_JADWAL.filter(j => {
    const d = new Date(j.tanggal); return d >= now && d <= in7 && j.status !== 'dibatalkan';
  });
  const total = upcoming.reduce((a,j) => a + (j.kuota_total||0), 0);
  const terisi = upcoming.reduce((a,j) => a + (j.kuota_terisi||0), 0);
  const pct = total ? Math.round(terisi/total*100) : 0;

  const circumference = 2 * Math.PI * 112;
  fill.setAttribute('stroke-dasharray', circumference);
  requestAnimationFrame(() => { fill.style.strokeDashoffset = circumference * (1 - pct/100); });

  document.getElementById('kuotaPercent').textContent = pct + '%';
  document.getElementById('kTotal').textContent = total;
  document.getElementById('kTerisi').textContent = terisi;
  document.getElementById('kSisa').textContent = Math.max(0, total - terisi);
  document.getElementById('kTrayek').textContent = upcoming.length;
}

// ═══════════════════════════════════════════════════════════════════
// AUTH STATE — nav & tombol pesan menyesuaikan status login
// ═══════════════════════════════════════════════════════════════════
async function twLoadAuth() {
  try {
    const res = await fetch('/trayek-wisata/api/auth-status');
    TW_AUTH = await res.json();
    if (TW_AUTH.logged_in) {
      const wlRes = await fetch('/trayek-wisata/api/waitlist');
      const wlData = await wlRes.json();
      TW_WAITLIST = wlData.jadwal_ids || [];
    }
  } catch (e) { TW_AUTH = { logged_in: false }; }
  renderAuthNav();
}

function renderAuthNav() {
  const cta = document.querySelector('.tw-nav-cta');
  if (!cta) return;
  if (TW_AUTH.logged_in) {
    cta.innerHTML = `
      <a class="btn btn-ghost btn-sm" href="/akun/saya">${TW_AUTH.profil_lengkap ? '✓ ' : '⚠ '}${twEsc(TW_AUTH.nama.split(' ')[0])}</a>
      <a class="btn btn-solid btn-sm" href="/akun/keluar">Keluar</a>`;
  } else {
    cta.innerHTML = `
      <a class="btn btn-ghost btn-sm" href="/admin/login">Masuk</a>
      <a class="btn btn-solid btn-sm" href="/trayek-wisata/jadwal">Pesan Kursi</a>`;
  }
  twInitMagneticButtons();
}

// ═══════════════════════════════════════════════════════════════════
// BOOKING FLOW
// ═══════════════════════════════════════════════════════════════════
function startBooking(jadwalId) {
  if (!TW_AUTH.logged_in) {
    window.location.href = '/akun/masuk?next=' + encodeURIComponent('/trayek-wisata/jadwal');
    return;
  }
  if (!TW_AUTH.profil_lengkap) {
    twToast('Lengkapi data diri & foto KTP dulu di halaman Akun');
    setTimeout(() => window.location.href = '/akun/saya', 1200);
    return;
  }
  openBookingModal(jadwalId);
}

let _bookingOverlay;
async function openBookingModal(jadwalId) {
  const jadwal = TW_JADWAL.find(j => j.id === jadwalId);
  const trayek = TW_TRAYEK.find(t => t.id === jadwal?.trayek_id);
  const famRes = await fetch('/akun/api/keluarga');
  const family = famRes.ok ? await famRes.json() : [];

  if (!_bookingOverlay) {
    _bookingOverlay = document.createElement('div');
    _bookingOverlay.className = 'modal-overlay';
    _bookingOverlay.style.cssText = 'position:fixed;inset:0;background:rgba(10,20,16,.6);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px;';
    document.body.appendChild(_bookingOverlay);
  }
  _bookingOverlay.classList.add('open');
  _bookingOverlay.style.display = 'flex';
  _bookingOverlay.innerHTML = `
    <div style="background:#fff;border-radius:20px;width:100%;max-width:440px;padding:26px;max-height:85vh;overflow-y:auto">
      <h3 style="font-family:var(--f-display);font-size:19px;margin:0 0 4px">${twEsc(trayek?.nama || 'Trayek')}</h3>
      <p style="font-size:12.5px;color:var(--ink-3);margin:0 0 18px">${jadwal.hari === 'SABTU'?'Sabtu':'Minggu'}, ${new Date(jadwal.tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'long'})} · Berangkat ${jadwal.jam_berangkat.slice(0,5)}</p>
      <div style="font-size:12.5px;font-weight:700;color:var(--ink-2);margin-bottom:8px">Pilih Penumpang</div>
      <div id="bkPaxList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px"></div>
      <div id="bkError" style="font-size:12px;color:#C23434;margin-bottom:10px;display:none"></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-ghost btn-sm" style="flex:1;justify-content:center" onclick="closeBookingModal()">Batal</button>
        <button class="btn btn-solid btn-sm" style="flex:1;justify-content:center" id="bkConfirmBtn" onclick="confirmBooking('${jadwalId}')">Pesan Sekarang</button>
      </div>
    </div>`;

  const paxList = _bookingOverlay.querySelector('#bkPaxList');
  const allPax = [{ nik: TW_AUTH.nik, nama: TW_AUTH.nama + ' (Anda)' }, ...family.map(f => ({ nik: f.nik, nama: `${f.nama} (${f.status})` }))];
  paxList.innerHTML = allPax.map((p,i) => `
    <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px">
      <input type="checkbox" class="bk-pax-chk" value="${i}" ${i===0?'checked':''}/>
      <span style="flex:1">${twEsc(p.nama)}</span>
      <span style="font-family:var(--f-mono);font-size:11px;color:var(--ink-3)">${twEsc(p.nik)}</span>
    </label>`).join('');
  window._bkAllPax = allPax;
}
function closeBookingModal() { if (_bookingOverlay) { _bookingOverlay.style.display = 'none'; _bookingOverlay.classList.remove('open'); } }

async function confirmBooking(jadwalId) {
  const checked = [...document.querySelectorAll('.bk-pax-chk:checked')].map(el => window._bkAllPax[parseInt(el.value)]);
  const errEl = document.getElementById('bkError');
  errEl.style.display = 'none';
  if (!checked.length) { errEl.textContent = 'Pilih minimal 1 penumpang.'; errEl.style.display = ''; return; }

  const btn = document.getElementById('bkConfirmBtn');
  btn.disabled = true; btn.textContent = 'Memeriksa...';
  try {
    const res = await fetch('/trayek-wisata/api/pesan', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': twCsrfToken() },
      body: JSON.stringify({ jadwal_id: jadwalId, penumpang: checked }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gagal memesan');
    closeBookingModal();
    twLoadJadwalData().then(() => { if (document.getElementById('jadwalScroll')) renderJadwal('', window._twJadwalOpts || {}); if (document.getElementById('kuotaRingFill')) renderKuota(); });

    const jadwal = TW_JADWAL.find(j => j.id === jadwalId);
    const trayek = TW_TRAYEK.find(t => t.id === jadwal?.trayek_id);
    showTicketModal({
      pemesananId: data.pemesanan_id,
      trayekNama: trayek?.nama,
      warna: trayek?.warna,
      tanggal: jadwal?.tanggal,
      jamBerangkat: jadwal?.jam_berangkat,
      jamPulang: jadwal?.jam_pulang,
      penumpang: checked,
      status: 'terkonfirmasi',
    });
  } catch (e) {
    errEl.textContent = e.message; errEl.style.display = '';
  } finally {
    btn.disabled = false; btn.textContent = 'Pesan Sekarang';
  }
}

// ── Trust signal: "sudah X orang ikut" ──────────────────────────────
async function twLoadHeroStat() {
  const el = document.getElementById('heroStat');
  if (!el) return;
  try {
    const res = await fetch('/trayek-wisata/api/stats-publik');
    const data = await res.json();
    if (!data.total_peserta || data.total_peserta < 1) return;
    const numEl = document.getElementById('heroStatNum');
    el.style.display = '';
    const target = data.total_peserta;
    const dur = 1200, start = performance.now();
    function step(t) {
      const p = Math.min(1, (t - start) / dur);
      numEl.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  } catch (e) { /* diam-diam gagal — bukan elemen kritikal */ }
}

async function twBootstrap(sections = {}) {
  try {
    const needTitikTrayek = sections.story || sections.explorer || sections.gallery;
    const needJadwal = sections.jadwal || sections.kuota;

    const jobs = [];
    if (needTitikTrayek) jobs.push(twLoadTitikTrayek());
    if (needJadwal) jobs.push(twLoadJadwalData());
    await Promise.all(jobs);

    if (sections.story) renderStory(sections.story === true ? {} : sections.story);
    if (sections.explorer) renderExplorerChips();
    if (sections.gallery) renderGallery(sections.gallery === true ? {} : sections.gallery);
    if (sections.jadwal) renderJadwal('', sections.jadwal === true ? {} : sections.jadwal);
    if (sections.kuota) renderKuota();
    if (sections.heroStat) twLoadHeroStat();

    twObserveReveals();
    twInitMagneticButtons();
  } catch (e) {
    console.error(e);
    twToast('Gagal memuat sebagian data. Coba muat ulang halaman.');
  }
}