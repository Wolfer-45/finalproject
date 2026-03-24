window.initTripMap = function(points){
  const map = L.map('trip-map').setView([20.5937, 78.9629], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const latlngs = [];
  points.forEach((p, i) => {
    if (!p.lat || !p.lng) return;
    const ll = [parseFloat(p.lat), parseFloat(p.lng)];
    latlngs.push(ll);
    L.marker(ll).addTo(map).bindPopup(`<strong>Day ${i+1}</strong><br>${p.place}<br><a target="_blank" href="https://maps.google.com/?q=${p.lat},${p.lng}">Open in Google Maps</a>`);
  });

  if (latlngs.length) {
    L.polyline(latlngs, {color:'#0EA5E9', weight:4}).addTo(map);
    map.fitBounds(latlngs, {padding:[24,24]});
  }
}
