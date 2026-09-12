
import { fetchRuteDetailGrouped } from './rute_detail_shared.js';

document.addEventListener('DOMContentLoaded', async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const id        = urlParams.get('id'); // = trayek_mpu.id, mis. "mpu_sendang"
  if (!id) return;

  const container = document.getElementById('rute-detail');

  try {
    const result = await fetchRuteDetailGrouped({
      trayekTable: 'trayek_mpu',
      driverTable: 'driver_mpu',
      id,
    });

    if (result.status === 'trayek_not_found') {
      container.innerHTML = '<p class="text-gray-500">Trayek tidak ditemukan.</p>';
      return;
    }
    if (result.status === 'route_not_found') {
      container.innerHTML = '<p class="text-gray-500">Rute tidak ditemukan.</p>';
      return;
    }

    const { trayekName, pagi, siang, lainnya } = result;

    const renderSection = (judul, items) => {
      if (!items.length) return '';

      let html = `<h3 class="text-lg font-semibold text-gray-500 mb-2 mt-6">${judul}</h3>`;
      items.forEach(item => {
        html += `
        <details class="mb-4 rounded-lg border border-gray-200 shadow-sm bg-white open:shadow-lg transition-all duration-300">
          <summary class="cursor-pointer px-4 py-3 font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-t-lg flex items-center justify-between">
            ${item.label}
            <svg class="w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </summary>
          <div class="p-4 space-y-2">
            <p class="text-gray-700"><span class="font-medium text-gray-600">Rute:</span> ${item.rute || '-'}</p>
            <div>
              <span class="font-medium text-gray-600">Driver:</span>
              <ul class="mt-1 space-y-1 list-disc list-inside text-sm text-gray-800">
                ${item.drivers.length
                  ? item.drivers.map(d => `<li><i class="fa-solid fa-user mr-1 text-blue-500"></i>${d.sopir} <span class="text-gray-500">(${d.nopol})</span></li>`).join('')
                  : '<li class="text-gray-400">Belum ada sopir terdaftar</li>'}
              </ul>
            </div>
            <div class="text-sm mt-3">
              <a href="/rute-sekolah/lihat-peta?id_map=${item.idMap}" class="text-blue-600 hover:underline">
                <i class="fa-solid fa-map-location-dot mr-1"></i>Lihat Peta
              </a>
            </div>
          </div>
        </details>`;
      });
      return html;
    };

    container.innerHTML = `
      <h2 class="text-xl font-bold mb-2">${trayekName}</h2>
      ${renderSection('Rute Pagi', pagi)}
      ${renderSection('Rute Siang', siang)}
      ${renderSection('Rute Lainnya', lainnya)}
    `;

    document.querySelectorAll('#rute-detail details').forEach(detail => {
      const icon = detail.querySelector('summary svg');
      detail.addEventListener('toggle', () => icon.classList.toggle('rotate-90', detail.open));
    });

  } catch (err) {
    console.error('[rute_mpu]', err);
    container.innerHTML = '<p class="text-red-500">Gagal memuat data rute.</p>';
  }
});
