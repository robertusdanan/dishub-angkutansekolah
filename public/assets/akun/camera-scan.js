

function createCameraScan({ container, jenis, onCapture }) {
  const guideRatio = 1.586; // rasio kartu ID standar

  container.innerHTML = `
    <div class="cscan-stage">
      <video class="cscan-video" autoplay muted playsinline></video>
      <canvas class="cscan-canvas" style="display:none"></canvas>
      <div class="cscan-guide"><div class="cscan-guide-frame"></div></div>
      <div class="cscan-warn" style="display:none">💡 Cahaya kurang - hidupkan flash</div>
      <button class="cscan-torch" style="display:none" title="Flash">🔦</button>
    </div>
    <div class="cscan-actions">
      <button class="btn btn-ghost btn-sm cscan-cancel" type="button">Batal</button>
      <button class="btn btn-solid btn-sm cscan-capture" type="button" disabled>Ambil Foto</button>
    </div>
    <p class="cscan-hint">Posisikan ${jenis === 'ktp' ? 'KTP' : 'Kartu Keluarga'} rata di dalam bingkai, pastikan seluruh dokumen terlihat &amp; cahaya cukup.</p>
  `;

  if (!document.getElementById('cscan-style')) {
    const style = document.createElement('style');
    style.id = 'cscan-style';
    style.textContent = `
      .cscan-stage{ position:relative; background:#000; border-radius:16px; overflow:hidden; aspect-ratio:4/3; }
      .cscan-video{ width:100%; height:100%; object-fit:cover; }
      .cscan-guide{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; }
      .cscan-guide-frame{ width:78%; aspect-ratio:${guideRatio}; border:3px solid #F26D6D; border-radius:12px; transition:border-color .25s; box-shadow:0 0 0 2000px rgba(0,0,0,.45); }
      .cscan-guide-frame.ok{ border-color:#5FD68F; }
      .cscan-warn{ position:absolute; top:14px; left:50%; transform:translateX(-50%); background:rgba(20,20,20,.75); color:#fff; padding:8px 16px; border-radius:100px; font-size:12.5px; font-weight:600; }
      .cscan-torch{ position:absolute; bottom:14px; right:14px; width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.3); font-size:18px; cursor:pointer; }
      .cscan-actions{ display:flex; gap:10px; margin-top:14px; }
      .cscan-actions .btn{ flex:1; justify-content:center; }
      .cscan-hint{ font-size:12px; color:var(--ink-3); margin-top:10px; text-align:center; }
    `;
    document.head.appendChild(style);
  }

  const video    = container.querySelector('.cscan-video');
  const canvas   = container.querySelector('.cscan-canvas');
  const guide    = container.querySelector('.cscan-guide-frame');
  const warnEl   = container.querySelector('.cscan-warn');
  const torchBtn = container.querySelector('.cscan-torch');
  const captureBtn = container.querySelector('.cscan-capture');
  const cancelBtn  = container.querySelector('.cscan-cancel');

  let stream = null, track = null, torchOn = false, rafId = null, readyToCapture = false;

  async function start() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } } });
    } catch (e) {
      container.innerHTML = `<p style="color:#b91c1c;font-size:13px">Tidak bisa mengakses kamera: ${e.message}. Pastikan izin kamera diizinkan.</p>`;
      return;
    }
    video.srcObject = stream;
    track = stream.getVideoTracks()[0];

    const caps = track.getCapabilities ? track.getCapabilities() : {};
    if (caps.torch) {
      torchBtn.style.display = '';
      torchBtn.onclick = async () => {
        torchOn = !torchOn;
        try { await track.applyConstraints({ advanced: [{ torch: torchOn }] }); } catch (e) { /* diam-diam gagal, tidak fatal */ }
        torchBtn.style.background = torchOn ? 'rgba(255,220,120,.4)' : 'rgba(255,255,255,.16)';
      };
    }

    monitorBrightness();
  }

  function monitorBrightness() {
    const sampleCanvas = document.createElement('canvas');
    const sctx = sampleCanvas.getContext('2d', { willReadFrequently: true });
    sampleCanvas.width = 60; sampleCanvas.height = 40;

    function tick() {
      if (video.readyState >= 2) {
        sctx.drawImage(video, 0, 0, 60, 40);
        const data = sctx.getImageData(0, 0, 60, 40).data;
        let sum = 0;
        for (let i = 0; i < data.length; i += 4) sum += (data[i] + data[i+1] + data[i+2]) / 3;
        const avgBrightness = sum / (data.length / 4); // 0-255

        const tooDark = avgBrightness < 55;
        warnEl.style.display = tooDark ? '' : 'none';
        readyToCapture = !tooDark;
        guide.classList.toggle('ok', readyToCapture);
        captureBtn.disabled = !readyToCapture;
      }
      rafId = requestAnimationFrame(tick);
    }
    tick();
  }

  captureBtn.addEventListener('click', () => {
    const vw = video.videoWidth, vh = video.videoHeight;
    const guideRect = guide.getBoundingClientRect();
    const videoRect = video.getBoundingClientRect();

    // Petakan area bingkai panduan (di layar) ke koordinat piksel video asli
    const scaleX = vw / videoRect.width, scaleY = vh / videoRect.height;
    const sx = (guideRect.left - videoRect.left) * scaleX;
    const sy = (guideRect.top - videoRect.top) * scaleY;
    const sw = guideRect.width * scaleX;
    const sh = guideRect.height * scaleY;

    canvas.width = 856; canvas.height = 540; // ukuran output tetap, rasio kartu ID
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(blob => {
      stopCamera();
      onCapture(blob);
    }, 'image/jpeg', 0.85);
  });

  cancelBtn.addEventListener('click', () => { stopCamera(); onCapture(null); });

  function stopCamera() {
    if (rafId) cancelAnimationFrame(rafId);
    if (stream) stream.getTracks().forEach(t => t.stop());
  }

  start();
  return { stop: stopCamera };
}
