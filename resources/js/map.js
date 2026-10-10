import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/**
 * Izbor tačke na mapi (Leaflet + OpenStreetMap, bez API ključeva).
 * Server računa udaljenost i doplatu; ovde se samo šalju koordinate.
 */
window.initDeliveryMap = (el, $wire, side) => {
    const base = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
    const maxKm = parseFloat(el.dataset.maxKm);
    const map = L.map(el, { scrollWheelZoom: false }).setView(base, 11);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> saradnici',
    }).addTo(map);

    const pin = (color) => L.divIcon({
        className: '',
        html: `<span style="display:block;width:18px;height:18px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:${color};border:2px solid #fff;box-shadow:0 1px 4px rgb(0 0 0 / .4)"></span>`,
        iconSize: [18, 18],
        iconAnchor: [9, 18],
    });

    L.marker(base, { icon: pin('#13222b'), interactive: false, keyboard: false }).addTo(map);
    const area = L.circle(base, { radius: maxKm * 1000, color: '#0e6e8c', weight: 2, dashArray: '6 6', fillOpacity: 0.04, interactive: false }).addTo(map);
    map.fitBounds(area.getBounds());

    let marker = null;
    const place = (lat, lng) => {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { icon: pin('#0e6e8c'), keyboard: false }).addTo(map);
        }
    };

    if (el.dataset.pointLat && el.dataset.pointLng) {
        place(parseFloat(el.dataset.pointLat), parseFloat(el.dataset.pointLng));
    }

    map.on('click', (e) => {
        place(e.latlng.lat, e.latlng.lng);
        $wire.setPoint(side, e.latlng.lat, e.latlng.lng);
    });

    // Mapa je renderovana u koraku koji se tek prikazuje: preračunaj veličinu.
    setTimeout(() => map.invalidateSize(), 50);
};
