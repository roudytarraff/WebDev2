@php
    $latitudeName = $latitudeName ?? 'latitude';
    $longitudeName = $longitudeName ?? 'longitude';
    $latitude = $latitude ?? null;
    $longitude = $longitude ?? null;
    $label = $label ?? 'Location';
    $mapId = 'map_picker_' . uniqid();
    $latId = $mapId . '_lat';
    $lngId = $mapId . '_lng';
    $searchId = $mapId . '_search';
    $apiKey = config('services.google.maps_api_key');
@endphp

<div class="field full map-picker">
    <label>{{ $label }}</label>

    <div class="map-tools">
        <input id="{{ $searchId }}" type="text" placeholder="Search address or place">
        <button type="button" class="button secondary" data-use-current-location="{{ $mapId }}">Use Current Location</button>
    </div>

    <div id="{{ $mapId }}" class="map map-picker-canvas"></div>

    <div class="map-coordinates">
        <div>
            <label>Latitude</label>
            <input id="{{ $latId }}" name="{{ $latitudeName }}" value="{{ old($latitudeName, $latitude) }}">
        </div>

        <div>
            <label>Longitude</label>
            <input id="{{ $lngId }}" name="{{ $longitudeName }}" value="{{ old($longitudeName, $longitude) }}">
        </div>
    </div>

    @if(! $apiKey)
        <p class="muted">Add your key in <strong>.env</strong> as <strong>GOOGLE_MAPS_API_KEY=your_key_here</strong> to enable Google Maps.</p>
    @endif
</div>

@if($apiKey)
    @once
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=places"></script>
    @endonce

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapElement = document.getElementById(@json($mapId));
            const latInput = document.getElementById(@json($latId));
            const lngInput = document.getElementById(@json($lngId));
            const searchInput = document.getElementById(@json($searchId));
            const currentButton = document.querySelector('[data-use-current-location="{{ $mapId }}"]');

            if (!mapElement || !window.google) {
                return;
            }

            const initial = {
                lat: parseFloat(latInput.value) || 33.8938,
                lng: parseFloat(lngInput.value) || 35.5018
            };

            const map = new google.maps.Map(mapElement, {
                center: initial,
                zoom: 14,
                mapTypeControl: false,
                streetViewControl: false
            });

            const marker = new google.maps.Marker({
                position: initial,
                map: map,
                draggable: true
            });

            function setPosition(position) {
                marker.setPosition(position);
                map.setCenter(position);
                latInput.value = position.lat().toFixed(7);
                lngInput.value = position.lng().toFixed(7);
            }

            map.addListener('click', function (event) {
                setPosition(event.latLng);
            });

            marker.addListener('dragend', function (event) {
                setPosition(event.latLng);
            });

            const autocomplete = new google.maps.places.Autocomplete(searchInput);
            autocomplete.bindTo('bounds', map);
            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }

                setPosition(place.geometry.location);
                map.setZoom(16);
            });

            currentButton.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Geolocation is not available in this browser.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(function (position) {
                    setPosition(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
                    map.setZoom(16);
                }, function () {
                    alert('Could not read your current location.');
                });
            });
        });
    </script>
@endif
