
import { renderRouteCards } from './route_card_render.js';
import { proxyGet } from './supabase_proxy_client.js';

export default async function renderBus(container) {
  try {

    const allData = await proxyGet('trayek_bus', '&order=nama.asc&select=id,nama,jenis');

    if (!Array.isArray(allData)) {
      container.innerHTML = '<p class="text-gray-500">Belum ada data Bus.</p>';
      return;
    }

    // Filter: hanya tampilkan yang jenis-nya bukan 'mpu'
    const data = allData.filter(item => {
      const j = (item.jenis || '').toLowerCase();
      return j !== 'mpu' && j !== 'minibus';
    });

    if (data.length === 0) {
      container.innerHTML = '<p class="text-gray-500">Belum ada data Bus.</p>';
      return;
    }

    renderRouteCards(container, data, {
      iconClass: 'route-card-icon-bus',
      btnClass: 'route-card-btn-bus',
      detailPage: 'bus',
      defaultIcon: 'bus',
    });

  } catch (err) {
    container.innerHTML = '<p class="text-red-500">Gagal memuat data Bus.</p>';
    console.error('[busangkutan]', err);
  }
}
