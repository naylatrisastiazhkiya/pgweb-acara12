<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "latihan_pgweb_acara8";
$port       = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
  die("Koneksi database gagal: " . $conn->connect_error);
}

$sql = "SELECT id, namasppg, kapanewon, kalurahan, alamat, longitude, latitude FROM data_sppg";
$result = $conn->query($sql);

if (!$result) {
  die("Query gagal dijalankan: " . $conn->error);
}

// Tampung data ke array agar bisa dipakai untuk tabel HTML sekaligus Tooltip & Popup peta
$dataSppg = [];
while ($row = $result->fetch_assoc()) {
  $dataSppg[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Web GIS Persebaran Titik SPPG Prambanan</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f6f9;
    }
    .map-header {
      background-color: #1e293b;
      color: #ffffff;
      padding: 14px 24px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .map-header h1 {
      margin: 0;
      font-size: 20px;
      letter-spacing: 0.3px;
    }
    #map {
      width: 100%;
      height: 72vh;
      border-bottom: 2px solid #cbd5e1;
    }
    .legend-box {
      background: #ffffff;
      padding: 10px 12px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);
      max-height: 270px;
      overflow-y: auto;
      font-size: 12px;
    }
    .legend-title {
      font-weight: bold;
      margin: 6px 0 4px 0;
      border-bottom: 1px solid #ddd;
      padding-bottom: 2px;
    }
    .legend-title:first-child { margin-top: 0; }
    .table-container {
      width: 95%;
      margin: 24px auto 40px auto;
      background: #ffffff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow-x: auto;
    }
    .table-container h2 {
      margin-top: 0;
      margin-bottom: 14px;
      font-size: 18px;
      color: #1e293b;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px 10px;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background-color: #1e293b;
      color: #ffffff;
    }
    tr:nth-child(even) { background-color: #f8fafc; }
    tr:hover { background-color: #eef4fa; }
    .btn-lokasi {
      background-color: #2563eb;
      color: #ffffff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      cursor: pointer;
      white-space: nowrap;
      transition: background-color 0.2s ease;
    }
    .btn-lokasi:hover {
      background-color: #1d4ed8;
    }
    /* Styling Popup & Tooltip */
    .sppg-tooltip {
      font-weight: bold;
      font-size: 12px;
      color: #1e293b;
      background-color: #ffffff;
      border: 1px solid #94a3b8;
      border-radius: 4px;
      padding: 4px 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .sppg-popup {
      font-family: Arial, sans-serif;
      font-size: 12.5px;
      line-height: 1.45;
      min-width: 210px;
    }
    .sppg-popup-title {
      font-weight: bold;
      font-size: 14px;
      color: #1e293b;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 4px;
      margin-bottom: 6px;
    }
    .sppg-popup table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    .sppg-popup td {
      border: none;
      padding: 2px 0;
      vertical-align: top;
    }
    .sppg-popup td:first-child {
      font-weight: bold;
      width: 82px;
      color: #475569;
    }
  </style>
</head>
<body>

  <!-- Judul Peta -->
  <header class="map-header">
    <h1>Web GIS Persebaran Titik SPPG Prambanan</h1>
  </header>

  <!-- Peta -->
  <div id="map"></div>

  <!-- Tabel Data -->
  <div class="table-container">
    <h2>Data SPPG Prambanan</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama SPPG</th>
          <th>Kapanewon</th>
          <th>Kalurahan</th>
          <th>Alamat</th>
          <th>Longitude</th>
          <th>Latitude</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($dataSppg) > 0): ?>
          <?php foreach ($dataSppg as $index => $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['namasppg']) ?></td>
              <td><?= htmlspecialchars($row['kapanewon']) ?></td>
              <td><?= htmlspecialchars($row['kalurahan']) ?></td>
              <td><?= htmlspecialchars($row['alamat']) ?></td>
              <td><?= htmlspecialchars($row['longitude']) ?></td>
              <td><?= htmlspecialchars($row['latitude']) ?></td>
              <td>
                <button type="button" class="btn-lokasi" onclick="zoomKeTitik(<?= $index ?>)">
                  Lihat di Peta
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8">Tabel kosong (0 baris ditemukan).</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    L.Geoserver = {
      wms: function (wmsUrl, options) {
        return L.tileLayer.wms(wmsUrl, Object.assign({
          format: "image/png",
          transparent: true
        }, options));
      },
      legend: function (wmsUrl, layerList) {
        var control = L.control({ position: "bottomleft" });
        control.onAdd = function () {
          var div = L.DomUtil.create("div", "legend-box");
          layerList.forEach(function (item) {
            var legendUrl = wmsUrl +
              "?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20" +
              "&LEGEND_OPTIONS=forceLabels:on&LAYER=" + encodeURIComponent(item.layer);
            div.innerHTML += '<div class="legend-title">' + item.title + '</div>';
            div.innerHTML += '<div><img src="' + legendUrl + '" alt="' + item.title + '" /></div>';
          });
          return div;
        };
        return control;
      }
    };

    var map = L.map("map").setView([-7.70, 110.45], 11);

    var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    });
    osm.addTo(map);

    var satelit = L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", {
      maxZoom: 19,
      attribution: 'Tiles &copy; Esri'
    });

    // Layer WMS dari GeoServer
    var wmsLayerBatas = L.Geoserver.wms("http://localhost:8080/geoserver/wms", {
      layers: "pgweb11:batas_sleman",
      transparent: true,
      zIndex: 1
    });
    wmsLayerBatas.addTo(map);

    var wmsLayerJalan = L.Geoserver.wms("http://localhost:8080/geoserver/wms", {
      layers: "pgweb11:jalan_sleman",
      transparent: true,
      zIndex: 2
    });
    wmsLayerJalan.addTo(map);

    var wmsLayerTitik = L.Geoserver.wms("http://localhost:8080/geoserver/wms", {
      layers: "pgweb11:data_titik_sppg_prambanan",
      transparent: true,
      zIndex: 3
    });
    wmsLayerTitik.addTo(map);

    // Data titik dari database untuk fungsi Tooltip & Popup interaktif
    var dataTitik = <?= json_encode($dataSppg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var interactiveGroup = L.layerGroup().addTo(map);
    var markerList = [];

    function escapeHtml(text) {
      if (text === null || text === undefined) return "";
      return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    dataTitik.forEach(function (item, idx) {
      var lat = parseFloat(item.latitude);
      var lng = parseFloat(item.longitude);

      if (!isNaN(lat) && !isNaN(lng)) {
        // CircleMarker transparan tepat di atas simbol WMS agar tetap menjaga warna SLD GeoServer
        var pointMarker = L.circleMarker([lat, lng], {
          radius: 9,
          color: "transparent",
          weight: 2,
          fillColor: "#ffffff",
          fillOpacity: 0.01
        });

        // 1. Fungsi Tooltip (muncul saat kursor diarahkan ke titik)
        pointMarker.bindTooltip(escapeHtml(item.namasppg), {
          direction: "top",
          offset: [0, -8],
          className: "sppg-tooltip"
        });

        // 2. Fungsi Popup (muncul saat titik diklik atau saat tombol "Lihat di Peta" ditekan)
        var popupContent =
          '<div class="sppg-popup">' +
            '<div class="sppg-popup-title">' + escapeHtml(item.namasppg) + '</div>' +
            '<table>' +
              '<tr><td>Kapanewon</td><td>: ' + escapeHtml(item.kapanewon) + '</td></tr>' +
              '<tr><td>Kalurahan</td><td>: ' + escapeHtml(item.kalurahan) + '</td></tr>' +
              '<tr><td>Alamat</td><td>: ' + escapeHtml(item.alamat) + '</td></tr>' +
              '<tr><td>Koordinat</td><td>: ' + lat + ', ' + lng + '</td></tr>' +
            '</table>' +
          '</div>';

        pointMarker.bindPopup(popupContent, { maxWidth: 280 });

        // Efek sorot lingkaran saat mouse hover
        pointMarker.on("mouseover", function () {
          this.setStyle({ color: "#1e293b", fillOpacity: 0.2 });
        });
        pointMarker.on("mouseout", function () {
          this.setStyle({ color: "transparent", fillOpacity: 0.01 });
        });

        pointMarker.addTo(interactiveGroup);
        markerList[idx] = pointMarker;
      }
    });

    // Sinkronkan visibility tooltip/popup saat layer Titik SPPG di-centang/uncheck di Layer Control
    map.on("overlayadd", function (e) {
      if (e.layer === wmsLayerTitik && !map.hasLayer(interactiveGroup)) {
        map.addLayer(interactiveGroup);
      }
    });
    map.on("overlayremove", function (e) {
      if (e.layer === wmsLayerTitik && map.hasLayer(interactiveGroup)) {
        map.removeLayer(interactiveGroup);
      }
    });

    // Legenda Gabungan
    var layerLegend = L.Geoserver.legend("http://localhost:8080/geoserver/wms", [
      { title: "Titik SPPG Prambanan", layer: "pgweb11:data_titik_sppg_prambanan" },
      { title: "Jaringan Jalan Sleman", layer: "pgweb11:jalan_sleman" },
      { title: "Batas Administrasi Sleman", layer: "pgweb11:batas_sleman" }
    ]);
    layerLegend.addTo(map);

    L.control.layers(
      { "OpenStreetMap": osm, "Citra Satelit": satelit },
      {
        "Batas Administrasi Sleman": wmsLayerBatas,
        "Jaringan Jalan Sleman": wmsLayerJalan,
        "Titik SPPG Prambanan": wmsLayerTitik
      }
    ).addTo(map);

    // Fungsi tombol "Lihat di Peta" di tabel
    function zoomKeTitik(index) {
      var targetMarker = markerList[index];
      if (!targetMarker) {
        alert("Koordinat titik tidak ditemukan.");
        return;
      }

      // Pastikan layer titik sedang aktif
      if (!map.hasLayer(wmsLayerTitik)) {
        map.addLayer(wmsLayerTitik);
      }

      document.querySelector(".map-header").scrollIntoView({ behavior: "smooth" });
      map.flyTo(targetMarker.getLatLng(), 16, { duration: 1.2 });

      setTimeout(function () {
        targetMarker.openPopup();
      }, 400);
    }
  </script>
</body>
</html>
<?php $conn->close(); ?>