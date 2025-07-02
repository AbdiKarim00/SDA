<?php
class MapCard {
    public static function render($title, $mapId, $locations) {
        ?>
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2">
                    <i class="bi bi-geo-alt"></i>
                    <?php echo htmlspecialchars($title); ?>
                </h3>
                <div class="flex gap-2">
                    <button class="btn btn-secondary btn-sm" onclick="toggleClustering()">
                        <i class="bi bi-grid-3x3"></i>
                        Toggle Clustering
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="toggleLegend()">
                        <i class="bi bi-list"></i>
                        Toggle Legend
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="refreshMap()">
                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="exportMapData()">
                        <i class="bi bi-download"></i>
                        Export
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="printMap()">
                        <i class="bi bi-printer"></i>
                        Print
                    </button>
                </div>
            </div>
            
            <div class="relative w-full" style="height: 600px;">
                <div id="<?php echo htmlspecialchars($mapId); ?>" class="absolute inset-0 rounded-lg border border-gray-200"></div>
                <div id="mapLegend" class="absolute bottom-4 right-4 bg-white p-4 rounded-lg shadow-lg z-10" style="display: none;">
                    <h4 class="font-medium mb-2">Vehicle Status</h4>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full bg-green-500"></div>
                            <span class="text-sm">Active</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full bg-yellow-500"></div>
                            <span class="text-sm">In Maintenance</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full bg-red-500"></div>
                            <span class="text-sm">Inactive</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Add Leaflet.MarkerCluster CSS and JS
                const markerClusterCSS = document.createElement('link');
                markerClusterCSS.rel = 'stylesheet';
                markerClusterCSS.href = 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css';
                document.head.appendChild(markerClusterCSS);

                const markerClusterJS = document.createElement('script');
                markerClusterJS.src = 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js';
                
                // Wait for MarkerCluster to load before initializing map
                markerClusterJS.onload = () => {
                    console.log('MarkerCluster loaded');
                    initMap();
                };
                document.head.appendChild(markerClusterJS);

                let markerClusterGroup = null;
                let isClusteringEnabled = true;
                let isLegendVisible = false;

                // Wait for the container to be visible before initializing the map
                const initMap = () => {
                    const container = document.getElementById('<?php echo $mapId; ?>');
                    const rect = container.getBoundingClientRect();
                    
                    // Only initialize if the container has dimensions
                    if (rect.width > 0 && rect.height > 0) {
                        console.log('Initializing map with dimensions:', rect);
                        // Initialize map
                        const map = L.map('<?php echo $mapId; ?>').setView([0.0236, 37.9062], 6);
                        
                        // Add OpenStreetMap tiles
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        // Create marker cluster group
                        markerClusterGroup = L.markerClusterGroup({
                            maxClusterRadius: 50,
                            spiderfyOnMaxZoom: true,
                            showCoverageOnHover: false,
                            zoomToBoundsOnClick: true,
                            iconCreateFunction: function(cluster) {
                                const count = cluster.getChildCount();
                                const size = Math.min(40 + (count * 2), 60); // Dynamic size based on count
                                
                                return L.divIcon({
                                    html: `
                                        <div style="
                                            background-color: #3b82f6;
                                            width: ${size}px;
                                            height: ${size}px;
                                            border-radius: 50%;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                                            border: 3px solid white;
                                            color: white;
                                            font-weight: bold;
                                            font-size: ${Math.min(14 + (count.toString().length * 2), 20)}px;
                                        ">
                                            ${count}
                                        </div>
                                    `,
                                    className: 'custom-cluster-icon',
                                    iconSize: L.point(size, size)
                                });
                            }
                        });

                        // Add custom CSS for cluster markers
                        const style = document.createElement('style');
                        style.textContent = `
                            .custom-cluster-icon {
                                background: none !important;
                                border: none !important;
                            }
                            .marker-cluster {
                                background: none !important;
                            }
                            .marker-cluster div {
                                background: none !important;
                            }
                        `;
                        document.head.appendChild(style);

                        // Create markers
                        const markers = [];
                        <?php foreach ($locations as $index => $location): ?>
                            const markerColor<?php echo $index; ?> = getMarkerColor('<?php echo $location['status']; ?>');
                            const markerIcon<?php echo $index; ?> = L.divIcon({
                                className: 'custom-div-icon',
                                html: `
                                    <div class="marker-container" style="
                                        position: relative;
                                        width: 32px;
                                        height: 32px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                    ">
                                        <div style="
                                            background-color: ${markerColor<?php echo $index; ?>};
                                            width: 32px;
                                            height: 32px;
                                            border-radius: 50%;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                                            border: 2px solid white;
                                        ">
                                            <i class="bi bi-truck" style="
                                                color: white;
                                                font-size: 16px;
                                            "></i>
                                        </div>
                                    </div>
                                `,
                                iconSize: [32, 32],
                                iconAnchor: [16, 16],
                                popupAnchor: [0, -16]
                            });

                            const marker<?php echo $index; ?> = L.marker([
                                <?php echo $location['latitude']; ?>,
                                <?php echo $location['longitude']; ?>
                            ], { icon: markerIcon<?php echo $index; ?> });

                            marker<?php echo $index; ?>.bindPopup(`
                                <div class="p-2">
                                    <h4 class="font-medium"><?php echo htmlspecialchars($location['registration_number']); ?></h4>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($location['driver_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Status: <?php echo htmlspecialchars($location['status']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Last Updated: <?php echo date('M d, Y H:i', strtotime($location['last_updated'])); ?>
                                    </p>
                                </div>
                            `);

                            markers.push(marker<?php echo $index; ?>);
                        <?php endforeach; ?>

                        // Add markers to cluster group
                        markerClusterGroup.addLayers(markers);
                        map.addLayer(markerClusterGroup);

                        // Fit map bounds to show all markers with padding
                        if (markers.length > 0) {
                            const group = new L.featureGroup(markers);
                            map.fitBounds(group.getBounds().pad(0.2));
                        }

                        // Store map instance for refresh function
                        window.mapInstance = map;
                        window.mapMarkers = markers;

                        // Force a resize event to ensure the map renders properly
                        setTimeout(() => {
                            map.invalidateSize();
                        }, 100);
                    } else {
                        console.log('Container has no dimensions, retrying...');
                        // If container is not visible yet, try again in 100ms
                        setTimeout(initMap, 100);
                    }
                };

                // Get marker color based on status
                function getMarkerColor(status) {
                    switch(status.toLowerCase()) {
                        case 'active':
                            return '#10b981'; // green-500
                        case 'maintenance':
                            return '#f59e0b'; // yellow-500
                        case 'inactive':
                            return '#ef4444'; // red-500
                        default:
                            return '#6b7280'; // gray-500
                    }
                }

                // Toggle clustering
                function toggleClustering() {
                    if (!window.mapInstance || !markerClusterGroup) return;
                    
                    isClusteringEnabled = !isClusteringEnabled;
                    if (isClusteringEnabled) {
                        markerClusterGroup.addLayers(window.mapMarkers);
                        window.mapInstance.addLayer(markerClusterGroup);
                    } else {
                        window.mapInstance.removeLayer(markerClusterGroup);
                        window.mapMarkers.forEach(marker => marker.addTo(window.mapInstance));
                    }
                }

                // Toggle legend
                function toggleLegend() {
                    const legend = document.getElementById('mapLegend');
                    isLegendVisible = !isLegendVisible;
                    legend.style.display = isLegendVisible ? 'block' : 'none';
                }

                // Print map
                function printMap() {
                    const mapContainer = document.getElementById('<?php echo $mapId; ?>');
                    const printWindow = window.open('', '_blank');
                    
                    printWindow.document.write(`
                        <html>
                            <head>
                                <title>Map Print</title>
                                <style>
                                    body { margin: 0; }
                                    .map-container { width: 100%; height: 100vh; }
                                </style>
                            </head>
                            <body>
                                <div class="map-container">${mapContainer.outerHTML}</div>
                            </body>
                        </html>
                    `);
                    
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => printWindow.print(), 1000);
                }

                // Refresh map function
                function refreshMap() {
                    fetch('../api/vehicle-locations.php')
                        .then(response => response.json())
                        .then(data => {
                            if (!window.mapInstance) return;

                            // Remove existing markers
                            window.mapMarkers.forEach(marker => {
                                window.mapInstance.removeLayer(marker);
                                if (markerClusterGroup) {
                                    markerClusterGroup.removeLayer(marker);
                                }
                            });
                            window.mapMarkers.length = 0;

                            // Add new markers
                            data.forEach((location, index) => {
                                const markerColor = getMarkerColor(location.status);
                                const markerIcon = L.divIcon({
                                    className: 'custom-div-icon',
                                    html: `
                                        <div class="marker-container" style="
                                            position: relative;
                                            width: 32px;
                                            height: 32px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                        ">
                                            <div style="
                                                background-color: ${markerColor};
                                                width: 32px;
                                                height: 32px;
                                                border-radius: 50%;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                                                border: 2px solid white;
                                            ">
                                                <i class="bi bi-truck" style="
                                                    color: white;
                                                    font-size: 16px;
                                                "></i>
                                            </div>
                                        </div>
                                    `,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16],
                                    popupAnchor: [0, -16]
                                });

                                const marker = L.marker([
                                    location.latitude,
                                    location.longitude
                                ], { icon: markerIcon });

                                marker.bindPopup(`
                                    <div class="p-2">
                                        <h4 class="font-medium">${location.registration_number}</h4>
                                        <p class="text-sm text-gray-600">${location.driver_name}</p>
                                        <p class="text-sm text-gray-500">Status: ${location.status}</p>
                                        <p class="text-sm text-gray-500">
                                            Last Updated: ${new Date(location.last_updated).toLocaleString()}
                                        </p>
                                    </div>
                                `);

                                window.mapMarkers.push(marker);
                            });

                            // Update cluster group
                            if (markerClusterGroup) {
                                markerClusterGroup.clearLayers();
                                if (isClusteringEnabled) {
                                    markerClusterGroup.addLayers(window.mapMarkers);
                                    window.mapInstance.addLayer(markerClusterGroup);
                                } else {
                                    window.mapMarkers.forEach(marker => marker.addTo(window.mapInstance));
                                }
                            }

                            // Update map bounds
                            if (window.mapMarkers.length > 0) {
                                const group = new L.featureGroup(window.mapMarkers);
                                window.mapInstance.fitBounds(group.getBounds().pad(0.2));
                            }

                            // Force a resize event to ensure the map renders properly
                            setTimeout(() => {
                                window.mapInstance.invalidateSize();
                            }, 100);
                        })
                        .catch(error => console.error('Error fetching vehicle locations:', error));
                }

                // Export map data function
                function exportMapData() {
                    if (!window.mapMarkers) return;
                    
                    const data = window.mapMarkers.map(marker => {
                        const popup = marker.getPopup();
                        const content = popup.getContent();
                        const div = document.createElement('div');
                        div.innerHTML = content;
                        
                        return {
                            registration_number: div.querySelector('h4').textContent,
                            driver_name: div.querySelector('p:nth-child(2)').textContent,
                            status: div.querySelector('p:nth-child(3)').textContent.replace('Status: ', ''),
                            last_updated: div.querySelector('p:nth-child(4)').textContent.replace('Last Updated: ', ''),
                            latitude: marker.getLatLng().lat,
                            longitude: marker.getLatLng().lng
                        };
                    });

                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'vehicle-locations.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            </script>
        </div>
        <?php
    }
} 