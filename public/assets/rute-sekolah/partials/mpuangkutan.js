
import { renderRouteCards } from './route_card_render.js';
import { PROXY, proxyGet } from './supabase_proxy_client.js';

export default async function renderMPU(container) {
  try {

    let data = await proxyGet('trayek_mpu', '&order=nama.asc&select=id,nama,jenis');

    // Fallback: jika trayek_mpu kosong, ambil dari trayek_bus yang jenis=mpu
    if (!Array.isArray(data) || data.length === 0) {
      const res2 = await fetch(`${PROXY}?table=trayek_bus&order=nama.asc&select=id,nama,jenis`);
      if (res2.ok) {
        const allBus = await res2.json();
        if (Array.isArray(allBus)) {
          data = allBus.filter(item => {
            const j = (item.jenis || '').toLowerCase();
            return j === 'mpu' || j === 'minibus';
          });
          // Arahkan ke rutempu.php agar sesuai halaman detail MPU
          if (data.length > 0) {
            renderRouteCards(container, data, {
              iconClass: 'route-card-icon-mpu',
              btnClass: 'route-card-btn-mpu',
              detailPage: 'mpu',
              defaultIcon: 'mpu',
            });
            return;
          }
        }
      }
      container.innerHTML = '<p class="text-gray-500">Belum ada data MPU.</p>';
      return;
    }

    renderRouteCards(container, data, {
      iconClass: 'route-card-icon-mpu',
      btnClass: 'route-card-btn-mpu',
      detailPage: 'mpu',
      defaultIcon: 'mpu',
    });

  } catch (err) {
    container.innerHTML = '<p class="text-red-500">Gagal memuat data MPU.</p>';
    console.error('[mpuangkutan]', err);
  }
}
