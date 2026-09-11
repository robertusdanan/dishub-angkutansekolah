
(function () {
  const bar = document.createElement('div');
  bar.id = 'twNavProgress';
  bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,#38BDF8,#1A56DB);z-index:100000;transition:width .3s ease,opacity .3s ease;box-shadow:0 0 8px rgba(26,86,219,.6);';
  document.documentElement.appendChild(bar);

  let width = 0, timer = null;
  function set(w) { width = w; bar.style.width = w + '%'; }

  function start() {
    bar.style.opacity = '1';
    set(20);
    clearInterval(timer);
    timer = setInterval(() => {
      if (width < 85) set(width + (85 - width) * 0.12);
    }, 150);
  }
  function done() {
    clearInterval(timer);
    set(100);
    setTimeout(() => { bar.style.opacity = '0'; setTimeout(() => set(0), 300); }, 200);
  }

  start();
  window.addEventListener('load', done);
  setTimeout(done, 4000); // jaga-jaga kalau event 'load' lambat/tidak terpicu

  // Mulai lagi progress bar saat klik link internal yang akan pindah halaman
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('http') && !href.includes(location.host)) return;
    if (a.target === '_blank') return;
    start();
  });
})();
