{{-- resources/views/layouts/admin_theme/gbs_dashboard/fleet.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Fleet Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <span class="badge bg-info" id="lastUpdateHeader">{{ $lastUpdate }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="fleet-dashboard-container" style="height: calc(100vh - 180px); position: relative;">
                            {{-- Toggle Sidebar Button --}}
                            <button class="btn btn-sm btn-primary position-absolute" id="toggleSidebar" style="top: 10px; left: 10px; z-index: 2000; border-radius: 50%; width: 40px; height: 40px; padding: 0;">
                                <i class="fas fa-bars"></i>
                            </button>

                            {{-- Dashboard Container --}}
                            <div class="dashboard-container d-flex" style="height: 100%; width: 100%;">
                                {{-- Sidebar --}}
                                <div class="sidebar" id="fleetSidebar" style="width: 320px; background: #fff; border-right: 1px solid rgba(0,0,0,0.125); display: flex; flex-direction: column; transition: all 0.3s ease; box-shadow: 2px 0 10px rgba(0,0,0,0.05);">
                                    {{-- <div class="sidebar-header p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <h2 class="h6 mb-1 text-white">
                                            <i class="fas fa-truck me-2"></i>Fleet Management
                                        </h2>
                                        <p class="small text-white-50 mb-0">
                                            <i class="far fa-clock me-1"></i>Last: <span id="lastUpdate">{{ $lastUpdate }}</span>
                                        </p>
                                    </div> --}}

                                    <div class="sidebar-tabs d-flex border-bottom">
                                        <button class="tab-btn flex-fill py-2 px-1 border-0 bg-transparent active" onclick="switchTab('fleet')" id="tab-fleet">
                                            <i class="fas fa-truck me-1"></i> Fleet
                                        </button>
                                        <button class="tab-btn flex-fill py-2 px-1 border-0 bg-transparent" onclick="switchTab('events')" id="tab-events">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Events
                                        </button>
                                        <button class="tab-btn flex-fill py-2 px-1 border-0 bg-transparent" onclick="switchTab('places')" id="tab-places">
                                            <i class="fas fa-map-marker-alt me-1"></i> Places
                                        </button>
                                        <button class="tab-btn flex-fill py-2 px-1 border-0 bg-transparent" onclick="switchTab('archive')" id="tab-archive">
                                            <i class="fas fa-archive me-1"></i> Archive
                                        </button>
                                    </div>

                                    {{-- Search Box (fleet tab) --}}
                                    <div class="search-box p-2 bg-light" id="fleet-search" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="vehicleSearch" class="form-control border-start-0" placeholder="Search vehicles..." onkeyup="filterVehicles()">
                                        </div>
                                    </div>

                                    {{-- Group Filter (fleet tab) --}}
                                    <div class="group-filter px-3 py-2 bg-light d-flex align-items-center" id="fleet-filter" style="border-bottom: 1px solid rgba(0,0,0,0.125); font-size: 12px;">
                                        <i class="fas fa-layer-group me-2 text-primary"></i>
                                        <span>No group assigned</span>
                                        <span class="badge bg-secondary ms-auto" id="ungroupedCount">{{ count($ungroupedVehicles) }}</span>
                                    </div>

                                    {{-- Tab Content Container --}}
                                    <div class="tab-content-container" style="flex: 1; overflow-y: auto; background: #f8f9fa;">
                                        {{-- Fleet Tab Content --}}
                                        <div id="fleet-content" class="tab-pane" style="height: 100%; overflow-y: auto; display: block;">
                                            <div class="vehicle-list p-2" id="vehicleList">
                                                @forelse($ungroupedVehicles as $vehicle)
                                                <div class="vehicle-card bg-white rounded p-2 mb-2" 
                                                     onclick="selectVehicle('{{ $vehicle['imei'] }}')" 
                                                     data-imei="{{ $vehicle['imei'] }}"
                                                     data-name="{{ $vehicle['name'] }}"
                                                     style="cursor: pointer; border: 1px solid #dee2e6; border-left: 3px solid transparent; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="me-2">
                                                            <i class="fas fa-bus text-primary" style="font-size: 16px;"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="vehicle-name fw-bold small">{{ $vehicle['name'] }}</div>
                                                            <div class="vehicle-time small text-muted">
                                                                <i class="far fa-clock me-1"></i>{{ $vehicle['last_update'] ?? 'No data' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="vehicle-speed badge rounded-pill {{ $vehicle['speed'] > 0 ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1">
                                                            <i class="fas fa-tachometer-alt me-1"></i>{{ $vehicle['speed'] }} km/h
                                                        </div>
                                                        {{-- <small class="text-muted">
                                                            <i class="fas fa-map-pin me-1"></i>{{ substr($vehicle['imei'], -4) }}
                                                        </small> --}}
                                                    </div>
                                                </div>
                                                @empty
                                                <div class="text-center p-4 text-muted">
                                                    <i class="fas fa-search fa-2x mb-2"></i>
                                                    <p class="small mb-0">No vehicles found</p>
                                                </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Events Tab Content --}}
                                        <div id="events-content" class="tab-pane" style="height: 100%; overflow-y: auto; display: none;">
                                            <div class="events-header p-2 bg-white sticky-top border-bottom">
                                                <h6 class="mb-0 small fw-bold">
                                                    <i class="fas fa-exclamation-triangle text-primary me-2"></i>Recent Events
                                                </h6>
                                            </div>
                                            <div class="events-list p-2" id="eventsList">
                                                <div class="text-center p-4">
                                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                                    <p class="small text-muted mb-0">Loading events...</p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Places Tab Content --}}
                                        <div id="places-content" class="tab-pane" style="height: 100%; overflow-y: auto; display: none;">
                                            <div class="places-header p-2 bg-white border-bottom">
                                                <h6 class="mb-0 small fw-bold">
                                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>Saved Places
                                                </h6>
                                            </div>
                                            <div class="empty-state text-center p-4 text-muted">
                                                <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                                                <p class="small mb-0">No places saved yet</p>
                                            </div>
                                        </div>

                                        {{-- Archive Tab Content --}}
                                        <div id="archive-content" class="tab-pane" style="height: 100%; overflow-y: auto; display: none;">
                                            <div class="archive-header p-2 bg-white border-bottom">
                                                <h6 class="mb-0 small fw-bold">
                                                    <i class="fas fa-archive text-primary me-2"></i>Archived Vehicles
                                                </h6>
                                            </div>
                                            <div class="empty-state text-center p-4 text-muted">
                                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                                <p class="small mb-0">No archived vehicles</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Map Container --}}
                                <div class="map-container" style="flex: 1; position: relative; background: #e9ecef;">
                                    {{-- Stats Bar --}}
                                    <div class="stats-bar position-absolute top-0 start-0 end-0 bg-white m-2 rounded shadow-sm p-2" style="z-index: 1000; left: 340px; transition: left 0.3s ease;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="stats d-flex gap-3">
                                                <div class="stat-item d-flex align-items-center">
                                                    <span class="stat-label text-muted small me-1">Total:</span>
                                                    <span class="stat-value fw-bold small" id="totalVehicles">{{ $stats['total'] }}</span>
                                                </div>
                                                <div class="stat-item d-flex align-items-center">
                                                    <span class="stat-label text-muted small me-1">Moving:</span>
                                                    <span class="stat-value fw-bold small text-success" id="movingVehicles">{{ $stats['moving'] }}</span>
                                                </div>
                                                <div class="stat-item d-flex align-items-center">
                                                    <span class="stat-label text-muted small me-1">Stopped:</span>
                                                    <span class="stat-value fw-bold small text-warning" id="stoppedVehicles">{{ $stats['stopped'] }}</span>
                                                </div>
                                                <div class="stat-item d-flex align-items-center">
                                                    <span class="stat-label text-muted small me-1">Offline:</span>
                                                    <span class="stat-value fw-bold small text-danger" id="offlineVehicles">{{ $stats['offline'] }}</span>
                                                </div>
                                            </div>
                                            <button class="refresh-btn btn btn-sm btn-outline-secondary py-0 px-2" onclick="refreshData()">
                                                <i class="fas fa-sync-alt fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Map --}}
                                    <div id="fleet-map" style="width: 100%; height: 100%;"></div>

                                    {{-- Map Legend --}}
                                    <div class="map-legend position-absolute bg-white rounded shadow-sm p-2" style="bottom: 20px; right: 20px; z-index: 1000; width: 250px; max-height: 300px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.125);">
                                        <div class="legend-title small fw-bold mb-2 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-map-marked-alt me-1 text-primary"></i> Vehicles</span>
                                            <span class="badge bg-secondary" id="legendCount">{{ count($vehicles) }}</span>
                                        </div>
                                        <div class="legend-items" id="vehicleLegend">
                                            @foreach($vehicles as $vehicle)
                                            <div class="legend-item d-flex align-items-center py-1 px-1 rounded" onclick="focusVehicle('{{ $vehicle['imei'] }}')" style="cursor: pointer; font-size: 11px; transition: all 0.2s;">
                                                <span class="marker-dot d-inline-block rounded-circle me-2" style="width: 8px; height: 8px; background: {{ $vehicle['speed'] > 0 ? '#28a745' : ($vehicle['loc_valid'] ? '#ffc107' : '#dc3545') }};"></span>
                                                <span class="legend-name flex-grow-1 text-truncate">{{ Str::limit($vehicle['name'], 20) }}</span>
                                                <span class="legend-speed ms-2 fw-bold">{{ $vehicle['speed'] }} km/h</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Vehicle Details Modal --}}
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title small fw-bold" id="vehicleModalLabel">
                    <i class="fas fa-truck me-2"></i>Vehicle Details
                </h5>
                <button type="button" class="close text-white" onclick="closeVehicleModal()" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; line-height: 1; opacity: 0.8; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" id="vehicleModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 small text-muted">Loading vehicle details...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer_js')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

{{-- Scripts --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Pass PHP variables to JavaScript
    const initialVehicles = @json($vehicles);
    
    // Initialize map
    let map = L.map('fleet-map', {
        zoomControl: false,
        attributionControl: false
    }).setView([27.6931398, 85.3032801], 11);
    
    let markers = {};
    let selectedVehicle = null;
    let refreshInterval;
    let sidebarCollapsed = false;

    // Add zoom control to top-right
    L.control.zoom({ position: 'topright' }).addTo(map);
    
    // Add attribution
    L.control.attribution({ position: 'bottomleft' }).addTo(map);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Initialize markers
    function initializeMarkers() {
        if (initialVehicles && initialVehicles.length > 0) {
            initialVehicles.forEach(vehicle => {
                if (vehicle.lat && vehicle.lng && !isNaN(vehicle.lat) && !isNaN(vehicle.lng)) {
                    addMarker(vehicle);
                }
            });
        }
    }

    // Add marker for vehicle
    function addMarker(vehicle) {
        const lat = parseFloat(vehicle.lat);
        const lng = parseFloat(vehicle.lng);
        
        if (isNaN(lat) || isNaN(lng)) return;

        const markerColor = vehicle.speed > 0 ? '#28a745' : (vehicle.loc_valid ? '#ffc107' : '#dc3545');
        const pulseClass = vehicle.speed > 0 ? 'pulse' : '';
        
        const markerIcon = L.divIcon({
            className: `custom-marker ${pulseClass}`,
            html: `<div style="
                background-color: ${markerColor};
                width: 14px;
                height: 14px;
                border-radius: 50%;
                border: 2px solid white;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                ${vehicle.speed > 0 ? 'animation: pulse 1.5s infinite;' : ''}
            "></div>`,
            iconSize: [14, 14]
        });

        const marker = L.marker([lat, lng], { 
            icon: markerIcon,
            title: vehicle.name || 'Unknown Vehicle'
        }).bindPopup(`
            <div style="min-width: 200px;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-bus text-primary me-2"></i>
                    <h6 class="mb-0 fw-bold">${vehicle.name || 'Unknown Vehicle'}</h6>
                </div>
                <hr class="my-2">
                <table class="table table-sm small mb-2">
                    <tr>
                        <td><i class="fas fa-tachometer-alt me-1"></i> Speed:</td>
                        <td class="fw-bold">${vehicle.speed || 0} km/h</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-clock me-1"></i> Last Update:</td>
                        <td>${vehicle.last_update || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-map-pin me-1"></i> Coordinates:</td>
                        <td class="small">${lat.toFixed(4)}, ${lng.toFixed(4)}</td>
                    </tr>
                </table>
                <div class="d-grid gap-2">
                    <button onclick="showVehicleDetails('${vehicle.imei}')" class="btn btn-primary btn-sm">
                        <i class="fas fa-info-circle me-1"></i>View Details
                    </button>
                </div>
            </div>
        `);

        marker.addTo(map);
        markers[vehicle.imei] = marker;
    }

    // Toggle sidebar
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        const sidebar = document.getElementById('fleetSidebar');
        const statsBar = document.querySelector('.stats-bar');
        const icon = this.querySelector('i');
        
        if (sidebarCollapsed) {
            sidebar.style.width = '320px';
            statsBar.style.left = '340px';
            icon.className = 'fas fa-bars';
        } else {
            sidebar.style.width = '0';
            statsBar.style.left = '60px';
            icon.className = 'fas fa-chevron-right';
        }
        
        sidebarCollapsed = !sidebarCollapsed;
        setTimeout(() => map.invalidateSize(), 300);
    });

    // Select vehicle
    function selectVehicle(imei) {
        document.querySelectorAll('.vehicle-card').forEach(card => {
            card.classList.remove('selected', 'border-primary', 'bg-light');
        });

        const selectedCard = document.querySelector(`[data-imei="${imei}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected', 'border-primary', 'bg-light');
        }

        if (markers[imei]) {
            const latLng = markers[imei].getLatLng();
            map.setView([latLng.lat, latLng.lng], 15);
            markers[imei].openPopup();
        }

        selectedVehicle = imei;
    }

    // Focus on vehicle
    function focusVehicle(imei) {
        if (markers[imei]) {
            const latLng = markers[imei].getLatLng();
            map.setView([latLng.lat, latLng.lng], 15);
            markers[imei].openPopup();
        }
        selectVehicle(imei);
    }

    function closeVehicleModal() {
    const modal = document.getElementById('vehicleModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        // Remove backdrop if exists
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

    // Filter vehicles
    function filterVehicles() {
        const searchTerm = document.getElementById('vehicleSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.vehicle-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name')?.toLowerCase() || '';
            card.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
    }

    // Switch tabs
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'text-primary', 'border-bottom', 'border-primary');
        });
        event.target.closest('.tab-btn').classList.add('active', 'text-primary', 'border-bottom', 'border-primary');
        
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.style.display = 'none';
        });
        
        const selectedPane = document.getElementById(`${tab}-content`);
        selectedPane.style.display = 'block';
        
        const fleetSearch = document.getElementById('fleet-search');
        const fleetFilter = document.getElementById('fleet-filter');
        
        if (tab === 'fleet') {
            if (fleetSearch) fleetSearch.style.display = 'block';
            if (fleetFilter) fleetFilter.style.display = 'flex';
        } else {
            if (fleetSearch) fleetSearch.style.display = 'none';
            if (fleetFilter) fleetFilter.style.display = 'none';
        }
        
        if (tab === 'events') {
            loadEvents();
        }
    }

    // Load events data
    function loadEvents() {
        const eventsList = document.getElementById('eventsList');
        
        eventsList.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <p class="small text-muted mb-0">Loading events...</p>
            </div>
        `;
        
        fetch('gpsdashboard/events/recent')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    
                    if (data.data && Object.keys(data.data).length > 0) {
                        Object.entries(data.data).forEach(([imei, events]) => {
                            if (Array.isArray(events) && events.length > 0) {
                                events.slice(0, 5).forEach(event => {
                                    const eventType = event.event || 'info';
                                    const eventClass = getEventClass(eventType);
                                    const timeAgo = event.dt_tracker ? new Date(event.dt_tracker).toLocaleString() : 'Unknown';
                                    
                                    html += `
                                        <div class="event-item bg-white rounded p-2 mb-2 border-start border-3 border-${eventClass === 'sos' ? 'danger' : (eventClass === 'alert' ? 'warning' : 'info')}" onclick="showEventDetails('${imei}', '${event.dt_tracker}')" style="cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                            <div class="small fw-bold mb-1">${eventType.toUpperCase()}</div>
                                            <div class="small text-muted mb-1">${event.message || 'No description'}</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-${eventClass === 'sos' ? 'danger' : (eventClass === 'alert' ? 'warning' : 'info')} px-2">${imei.substring(0, 8)}...</span>
                                                <small class="text-muted"><i class="far fa-clock me-1"></i>${timeAgo}</small>
                                            </div>
                                        </div>
                                    `;
                                });
                            }
                        });
                    } else {
                        html = `
                            <div class="empty-state text-center p-4 text-muted">
                                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                <p class="small mb-0">No recent events</p>
                            </div>
                        `;
                    }
                    
                    eventsList.innerHTML = html;
                } else {
                    eventsList.innerHTML = `
                        <div class="empty-state text-center p-4 text-muted">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <p class="small mb-0">Failed to load events</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                eventsList.innerHTML = `
                    <div class="empty-state text-center p-4 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p class="small mb-0">Error loading events</p>
                    </div>
                `;
            });
    }

    // Show event details
    function showEventDetails(imei, time) {
        alert(`Event for vehicle ${imei} at ${time}`);
    }

    // Get event class for styling
    function getEventClass(eventType) {
        const type = eventType.toLowerCase();
        const sosEvents = ['sos', 'panic', 'emergency', 'alarm'];
        const alertEvents = ['alert', 'warning', 'overspeed', 'lowbat', 'pwrcut', 'shock', 'tow'];
        
        if (sosEvents.includes(type)) return 'sos';
        if (alertEvents.includes(type)) return 'alert';
        return 'info';
    }

    function openGoogleMaps(lat, lng, vehicleName) {
        const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving`;
        window.open(googleMapsUrl, '_blank');
    }

    // Show vehicle details
    function showVehicleDetails(imei) {
        const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
        modal.show();
        
        document.getElementById('vehicleModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 small text-muted">Loading vehicle details...</p>
            </div>
        `;
        
        fetch(`gpsdashboard/vehicle/${imei}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<div class="vehicle-details">';
                    
                    if (data.data) {
                        const vehicle = data.data[imei] || data.data;
                        const lat = vehicle.lat;
                        const lng = vehicle.lng;
                        html += `
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded">
                                        <h6 class="small fw-bold mb-2"><i class="fas fa-info-circle me-1"></i>Basic Info</h6>
                                        <table class="table table-sm small mb-0">
                                            <tr><td class="text-muted">IMEI:</td><td class="fw-bold">${imei}</td></tr>
                                            <tr><td class="text-muted">Name:</td><td>${vehicle.name || 'N/A'}</td></tr>
                                            <tr><td class="text-muted">Speed:</td><td class="fw-bold text-${vehicle.speed > 0 ? 'success' : 'warning'}">${vehicle.speed || 0} km/h</td></tr>
                                            <tr><td class="text-muted">Last Update:</td><td>${vehicle.dt_tracker || 'N/A'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded">
                                        <h6 class="small fw-bold mb-2"><i class="fas fa-map-marker-alt me-1"></i>Location</h6>
                                        <table class="table table-sm small mb-0">
                                            <tr>
                                                <td class="text-muted">Position:</td>
                                                <td>
                                                    ${lat && lng ? 
                                                        `<a href="javascript:void(0)" onclick="openGoogleMaps('${lat}', '${lng}', '${vehicle.name}')" class="text-primary text-decoration-none">
                                                            <i class="fas fa-external-link-alt me-1"></i>${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}
                                                        </a>` 
                                                        : 'N/A'
                                                    }
                                                </td>
                                            </tr>
                                            <tr><td class="text-muted">Altitude:</td><td>${vehicle.altitude || 0} m</td></tr>
                                            <tr><td class="text-muted">Angle:</td><td>${vehicle.angle || 0}°</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="bg-light p-2 rounded">
                                        <h6 class="small fw-bold mb-2"><i class="fas fa-cog me-1"></i>Additional Info</h6>
                                        <table class="table table-sm small mb-0">
                                            <tr><td class="text-muted">Location Valid:</td><td>${vehicle.loc_valid ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td></tr>
                                            <tr><td class="text-muted">Odometer:</td><td>${vehicle.odometer ? vehicle.odometer + ' km' : 'N/A'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    html += '</div>';
                    document.getElementById('vehicleModalBody').innerHTML = html;
                } else {
                    document.getElementById('vehicleModalBody').innerHTML = `
                        <div class="alert alert-danger mb-0">Failed to load vehicle details</div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('vehicleModalBody').innerHTML = `
                    <div class="alert alert-danger mb-0">Error loading vehicle details</div>
                `;
            });
    }

    // Refresh data
    function refreshData() {
        const refreshBtn = document.querySelector('.refresh-btn');
        const originalHtml = refreshBtn.innerHTML;
        
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i>';
        refreshBtn.disabled = true;

        Promise.all([
            fetch('gpsdashboard/live-data').then(res => res.json())
        ]).then(([data]) => {
            if (data.success) {
                document.getElementById('totalVehicles').textContent = data.stats.total;
                document.getElementById('movingVehicles').textContent = data.stats.moving;
                document.getElementById('stoppedVehicles').textContent = data.stats.stopped;
                document.getElementById('offlineVehicles').textContent = data.stats.offline;
                document.getElementById('lastUpdate').textContent = data.last_update;
                document.getElementById('lastUpdateHeader').textContent = data.last_update;
                document.getElementById('legendCount').textContent = data.vehicles.length;

                updateMapData(data.vehicles);
            }
            
            if (document.querySelector('.tab-btn.active').id === 'tab-events') {
                loadEvents();
            }
        }).finally(() => {
            refreshBtn.innerHTML = originalHtml;
            refreshBtn.disabled = false;
        });
    }

    // Update map data
    function updateMapData(vehicles) {
        if (!vehicles || vehicles.length === 0) return;
        
        const legend = document.getElementById('vehicleLegend');
        if (legend) legend.innerHTML = '';

        vehicles.forEach(vehicle => {
            if (vehicle.lat && vehicle.lng && !isNaN(parseFloat(vehicle.lat)) && !isNaN(parseFloat(vehicle.lng))) {
                const lat = parseFloat(vehicle.lat);
                const lng = parseFloat(vehicle.lng);
                
                if (markers[vehicle.imei]) {
                    markers[vehicle.imei].setLatLng([lat, lng]);
                } else {
                    addMarker(vehicle);
                }
            }

            if (legend) {
                const statusColor = vehicle.speed > 0 ? '#28a745' : (vehicle.loc_valid ? '#ffc107' : '#dc3545');
                
                legend.innerHTML += `
                    <div class="legend-item d-flex align-items-center py-1 px-1 rounded" onclick="focusVehicle('${vehicle.imei}')" style="cursor: pointer; font-size: 11px;">
                        <span class="marker-dot d-inline-block rounded-circle me-2" style="width: 8px; height: 8px; background: ${statusColor};"></span>
                        <span class="legend-name flex-grow-1 text-truncate">${(vehicle.name || 'Unknown').substring(0, 20)}</span>
                        <span class="legend-speed ms-2 fw-bold">${vehicle.speed || 0}</span>
                    </div>
                `;
            }

            const card = document.querySelector(`[data-imei="${vehicle.imei}"]`);
            if (card) {
                const timeElement = card.querySelector('.vehicle-time');
                const speedElement = card.querySelector('.vehicle-speed');
                
                if (timeElement) {
                    const timeAgo = vehicle.last_update ? new Date(vehicle.last_update).toLocaleString() : 'No data';
                    timeElement.innerHTML = `<i class="far fa-clock me-1"></i>${timeAgo}`;
                }
                
                if (speedElement) {
                    speedElement.className = `vehicle-speed badge rounded-pill ${vehicle.speed > 0 ? 'bg-success' : 'bg-warning text-dark'} px-2 py-1`;
                    speedElement.innerHTML = `<i class="fas fa-tachometer-alt me-1"></i>${vehicle.speed || 0} km/h`;
                }
            }
        });
    }

    // Auto refresh every 30 seconds
    function startAutoRefresh() {
        refreshInterval = setInterval(refreshData, 30000);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeMarkers();
        startAutoRefresh();
        
        // Fix map size after initial render
        setTimeout(() => map.invalidateSize(), 100);
    });

    // Cleanup
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) clearInterval(refreshInterval);
    });
</script>

<style>
    .tab-btn {
        font-size: 12px;
        transition: all 0.2s;
        color: #6c757d;
    }
    
    .tab-btn:hover {
        color: #007bff;
        background: #f8f9fa;
    }
    
    .tab-btn.active {
        color: #007bff !important;
        border-bottom: 2px solid #007bff !important;
    }
    
    .vehicle-card {
        transition: all 0.2s;
    }
    
    .vehicle-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        border-color: #007bff !important;
    }
    
    .vehicle-card.selected {
        background: #f0f7ff !important;
        border-left-color: #007bff !important;
        border-color: #007bff !important;
    }
    
    .event-item:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .legend-item:hover {
        background: #f8f9fa;
    }
    
    /* Pulse animation for moving vehicles */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    .custom-marker.pulse div {
        animation: pulse 1.5s infinite;
    }
    
    /* Scrollbar styling */
    .tab-content-container::-webkit-scrollbar,
    .map-legend::-webkit-scrollbar {
        width: 4px;
    }
    
    .tab-content-container::-webkit-scrollbar-track,
    .map-legend::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .tab-content-container::-webkit-scrollbar-thumb,
    .map-legend::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .tab-content-container::-webkit-scrollbar-thumb:hover,
    .map-legend::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    
    /* Modal close button styling */
    .btn-close-white {
        filter: brightness(0) invert(1);
        opacity: 1;
    }
    
    .btn-close-white:hover {
        opacity: 0.8;
    }
    
    /* Map container adjustments */
    .leaflet-control-attribution {
        font-size: 9px !important;
        background: rgba(255, 255, 255, 0.8) !important;
        padding: 2px 5px !important;
        border-radius: 4px !important;
    }
    
    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
    }
    
    .leaflet-control-zoom a {
        background: white !important;
        color: #333 !important;
        width: 30px !important;
        height: 30px !important;
        line-height: 30px !important;
        font-size: 16px !important;
    }
    
    .leaflet-control-zoom a:hover {
        background: #f8f9fa !important;
        color: #007bff !important;
    }
    
    /* Stats bar transition */
    .stats-bar {
        transition: left 0.3s ease;
    }
    
    /* Badge styling */
    .badge.bg-success {
        background-color: #28a745 !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
</style>
@endsection