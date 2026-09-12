
import { fetchRuteDetailGrouped } from './rute_detail_shared.js';

document.addEventListener('DOMContentLoaded', async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const id        = urlParams.get('id'); // = trayek_bus.id, mis. "bus_kalidawir"
  if (!id) return;

  const container = document.getElementById('rute-detail');

  try {
    const result = await fetchRuteDetailGrouped({
      trayekTable: 'trayek_bus',
      driverTable: 'driver_bus',
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
        <div class="mb-4 p-5 rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition duration-200">
          <p class="text-xs font-semibold text-blue-600 mb-1">${item.label}</p>
          <p class="text-sm text-gray-700 leading-relaxed mb-3">${item.rute || '-'}</p>
          <div class="text-sm text-gray-600">
            <i class="fa-solid fa-user mr-1 text-blue-500"></i> Driver:
            <ul class="mt-1 space-y-1 list-disc list-inside">
              ${item.drivers.length
                ? item.drivers.map(d => `<li>${d.sopir} <span class="text-gray-500">(${d.nopol})</span></li>`).join('')
                : '<li class="text-gray-400">Belum ada sopir terdaftar</li>'}
            </ul>
          </div>
          <div class="text-sm mt-2">
            <a href="/rute-sekolah/lihat-peta?id_map=${item.idMap}" class="text-blue-600 hover:underline">
              <i class="fa-solid fa-map-location-dot mr-1"></i>Lihat Peta
            </a>
          </div>
        </div>`;
      });
      return html;
    };

    container.innerHTML = `
      <h2 class="text-xl font-bold mb-2">${trayekName}</h2>
      ${renderSection('Rute Pagi', pagi)}
      ${renderSection('Rute Siang', siang)}
      ${renderSection('Rute Lainnya', lainnya)}
    `;

  } catch (err) {
    console.error('[rute_bus]', err);
    container.innerHTML = '<p class="text-red-500">Gagal memuat data rute.</p>';
  }
});
