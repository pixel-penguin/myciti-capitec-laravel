<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shuttle Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #tracking-map { height: 350px; width: 100%; }
        @media (min-width: 768px) { #tracking-map { height: 450px; } }

        .bus-marker {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%;
            border: 3px solid #fff; color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            font-size: 18px; transition: background-color 0.3s;
        }
        .bus-marker.moving  { background-color: #4CAF50; }
        .bus-marker.stopped { background-color: #F57C00; }
        .bus-marker.offline { background-color: #9E9E9E; }

        .live-badge {
            position: absolute; top: 12px; right: 12px; z-index: 1000;
            background: #fff; border-radius: 16px; padding: 5px 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; color: #333;
        }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #4CAF50;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .bus-card {
            border: 1px solid #e2e8f0; border-radius: 12px;
            overflow: hidden; background: #fff;
        }
        .bus-card-header {
            padding: 12px 14px; display: flex; align-items: center; gap: 10px;
        }
        .bus-card-header.moving  { background: rgba(76,175,80,0.1); color: #4CAF50; }
        .bus-card-header.stopped { background: rgba(245,124,0,0.1); color: #F57C00; }
        .bus-card-header.offline { background: rgba(158,158,158,0.1); color: #9E9E9E; }

        .speed-badge {
            padding: 3px 10px; border-radius: 12px; font-size: 13px; font-weight: 700;
        }
        .speed-badge.moving  { background: rgba(76,175,80,0.15); color: #4CAF50; }
        .speed-badge.stopped { background: rgba(245,124,0,0.15); color: #F57C00; }
        .speed-badge.offline { background: rgba(158,158,158,0.15); color: #9E9E9E; }

        .bus-card-details {
            padding: 14px; display: flex; gap: 12px;
        }
        .detail-item {
            flex: 1; display: flex; align-items: center; gap: 6px; justify-content: center;
        }
        .detail-divider {
            width: 1px; background: #e2e8f0; align-self: stretch;
        }
        .detail-label { font-size: 11px; color: #94a3b8; }
        .detail-value { font-size: 13px; font-weight: 600; color: #1e293b; }

        .empty-state {
            text-align: center; padding: 32px 20px; background: #f8fafc;
            border: 1px solid #e2e8f0; border-radius: 12px;
        }
        .empty-state svg { margin: 0 auto 12px; color: #94a3b8; }

        .footer-row {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 12px; color: #94a3b8; margin-top: 8px;
        }
        .auto-update-dot {
            width: 6px; height: 6px; border-radius: 50%; background: #4CAF50;
            display: inline-block; margin-right: 4px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900" x-data="trackingApp()" x-init="init()">
    <div class="max-w-4xl mx-auto">
        {{-- Map --}}
        <div class="relative">
            <div id="tracking-map"></div>
            <div class="live-badge">
                <span class="live-dot"></span>
                LIVE
            </div>
        </div>

        {{-- Content below map --}}
        <div class="px-4 py-4 space-y-3">
            {{-- Loading --}}
            <template x-if="loading">
                <div class="flex justify-center py-4">
                    <svg class="animate-spin h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </template>

            {{-- Error --}}
            <template x-if="error">
                <div class="bg-red-50 text-red-600 text-sm rounded-lg p-4" x-text="error"></div>
            </template>

            {{-- Empty state --}}
            <template x-if="!loading && !error && Object.keys(buses).length === 0">
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 6v6m7-6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H6c-1.1 0-2.1.8-2.4 1.8l-1.4 5c-.1.4-.2.8-.2 1.2 0 .4.1.8.2 1.2C2.5 16.3 3 18 3 18h3m0 0a2 2 0 1 0 4 0m-4 0a2 2 0 1 1 4 0m4 0a2 2 0 1 0 4 0m-4 0a2 2 0 1 1 4 0"/>
                    </svg>
                    <p class="text-base font-semibold text-slate-500">No shuttles currently on route</p>
                    <p class="text-sm text-slate-400 mt-1">Shuttle locations will appear here once a bus is active.</p>
                </div>
            </template>

            {{-- Bus cards --}}
            <template x-for="bus in busList" :key="bus.bus_id">
                <div class="bus-card">
                    <div class="bus-card-header" :class="bus.status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <template x-if="bus.status === 'moving'">
                                <path d="M4 16c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.5-3.58-4-8-4s-8 .5-8 4v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                            </template>
                            <template x-if="bus.status === 'stopped'">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                            </template>
                            <template x-if="bus.status === 'offline'">
                                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4c-1.48 0-2.85.43-4.01 1.17l1.46 1.46C10.21 6.23 11.08 6 12 6c3.04 0 5.5 2.46 5.5 5.5v.5H19c1.66 0 3 1.34 3 3 0 1.13-.64 2.11-1.56 2.62l1.45 1.45C23.16 18.16 24 16.68 24 15c0-2.64-2.05-4.78-4.65-4.96zM3 5.27l2.75 2.74C2.56 8.15 0 10.77 0 14c0 3.31 2.69 6 6 6h11.73l2 2 1.27-1.27L4.27 4 3 5.27zM7.73 10l8 8H6c-2.21 0-4-1.79-4-4s1.79-4 4-4h1.73z"/>
                            </template>
                        </svg>
                        <div class="flex-1">
                            <div class="font-bold text-[15px]" x-text="bus.bus_name"></div>
                            <div class="text-[13px] opacity-80" x-text="statusLabel(bus.status)"></div>
                        </div>
                        <template x-if="bus.speed !== null">
                            <span class="speed-badge" :class="bus.status" x-text="Math.round(bus.speed) + ' km/h'"></span>
                        </template>
                    </div>
                    <div class="bus-card-details">
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            <div>
                                <div class="detail-label">Direction</div>
                                <div class="detail-value" x-text="headingText(bus.heading)"></div>
                            </div>
                        </div>
                        <div class="detail-divider"></div>
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <div>
                                <div class="detail-label">Last seen</div>
                                <div class="detail-value" x-text="lastSeenText(bus.recorded_at)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Footer --}}
            <template x-if="Object.keys(buses).length > 0">
                <div class="footer-row">
                    <span x-text="'Last updated: ' + lastUpdatedText"></span>
                    <span><span class="auto-update-dot"></span>Auto-updating</span>
                </div>
            </template>
        </div>
    </div>

    <script>
    function trackingApp() {
        return {
            buses: {},
            markers: {},
            map: null,
            loading: true,
            error: null,
            lastUpdated: null,
            pusher: null,

            get busList() {
                return Object.values(this.buses);
            },

            get lastUpdatedText() {
                if (!this.lastUpdated) return '';
                return this.formatTime(this.lastUpdated);
            },

            async init() {
                this.initMap();
                await this.loadLocations();
                this.subscribeToPusher();
            },

            initMap() {
                this.map = L.map('tracking-map', { zoomControl: true }).setView([-33.9, 18.48], 11);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(this.map);
            },

            async loadLocations() {
                this.loading = true;
                this.error = null;
                try {
                    const resp = await axios.get('/api/tracking/locations');
                    const data = Array.isArray(resp.data) ? resp.data : [];
                    this.applyUpdates(data, true);
                } catch (e) {
                    this.error = 'Unable to load tracking data.';
                } finally {
                    this.loading = false;
                }
            },

            subscribeToPusher() {
                const key = '{{ config("broadcasting.connections.pusher.key") }}';
                const cluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
                if (!key) return;

                this.pusher = new Pusher(key, { cluster });
                const channel = this.pusher.subscribe('tracking');
                channel.bind('BusLocationUpdated', (data) => {
                    if (!data.locations || !Array.isArray(data.locations)) return;
                    this.applyUpdates(data.locations, false);
                });
            },

            applyUpdates(locations, isInitial) {
                if (!locations.length) return;

                for (const loc of locations) {
                    const id = loc.bus_id;
                    const lat = parseFloat(loc.latitude);
                    const lng = parseFloat(loc.longitude);
                    if (isNaN(lat) || isNaN(lng)) continue;

                    if (this.buses[id]) {
                        // Update existing bus data
                        const bus = this.buses[id];
                        bus.latitude = lat;
                        bus.longitude = lng;
                        bus.heading = loc.heading;
                        bus.speed = loc.speed;
                        bus.status = loc.status || 'moving';
                        bus.bus_name = loc.bus_name || bus.bus_name;
                        bus.recorded_at = loc.recorded_at;
                    } else {
                        this.buses[id] = { ...loc, latitude: lat, longitude: lng };
                    }

                    // Update or create marker
                    this.updateMarker(id, lat, lng, loc.status || 'moving', isInitial);
                }

                this.lastUpdated = new Date();

                // Fit map to markers on first load
                if (isInitial) {
                    this.fitMapToMarkers();
                }
            },

            updateMarker(busId, lat, lng, status, snap) {
                const newLatLng = L.latLng(lat, lng);

                if (this.markers[busId]) {
                    const marker = this.markers[busId];
                    // Update icon class for status
                    marker.setIcon(this.createIcon(status));
                    if (snap) {
                        marker.setLatLng(newLatLng);
                    } else {
                        this.animateMarker(marker, newLatLng, 800);
                    }
                } else {
                    const marker = L.marker(newLatLng, {
                        icon: this.createIcon(status),
                    }).addTo(this.map);
                    const bus = this.buses[busId];
                    if (bus) {
                        marker.bindTooltip(bus.bus_name || 'Bus ' + busId, {
                            direction: 'top', offset: [0, -20],
                        });
                    }
                    this.markers[busId] = marker;
                }
            },

            createIcon(status) {
                return L.divIcon({
                    className: '',
                    html: `<div class="bus-marker ${status}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 16c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.5-3.58-4-8-4s-8 .5-8 4v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                        </svg>
                    </div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                });
            },

            animateMarker(marker, targetLatLng, duration) {
                const start = marker.getLatLng();
                const startTime = performance.now();

                const animate = (now) => {
                    const elapsed = now - startTime;
                    const t = Math.min(elapsed / duration, 1);
                    // easeInOut
                    const ease = t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

                    const lat = start.lat + (targetLatLng.lat - start.lat) * ease;
                    const lng = start.lng + (targetLatLng.lng - start.lng) * ease;
                    marker.setLatLng([lat, lng]);

                    if (t < 1) {
                        requestAnimationFrame(animate);
                    }
                };

                requestAnimationFrame(animate);
            },

            fitMapToMarkers() {
                const latlngs = Object.values(this.markers).map(m => m.getLatLng());
                if (latlngs.length === 0) return;
                if (latlngs.length === 1) {
                    this.map.setView(latlngs[0], 14);
                } else {
                    this.map.fitBounds(L.latLngBounds(latlngs), { padding: [50, 50] });
                }
            },

            // Helpers
            statusLabel(status) {
                return { moving: 'Moving', stopped: 'Stopped', offline: 'Offline' }[status] || 'Moving';
            },

            headingText(heading) {
                if (heading == null) return '--';
                const deg = parseFloat(heading);
                if (isNaN(deg)) return '--';
                if (deg >= 337.5 || deg < 22.5) return 'North';
                if (deg < 67.5) return 'North-East';
                if (deg < 112.5) return 'East';
                if (deg < 157.5) return 'South-East';
                if (deg < 202.5) return 'South';
                if (deg < 247.5) return 'South-West';
                if (deg < 292.5) return 'West';
                return 'North-West';
            },

            lastSeenText(recordedAt) {
                if (!recordedAt) return '--';
                try {
                    return this.formatTime(new Date(recordedAt));
                } catch { return '--'; }
            },

            formatTime(dt) {
                let h = dt.getHours();
                const m = String(dt.getMinutes()).padStart(2, '0');
                const s = String(dt.getSeconds()).padStart(2, '0');
                const period = h >= 12 ? 'PM' : 'AM';
                h = h > 12 ? h - 12 : (h === 0 ? 12 : h);
                return `${h}:${m}:${s} ${period}`;
            },
        };
    }
    </script>
</body>
</html>
