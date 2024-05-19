@extends('layouts.app')
@section('title', __('business.business_location_coordinates'))

@section('content')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=geometry"></script>

    <style>
        #map {
            height: 400px;
        }

        #markers-list {
            list-style: none;
            padding: 0;
            margin-top: 10px;
            border: 1px solid #ccc;
            /* Add a border */
            border-radius: 5px;
            /* Add border-radius for rounded corners */
        }

        .marker-item {
            display: flex;
            justify-content: space-between;
            /* Align delete button to the right */
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
            /* Add a bottom border between items */
        }

        .delete-marker {
            cursor: pointer;
            color: red;
            border: none;
            /* Remove the default button border */
            background-color: transparent;
            /* Make the button background transparent */
            padding: 0;
            /* Remove default padding */
            margin-left: 10px;
        }
    </style>

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('business.business_locations')
            <small>@lang('business.business_location_coordinates')</small>
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => route('location.save_polygon', ['location_id' => $location_id]),
            'method' => 'post',
            'id' => 'business_location_markers_add_form',
        ]) !!}
        {!! Form::hidden('location_id', $location_id) !!}
        {!! Form::hidden('markers', null, ['id' => 'markers-input']) !!}

        <div class="row">


            <div class="col-md-4">
                <ul id="markers-list"></ul>
            </div>
            <div class="col-md-8">
                <div id="map"></div>
                <br>

                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" onclick="goBack()">@lang('messages.go_back')</button>
            </div>
        </div>

        {!! Form::close() !!}
    </section>
    <!-- /.content -->

@endsection
@section('javascript')

    <script type="text/javascript" async defer
        src="https://maps.google.com/maps/api/js?key={{ Config::get('app.GOOGLE_MAP_KEY') }}&callback=initMap"></script>

    {{-- <script>
        var map;
        var markers = [];
        var polygon;

        function initMap() {
            var riyadh = {
                lat: 24.7136,
                lng: 46.6753
            };

            map = new google.maps.Map(document.getElementById('map'), {
                center: riyadh,
                zoom: 13,
                mapTypeId: 'hybrid', // Use hybrid mode for satellite imagery with labels
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    position: google.maps.ControlPosition.TOP_RIGHT,
                    mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain']
                }
            });

            google.maps.event.addListener(map, 'click', function(event) {
                addMarker(event.latLng);
                updatePolygon();
            });
        }

        function addMarker(location) {
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                draggable: true
            });

            markers.push(marker);

            // Display marker above the map with a delete button
            displayMarkerItem(marker, markers.length);

            // Listen for marker drag events
            google.maps.event.addListener(marker, 'dragend', function() {
                updatePolygon();
                updateMarkerList();
                updateMarkersInput();
            });
        }

        function displayMarkerItem(marker, index) {
            var markersList = document.getElementById('markers-list');

            var markerItem = document.createElement('li');
            markerItem.className = 'marker-item';

            var markerText = document.createTextNode(
                'Marker ' + index + ': ' + marker.getPosition().toUrlValue(6)
            );
            markerItem.appendChild(markerText);

            var deleteButton = document.createElement('span');
            deleteButton.className = 'delete-marker';
            deleteButton.innerHTML = 'Delete';
            deleteButton.onclick = function() {
                deleteMarker(marker);
            };

            markerItem.appendChild(deleteButton);

            markersList.appendChild(markerItem);
        }

        function deleteMarker(marker) {
            marker.setMap(null); // Remove marker from the map
            markers = markers.filter(function(m) {
                return m !== marker;
            });

            updatePolygon();
            updateMarkerList();
            updateMarkersInput();
        }

        function updatePolygon() {
            if (markers.length >= 3) {
                // Create a new polygon with the updated marker positions
                var polygonCoordinates = markers.map(function(marker) {
                    return marker.getPosition();
                });

                // Remove the existing polygon from the map
                if (polygon) {
                    polygon.setMap(null);
                }

                // Create a new polygon
                polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35
                });

                // Set the polygon on the map
                polygon.setMap(map);
            } else {
                // Remove the polygon if there are less than 3 markers
                if (polygon) {
                    polygon.setMap(null);
                }
            }
            updateMarkersInput();
        }

        function updateMarkerList() {
            // Clear the markers list
            var markersList = document.getElementById('markers-list');
            markersList.innerHTML = '';

            // Display each marker above the map
            markers.forEach(function(marker, index) {
                displayMarkerItem(marker, index + 1);
            });

        }

        function updateMarkersInput() {
            var markersInput = document.getElementById('markers-input');
            markersInput.value = JSON.stringify(markers.map(function(marker) {
                return {
                    lat: marker.getPosition().lat(),
                    lng: marker.getPosition().lng()
                };
            }));
        }
    </script> --}}

    <script>
        var map;
        var markers = [];
        var polygon;

        function initMap() {
            var riyadh = {
                lat: 24.7136,
                lng: 46.6753
            };

            map = new google.maps.Map(document.getElementById('map'), {
                center: riyadh,
                zoom: 13,
                mapTypeId: 'hybrid',
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    position: google.maps.ControlPosition.TOP_RIGHT,
                    mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain']
                }
            });
            @if (isset($existingMarkersJson))
                var existingMarkers = {!! $existingMarkersJson !!};

                existingMarkers.forEach(function(existingMarker) {
                    var location = new google.maps.LatLng(existingMarker.lat, existingMarker.lng);
                    addMarker(location);
                });
                updatePolygon();
            @endif

            google.maps.event.addListener(map, 'click', function(event) {
                addMarker(event.latLng);
                updatePolygon();
            });
        }

        function goBack() {
            window.history.back();
        }

        function addMarker(location) {
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                draggable: true
            });

            markers.push(marker);

            displayMarkerItem(marker, markers.length);

            google.maps.event.addListener(marker, 'dragend', function() {
                updatePolygon();
                updateMarkerList();
            });
        }

        function displayMarkerItem(marker, index) {
            var markersList = document.getElementById('markers-list');

            var markerItem = document.createElement('li');
            markerItem.className = 'marker-item';

            var markerText = document.createTextNode(
                'Marker ' + index + ': ' + marker.getPosition().toUrlValue(6)
            );
            markerItem.appendChild(markerText);

            var deleteButton = document.createElement('span');
            deleteButton.className = 'delete-marker';
            deleteButton.innerHTML = 'Delete';
            deleteButton.onclick = function() {
                deleteMarker(marker);
            };

            markerItem.appendChild(deleteButton);

            markersList.appendChild(markerItem);
        }

        function deleteMarker(marker) {
            marker.setMap(null);
            markers = markers.filter(function(m) {
                return m !== marker;
            });

            updatePolygon();
            updateMarkerList();
        }

        function updatePolygon() {
            if (markers.length >= 3) {
                var polygonCoordinates = markers.map(function(marker) {
                    return marker.getPosition();
                });

                if (polygon) {
                    polygon.setMap(null);
                }

                polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35
                });

                polygon.setMap(map);
            } else {
                if (polygon) {
                    polygon.setMap(null);
                }
            }
            updateMarkersInput();
        }

        function updateMarkerList() {
            var markersList = document.getElementById('markers-list');
            markersList.innerHTML = '';

            markers.forEach(function(marker, index) {
                displayMarkerItem(marker, index + 1);
            });
            updateMarkersInput();
        }

        function updateMarkersInput() {
            var markersInput = document.getElementById('markers-input');
            markersInput.value = JSON.stringify(markers.map(function(marker) {
                return {
                    lat: marker.getPosition().lat(),
                    lng: marker.getPosition().lng()
                };
            }));
        }
    </script>
@endsection
