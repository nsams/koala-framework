Ext2.namespace('Kwf.GoogleMap');
Kwf.GoogleMap.isLoaded = false;
Kwf.GoogleMap.isCallbackCalled = false;
Kwf.GoogleMap.callbacks = [];

Kwf.GoogleMap.load = function(callback, scope)
{
    if (Kwf.GoogleMap.isCallbackCalled) {
        callback.call(scope || window);
        return;
    }
    Kwf.GoogleMap.callbacks.push({
        callback: callback,
        scope: scope
    });
    if (Kwf.GoogleMap.isLoaded) return;

    Kwf.GoogleMap.isLoaded = true;

    //try find the correct api key
    //Kwf.GoogleMap.apiKeys is set by Kwf_Assets_Dependency_Dynamic_GoogleMapsApiKeys
    //and contains possibly multiple api keys (to support multiple domains)
    var apiKeyIndex;

    var hostParts = location.host.split('.');
    if (hostParts.length <= 1) {
        apiKeyIndex = location.host;
    } else {
        apiKeyIndex = hostParts[hostParts.length-2]  // eg. 'koala-framework'
                     +hostParts[hostParts.length-1]; // eg. 'org'
    }
    if (['orat', 'coat', 'gvat', 'couk'].indexOf(apiKeyIndex) != -1) {
        //one part more for those
        apiKeyIndex = hostParts[hostParts.length-3]+apiKeyIndex;
    }

    var key = '';
    if (apiKeyIndex in Kwf.GoogleMap.apiKeys) {
        key = Kwf.GoogleMap.apiKeys[apiKeyIndex];
    }
    var url = location.protocol+'/'+'/maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='+key+'&c&libraries=places&async=2&language='+trlKwf('en');
    url += '&callback=Kwf.GoogleMap._loaded';
    var s = document.createElement('script');
    s.setAttribute('type', 'text/javascript');
    s.setAttribute('src', url);
    document.getElementsByTagName("head")[0].appendChild(s);
};

Kwf.GoogleMap._loaded = function()
{
    Kwf.GoogleMap.isCallbackCalled = true;
    Kwf.GoogleMap.callbacks.forEach(function(i) {
        i.callback.call(i.scope || window);
    });
};



Kwf.GoogleMap.maps = [];

/**
 * The Kwf GoogleMaps object
 *
 * @param config The configuration object that may / must contain the following values
 *     mapContainer (mandatory): The wrapper element of the map (id, dom-element or ext-element).
 *             Must contain a div with class 'container', where the google map itself will be put into-
 *     longitude (optional if coordinates is set): The longitude of the initial center point.
 *     latitude (optional if coordinates is set): The latitude of the initial center point.
 *     coordinates (optional if longitude and latitude is set): The longitude and
 *            latitude of the initial center point. E.g.: '47.802594,13.0433173'
 *     markers (optional): An object of one marker, an array of markers or a an url (string)
 *            to load markers on demand.
 *            Longitude and latitude, or coordinates are mandatory.
 *            See the following example:
 *            [
 *                { longitude: 47.802594, latitude: 13.0433173, infoHtml: 'Text 1' },
 *                { coordinates: '46.1234,12.321', infoHtml: 'Text 2' }
 *            ]
 *     markerSrc (optional): An url to an image that should be used as marker.
 *     lightMarkers (optional): An object of one marker or an array of markers.
 *            Markers at these positions will have another marker color.
 *     lightMarkerSrc (optional): An url to an image that should be used as light marker.
 *     zoom (optional): The initial zoom value. Either an integer, an array of
 *            longitude / latitude values that should be visible in the map or null.
 *            If null all existing markers are centered.
 *            Default value for zoom is 13.
 *            Example for array usage:
 *            [ top, right, bottom, left ]
 *            - or in coordinates -
 *            [ highest longitude, highest latitude, lowest longitude, lowest latitude ]
 *     width (optional): The width of the map container in pixel. Defaults to 350.
 *     height (optional): The height of the map container in pixel. Defaults to 300.
 *     satelite (optional): 0 or 1, whether it should be possible to switch to satelite
 *            view or not. Defaults to 1.
 *     scale (optional): 0 or 1, whether to show the scale bar or not. Defaults to 1.
 *     zoomProperties (optional): 0 to show large zoom an move controls,
 *            1 to show small zoom an move controls. Defaults to 0.
 *     overview (optional): 0 or 1, whether to show a small overview map at the bottom.
 */
Kwf.GoogleMap.Map = function(config) {
    if (!config.mapContainer) throw new Error('config value mapContainer not set');

    this.addEvents({
        'show': true,
        'useFrom': true,
        'reload': true
    });

    this.mapContainer = Ext2.get(config.mapContainer);
    this._baseParams = Ext2.applyIf({}, config.baseParams);
    this.markers = [];
    this.config = config;
    if (typeof this.config.width == 'undefined') this.config.width = 350;
    if (typeof this.config.height == 'undefined') this.config.height = 300;
    if (typeof this.config.satelite == 'undefined') this.config.satelite = 1;
    if (typeof this.config.scale == 'undefined') this.config.scale = 1;
    if (typeof this.config.zoomProperties == 'undefined') this.config.zoomProperties = 0;
    if (typeof this.config.overview == 'undefined') this.config.overview = 1;
    if (typeof this.config.zoom == 'undefined') this.config.zoom = 13;
    if (typeof this.config.markerSrc == 'undefined') this.config.markerSrc = null;
    if (typeof this.config.lightMarkerSrc == 'undefined') this.config.lightMarkerSrc = '/assets/kwf/images/googlemap/markerBlue.png';
    if (typeof this.config.scrollwheel == 'undefined') this.config.scrollwheel = 1;
    if (typeof this.config.zoomControlStyle == 'undefined') this.config.zoomControlStyle = 'LARGE';
    if (typeof this.config.zoomControlPosition == 'undefined') this.config.zoomControlPosition = 'LEFT_TOP';
    if (typeof this.config.panControl == 'undefined') this.config.panControl = false;
    if (typeof this.config.streetViewControl == 'undefined') this.config.streetViewControl = false;

    if (!this.config.markers) this.config.markers = [ ];
    if (typeof this.config.markers[0] == 'undefined' &&
        (this.config.markers.longitude || this.config.markers.coordinates)
    ) {
        this.config.markers = [ this.config.markers ];
    }

    if (typeof this.config.markers == 'object') {
        for (var i = 0; i < this.config.markers.length; i++) {
            if (this.config.markers[i] && typeof this.config.markers[i].coordinates != 'undefined') {
                if (typeof this.config.markers[i].latitude == 'undefined') {
                    var splits = this.config.markers[i].coordinates.split(',');
                    this.config.markers[i].latitude = splits[0];
                }
                if (typeof this.config.markers[i].longitude == 'undefined') {
                    var splits = this.config.markers[i].coordinates.split(',');
                    this.config.markers[i].longitude = splits[1];
                }
            }
        }
    }

    if (typeof this.config.coordinates != 'undefined') {
        if (typeof this.config.latitude == 'undefined') {
            var splits = this.config.coordinates.split(',');
            this.config.latitude = splits[0];
        }
        if (typeof this.config.longitude == 'undefined') {
            var splits = this.config.coordinates.split(',');
            this.config.longitude = splits[1];
        }
    }

    if (!this.config.longitude) throw new Error('Either longitude or coordinates must be set in config');
    if (!this.config.latitude) throw new Error('Either latitude or coordinates must be set in config');

    var fromEl = this.mapContainer.down("form.fromAddress");
    if (fromEl) {
        var input = this.mapContainer.down("form.fromAddress input");
        fromEl.on('submit', function(e) {
            this.setMapDir(input.getValue());
            e.stopEvent();
        }, this);
    }

    if (typeof this.config.markers == 'string') {
        if (typeof Kwf.Connection == 'undefined') {
            alert('Dependency ExtConnection (that includes Kwf.Connection object) must be set when you wish to reload markers in an google map');
        }
        this.ajax = new Kwf.Connection({
            autoAbort : true
        });
    }

    var container = this.mapContainer.down(".container");
    container.setWidth(this.config.width);
    container.setHeight(this.config.height);
};

Ext2.extend(Kwf.GoogleMap.Map, Ext2.util.Observable, {

    markers: null,
    show : function()
    {
        this.directionsService = new google.maps.DirectionsService();
        this.directionsDisplay = new google.maps.DirectionsRenderer();
        //CONTROLLS
        if (parseInt(this.config.satelite)) {
            this.config.mapType = true;
        } else {
            this.config.mapType = false;
        }
        var mapOptions = {
            center: new google.maps.LatLng(parseFloat(this.config.latitude), parseFloat(this.config.longitude)),
            zoom: (typeof this.config.zoom == 'number')? parseInt(this.config.zoom) : 13, //initial zoom has to be set
            panControl: this.config.panControl,
            zoomControl: this.config.zoomProperties,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[this.config.zoomControlStyle],
                position: google.maps.ControlPosition[this.config.zoomControlPosition]
            },
            scaleControl: this.config.scale,
            mapTypeControl: this.config.mapType,
            overviewMapControl: this.config.overview,
            streetViewControl: this.config.streetViewControl,
            scrollwheel: this.config.scrollwheel
        };

        this.gmap = new google.maps.Map(this.mapContainer.down(".container").dom,
            mapOptions);
        if (this.mapContainer.down(".mapDir")) {
            this.directionsDisplay.setMap(this.gmap);
            this.directionsDisplay.setPanel(this.mapContainer.down(".mapDir").dom);
        }

        if (this.config.mapType == 'satellite') {
            this.gmap.setMapTypeId(google.maps.MapTypeId.SATELLITE);
        } else if (this.config.mapType == 'hybrid') {
            this.gmap.setMapTypeId(google.maps.MapTypeId.HYBRID);
        }

        // zoom = null: load all markers
        // zoom = 1-13: load displayed markers
        // zoom = [a, b, x, y]: load displayed markers, but not on first idle because we need to initialise with zoom = 1-13
        if (typeof this.config.zoom == 'object'
            && this.config.zoom != null
            && this.config.zoom[0] && this.config.zoom[1]
            && this.config.zoom[2] && this.config.zoom[3]
        ) {
            var bounds = new google.maps.LatLngBounds();
            bounds.extend(new google.maps.LatLng(this.config.zoom[0], this.config.zoom[1]));
            bounds.extend(new google.maps.LatLng(this.config.zoom[2], this.config.zoom[3]));
            this.gmap.fitBounds(bounds);
        }

        if (typeof this.config.markers == 'string') {
            if (this.config.zoom == null) {
                /* 1. Wait till map is ready: first idle.
                 * 2. Then load all markers.
                 * */
                google.maps.event.addListenerOnce(this.gmap, "idle",
                    this._loadAllMarkers.createDelegate(this, []));
            } else {
                google.maps.event.addListener(this.gmap, "idle",
                    this._reloadMarkersOnMapChange.createDelegate(this, [ ]));
            }
        } else {
            this.config.markers.each(function(marker) {
                this.addMarker(marker);
            }, this);
        }

        // Opens the first InfoWindow. Must be deferred, because there were
        // problems opening InfoWindows in multiple maps on one site
        var showNextWindow = function() {
            var map = Kwf.GoogleMap.maps.shift();
            if (!map) return;
            map.markers.each(function(m) {
                if (m.kwfConfig.autoOpenInfoWindow) this.showWindow(m);
            }, map);
        };
        if (Kwf.GoogleMap.maps.length == 0) {
            showNextWindow.defer(1, this);
        }
        Kwf.GoogleMap.maps.push(this);
        this.fireEvent('show', this);
    },

    _loadAllMarkers: function()
    {
        /* 1. Add listener for markers finished loading
         * 2. Load markers
         * 3. Center all markers
         * 4. Add listener for further map-movements
         */
        this.on('reload', function () {
            // Listener for centering view to all shown markers on first load
            if (this.markers.length == 0) return;
            // Calculate center of all markers via gmap
            var latlngbounds = new google.maps.LatLngBounds();
            this.markers.each(function(n){
                if (n.kwfConfig.isLightMarker) latlngbounds.extend(n.getPosition());
            });
            this.gmap.setCenter(latlngbounds.getCenter());
            this.gmap.fitBounds(latlngbounds);

            google.maps.event.addListener(this.gmap, "idle",
                this._reloadMarkersOnMapChange.createDelegate(this, [ ]));
        }, this, { single: true });

        this._reloadMarkers(Ext2.applyIf({}, this._baseParams));
    },

    centerMarkersIntoView: function()
    {
        google.maps.event.clearListeners(this.gmap, "idle");
        this._loadAllMarkers();
    },

    _reloadMarkersOnMapChange: function()
    {
        var params = Ext2.applyIf({}, this._baseParams);
        var bounds = this.gmap.getBounds();
        params.lowestLng = bounds.getSouthWest().lng();
        params.lowestLat = bounds.getSouthWest().lat();
        params.highestLng = bounds.getNorthEast().lng();
        params.highestLat = bounds.getNorthEast().lat();
        this._reloadMarkers(params);
    },

    _reloadMarkers: function(params)
    {
        if (!this.gmapLoader) {
            this.gmapLoader = Ext2.getBody().createChild({ tag: 'div', id: 'gmapLoader' });
            this.gmapLoader.dom.innerHTML = trlKwf('Loading...');
            this.gmapLoader.alignTo(this.mapContainer, 'tr-tr', [ -10, 50 ]);
        }
        this.gmapLoader.show();

        this.lastReloadMarkersRequestId = this.ajax.request({
            url: this.config.markers,
            params: params,
            success: function(response, options, result) {
                var reuseMarkers = [];
                var newMarkers = [];
                result.markers.each(function(m) {
                    var doAdd = true;
                    for (var i = 0; i < this.markers.length; i++) {
                        if (this.markers[i].kwfConfig.latitude == m.latitude
                            && this.markers[i].kwfConfig.longitude == m.longitude
                            && this.markers[i].kwfConfig.isLightMarker == m.isLightMarker
                        ) {
                            reuseMarkers.push(this.markers[i]);
                            doAdd = false;
                            break;
                        }
                    }
                    if (doAdd) newMarkers.push(m);
                }, this);

                for (var i = 0; i < this.markers.length; i++) {
                    if (reuseMarkers.indexOf(this.markers[i]) == -1) {
                        this.markers[i].setMap(null);
                    }
                }
                this.markers = reuseMarkers;
                for (var i = 0; i < newMarkers.length; i++) {
                    this.addMarker(newMarkers[i]);
                }
                Kwf.callOnContentReady(this.mapContainer, {newRender: true});
                this.gmapLoader.hide();
                this.fireEvent('reload', this);
            },
            scope: this
        });
    },

    setBaseParams: function(params)
    {
        this._baseParams = params;
    },

    getBaseParams: function()
    {
        return this._baseParams;
    },

    addMarker : function(markerConfig)
    {
        var marker = this.createMarker(markerConfig);
        marker.kwfConfig = markerConfig;
        marker.setMap(this.gmap);
        this.markers.push(marker);
        if (markerConfig.infoHtml) {
            google.maps.event.addListener(marker, 'click', this.toggleWindow.createDelegate(
                this, [ marker ]
            ));
        }
    },

    createMarker : function(markerConfig)
    {
        var gmarkCfg = { draggable: false };
        if (markerConfig.draggable) gmarkCfg.draggable = true;
        var image = this.getMarkerIcon(markerConfig);
        var myLatLng = new google.maps.LatLng(parseFloat(markerConfig.latitude), parseFloat(markerConfig.longitude));
        return new google.maps.Marker({
            position: myLatLng,
            icon: image
        });
    },

    getMarkerIcon : function(markerConfig)
    {
        var image = '';
        if (markerConfig.isLightMarker && this.config.lightMarkerSrc) {
            image = this.config.lightMarkerSrc;
        } else if (this.config.markerSrc) {
            image = this.config.markerSrc;
        }
        return image;
    },

    /** For images in marker popup **/
    markerWindowReady: function() {
        Kwf.callOnContentReady(this.mapContainer, {newRender: true});
    },

    /**
     * @param marker: The marker with 'kwfConfig' property inside
     */
    showWindow : function(marker) {
        marker.infoWindow = new google.maps.InfoWindow();
        if (marker.kwfConfig.infoHtml && marker.kwfConfig.infoHtml != ""
            && "<br />" != marker.kwfConfig.infoHtml.toLowerCase()
            ) {
            marker.infoWindow.setContent(marker.kwfConfig.infoHtml);
            marker.infoWindow.open(marker.map, marker);
        }
        google.maps.event.addListener(marker.infoWindow, 'domready', this.markerWindowReady.createDelegate(
            this, [ marker ]
        ));
    },
    closeWindow: function(marker) {
        marker.infoWindow.close();
    },
    toggleWindow: function(marker) {
        if (marker.infoWindow && marker.infoWindow.getMap() !== null && typeof marker.infoWindow.getMap() !== "undefined") {
            this.closeWindow(marker);
        } else {
            this.showWindow(marker);
        }
    },

    setMapDir : function (fromAddress) {
        var end = new google.maps.LatLng(parseFloat(this.config.latitude), parseFloat(this.config.longitude));
        var request = {
            origin:fromAddress,
            destination:end,
            travelMode: google.maps.TravelMode.DRIVING
        };
        this.directionsService.route(request, this._directionsCallback.createDelegate(
            this
        ));
    },
    _directionsCallback: function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            this.directionsDisplay.setDirections(response);
            (function() {
                Kwf.callOnContentReady(this.directionsDisplay.getPanel(), { newRender: true});
            }).defer(1, this);
        } else {
            alert(trlKwf('Entered place could not been found!'));
        }
    }
});
