'use strict';

const DRIVER_STATUS = (() => {


  const LOCATION_STALE_MS = 3 * 60 * 1000;


  const OPERATIONAL_START_HOUR = 4;  // 04:00
  const OPERATIONAL_END_HOUR   = 20; // 20:00

  function isWithinOperationalHours(date = new Date()) {
    const h = date.getHours();
    return h >= OPERATIONAL_START_HOUR && h < OPERATIONAL_END_HOUR;
  }

  /**
   * @param {boolean}      presenceOnline  true kalau channel presence driver ini aktif
   * @param {string|null}  updateAt        ISO timestamp lat/lng terakhir (kolom update_at)
   * @param {Date}         [now]           dipakai untuk testing, default waktu sekarang
   */
  function compute(presenceOnline, updateAt, now = new Date()) {
    const ageMs = updateAt ? now.getTime() - new Date(updateAt).getTime() : Infinity;
    const fresh = ageMs <= LOCATION_STALE_MS;

    if (presenceOnline && fresh) {
      return { key: 'online', label: 'Online', color: '#22c55e', dim: false };
    }
    if (presenceOnline && !fresh) {
      return { key: 'weak', label: 'Sinyal GPS Lemah', color: '#F59E0B', dim: false };
    }
    if (!isWithinOperationalHours(now)) {
      return { key: 'inactive', label: 'Belum Beroperasi', color: '#64748B', dim: true };
    }
    return { key: 'offline', label: 'Offline', color: '#EF4444', dim: true };
  }

  return { compute, isWithinOperationalHours, LOCATION_STALE_MS };
})();

window.DRIVER_STATUS = DRIVER_STATUS;
