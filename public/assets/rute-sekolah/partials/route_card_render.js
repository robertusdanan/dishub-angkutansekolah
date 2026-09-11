

/**
 * @param {HTMLElement} container
 * @param {Array<{id:string, nama:string, jenis?:string}>} data
 * @param {{iconClass:string, btnClass:string, detailPage:string, defaultIcon:string}} opts
 */
export function renderRouteCards(container, data, { iconClass, btnClass, detailPage, defaultIcon }) {
  container.innerHTML = data.map(item => `
    <div class="route-card">
      <div class="route-card-top">
        <div class="route-card-icon ${iconClass}">
          <img src="/assets/rute-sekolah/icons/${item.jenis || defaultIcon}.svg" alt="Ikon">
        </div>
        <h2 class="route-card-title">${item.nama}</h2>
      </div>
      <button onclick="location.href='/rute-sekolah/${detailPage}?id=${item.id}'" class="route-card-btn ${btnClass}">
        Lihat Rute <i class="fa-solid fa-arrow-right"></i>
      </button>
    </div>
  `).join('');
}
