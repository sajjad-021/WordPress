(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @externs_url http://closure-compiler.googlecode.com/svn/trunk/contrib/externs/maps/google_maps_api_v3_3.js
// ==/ClosureCompiler==

/**
 * @name MarkerClusterer for Google Maps v3
 * @version version 1.0.1
 * @author Luke Mahe
 * @fileoverview
 * The library creates and manages per-zoom-level clusters for large amounts of
 * markers.
 * <br/>
 * This is a v3 implementation of the
 * <a href="http://gmaps-utility-library-dev.googlecode.com/svn/tags/markerclusterer/"
 * >v2 MarkerClusterer</a>.
 */

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * A Marker Clusterer that clusters markers.
 *
 * @param {google.maps.Map} map The Google map to attach to.
 * @param {Array.<google.maps.Marker>=} opt_markers Optional markers to add to
 *   the cluster.
 * @param {Object=} opt_options support the following options:
 *     'gridSize': (number) The grid size of a cluster in pixels.
 *     'maxZoom': (number) The maximum zoom level that a marker can be part of a
 *                cluster.
 *     'zoomOnClick': (boolean) Whether the default behaviour of clicking on a
 *                    cluster is to zoom into it.
 *     'imagePath': (string) The base URL where the images representing
 *                  clusters will be found. The full URL will be:
 *                  {imagePath}[1-5].{imageExtension}
 *                  Default: '../images/m'.
 *     'imageExtension': (string) The suffix for images URL representing
 *                       clusters will be found. See _imagePath_ for details.
 *                       Default: 'png'.
 *     'averageCenter': (boolean) Whether the center of each cluster should be
 *                      the average of all markers in the cluster.
 *     'minimumClusterSize': (number) The minimum number of markers to be in a
 *                           cluster before the markers are hidden and a count
 *                           is shown.
 *     'styles': (object) An object that has style properties:
 *       'url': (string) The image url.
 *       'height': (number) The image height.
 *       'width': (number) The image width.
 *       'anchor': (Array) The anchor position of the label text.
 *       'textColor': (string) The text color.
 *       'textSize': (number) The text size.
 *       'backgroundPosition': (string) The position of the backgound x, y.
 * @constructor
 * @extends google.maps.OverlayView
 */
function MarkerClusterer(map, opt_markers, opt_options) {
    // MarkerClusterer implements google.maps.OverlayView interface. We use the
    // extend function to extend MarkerClusterer with google.maps.OverlayView
    // because it might not always be available when the code is defined so we
    // look for it at the last possible moment. If it doesn't exist now then
    // there is no point going ahead :)
    this.extend(MarkerClusterer, google.maps.OverlayView);
    this.map_ = map;

    /**
     * @type {Array.<google.maps.Marker>}
     * @private
     */
    this.markers_ = [];

    /**
     *  @type {Array.<Cluster>}
     */
    this.clusters_ = [];

    this.sizes = [53, 56, 66, 78, 90];

    /**
     * @private
     */
    this.styles_ = [];

    /**
     * @type {boolean}
     * @private
     */
    this.ready_ = false;

    var options = opt_options || {};

    /**
     * @type {number}
     * @private
     */
    this.gridSize_ = options['gridSize'] || 60;

    /**
     * @private
     */
    this.minClusterSize_ = options['minimumClusterSize'] || 2;

    /**
     * @type {?number}
     * @private
     */
    this.maxZoom_ = options['maxZoom'] || null;

    this.styles_ = options['styles'] || [];

    /**
     * @type {string}
     * @private
     */
    this.imagePath_ = options['imagePath'] || this.MARKER_CLUSTER_IMAGE_PATH_;

    /**
     * @type {string}
     * @private
     */
    this.imageExtension_ = options['imageExtension'] || this.MARKER_CLUSTER_IMAGE_EXTENSION_;

    /**
     * @type {boolean}
     * @private
     */
    this.zoomOnClick_ = true;

    if (options['zoomOnClick'] != undefined) {
        this.zoomOnClick_ = options['zoomOnClick'];
    }

    /**
     * @type {boolean}
     * @private
     */
    this.averageCenter_ = false;

    if (options['averageCenter'] != undefined) {
        this.averageCenter_ = options['averageCenter'];
    }

    this.setupStyles_();

    this.setMap(map);

    /**
     * @type {number}
     * @private
     */
    this.prevZoom_ = this.map_.getZoom();

    // Add the map event listeners
    var that = this;
    google.maps.event.addListener(this.map_, 'zoom_changed', function () {
        // Determines map type and prevent illegal zoom levels
        var zoom = that.map_.getZoom();
        var minZoom = that.map_.minZoom || 0;
        var maxZoom = Math.min(that.map_.maxZoom || 100, that.map_.mapTypes[that.map_.getMapTypeId()].maxZoom);
        zoom = Math.min(Math.max(zoom, minZoom), maxZoom);

        if (that.prevZoom_ != zoom) {
            that.prevZoom_ = zoom;
            that.resetViewport();
        }
    });

    google.maps.event.addListener(this.map_, 'idle', function () {
        that.redraw();
    });

    // Finally, add the markers
    if (opt_markers && (opt_markers.length || Object.keys(opt_markers).length)) {
        this.addMarkers(opt_markers, false);
    }
}

/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_PATH_ = '../images/m';

/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_EXTENSION_ = 'png';

/**
 * Extends a objects prototype by anothers.
 *
 * @param {Object} obj1 The object to be extended.
 * @param {Object} obj2 The object to extend with.
 * @return {Object} The new extended object.
 * @ignore
 */
MarkerClusterer.prototype.extend = function (obj1, obj2) {
    return function (object) {
        for (var property in object.prototype) {
            this.prototype[property] = object.prototype[property];
        }
        return this;
    }.apply(obj1, [obj2]);
};

/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.onAdd = function () {
    this.setReady_(true);
};

/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.draw = function () {};

/**
 * Sets up the styles object.
 *
 * @private
 */
MarkerClusterer.prototype.setupStyles_ = function () {
    if (this.styles_.length) {
        return;
    }

    for (var i = 0, size; size = this.sizes[i]; i++) {
        this.styles_.push({
            url: this.imagePath_ + (i + 1) + '.' + this.imageExtension_,
            height: size,
            width: size
        });
    }
};

/**
 *  Fit the map to the bounds of the markers in the clusterer.
 */
MarkerClusterer.prototype.fitMapToMarkers = function () {
    var markers = this.getMarkers();
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, marker; marker = markers[i]; i++) {
        bounds.extend(marker.getPosition());
    }

    this.map_.fitBounds(bounds);
};

/**
 *  Sets the styles.
 *
 *  @param {Object} styles The style to set.
 */
MarkerClusterer.prototype.setStyles = function (styles) {
    this.styles_ = styles;
};

/**
 *  Gets the styles.
 *
 *  @return {Object} The styles object.
 */
MarkerClusterer.prototype.getStyles = function () {
    return this.styles_;
};

/**
 * Whether zoom on click is set.
 *
 * @return {boolean} True if zoomOnClick_ is set.
 */
MarkerClusterer.prototype.isZoomOnClick = function () {
    return this.zoomOnClick_;
};

/**
 * Whether average center is set.
 *
 * @return {boolean} True if averageCenter_ is set.
 */
MarkerClusterer.prototype.isAverageCenter = function () {
    return this.averageCenter_;
};

/**
 *  Returns the array of markers in the clusterer.
 *
 *  @return {Array.<google.maps.Marker>} The markers.
 */
MarkerClusterer.prototype.getMarkers = function () {
    return this.markers_;
};

/**
 *  Returns the number of markers in the clusterer
 *
 *  @return {Number} The number of markers.
 */
MarkerClusterer.prototype.getTotalMarkers = function () {
    return this.markers_.length;
};

/**
 *  Sets the max zoom for the clusterer.
 *
 *  @param {number} maxZoom The max zoom level.
 */
MarkerClusterer.prototype.setMaxZoom = function (maxZoom) {
    this.maxZoom_ = maxZoom;
};

/**
 *  Gets the max zoom for the clusterer.
 *
 *  @return {number} The max zoom level.
 */
MarkerClusterer.prototype.getMaxZoom = function () {
    return this.maxZoom_;
};

/**
 *  The function for calculating the cluster icon image.
 *
 *  @param {Array.<google.maps.Marker>} markers The markers in the clusterer.
 *  @param {number} numStyles The number of styles available.
 *  @return {Object} A object properties: 'text' (string) and 'index' (number).
 *  @private
 */
MarkerClusterer.prototype.calculator_ = function (markers, numStyles) {
    var index = 0;
    var count = markers.length;
    var dv = count;
    while (dv !== 0) {
        dv = parseInt(dv / 10, 10);
        index++;
    }

    index = Math.min(index, numStyles);
    return {
        text: count,
        index: index
    };
};

/**
 * Set the calculator function.
 *
 * @param {function(Array, number)} calculator The function to set as the
 *     calculator. The function should return a object properties:
 *     'text' (string) and 'index' (number).
 *
 */
MarkerClusterer.prototype.setCalculator = function (calculator) {
    this.calculator_ = calculator;
};

/**
 * Get the calculator function.
 *
 * @return {function(Array, number)} the calculator function.
 */
MarkerClusterer.prototype.getCalculator = function () {
    return this.calculator_;
};

/**
 * Add an array of markers to the clusterer.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarkers = function (markers, opt_nodraw) {
    if (markers.length) {
        for (var i = 0, marker; marker = markers[i]; i++) {
            this.pushMarkerTo_(marker);
        }
    } else if (Object.keys(markers).length) {
        for (var marker in markers) {
            this.pushMarkerTo_(markers[marker]);
        }
    }
    if (!opt_nodraw) {
        this.redraw();
    }
};

/**
 * Pushes a marker to the clusterer.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.pushMarkerTo_ = function (marker) {
    marker.isAdded = false;
    if (marker['draggable']) {
        // If the marker is draggable add a listener so we update the clusters on
        // the drag end.
        var that = this;
        google.maps.event.addListener(marker, 'dragend', function () {
            marker.isAdded = false;
            that.repaint();
        });
    }
    this.markers_.push(marker);
};

/**
 * Adds a marker to the clusterer and redraws if needed.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarker = function (marker, opt_nodraw) {
    this.pushMarkerTo_(marker);
    if (!opt_nodraw) {
        this.redraw();
    }
};

/**
 * Removes a marker and returns true if removed, false if not
 *
 * @param {google.maps.Marker} marker The marker to remove
 * @return {boolean} Whether the marker was removed or not
 * @private
 */
MarkerClusterer.prototype.removeMarker_ = function (marker) {
    var index = -1;
    if (this.markers_.indexOf) {
        index = this.markers_.indexOf(marker);
    } else {
        for (var i = 0, m; m = this.markers_[i]; i++) {
            if (m == marker) {
                index = i;
                break;
            }
        }
    }

    if (index == -1) {
        // Marker is not in our list of markers.
        return false;
    }

    marker.setMap(null);

    this.markers_.splice(index, 1);

    return true;
};

/**
 * Remove a marker from the cluster.
 *
 * @param {google.maps.Marker} marker The marker to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 * @return {boolean} True if the marker was removed.
 */
MarkerClusterer.prototype.removeMarker = function (marker, opt_nodraw) {
    var removed = this.removeMarker_(marker);

    if (!opt_nodraw && removed) {
        this.resetViewport();
        this.redraw();
        return true;
    } else {
        return false;
    }
};

/**
 * Removes an array of markers from the cluster.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 */
MarkerClusterer.prototype.removeMarkers = function (markers, opt_nodraw) {
    // create a local copy of markers if required
    // (removeMarker_ modifies the getMarkers() array in place)
    var markersCopy = markers === this.getMarkers() ? markers.slice() : markers;
    var removed = false;

    for (var i = 0, marker; marker = markersCopy[i]; i++) {
        var r = this.removeMarker_(marker);
        removed = removed || r;
    }

    if (!opt_nodraw && removed) {
        this.resetViewport();
        this.redraw();
        return true;
    }
};

/**
 * Sets the clusterer's ready state.
 *
 * @param {boolean} ready The state.
 * @private
 */
MarkerClusterer.prototype.setReady_ = function (ready) {
    if (!this.ready_) {
        this.ready_ = ready;
        this.createClusters_();
    }
};

/**
 * Returns the number of clusters in the clusterer.
 *
 * @return {number} The number of clusters.
 */
MarkerClusterer.prototype.getTotalClusters = function () {
    return this.clusters_.length;
};

/**
 * Returns the google map that the clusterer is associated with.
 *
 * @return {google.maps.Map} The map.
 */
MarkerClusterer.prototype.getMap = function () {
    return this.map_;
};

/**
 * Sets the google map that the clusterer is associated with.
 *
 * @param {google.maps.Map} map The map.
 */
MarkerClusterer.prototype.setMap = function (map) {
    this.map_ = map;
};

/**
 * Returns the size of the grid.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getGridSize = function () {
    return this.gridSize_;
};

/**
 * Sets the size of the grid.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setGridSize = function (size) {
    this.gridSize_ = size;
};

/**
 * Returns the min cluster size.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getMinClusterSize = function () {
    return this.minClusterSize_;
};

/**
 * Sets the min cluster size.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setMinClusterSize = function (size) {
    this.minClusterSize_ = size;
};

/**
 * Extends a bounds object by the grid size.
 *
 * @param {google.maps.LatLngBounds} bounds The bounds to extend.
 * @return {google.maps.LatLngBounds} The extended bounds.
 */
MarkerClusterer.prototype.getExtendedBounds = function (bounds) {
    var projection = this.getProjection();

    // Turn the bounds into latlng.
    var tr = new google.maps.LatLng(bounds.getNorthEast().lat(), bounds.getNorthEast().lng());
    var bl = new google.maps.LatLng(bounds.getSouthWest().lat(), bounds.getSouthWest().lng());

    // Convert the points to pixels and the extend out by the grid size.
    var trPix = projection.fromLatLngToDivPixel(tr);
    trPix.x += this.gridSize_;
    trPix.y -= this.gridSize_;

    var blPix = projection.fromLatLngToDivPixel(bl);
    blPix.x -= this.gridSize_;
    blPix.y += this.gridSize_;

    // Convert the pixel points back to LatLng
    var ne = projection.fromDivPixelToLatLng(trPix);
    var sw = projection.fromDivPixelToLatLng(blPix);

    // Extend the bounds to contain the new bounds.
    bounds.extend(ne);
    bounds.extend(sw);

    return bounds;
};

/**
 * Determins if a marker is contained in a bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @param {google.maps.LatLngBounds} bounds The bounds to check against.
 * @return {boolean} True if the marker is in the bounds.
 * @private
 */
MarkerClusterer.prototype.isMarkerInBounds_ = function (marker, bounds) {
    return bounds.contains(marker.getPosition());
};

/**
 * Clears all clusters and markers from the clusterer.
 */
MarkerClusterer.prototype.clearMarkers = function () {
    this.resetViewport(true);

    // Set the markers a empty array.
    this.markers_ = [];
};

/**
 * Clears all existing clusters and recreates them.
 * @param {boolean} opt_hide To also hide the marker.
 */
MarkerClusterer.prototype.resetViewport = function (opt_hide) {
    // Remove all the clusters
    for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
        cluster.remove();
    }

    // Reset the markers to not be added and to be invisible.
    for (var i = 0, marker; marker = this.markers_[i]; i++) {
        marker.isAdded = false;
        if (opt_hide) {
            marker.setMap(null);
        }
    }

    this.clusters_ = [];
};

/**
 *
 */
MarkerClusterer.prototype.repaint = function () {
    var oldClusters = this.clusters_.slice();
    this.clusters_.length = 0;
    this.resetViewport();
    this.redraw();

    // Remove the old clusters.
    // Do it in a timeout so the other clusters have been drawn first.
    window.setTimeout(function () {
        for (var i = 0, cluster; cluster = oldClusters[i]; i++) {
            cluster.remove();
        }
    }, 0);
};

/**
 * Redraws the clusters.
 */
MarkerClusterer.prototype.redraw = function () {
    this.createClusters_();
};

/**
 * Calculates the distance between two latlng locations in km.
 * @see http://www.movable-type.co.uk/scripts/latlong.html
 *
 * @param {google.maps.LatLng} p1 The first lat lng point.
 * @param {google.maps.LatLng} p2 The second lat lng point.
 * @return {number} The distance between the two points in km.
 * @private
 */
MarkerClusterer.prototype.distanceBetweenPoints_ = function (p1, p2) {
    if (!p1 || !p2) {
        return 0;
    }

    var R = 6371; // Radius of the Earth in km
    var dLat = (p2.lat() - p1.lat()) * Math.PI / 180;
    var dLon = (p2.lng() - p1.lng()) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(p1.lat() * Math.PI / 180) * Math.cos(p2.lat() * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;
    return d;
};

/**
 * Add a marker to a cluster, or creates a new cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.addToClosestCluster_ = function (marker) {
    var distance = 40000; // Some large number
    var clusterToAddTo = null;
    var pos = marker.getPosition();
    for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
        var center = cluster.getCenter();
        if (center) {
            var d = this.distanceBetweenPoints_(center, marker.getPosition());
            if (d < distance) {
                distance = d;
                clusterToAddTo = cluster;
            }
        }
    }

    if (clusterToAddTo && clusterToAddTo.isMarkerInClusterBounds(marker)) {
        clusterToAddTo.addMarker(marker);
    } else {
        var cluster = new Cluster(this);
        cluster.addMarker(marker);
        this.clusters_.push(cluster);
    }
};

/**
 * Creates the clusters.
 *
 * @private
 */
MarkerClusterer.prototype.createClusters_ = function () {
    if (!this.ready_) {
        return;
    }

    // Get our current map view bounds.
    // Create a new bounds object so we don't affect the map.
    var mapBounds = new google.maps.LatLngBounds(this.map_.getBounds().getSouthWest(), this.map_.getBounds().getNorthEast());
    var bounds = this.getExtendedBounds(mapBounds);

    for (var i = 0, marker; marker = this.markers_[i]; i++) {
        if (!marker.isAdded && this.isMarkerInBounds_(marker, bounds)) {
            this.addToClosestCluster_(marker);
        }
    }
};

/**
 * A cluster that contains markers.
 *
 * @param {MarkerClusterer} markerClusterer The markerclusterer that this
 *     cluster is associated with.
 * @constructor
 * @ignore
 */
function Cluster(markerClusterer) {
    this.markerClusterer_ = markerClusterer;
    this.map_ = markerClusterer.getMap();
    this.gridSize_ = markerClusterer.getGridSize();
    this.minClusterSize_ = markerClusterer.getMinClusterSize();
    this.averageCenter_ = markerClusterer.isAverageCenter();
    this.center_ = null;
    this.markers_ = [];
    this.bounds_ = null;
    this.clusterIcon_ = new ClusterIcon(this, markerClusterer.getStyles(), markerClusterer.getGridSize());
}

/**
 * Determins if a marker is already added to the cluster.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker is already added.
 */
Cluster.prototype.isMarkerAlreadyAdded = function (marker) {
    if (this.markers_.indexOf) {
        return this.markers_.indexOf(marker) != -1;
    } else {
        for (var i = 0, m; m = this.markers_[i]; i++) {
            if (m == marker) {
                return true;
            }
        }
    }
    return false;
};

/**
 * Add a marker the cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @return {boolean} True if the marker was added.
 */
Cluster.prototype.addMarker = function (marker) {
    if (this.isMarkerAlreadyAdded(marker)) {
        return false;
    }

    if (!this.center_) {
        this.center_ = marker.getPosition();
        this.calculateBounds_();
    } else {
        if (this.averageCenter_) {
            var l = this.markers_.length + 1;
            var lat = (this.center_.lat() * (l - 1) + marker.getPosition().lat()) / l;
            var lng = (this.center_.lng() * (l - 1) + marker.getPosition().lng()) / l;
            this.center_ = new google.maps.LatLng(lat, lng);
            this.calculateBounds_();
        }
    }

    marker.isAdded = true;
    this.markers_.push(marker);

    var len = this.markers_.length;
    if (len < this.minClusterSize_ && marker.getMap() != this.map_) {
        // Min cluster size not reached so show the marker.
        marker.setMap(this.map_);
    }

    if (len == this.minClusterSize_) {
        // Hide the markers that were showing.
        for (var i = 0; i < len; i++) {
            this.markers_[i].setMap(null);
        }
    }

    if (len >= this.minClusterSize_) {
        marker.setMap(null);
    }

    this.updateIcon();
    return true;
};

/**
 * Returns the marker clusterer that the cluster is associated with.
 *
 * @return {MarkerClusterer} The associated marker clusterer.
 */
Cluster.prototype.getMarkerClusterer = function () {
    return this.markerClusterer_;
};

/**
 * Returns the bounds of the cluster.
 *
 * @return {google.maps.LatLngBounds} the cluster bounds.
 */
Cluster.prototype.getBounds = function () {
    var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
    var markers = this.getMarkers();
    for (var i = 0, marker; marker = markers[i]; i++) {
        bounds.extend(marker.getPosition());
    }
    return bounds;
};

/**
 * Removes the cluster
 */
Cluster.prototype.remove = function () {
    this.clusterIcon_.remove();
    this.markers_.length = 0;
    delete this.markers_;
};

/**
 * Returns the number of markers in the cluster.
 *
 * @return {number} The number of markers in the cluster.
 */
Cluster.prototype.getSize = function () {
    return this.markers_.length;
};

/**
 * Returns a list of the markers in the cluster.
 *
 * @return {Array.<google.maps.Marker>} The markers in the cluster.
 */
Cluster.prototype.getMarkers = function () {
    return this.markers_;
};

/**
 * Returns the center of the cluster.
 *
 * @return {google.maps.LatLng} The cluster center.
 */
Cluster.prototype.getCenter = function () {
    return this.center_;
};

/**
 * Calculated the extended bounds of the cluster with the grid.
 *
 * @private
 */
Cluster.prototype.calculateBounds_ = function () {
    var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
    this.bounds_ = this.markerClusterer_.getExtendedBounds(bounds);
};

/**
 * Determines if a marker lies in the clusters bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker lies in the bounds.
 */
Cluster.prototype.isMarkerInClusterBounds = function (marker) {
    return this.bounds_.contains(marker.getPosition());
};

/**
 * Returns the map that the cluster is associated with.
 *
 * @return {google.maps.Map} The map.
 */
Cluster.prototype.getMap = function () {
    return this.map_;
};

/**
 * Updates the cluster icon
 */
Cluster.prototype.updateIcon = function () {
    var zoom = this.map_.getZoom();
    var mz = this.markerClusterer_.getMaxZoom();

    if (mz && zoom > mz) {
        // The zoom is greater than our max zoom so show all the markers in cluster.
        for (var i = 0, marker; marker = this.markers_[i]; i++) {
            marker.setMap(this.map_);
        }
        return;
    }

    if (this.markers_.length < this.minClusterSize_) {
        // Min cluster size not yet reached.
        this.clusterIcon_.hide();
        return;
    }

    var numStyles = this.markerClusterer_.getStyles().length;
    var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
    this.clusterIcon_.setCenter(this.center_);
    this.clusterIcon_.setSums(sums);
    this.clusterIcon_.show();
};

/**
 * A cluster icon
 *
 * @param {Cluster} cluster The cluster to be associated with.
 * @param {Object} styles An object that has style properties:
 *     'url': (string) The image url.
 *     'height': (number) The image height.
 *     'width': (number) The image width.
 *     'anchor': (Array) The anchor position of the label text.
 *     'textColor': (string) The text color.
 *     'textSize': (number) The text size.
 *     'backgroundPosition: (string) The background postition x, y.
 * @param {number=} opt_padding Optional padding to apply to the cluster icon.
 * @constructor
 * @extends google.maps.OverlayView
 * @ignore
 */
function ClusterIcon(cluster, styles, opt_padding) {
    cluster.getMarkerClusterer().extend(ClusterIcon, google.maps.OverlayView);

    this.styles_ = styles;
    this.padding_ = opt_padding || 0;
    this.cluster_ = cluster;
    this.center_ = null;
    this.map_ = cluster.getMap();
    this.div_ = null;
    this.sums_ = null;
    this.visible_ = false;

    this.setMap(this.map_);
}

/**
 * Triggers the clusterclick event and zoom's if the option is set.
 */
ClusterIcon.prototype.triggerClusterClick = function () {
    var markerClusterer = this.cluster_.getMarkerClusterer();

    // Trigger the clusterclick event.
    google.maps.event.trigger(markerClusterer.map_, 'clusterclick', this.cluster_);

    if (markerClusterer.isZoomOnClick()) {
        // Zoom into the cluster.
        this.map_.fitBounds(this.cluster_.getBounds());
    }
};

/**
 * Adding the cluster icon to the dom.
 * @ignore
 */
ClusterIcon.prototype.onAdd = function () {
    this.div_ = document.createElement('DIV');
    if (this.visible_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.cssText = this.createCss(pos);
        this.div_.innerHTML = this.sums_.text;
    }

    var panes = this.getPanes();
    panes.overlayMouseTarget.appendChild(this.div_);

    var that = this;
    google.maps.event.addDomListener(this.div_, 'click', function () {
        that.triggerClusterClick();
    });
};

/**
 * Returns the position to place the div dending on the latlng.
 *
 * @param {google.maps.LatLng} latlng The position in latlng.
 * @return {google.maps.Point} The position in pixels.
 * @private
 */
ClusterIcon.prototype.getPosFromLatLng_ = function (latlng) {
    var pos = this.getProjection().fromLatLngToDivPixel(latlng);
    pos.x -= parseInt(this.width_ / 2, 10);
    pos.y -= parseInt(this.height_ / 2, 10);
    return pos;
};

/**
 * Draw the icon.
 * @ignore
 */
ClusterIcon.prototype.draw = function () {
    if (this.visible_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.top = pos.y + 'px';
        this.div_.style.left = pos.x + 'px';
    }
};

/**
 * Hide the icon.
 */
ClusterIcon.prototype.hide = function () {
    if (this.div_) {
        this.div_.style.display = 'none';
    }
    this.visible_ = false;
};

/**
 * Position and show the icon.
 */
ClusterIcon.prototype.show = function () {
    if (this.div_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.cssText = this.createCss(pos);
        this.div_.style.display = '';
    }
    this.visible_ = true;
};

/**
 * Remove the icon from the map
 */
ClusterIcon.prototype.remove = function () {
    this.setMap(null);
};

/**
 * Implementation of the onRemove interface.
 * @ignore
 */
ClusterIcon.prototype.onRemove = function () {
    if (this.div_ && this.div_.parentNode) {
        this.hide();
        this.div_.parentNode.removeChild(this.div_);
        this.div_ = null;
    }
};

/**
 * Set the sums of the icon.
 *
 * @param {Object} sums The sums containing:
 *   'text': (string) The text to display in the icon.
 *   'index': (number) The style index of the icon.
 */
ClusterIcon.prototype.setSums = function (sums) {
    this.sums_ = sums;
    this.text_ = sums.text;
    this.index_ = sums.index;
    if (this.div_) {
        this.div_.innerHTML = sums.text;
    }

    this.useStyle();
};

/**
 * Sets the icon to the the styles.
 */
ClusterIcon.prototype.useStyle = function () {
    var index = Math.max(0, this.sums_.index - 1);
    index = Math.min(this.styles_.length - 1, index);
    var style = this.styles_[index];
    this.url_ = style['url'];
    this.height_ = style['height'];
    this.width_ = style['width'];
    this.textColor_ = style['textColor'];
    this.anchor_ = style['anchor'];
    this.textSize_ = style['textSize'];
    this.backgroundPosition_ = style['backgroundPosition'];
};

/**
 * Sets the center of the icon.
 *
 * @param {google.maps.LatLng} center The latlng to set as the center.
 */
ClusterIcon.prototype.setCenter = function (center) {
    this.center_ = center;
};

/**
 * Create the css text based on the position of the icon.
 *
 * @param {google.maps.Point} pos The position.
 * @return {string} The css style text.
 */
ClusterIcon.prototype.createCss = function (pos) {
    var style = [];
    style.push('background-image:url(' + this.url_ + ');');
    var backgroundPosition = this.backgroundPosition_ ? this.backgroundPosition_ : '0 0';
    style.push('background-position:' + backgroundPosition + ';');

    if (_typeof(this.anchor_) === 'object') {
        if (typeof this.anchor_[0] === 'number' && this.anchor_[0] > 0 && this.anchor_[0] < this.height_) {
            style.push('height:' + (this.height_ - this.anchor_[0]) + 'px; padding-top:' + this.anchor_[0] + 'px;');
        } else {
            style.push('height:' + this.height_ + 'px; line-height:' + this.height_ + 'px;');
        }
        if (typeof this.anchor_[1] === 'number' && this.anchor_[1] > 0 && this.anchor_[1] < this.width_) {
            style.push('width:' + (this.width_ - this.anchor_[1]) + 'px; padding-left:' + this.anchor_[1] + 'px;');
        } else {
            style.push('width:' + this.width_ + 'px; text-align:center;');
        }
    } else {
        style.push('height:' + this.height_ + 'px; line-height:' + this.height_ + 'px; width:' + this.width_ + 'px; text-align:center;');
    }

    var txtColor = this.textColor_ ? this.textColor_ : 'black';
    var txtSize = this.textSize_ ? this.textSize_ : 11;

    style.push('cursor:pointer; top:' + pos.y + 'px; left:' + pos.x + 'px; color:' + txtColor + '; position:absolute; font-size:' + txtSize + 'px; font-family:Arial,sans-serif; font-weight:bold');
    return style.join('');
};

// Export Symbols for Closure
// If you are not going to compile with closure then you can remove the
// code below.
window['MarkerClusterer'] = MarkerClusterer;
MarkerClusterer.prototype['addMarker'] = MarkerClusterer.prototype.addMarker;
MarkerClusterer.prototype['addMarkers'] = MarkerClusterer.prototype.addMarkers;
MarkerClusterer.prototype['clearMarkers'] = MarkerClusterer.prototype.clearMarkers;
MarkerClusterer.prototype['fitMapToMarkers'] = MarkerClusterer.prototype.fitMapToMarkers;
MarkerClusterer.prototype['getCalculator'] = MarkerClusterer.prototype.getCalculator;
MarkerClusterer.prototype['getGridSize'] = MarkerClusterer.prototype.getGridSize;
MarkerClusterer.prototype['getExtendedBounds'] = MarkerClusterer.prototype.getExtendedBounds;
MarkerClusterer.prototype['getMap'] = MarkerClusterer.prototype.getMap;
MarkerClusterer.prototype['getMarkers'] = MarkerClusterer.prototype.getMarkers;
MarkerClusterer.prototype['getMaxZoom'] = MarkerClusterer.prototype.getMaxZoom;
MarkerClusterer.prototype['getStyles'] = MarkerClusterer.prototype.getStyles;
MarkerClusterer.prototype['getTotalClusters'] = MarkerClusterer.prototype.getTotalClusters;
MarkerClusterer.prototype['getTotalMarkers'] = MarkerClusterer.prototype.getTotalMarkers;
MarkerClusterer.prototype['redraw'] = MarkerClusterer.prototype.redraw;
MarkerClusterer.prototype['removeMarker'] = MarkerClusterer.prototype.removeMarker;
MarkerClusterer.prototype['removeMarkers'] = MarkerClusterer.prototype.removeMarkers;
MarkerClusterer.prototype['resetViewport'] = MarkerClusterer.prototype.resetViewport;
MarkerClusterer.prototype['repaint'] = MarkerClusterer.prototype.repaint;
MarkerClusterer.prototype['setCalculator'] = MarkerClusterer.prototype.setCalculator;
MarkerClusterer.prototype['setGridSize'] = MarkerClusterer.prototype.setGridSize;
MarkerClusterer.prototype['setMaxZoom'] = MarkerClusterer.prototype.setMaxZoom;
MarkerClusterer.prototype['onAdd'] = MarkerClusterer.prototype.onAdd;
MarkerClusterer.prototype['draw'] = MarkerClusterer.prototype.draw;

Cluster.prototype['getCenter'] = Cluster.prototype.getCenter;
Cluster.prototype['getSize'] = Cluster.prototype.getSize;
Cluster.prototype['getMarkers'] = Cluster.prototype.getMarkers;

ClusterIcon.prototype['onAdd'] = ClusterIcon.prototype.onAdd;
ClusterIcon.prototype['draw'] = ClusterIcon.prototype.draw;
ClusterIcon.prototype['onRemove'] = ClusterIcon.prototype.onRemove;

Object.keys = Object.keys || function (o) {
    var result = [];
    for (var name in o) {
        if (o.hasOwnProperty(name)) result.push(name);
    }
    return result;
};

},{}],2:[function(require,module,exports){
'use strict';

var wpseo_directions = [];
var wpseo_maps = [];
var markers = new Object();

var wpseo_directions = [];
var wpseo_maps = [];
var markers = new Object();

window.wpseo_show_map = function wpseo_show_map(location_data, counter, center_lat, center_long, zoom, map_style, scrollable, draggable, default_show_infowindow, is_admin, marker_clustering) {
    var bounds = new google.maps.LatLngBounds();
    var center = new google.maps.LatLng(center_lat, center_long);
    var mobileBreakpoint = 480;
    markers[counter] = [];

    var wpseo_map_options = {
        zoom: zoom,
        minZoom: 1,
        mapTypeControl: true,
        zoomControl: scrollable,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId[map_style.toUpperCase()],
        scrollwheel: scrollable && window.innerWidth > mobileBreakpoint
    };

    // gestureHandling should only be set on devices that support touch.
    if (checkForTouch()) {
        wpseo_map_options.gestureHandling = draggable ? 'auto' : 'none';
    } else {
        wpseo_map_options.draggable = draggable;
    }

    // Set center
    if (zoom == -1) {
        for (var i = 0; i < location_data.length; i++) {
            var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
            bounds.extend(latLong);
        }

        center = bounds.getCenter();
    }
    wpseo_map_options.center = center;

    try {
        var map = new google.maps.Map(document.getElementById("map_canvas" + (counter != 0 ? '_' + counter : '')), wpseo_map_options);
    } catch (error) {
        return;
    }

    if (zoom === -1) {
        map.fitBounds(bounds);
    }

    // Set markers + info
    var infoWindow = new google.maps.InfoWindow({
        content: infoWindowHTML
    });

    for (var i = 0; i < location_data.length; i++) {
        // Create info window HTML
        var infoWindowHTML = getInfoBubbleText(location_data[i]["name"], location_data[i]["address"], location_data[i]["url"], location_data[i]["self_url"]);

        var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
        var icon = location_data[i]["custom_marker"];
        var categories = location_data[i]["categories"];

        markers[counter][i] = new google.maps.Marker({
            position: latLong,
            center: center,
            map: map,
            map_id: counter,
            html: infoWindowHTML,
            draggable: Boolean(is_admin),
            icon: typeof icon !== 'undefined' && icon || '',
            categories: typeof categories !== 'undefined' && categories || ''
        });
    }
    for (var i = 0; i < markers[counter].length; i++) {
        var marker = markers[counter][i];

        google.maps.event.addListener(marker, "click", function () {
            infoWindow.setContent(this.html);
            infoWindow.open(map, this);
        });

        google.maps.event.addListener(infoWindow, 'closeclick', function () {
            map.setCenter(this.getPosition());
        });

        google.maps.event.addListener(marker, 'dragend', function (event) {
            // If on a single location page in a multiple location setup.
            if (document.getElementById('wpseo_coordinates_lat') && document.getElementById('wpseo_coordinates_long')) {
                document.getElementById('wpseo_coordinates_lat').value = event.latLng.lat();
                document.getElementById('wpseo_coordinates_long').value = event.latLng.lng();
            }

            // If on the Yoast Local SEO settings page, using a single location.
            if (document.getElementById('location_coords_lat') && document.getElementById('location_coords_long')) {
                document.getElementById('location_coords_lat').value = event.latLng.lat();
                document.getElementById('location_coords_long').value = event.latLng.lng();
            }
        });
    }

    // If marker clustering is set, use it.
    if (marker_clustering) {
        new MarkerClusterer(map, markers[counter], { imagePath: wpseo_local_data.marker_cluster_image_path });
    }

    // If there is only one marker and the infowindow should be shown, make it so.
    if (markers[counter].length == 1 && default_show_infowindow) {
        infoWindow.setContent(markers[counter][0].html);
        infoWindow.open(map, marker);
    }

    return map;
};

window.checkForTouch = function () {
    return !!(navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i));
};

window.wpseo_get_directions = function (map, location_data, counter, show_route) {
    var directionsDisplay = '';

    if (show_route && location_data.length >= 1) {
        directionsDisplay = new google.maps.DirectionsRenderer();
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById("directions" + (counter != 0 ? '_' + counter : '')));
    }

    return directionsDisplay;
};

window.getInfoBubbleText = function (business_name, business_city_address, business_url, self_url) {
    var infoWindowHTML = '<div class="wpseo-info-window-wrapper">';

    var showSelfLink = false;
    if (self_url != undefined && wpseo_local_data.has_multiple_locations != '' && self_url != window.location.href) showSelfLink = true;

    if (showSelfLink) infoWindowHTML += '<a href="' + self_url + '">';
    infoWindowHTML += '<strong>' + business_name + '</strong>';
    if (showSelfLink) infoWindowHTML += '</a>';
    infoWindowHTML += '<br>';
    infoWindowHTML += business_city_address;

    infoWindowHTML += '</div>';

    return infoWindowHTML;
};

window.wpseo_calculate_route = function (map, dirDisplay, coords_lat, coords_long, counter) {
    if (document.getElementById('wpseo-sl-coords-lat') != null) coords_lat = document.getElementById('wpseo-sl-coords-lat').value;
    if (document.getElementById('wpseo-sl-coords-long') != null) coords_long = document.getElementById('wpseo-sl-coords-long').value;

    var start = document.getElementById("origin" + (counter != 0 ? "_" + counter : "")).value + ' ' + wpseo_local_data.default_country;
    var unit_system = google.maps.UnitSystem.METRIC;
    if (wpseo_local_data.unit_system == 'IMPERIAL') unit_system = google.maps.UnitSystem.IMPERIAL;

    // Clear all markers from the map, only show A and B
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    // Change button to link to Google Maps. iPhones and Android phones will automatically open them in Maps app, when available.
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        var url = 'https://maps.google.com/maps?saddr=' + escape(start) + '&daddr=' + coords_lat + ',' + coords_long;
        window.open(url, '_blank');

        return false;
    } else {
        var latlng = new google.maps.LatLng(coords_lat, coords_long);

        var request = {
            origin: start,
            destination: latlng,
            provideRouteAlternatives: true,
            optimizeWaypoints: true,
            travelMode: google.maps.DirectionsTravelMode.DRIVING,
            unitSystem: unit_system
        };

        var directionsService = new google.maps.DirectionsService();

        directionsService.route(request, function (response, status2) {
            if (status2 == google.maps.DirectionsStatus.OK) {
                dirDisplay.setDirections(response);
            } else if (status2 == google.maps.DirectionsStatus.ZERO_RESULTS) {
                var noroute = document.getElementById('wpseo-noroute');
                noroute.setAttribute('style', 'clear: both; display: block;');
            }
        });
    }
};

window.wpseo_sl_show_route = function (obj, coords_lat, coords_long) {
    $ = jQuery;

    // Create hidden inputs to pass through the lat/long coordinates for which is needed for calculating the route.
    $('.wpseo-sl-coords').remove();
    var inputs = '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-lat" value="' + coords_lat + '">';
    inputs += '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-long" value="' + coords_long + '">';

    $('#wpseo-directions-form').append(inputs).submit();
    $('#wpseo-directions-wrapper').slideUp(function () {
        $(this).insertAfter($(obj).parents('.wpseo-result')).slideDown();
    });
};

window.filterMarkers = function (category, map_id) {
    for (i = 0; i < markers[map_id].length; i++) {
        marker = markers[map_id][i];

        // If is same category or category not picked
        if (marker.categories.hasOwnProperty(category) || category.length === 0) {
            marker.setVisible(true);
        }
        // Categories don't match
        else {
                marker.setVisible(false);
            }
    }
};

},{}]},{},[1,2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvdmVuZG9yL21hcmtlcmNsdXN0ZXIuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWZyb250ZW5kLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7OztBQ0FBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOzs7Ozs7Ozs7Ozs7O0FBYUE7Ozs7Ozs7Ozs7Ozs7O0FBZUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBbUNBLFNBQVMsZUFBVCxDQUF5QixHQUF6QixFQUE4QixXQUE5QixFQUEyQyxXQUEzQyxFQUF3RDtBQUNwRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBSyxNQUFMLENBQVksZUFBWixFQUE2QixPQUFPLElBQVAsQ0FBWSxXQUF6QztBQUNBLFNBQUssSUFBTCxHQUFZLEdBQVo7O0FBRUE7Ozs7QUFJQSxTQUFLLFFBQUwsR0FBZ0IsRUFBaEI7O0FBRUE7OztBQUdBLFNBQUssU0FBTCxHQUFpQixFQUFqQjs7QUFFQSxTQUFLLEtBQUwsR0FBYSxDQUFDLEVBQUQsRUFBSyxFQUFMLEVBQVMsRUFBVCxFQUFhLEVBQWIsRUFBaUIsRUFBakIsQ0FBYjs7QUFFQTs7O0FBR0EsU0FBSyxPQUFMLEdBQWUsRUFBZjs7QUFFQTs7OztBQUlBLFNBQUssTUFBTCxHQUFjLEtBQWQ7O0FBRUEsUUFBSSxVQUFVLGVBQWUsRUFBN0I7O0FBRUE7Ozs7QUFJQSxTQUFLLFNBQUwsR0FBaUIsUUFBUSxVQUFSLEtBQXVCLEVBQXhDOztBQUVBOzs7QUFHQSxTQUFLLGVBQUwsR0FBdUIsUUFBUSxvQkFBUixLQUFpQyxDQUF4RDs7QUFHQTs7OztBQUlBLFNBQUssUUFBTCxHQUFnQixRQUFRLFNBQVIsS0FBc0IsSUFBdEM7O0FBRUEsU0FBSyxPQUFMLEdBQWUsUUFBUSxRQUFSLEtBQXFCLEVBQXBDOztBQUVBOzs7O0FBSUEsU0FBSyxVQUFMLEdBQWtCLFFBQVEsV0FB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QUFDOUMsU0FBSyxJQUFJLENBQVQsRUFBWSxJQUFJLFFBQVEsTUFBUixFQUFnQixNQUFoQyxFQUF3QyxHQUF4QyxFQUE2QztBQUN6QyxpQkFBUyxRQUFRLE1BQVIsRUFBZ0IsQ0FBaEIsQ0FBVDs7QUFFQTtBQUNBLFlBQUksT0FBTyxVQUFQLENBQWtCLGNBQWxCLENBQWlDLFFBQWpDLEtBQThDLFNBQVMsTUFBVCxLQUFvQixDQUF0RSxFQUF5RTtBQUNyRSxtQkFBTyxVQUFQLENBQWtCLElBQWxCO0FBQ0g7QUFDRDtBQUhBLGFBSUs7QUFDRCx1QkFBTyxVQUFQLENBQWtCLEtBQWxCO0FBQ0g7QUFDSjtBQUNKLENBYkQiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvLyA9PUNsb3N1cmVDb21waWxlcj09XG4vLyBAY29tcGlsYXRpb25fbGV2ZWwgQURWQU5DRURfT1BUSU1JWkFUSU9OU1xuLy8gQGV4dGVybnNfdXJsIGh0dHA6Ly9jbG9zdXJlLWNvbXBpbGVyLmdvb2dsZWNvZGUuY29tL3N2bi90cnVuay9jb250cmliL2V4dGVybnMvbWFwcy9nb29nbGVfbWFwc19hcGlfdjNfMy5qc1xuLy8gPT0vQ2xvc3VyZUNvbXBpbGVyPT1cblxuLyoqXG4gKiBAbmFtZSBNYXJrZXJDbHVzdGVyZXIgZm9yIEdvb2dsZSBNYXBzIHYzXG4gKiBAdmVyc2lvbiB2ZXJzaW9uIDEuMC4xXG4gKiBAYXV0aG9yIEx1a2UgTWFoZVxuICogQGZpbGVvdmVydmlld1xuICogVGhlIGxpYnJhcnkgY3JlYXRlcyBhbmQgbWFuYWdlcyBwZXItem9vbS1sZXZlbCBjbHVzdGVycyBmb3IgbGFyZ2UgYW1vdW50cyBvZlxuICogbWFya2Vycy5cbiAqIDxici8+XG4gKiBUaGlzIGlzIGEgdjMgaW1wbGVtZW50YXRpb24gb2YgdGhlXG4gKiA8YSBocmVmPVwiaHR0cDovL2dtYXBzLXV0aWxpdHktbGlicmFyeS1kZXYuZ29vZ2xlY29kZS5jb20vc3ZuL3RhZ3MvbWFya2VyY2x1c3RlcmVyL1wiXG4gKiA+djIgTWFya2VyQ2x1c3RlcmVyPC9hPi5cbiAqL1xuXG4vKipcbiAqIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XG4gKiB5b3UgbWF5IG5vdCB1c2UgdGhpcyBmaWxlIGV4Y2VwdCBpbiBjb21wbGlhbmNlIHdpdGggdGhlIExpY2Vuc2UuXG4gKiBZb3UgbWF5IG9idGFpbiBhIGNvcHkgb2YgdGhlIExpY2Vuc2UgYXRcbiAqXG4gKiAgICAgaHR0cDovL3d3dy5hcGFjaGUub3JnL2xpY2Vuc2VzL0xJQ0VOU0UtMi4wXG4gKlxuICogVW5sZXNzIHJlcXVpcmVkIGJ5IGFwcGxpY2FibGUgbGF3IG9yIGFncmVlZCB0byBpbiB3cml0aW5nLCBzb2Z0d2FyZVxuICogZGlzdHJpYnV0ZWQgdW5kZXIgdGhlIExpY2Vuc2UgaXMgZGlzdHJpYnV0ZWQgb24gYW4gXCJBUyBJU1wiIEJBU0lTLFxuICogV0lUSE9VVCBXQVJSQU5USUVTIE9SIENPTkRJVElPTlMgT0YgQU5ZIEtJTkQsIGVpdGhlciBleHByZXNzIG9yIGltcGxpZWQuXG4gKiBTZWUgdGhlIExpY2Vuc2UgZm9yIHRoZSBzcGVjaWZpYyBsYW5ndWFnZSBnb3Zlcm5pbmcgcGVybWlzc2lvbnMgYW5kXG4gKiBsaW1pdGF0aW9ucyB1bmRlciB0aGUgTGljZW5zZS5cbiAqL1xuXG5cbi8qKlxuICogQSBNYXJrZXIgQ2x1c3RlcmVyIHRoYXQgY2x1c3RlcnMgbWFya2Vycy5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLk1hcH0gbWFwIFRoZSBHb29nbGUgbWFwIHRvIGF0dGFjaCB0by5cbiAqIEBwYXJhbSB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj49fSBvcHRfbWFya2VycyBPcHRpb25hbCBtYXJrZXJzIHRvIGFkZCB0b1xuICogICB0aGUgY2x1c3Rlci5cbiAqIEBwYXJhbSB7T2JqZWN0PX0gb3B0X29wdGlvbnMgc3VwcG9ydCB0aGUgZm9sbG93aW5nIG9wdGlvbnM6XG4gKiAgICAgJ2dyaWRTaXplJzogKG51bWJlcikgVGhlIGdyaWQgc2l6ZSBvZiBhIGNsdXN0ZXIgaW4gcGl4ZWxzLlxuICogICAgICdtYXhab29tJzogKG51bWJlcikgVGhlIG1heGltdW0gem9vbSBsZXZlbCB0aGF0IGEgbWFya2VyIGNhbiBiZSBwYXJ0IG9mIGFcbiAqICAgICAgICAgICAgICAgIGNsdXN0ZXIuXG4gKiAgICAgJ3pvb21PbkNsaWNrJzogKGJvb2xlYW4pIFdoZXRoZXIgdGhlIGRlZmF1bHQgYmVoYXZpb3VyIG9mIGNsaWNraW5nIG9uIGFcbiAqICAgICAgICAgICAgICAgICAgICBjbHVzdGVyIGlzIHRvIHpvb20gaW50byBpdC5cbiAqICAgICAnaW1hZ2VQYXRoJzogKHN0cmluZykgVGhlIGJhc2UgVVJMIHdoZXJlIHRoZSBpbWFnZXMgcmVwcmVzZW50aW5nXG4gKiAgICAgICAgICAgICAgICAgIGNsdXN0ZXJzIHdpbGwgYmUgZm91bmQuIFRoZSBmdWxsIFVSTCB3aWxsIGJlOlxuICogICAgICAgICAgICAgICAgICB7aW1hZ2VQYXRofVsxLTVdLntpbWFnZUV4dGVuc2lvbn1cbiAqICAgICAgICAgICAgICAgICAgRGVmYXVsdDogJy4uL2ltYWdlcy9tJy5cbiAqICAgICAnaW1hZ2VFeHRlbnNpb24nOiAoc3RyaW5nKSBUaGUgc3VmZml4IGZvciBpbWFnZXMgVVJMIHJlcHJlc2VudGluZ1xuICogICAgICAgICAgICAgICAgICAgICAgIGNsdXN0ZXJzIHdpbGwgYmUgZm91bmQuIFNlZSBfaW1hZ2VQYXRoXyBmb3IgZGV0YWlscy5cbiAqICAgICAgICAgICAgICAgICAgICAgICBEZWZhdWx0OiAncG5nJy5cbiAqICAgICAnYXZlcmFnZUNlbnRlcic6IChib29sZWFuKSBXaGV0aGVyIHRoZSBjZW50ZXIgb2YgZWFjaCBjbHVzdGVyIHNob3VsZCBiZVxuICogICAgICAgICAgICAgICAgICAgICAgdGhlIGF2ZXJhZ2Ugb2YgYWxsIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXIuXG4gKiAgICAgJ21pbmltdW1DbHVzdGVyU2l6ZSc6IChudW1iZXIpIFRoZSBtaW5pbXVtIG51bWJlciBvZiBtYXJrZXJzIHRvIGJlIGluIGFcbiAqICAgICAgICAgICAgICAgICAgICAgICAgICAgY2x1c3RlciBiZWZvcmUgdGhlIG1hcmtlcnMgYXJlIGhpZGRlbiBhbmQgYSBjb3VudFxuICogICAgICAgICAgICAgICAgICAgICAgICAgICBpcyBzaG93bi5cbiAqICAgICAnc3R5bGVzJzogKG9iamVjdCkgQW4gb2JqZWN0IHRoYXQgaGFzIHN0eWxlIHByb3BlcnRpZXM6XG4gKiAgICAgICAndXJsJzogKHN0cmluZykgVGhlIGltYWdlIHVybC5cbiAqICAgICAgICdoZWlnaHQnOiAobnVtYmVyKSBUaGUgaW1hZ2UgaGVpZ2h0LlxuICogICAgICAgJ3dpZHRoJzogKG51bWJlcikgVGhlIGltYWdlIHdpZHRoLlxuICogICAgICAgJ2FuY2hvcic6IChBcnJheSkgVGhlIGFuY2hvciBwb3NpdGlvbiBvZiB0aGUgbGFiZWwgdGV4dC5cbiAqICAgICAgICd0ZXh0Q29sb3InOiAoc3RyaW5nKSBUaGUgdGV4dCBjb2xvci5cbiAqICAgICAgICd0ZXh0U2l6ZSc6IChudW1iZXIpIFRoZSB0ZXh0IHNpemUuXG4gKiAgICAgICAnYmFja2dyb3VuZFBvc2l0aW9uJzogKHN0cmluZykgVGhlIHBvc2l0aW9uIG9mIHRoZSBiYWNrZ291bmQgeCwgeS5cbiAqIEBjb25zdHJ1Y3RvclxuICogQGV4dGVuZHMgZ29vZ2xlLm1hcHMuT3ZlcmxheVZpZXdcbiAqL1xuZnVuY3Rpb24gTWFya2VyQ2x1c3RlcmVyKG1hcCwgb3B0X21hcmtlcnMsIG9wdF9vcHRpb25zKSB7XG4gICAgLy8gTWFya2VyQ2x1c3RlcmVyIGltcGxlbWVudHMgZ29vZ2xlLm1hcHMuT3ZlcmxheVZpZXcgaW50ZXJmYWNlLiBXZSB1c2UgdGhlXG4gICAgLy8gZXh0ZW5kIGZ1bmN0aW9uIHRvIGV4dGVuZCBNYXJrZXJDbHVzdGVyZXIgd2l0aCBnb29nbGUubWFwcy5PdmVybGF5Vmlld1xuICAgIC8vIGJlY2F1c2UgaXQgbWlnaHQgbm90IGFsd2F5cyBiZSBhdmFpbGFibGUgd2hlbiB0aGUgY29kZSBpcyBkZWZpbmVkIHNvIHdlXG4gICAgLy8gbG9vayBmb3IgaXQgYXQgdGhlIGxhc3QgcG9zc2libGUgbW9tZW50LiBJZiBpdCBkb2Vzbid0IGV4aXN0IG5vdyB0aGVuXG4gICAgLy8gdGhlcmUgaXMgbm8gcG9pbnQgZ29pbmcgYWhlYWQgOilcbiAgICB0aGlzLmV4dGVuZChNYXJrZXJDbHVzdGVyZXIsIGdvb2dsZS5tYXBzLk92ZXJsYXlWaWV3KTtcbiAgICB0aGlzLm1hcF8gPSBtYXA7XG5cbiAgICAvKipcbiAgICAgKiBAdHlwZSB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj59XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLm1hcmtlcnNfID0gW107XG5cbiAgICAvKipcbiAgICAgKiAgQHR5cGUge0FycmF5LjxDbHVzdGVyPn1cbiAgICAgKi9cbiAgICB0aGlzLmNsdXN0ZXJzXyA9IFtdO1xuXG4gICAgdGhpcy5zaXplcyA9IFs1MywgNTYsIDY2LCA3OCwgOTBdO1xuXG4gICAgLyoqXG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLnN0eWxlc18gPSBbXTtcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtib29sZWFufVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgdGhpcy5yZWFkeV8gPSBmYWxzZTtcblxuICAgIHZhciBvcHRpb25zID0gb3B0X29wdGlvbnMgfHwge307XG5cbiAgICAvKipcbiAgICAgKiBAdHlwZSB7bnVtYmVyfVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgdGhpcy5ncmlkU2l6ZV8gPSBvcHRpb25zWydncmlkU2l6ZSddIHx8IDYwO1xuXG4gICAgLyoqXG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLm1pbkNsdXN0ZXJTaXplXyA9IG9wdGlvbnNbJ21pbmltdW1DbHVzdGVyU2l6ZSddIHx8IDI7XG5cblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHs/bnVtYmVyfVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgdGhpcy5tYXhab29tXyA9IG9wdGlvbnNbJ21heFpvb20nXSB8fCBudWxsO1xuXG4gICAgdGhpcy5zdHlsZXNfID0gb3B0aW9uc1snc3R5bGVzJ10gfHwgW107XG5cbiAgICAvKipcbiAgICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgdGhpcy5pbWFnZVBhdGhfID0gb3B0aW9uc1snaW1hZ2VQYXRoJ10gfHxcbiAgICAgICAgdGhpcy5NQVJLRVJfQ0xVU1RFUl9JTUFHRV9QQVRIXztcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLmltYWdlRXh0ZW5zaW9uXyA9IG9wdGlvbnNbJ2ltYWdlRXh0ZW5zaW9uJ10gfHxcbiAgICAgICAgdGhpcy5NQVJLRVJfQ0xVU1RFUl9JTUFHRV9FWFRFTlNJT05fO1xuXG4gICAgLyoqXG4gICAgICogQHR5cGUge2Jvb2xlYW59XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLnpvb21PbkNsaWNrXyA9IHRydWU7XG5cbiAgICBpZiAob3B0aW9uc1snem9vbU9uQ2xpY2snXSAhPSB1bmRlZmluZWQpIHtcbiAgICAgICAgdGhpcy56b29tT25DbGlja18gPSBvcHRpb25zWyd6b29tT25DbGljayddO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtib29sZWFufVxuICAgICAqIEBwcml2YXRlXG4gICAgICovXG4gICAgdGhpcy5hdmVyYWdlQ2VudGVyXyA9IGZhbHNlO1xuXG4gICAgaWYgKG9wdGlvbnNbJ2F2ZXJhZ2VDZW50ZXInXSAhPSB1bmRlZmluZWQpIHtcbiAgICAgICAgdGhpcy5hdmVyYWdlQ2VudGVyXyA9IG9wdGlvbnNbJ2F2ZXJhZ2VDZW50ZXInXTtcbiAgICB9XG5cbiAgICB0aGlzLnNldHVwU3R5bGVzXygpO1xuXG4gICAgdGhpcy5zZXRNYXAobWFwKTtcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtudW1iZXJ9XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLnByZXZab29tXyA9IHRoaXMubWFwXy5nZXRab29tKCk7XG5cbiAgICAvLyBBZGQgdGhlIG1hcCBldmVudCBsaXN0ZW5lcnNcbiAgICB2YXIgdGhhdCA9IHRoaXM7XG4gICAgZ29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIodGhpcy5tYXBfLCAnem9vbV9jaGFuZ2VkJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIC8vIERldGVybWluZXMgbWFwIHR5cGUgYW5kIHByZXZlbnQgaWxsZWdhbCB6b29tIGxldmVsc1xuICAgICAgICB2YXIgem9vbSA9IHRoYXQubWFwXy5nZXRab29tKCk7XG4gICAgICAgIHZhciBtaW5ab29tID0gdGhhdC5tYXBfLm1pblpvb20gfHwgMDtcbiAgICAgICAgdmFyIG1heFpvb20gPSBNYXRoLm1pbih0aGF0Lm1hcF8ubWF4Wm9vbSB8fCAxMDAsXG4gICAgICAgICAgICB0aGF0Lm1hcF8ubWFwVHlwZXNbdGhhdC5tYXBfLmdldE1hcFR5cGVJZCgpXS5tYXhab29tKTtcbiAgICAgICAgem9vbSA9IE1hdGgubWluKE1hdGgubWF4KHpvb20sbWluWm9vbSksbWF4Wm9vbSk7XG5cbiAgICAgICAgaWYgKHRoYXQucHJldlpvb21fICE9IHpvb20pIHtcbiAgICAgICAgICAgIHRoYXQucHJldlpvb21fID0gem9vbTtcbiAgICAgICAgICAgIHRoYXQucmVzZXRWaWV3cG9ydCgpO1xuICAgICAgICB9XG4gICAgfSk7XG5cbiAgICBnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcih0aGlzLm1hcF8sICdpZGxlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHRoYXQucmVkcmF3KCk7XG4gICAgfSk7XG5cbiAgICAvLyBGaW5hbGx5LCBhZGQgdGhlIG1hcmtlcnNcbiAgICBpZiAob3B0X21hcmtlcnMgJiYgKG9wdF9tYXJrZXJzLmxlbmd0aCB8fCBPYmplY3Qua2V5cyhvcHRfbWFya2VycykubGVuZ3RoKSkge1xuICAgICAgICB0aGlzLmFkZE1hcmtlcnMob3B0X21hcmtlcnMsIGZhbHNlKTtcbiAgICB9XG59XG5cblxuLyoqXG4gKiBUaGUgbWFya2VyIGNsdXN0ZXIgaW1hZ2UgcGF0aC5cbiAqXG4gKiBAdHlwZSB7c3RyaW5nfVxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5NQVJLRVJfQ0xVU1RFUl9JTUFHRV9QQVRIXyA9ICcuLi9pbWFnZXMvbSc7XG5cblxuLyoqXG4gKiBUaGUgbWFya2VyIGNsdXN0ZXIgaW1hZ2UgcGF0aC5cbiAqXG4gKiBAdHlwZSB7c3RyaW5nfVxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5NQVJLRVJfQ0xVU1RFUl9JTUFHRV9FWFRFTlNJT05fID0gJ3BuZyc7XG5cblxuLyoqXG4gKiBFeHRlbmRzIGEgb2JqZWN0cyBwcm90b3R5cGUgYnkgYW5vdGhlcnMuXG4gKlxuICogQHBhcmFtIHtPYmplY3R9IG9iajEgVGhlIG9iamVjdCB0byBiZSBleHRlbmRlZC5cbiAqIEBwYXJhbSB7T2JqZWN0fSBvYmoyIFRoZSBvYmplY3QgdG8gZXh0ZW5kIHdpdGguXG4gKiBAcmV0dXJuIHtPYmplY3R9IFRoZSBuZXcgZXh0ZW5kZWQgb2JqZWN0LlxuICogQGlnbm9yZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmV4dGVuZCA9IGZ1bmN0aW9uKG9iajEsIG9iajIpIHtcbiAgICByZXR1cm4gKGZ1bmN0aW9uKG9iamVjdCkge1xuICAgICAgICBmb3IgKHZhciBwcm9wZXJ0eSBpbiBvYmplY3QucHJvdG90eXBlKSB7XG4gICAgICAgICAgICB0aGlzLnByb3RvdHlwZVtwcm9wZXJ0eV0gPSBvYmplY3QucHJvdG90eXBlW3Byb3BlcnR5XTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9KS5hcHBseShvYmoxLCBbb2JqMl0pO1xufTtcblxuXG4vKipcbiAqIEltcGxlbWVudGFpb24gb2YgdGhlIGludGVyZmFjZSBtZXRob2QuXG4gKiBAaWdub3JlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUub25BZGQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnNldFJlYWR5Xyh0cnVlKTtcbn07XG5cbi8qKlxuICogSW1wbGVtZW50YWlvbiBvZiB0aGUgaW50ZXJmYWNlIG1ldGhvZC5cbiAqIEBpZ25vcmVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5kcmF3ID0gZnVuY3Rpb24oKSB7fTtcblxuLyoqXG4gKiBTZXRzIHVwIHRoZSBzdHlsZXMgb2JqZWN0LlxuICpcbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0dXBTdHlsZXNfID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHRoaXMuc3R5bGVzXy5sZW5ndGgpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGZvciAodmFyIGkgPSAwLCBzaXplOyBzaXplID0gdGhpcy5zaXplc1tpXTsgaSsrKSB7XG4gICAgICAgIHRoaXMuc3R5bGVzXy5wdXNoKHtcbiAgICAgICAgICAgIHVybDogdGhpcy5pbWFnZVBhdGhfICsgKGkgKyAxKSArICcuJyArIHRoaXMuaW1hZ2VFeHRlbnNpb25fLFxuICAgICAgICAgICAgaGVpZ2h0OiBzaXplLFxuICAgICAgICAgICAgd2lkdGg6IHNpemVcbiAgICAgICAgfSk7XG4gICAgfVxufTtcblxuLyoqXG4gKiAgRml0IHRoZSBtYXAgdG8gdGhlIGJvdW5kcyBvZiB0aGUgbWFya2VycyBpbiB0aGUgY2x1c3RlcmVyLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmZpdE1hcFRvTWFya2VycyA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBtYXJrZXJzID0gdGhpcy5nZXRNYXJrZXJzKCk7XG4gICAgdmFyIGJvdW5kcyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHMoKTtcbiAgICBmb3IgKHZhciBpID0gMCwgbWFya2VyOyBtYXJrZXIgPSBtYXJrZXJzW2ldOyBpKyspIHtcbiAgICAgICAgYm91bmRzLmV4dGVuZChtYXJrZXIuZ2V0UG9zaXRpb24oKSk7XG4gICAgfVxuXG4gICAgdGhpcy5tYXBfLmZpdEJvdW5kcyhib3VuZHMpO1xufTtcblxuXG4vKipcbiAqICBTZXRzIHRoZSBzdHlsZXMuXG4gKlxuICogIEBwYXJhbSB7T2JqZWN0fSBzdHlsZXMgVGhlIHN0eWxlIHRvIHNldC5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRTdHlsZXMgPSBmdW5jdGlvbihzdHlsZXMpIHtcbiAgICB0aGlzLnN0eWxlc18gPSBzdHlsZXM7XG59O1xuXG5cbi8qKlxuICogIEdldHMgdGhlIHN0eWxlcy5cbiAqXG4gKiAgQHJldHVybiB7T2JqZWN0fSBUaGUgc3R5bGVzIG9iamVjdC5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRTdHlsZXMgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5zdHlsZXNfO1xufTtcblxuXG4vKipcbiAqIFdoZXRoZXIgem9vbSBvbiBjbGljayBpcyBzZXQuXG4gKlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB6b29tT25DbGlja18gaXMgc2V0LlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmlzWm9vbU9uQ2xpY2sgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy56b29tT25DbGlja187XG59O1xuXG4vKipcbiAqIFdoZXRoZXIgYXZlcmFnZSBjZW50ZXIgaXMgc2V0LlxuICpcbiAqIEByZXR1cm4ge2Jvb2xlYW59IFRydWUgaWYgYXZlcmFnZUNlbnRlcl8gaXMgc2V0LlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmlzQXZlcmFnZUNlbnRlciA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLmF2ZXJhZ2VDZW50ZXJfO1xufTtcblxuXG4vKipcbiAqICBSZXR1cm5zIHRoZSBhcnJheSBvZiBtYXJrZXJzIGluIHRoZSBjbHVzdGVyZXIuXG4gKlxuICogIEByZXR1cm4ge0FycmF5Ljxnb29nbGUubWFwcy5NYXJrZXI+fSBUaGUgbWFya2Vycy5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNYXJrZXJzID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWFya2Vyc187XG59O1xuXG5cbi8qKlxuICogIFJldHVybnMgdGhlIG51bWJlciBvZiBtYXJrZXJzIGluIHRoZSBjbHVzdGVyZXJcbiAqXG4gKiAgQHJldHVybiB7TnVtYmVyfSBUaGUgbnVtYmVyIG9mIG1hcmtlcnMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0VG90YWxNYXJrZXJzID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWFya2Vyc18ubGVuZ3RoO1xufTtcblxuXG4vKipcbiAqICBTZXRzIHRoZSBtYXggem9vbSBmb3IgdGhlIGNsdXN0ZXJlci5cbiAqXG4gKiAgQHBhcmFtIHtudW1iZXJ9IG1heFpvb20gVGhlIG1heCB6b29tIGxldmVsLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldE1heFpvb20gPSBmdW5jdGlvbihtYXhab29tKSB7XG4gICAgdGhpcy5tYXhab29tXyA9IG1heFpvb207XG59O1xuXG5cbi8qKlxuICogIEdldHMgdGhlIG1heCB6b29tIGZvciB0aGUgY2x1c3RlcmVyLlxuICpcbiAqICBAcmV0dXJuIHtudW1iZXJ9IFRoZSBtYXggem9vbSBsZXZlbC5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNYXhab29tID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWF4Wm9vbV87XG59O1xuXG5cbi8qKlxuICogIFRoZSBmdW5jdGlvbiBmb3IgY2FsY3VsYXRpbmcgdGhlIGNsdXN0ZXIgaWNvbiBpbWFnZS5cbiAqXG4gKiAgQHBhcmFtIHtBcnJheS48Z29vZ2xlLm1hcHMuTWFya2VyPn0gbWFya2VycyBUaGUgbWFya2VycyBpbiB0aGUgY2x1c3RlcmVyLlxuICogIEBwYXJhbSB7bnVtYmVyfSBudW1TdHlsZXMgVGhlIG51bWJlciBvZiBzdHlsZXMgYXZhaWxhYmxlLlxuICogIEByZXR1cm4ge09iamVjdH0gQSBvYmplY3QgcHJvcGVydGllczogJ3RleHQnIChzdHJpbmcpIGFuZCAnaW5kZXgnIChudW1iZXIpLlxuICogIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuY2FsY3VsYXRvcl8gPSBmdW5jdGlvbihtYXJrZXJzLCBudW1TdHlsZXMpIHtcbiAgICB2YXIgaW5kZXggPSAwO1xuICAgIHZhciBjb3VudCA9IG1hcmtlcnMubGVuZ3RoO1xuICAgIHZhciBkdiA9IGNvdW50O1xuICAgIHdoaWxlIChkdiAhPT0gMCkge1xuICAgICAgICBkdiA9IHBhcnNlSW50KGR2IC8gMTAsIDEwKTtcbiAgICAgICAgaW5kZXgrKztcbiAgICB9XG5cbiAgICBpbmRleCA9IE1hdGgubWluKGluZGV4LCBudW1TdHlsZXMpO1xuICAgIHJldHVybiB7XG4gICAgICAgIHRleHQ6IGNvdW50LFxuICAgICAgICBpbmRleDogaW5kZXhcbiAgICB9O1xufTtcblxuXG4vKipcbiAqIFNldCB0aGUgY2FsY3VsYXRvciBmdW5jdGlvbi5cbiAqXG4gKiBAcGFyYW0ge2Z1bmN0aW9uKEFycmF5LCBudW1iZXIpfSBjYWxjdWxhdG9yIFRoZSBmdW5jdGlvbiB0byBzZXQgYXMgdGhlXG4gKiAgICAgY2FsY3VsYXRvci4gVGhlIGZ1bmN0aW9uIHNob3VsZCByZXR1cm4gYSBvYmplY3QgcHJvcGVydGllczpcbiAqICAgICAndGV4dCcgKHN0cmluZykgYW5kICdpbmRleCcgKG51bWJlcikuXG4gKlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldENhbGN1bGF0b3IgPSBmdW5jdGlvbihjYWxjdWxhdG9yKSB7XG4gICAgdGhpcy5jYWxjdWxhdG9yXyA9IGNhbGN1bGF0b3I7XG59O1xuXG5cbi8qKlxuICogR2V0IHRoZSBjYWxjdWxhdG9yIGZ1bmN0aW9uLlxuICpcbiAqIEByZXR1cm4ge2Z1bmN0aW9uKEFycmF5LCBudW1iZXIpfSB0aGUgY2FsY3VsYXRvciBmdW5jdGlvbi5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRDYWxjdWxhdG9yID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMuY2FsY3VsYXRvcl87XG59O1xuXG5cbi8qKlxuICogQWRkIGFuIGFycmF5IG9mIG1hcmtlcnMgdG8gdGhlIGNsdXN0ZXJlci5cbiAqXG4gKiBAcGFyYW0ge0FycmF5Ljxnb29nbGUubWFwcy5NYXJrZXI+fSBtYXJrZXJzIFRoZSBtYXJrZXJzIHRvIGFkZC5cbiAqIEBwYXJhbSB7Ym9vbGVhbj19IG9wdF9ub2RyYXcgV2hldGhlciB0byByZWRyYXcgdGhlIGNsdXN0ZXJzLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmFkZE1hcmtlcnMgPSBmdW5jdGlvbihtYXJrZXJzLCBvcHRfbm9kcmF3KSB7XG4gICAgaWYgKG1hcmtlcnMubGVuZ3RoKSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBtYXJrZXI7IG1hcmtlciA9IG1hcmtlcnNbaV07IGkrKykge1xuICAgICAgICAgICAgdGhpcy5wdXNoTWFya2VyVG9fKG1hcmtlcik7XG4gICAgICAgIH1cbiAgICB9IGVsc2UgaWYgKE9iamVjdC5rZXlzKG1hcmtlcnMpLmxlbmd0aCkge1xuICAgICAgICBmb3IgKHZhciBtYXJrZXIgaW4gbWFya2Vycykge1xuICAgICAgICAgICAgdGhpcy5wdXNoTWFya2VyVG9fKG1hcmtlcnNbbWFya2VyXSk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgaWYgKCFvcHRfbm9kcmF3KSB7XG4gICAgICAgIHRoaXMucmVkcmF3KCk7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIFB1c2hlcyBhIG1hcmtlciB0byB0aGUgY2x1c3RlcmVyLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byBhZGQuXG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnB1c2hNYXJrZXJUb18gPSBmdW5jdGlvbihtYXJrZXIpIHtcbiAgICBtYXJrZXIuaXNBZGRlZCA9IGZhbHNlO1xuICAgIGlmIChtYXJrZXJbJ2RyYWdnYWJsZSddKSB7XG4gICAgICAgIC8vIElmIHRoZSBtYXJrZXIgaXMgZHJhZ2dhYmxlIGFkZCBhIGxpc3RlbmVyIHNvIHdlIHVwZGF0ZSB0aGUgY2x1c3RlcnMgb25cbiAgICAgICAgLy8gdGhlIGRyYWcgZW5kLlxuICAgICAgICB2YXIgdGhhdCA9IHRoaXM7XG4gICAgICAgIGdvb2dsZS5tYXBzLmV2ZW50LmFkZExpc3RlbmVyKG1hcmtlciwgJ2RyYWdlbmQnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIG1hcmtlci5pc0FkZGVkID0gZmFsc2U7XG4gICAgICAgICAgICB0aGF0LnJlcGFpbnQoKTtcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIHRoaXMubWFya2Vyc18ucHVzaChtYXJrZXIpO1xufTtcblxuXG4vKipcbiAqIEFkZHMgYSBtYXJrZXIgdG8gdGhlIGNsdXN0ZXJlciBhbmQgcmVkcmF3cyBpZiBuZWVkZWQuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGFkZC5cbiAqIEBwYXJhbSB7Ym9vbGVhbj19IG9wdF9ub2RyYXcgV2hldGhlciB0byByZWRyYXcgdGhlIGNsdXN0ZXJzLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmFkZE1hcmtlciA9IGZ1bmN0aW9uKG1hcmtlciwgb3B0X25vZHJhdykge1xuICAgIHRoaXMucHVzaE1hcmtlclRvXyhtYXJrZXIpO1xuICAgIGlmICghb3B0X25vZHJhdykge1xuICAgICAgICB0aGlzLnJlZHJhdygpO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBSZW1vdmVzIGEgbWFya2VyIGFuZCByZXR1cm5zIHRydWUgaWYgcmVtb3ZlZCwgZmFsc2UgaWYgbm90XG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIHJlbW92ZVxuICogQHJldHVybiB7Ym9vbGVhbn0gV2hldGhlciB0aGUgbWFya2VyIHdhcyByZW1vdmVkIG9yIG5vdFxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZW1vdmVNYXJrZXJfID0gZnVuY3Rpb24obWFya2VyKSB7XG4gICAgdmFyIGluZGV4ID0gLTE7XG4gICAgaWYgKHRoaXMubWFya2Vyc18uaW5kZXhPZikge1xuICAgICAgICBpbmRleCA9IHRoaXMubWFya2Vyc18uaW5kZXhPZihtYXJrZXIpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBtOyBtID0gdGhpcy5tYXJrZXJzX1tpXTsgaSsrKSB7XG4gICAgICAgICAgICBpZiAobSA9PSBtYXJrZXIpIHtcbiAgICAgICAgICAgICAgICBpbmRleCA9IGk7XG4gICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAoaW5kZXggPT0gLTEpIHtcbiAgICAgICAgLy8gTWFya2VyIGlzIG5vdCBpbiBvdXIgbGlzdCBvZiBtYXJrZXJzLlxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuXG4gICAgbWFya2VyLnNldE1hcChudWxsKTtcblxuICAgIHRoaXMubWFya2Vyc18uc3BsaWNlKGluZGV4LCAxKTtcblxuICAgIHJldHVybiB0cnVlO1xufTtcblxuXG4vKipcbiAqIFJlbW92ZSBhIG1hcmtlciBmcm9tIHRoZSBjbHVzdGVyLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byByZW1vdmUuXG4gKiBAcGFyYW0ge2Jvb2xlYW49fSBvcHRfbm9kcmF3IE9wdGlvbmFsIGJvb2xlYW4gdG8gZm9yY2Ugbm8gcmVkcmF3LlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgbWFya2VyIHdhcyByZW1vdmVkLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlbW92ZU1hcmtlciA9IGZ1bmN0aW9uKG1hcmtlciwgb3B0X25vZHJhdykge1xuICAgIHZhciByZW1vdmVkID0gdGhpcy5yZW1vdmVNYXJrZXJfKG1hcmtlcik7XG5cbiAgICBpZiAoIW9wdF9ub2RyYXcgJiYgcmVtb3ZlZCkge1xuICAgICAgICB0aGlzLnJlc2V0Vmlld3BvcnQoKTtcbiAgICAgICAgdGhpcy5yZWRyYXcoKTtcbiAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBSZW1vdmVzIGFuIGFycmF5IG9mIG1hcmtlcnMgZnJvbSB0aGUgY2x1c3Rlci5cbiAqXG4gKiBAcGFyYW0ge0FycmF5Ljxnb29nbGUubWFwcy5NYXJrZXI+fSBtYXJrZXJzIFRoZSBtYXJrZXJzIHRvIHJlbW92ZS5cbiAqIEBwYXJhbSB7Ym9vbGVhbj19IG9wdF9ub2RyYXcgT3B0aW9uYWwgYm9vbGVhbiB0byBmb3JjZSBubyByZWRyYXcuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVtb3ZlTWFya2VycyA9IGZ1bmN0aW9uKG1hcmtlcnMsIG9wdF9ub2RyYXcpIHtcbiAgICAvLyBjcmVhdGUgYSBsb2NhbCBjb3B5IG9mIG1hcmtlcnMgaWYgcmVxdWlyZWRcbiAgICAvLyAocmVtb3ZlTWFya2VyXyBtb2RpZmllcyB0aGUgZ2V0TWFya2VycygpIGFycmF5IGluIHBsYWNlKVxuICAgIHZhciBtYXJrZXJzQ29weSA9IG1hcmtlcnMgPT09IHRoaXMuZ2V0TWFya2VycygpID8gbWFya2Vycy5zbGljZSgpIDogbWFya2VycztcbiAgICB2YXIgcmVtb3ZlZCA9IGZhbHNlO1xuXG4gICAgZm9yICh2YXIgaSA9IDAsIG1hcmtlcjsgbWFya2VyID0gbWFya2Vyc0NvcHlbaV07IGkrKykge1xuICAgICAgICB2YXIgciA9IHRoaXMucmVtb3ZlTWFya2VyXyhtYXJrZXIpO1xuICAgICAgICByZW1vdmVkID0gcmVtb3ZlZCB8fCByO1xuICAgIH1cblxuICAgIGlmICghb3B0X25vZHJhdyAmJiByZW1vdmVkKSB7XG4gICAgICAgIHRoaXMucmVzZXRWaWV3cG9ydCgpO1xuICAgICAgICB0aGlzLnJlZHJhdygpO1xuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG59O1xuXG5cbi8qKlxuICogU2V0cyB0aGUgY2x1c3RlcmVyJ3MgcmVhZHkgc3RhdGUuXG4gKlxuICogQHBhcmFtIHtib29sZWFufSByZWFkeSBUaGUgc3RhdGUuXG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldFJlYWR5XyA9IGZ1bmN0aW9uKHJlYWR5KSB7XG4gICAgaWYgKCF0aGlzLnJlYWR5Xykge1xuICAgICAgICB0aGlzLnJlYWR5XyA9IHJlYWR5O1xuICAgICAgICB0aGlzLmNyZWF0ZUNsdXN0ZXJzXygpO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBudW1iZXIgb2YgY2x1c3RlcnMgaW4gdGhlIGNsdXN0ZXJlci5cbiAqXG4gKiBAcmV0dXJuIHtudW1iZXJ9IFRoZSBudW1iZXIgb2YgY2x1c3RlcnMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0VG90YWxDbHVzdGVycyA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLmNsdXN0ZXJzXy5sZW5ndGg7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgZ29vZ2xlIG1hcCB0aGF0IHRoZSBjbHVzdGVyZXIgaXMgYXNzb2NpYXRlZCB3aXRoLlxuICpcbiAqIEByZXR1cm4ge2dvb2dsZS5tYXBzLk1hcH0gVGhlIG1hcC5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNYXAgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXBfO1xufTtcblxuXG4vKipcbiAqIFNldHMgdGhlIGdvb2dsZSBtYXAgdGhhdCB0aGUgY2x1c3RlcmVyIGlzIGFzc29jaWF0ZWQgd2l0aC5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLk1hcH0gbWFwIFRoZSBtYXAuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0TWFwID0gZnVuY3Rpb24obWFwKSB7XG4gICAgdGhpcy5tYXBfID0gbWFwO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIHNpemUgb2YgdGhlIGdyaWQuXG4gKlxuICogQHJldHVybiB7bnVtYmVyfSBUaGUgZ3JpZCBzaXplLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldEdyaWRTaXplID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMuZ3JpZFNpemVfO1xufTtcblxuXG4vKipcbiAqIFNldHMgdGhlIHNpemUgb2YgdGhlIGdyaWQuXG4gKlxuICogQHBhcmFtIHtudW1iZXJ9IHNpemUgVGhlIGdyaWQgc2l6ZS5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRHcmlkU2l6ZSA9IGZ1bmN0aW9uKHNpemUpIHtcbiAgICB0aGlzLmdyaWRTaXplXyA9IHNpemU7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgbWluIGNsdXN0ZXIgc2l6ZS5cbiAqXG4gKiBAcmV0dXJuIHtudW1iZXJ9IFRoZSBncmlkIHNpemUuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0TWluQ2x1c3RlclNpemUgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5taW5DbHVzdGVyU2l6ZV87XG59O1xuXG4vKipcbiAqIFNldHMgdGhlIG1pbiBjbHVzdGVyIHNpemUuXG4gKlxuICogQHBhcmFtIHtudW1iZXJ9IHNpemUgVGhlIGdyaWQgc2l6ZS5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRNaW5DbHVzdGVyU2l6ZSA9IGZ1bmN0aW9uKHNpemUpIHtcbiAgICB0aGlzLm1pbkNsdXN0ZXJTaXplXyA9IHNpemU7XG59O1xuXG5cbi8qKlxuICogRXh0ZW5kcyBhIGJvdW5kcyBvYmplY3QgYnkgdGhlIGdyaWQgc2l6ZS5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLkxhdExuZ0JvdW5kc30gYm91bmRzIFRoZSBib3VuZHMgdG8gZXh0ZW5kLlxuICogQHJldHVybiB7Z29vZ2xlLm1hcHMuTGF0TG5nQm91bmRzfSBUaGUgZXh0ZW5kZWQgYm91bmRzLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldEV4dGVuZGVkQm91bmRzID0gZnVuY3Rpb24oYm91bmRzKSB7XG4gICAgdmFyIHByb2plY3Rpb24gPSB0aGlzLmdldFByb2plY3Rpb24oKTtcblxuICAgIC8vIFR1cm4gdGhlIGJvdW5kcyBpbnRvIGxhdGxuZy5cbiAgICB2YXIgdHIgPSBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKGJvdW5kcy5nZXROb3J0aEVhc3QoKS5sYXQoKSxcbiAgICAgICAgYm91bmRzLmdldE5vcnRoRWFzdCgpLmxuZygpKTtcbiAgICB2YXIgYmwgPSBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKGJvdW5kcy5nZXRTb3V0aFdlc3QoKS5sYXQoKSxcbiAgICAgICAgYm91bmRzLmdldFNvdXRoV2VzdCgpLmxuZygpKTtcblxuICAgIC8vIENvbnZlcnQgdGhlIHBvaW50cyB0byBwaXhlbHMgYW5kIHRoZSBleHRlbmQgb3V0IGJ5IHRoZSBncmlkIHNpemUuXG4gICAgdmFyIHRyUGl4ID0gcHJvamVjdGlvbi5mcm9tTGF0TG5nVG9EaXZQaXhlbCh0cik7XG4gICAgdHJQaXgueCArPSB0aGlzLmdyaWRTaXplXztcbiAgICB0clBpeC55IC09IHRoaXMuZ3JpZFNpemVfO1xuXG4gICAgdmFyIGJsUGl4ID0gcHJvamVjdGlvbi5mcm9tTGF0TG5nVG9EaXZQaXhlbChibCk7XG4gICAgYmxQaXgueCAtPSB0aGlzLmdyaWRTaXplXztcbiAgICBibFBpeC55ICs9IHRoaXMuZ3JpZFNpemVfO1xuXG4gICAgLy8gQ29udmVydCB0aGUgcGl4ZWwgcG9pbnRzIGJhY2sgdG8gTGF0TG5nXG4gICAgdmFyIG5lID0gcHJvamVjdGlvbi5mcm9tRGl2UGl4ZWxUb0xhdExuZyh0clBpeCk7XG4gICAgdmFyIHN3ID0gcHJvamVjdGlvbi5mcm9tRGl2UGl4ZWxUb0xhdExuZyhibFBpeCk7XG5cbiAgICAvLyBFeHRlbmQgdGhlIGJvdW5kcyB0byBjb250YWluIHRoZSBuZXcgYm91bmRzLlxuICAgIGJvdW5kcy5leHRlbmQobmUpO1xuICAgIGJvdW5kcy5leHRlbmQoc3cpO1xuXG4gICAgcmV0dXJuIGJvdW5kcztcbn07XG5cblxuLyoqXG4gKiBEZXRlcm1pbnMgaWYgYSBtYXJrZXIgaXMgY29udGFpbmVkIGluIGEgYm91bmRzLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byBjaGVjay5cbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nQm91bmRzfSBib3VuZHMgVGhlIGJvdW5kcyB0byBjaGVjayBhZ2FpbnN0LlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgbWFya2VyIGlzIGluIHRoZSBib3VuZHMuXG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmlzTWFya2VySW5Cb3VuZHNfID0gZnVuY3Rpb24obWFya2VyLCBib3VuZHMpIHtcbiAgICByZXR1cm4gYm91bmRzLmNvbnRhaW5zKG1hcmtlci5nZXRQb3NpdGlvbigpKTtcbn07XG5cblxuLyoqXG4gKiBDbGVhcnMgYWxsIGNsdXN0ZXJzIGFuZCBtYXJrZXJzIGZyb20gdGhlIGNsdXN0ZXJlci5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5jbGVhck1hcmtlcnMgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnJlc2V0Vmlld3BvcnQodHJ1ZSk7XG5cbiAgICAvLyBTZXQgdGhlIG1hcmtlcnMgYSBlbXB0eSBhcnJheS5cbiAgICB0aGlzLm1hcmtlcnNfID0gW107XG59O1xuXG5cbi8qKlxuICogQ2xlYXJzIGFsbCBleGlzdGluZyBjbHVzdGVycyBhbmQgcmVjcmVhdGVzIHRoZW0uXG4gKiBAcGFyYW0ge2Jvb2xlYW59IG9wdF9oaWRlIFRvIGFsc28gaGlkZSB0aGUgbWFya2VyLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlc2V0Vmlld3BvcnQgPSBmdW5jdGlvbihvcHRfaGlkZSkge1xuICAgIC8vIFJlbW92ZSBhbGwgdGhlIGNsdXN0ZXJzXG4gICAgZm9yICh2YXIgaSA9IDAsIGNsdXN0ZXI7IGNsdXN0ZXIgPSB0aGlzLmNsdXN0ZXJzX1tpXTsgaSsrKSB7XG4gICAgICAgIGNsdXN0ZXIucmVtb3ZlKCk7XG4gICAgfVxuXG4gICAgLy8gUmVzZXQgdGhlIG1hcmtlcnMgdG8gbm90IGJlIGFkZGVkIGFuZCB0byBiZSBpbnZpc2libGUuXG4gICAgZm9yICh2YXIgaSA9IDAsIG1hcmtlcjsgbWFya2VyID0gdGhpcy5tYXJrZXJzX1tpXTsgaSsrKSB7XG4gICAgICAgIG1hcmtlci5pc0FkZGVkID0gZmFsc2U7XG4gICAgICAgIGlmIChvcHRfaGlkZSkge1xuICAgICAgICAgICAgbWFya2VyLnNldE1hcChudWxsKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHRoaXMuY2x1c3RlcnNfID0gW107XG59O1xuXG4vKipcbiAqXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVwYWludCA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBvbGRDbHVzdGVycyA9IHRoaXMuY2x1c3RlcnNfLnNsaWNlKCk7XG4gICAgdGhpcy5jbHVzdGVyc18ubGVuZ3RoID0gMDtcbiAgICB0aGlzLnJlc2V0Vmlld3BvcnQoKTtcbiAgICB0aGlzLnJlZHJhdygpO1xuXG4gICAgLy8gUmVtb3ZlIHRoZSBvbGQgY2x1c3RlcnMuXG4gICAgLy8gRG8gaXQgaW4gYSB0aW1lb3V0IHNvIHRoZSBvdGhlciBjbHVzdGVycyBoYXZlIGJlZW4gZHJhd24gZmlyc3QuXG4gICAgd2luZG93LnNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBjbHVzdGVyOyBjbHVzdGVyID0gb2xkQ2x1c3RlcnNbaV07IGkrKykge1xuICAgICAgICAgICAgY2x1c3Rlci5yZW1vdmUoKTtcbiAgICAgICAgfVxuICAgIH0sIDApO1xufTtcblxuXG4vKipcbiAqIFJlZHJhd3MgdGhlIGNsdXN0ZXJzLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlZHJhdyA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuY3JlYXRlQ2x1c3RlcnNfKCk7XG59O1xuXG5cbi8qKlxuICogQ2FsY3VsYXRlcyB0aGUgZGlzdGFuY2UgYmV0d2VlbiB0d28gbGF0bG5nIGxvY2F0aW9ucyBpbiBrbS5cbiAqIEBzZWUgaHR0cDovL3d3dy5tb3ZhYmxlLXR5cGUuY28udWsvc2NyaXB0cy9sYXRsb25nLmh0bWxcbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLkxhdExuZ30gcDEgVGhlIGZpcnN0IGxhdCBsbmcgcG9pbnQuXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLkxhdExuZ30gcDIgVGhlIHNlY29uZCBsYXQgbG5nIHBvaW50LlxuICogQHJldHVybiB7bnVtYmVyfSBUaGUgZGlzdGFuY2UgYmV0d2VlbiB0aGUgdHdvIHBvaW50cyBpbiBrbS5cbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZGlzdGFuY2VCZXR3ZWVuUG9pbnRzXyA9IGZ1bmN0aW9uKHAxLCBwMikge1xuICAgIGlmICghcDEgfHwgIXAyKSB7XG4gICAgICAgIHJldHVybiAwO1xuICAgIH1cblxuICAgIHZhciBSID0gNjM3MTsgLy8gUmFkaXVzIG9mIHRoZSBFYXJ0aCBpbiBrbVxuICAgIHZhciBkTGF0ID0gKHAyLmxhdCgpIC0gcDEubGF0KCkpICogTWF0aC5QSSAvIDE4MDtcbiAgICB2YXIgZExvbiA9IChwMi5sbmcoKSAtIHAxLmxuZygpKSAqIE1hdGguUEkgLyAxODA7XG4gICAgdmFyIGEgPSBNYXRoLnNpbihkTGF0IC8gMikgKiBNYXRoLnNpbihkTGF0IC8gMikgK1xuICAgICAgICBNYXRoLmNvcyhwMS5sYXQoKSAqIE1hdGguUEkgLyAxODApICogTWF0aC5jb3MocDIubGF0KCkgKiBNYXRoLlBJIC8gMTgwKSAqXG4gICAgICAgIE1hdGguc2luKGRMb24gLyAyKSAqIE1hdGguc2luKGRMb24gLyAyKTtcbiAgICB2YXIgYyA9IDIgKiBNYXRoLmF0YW4yKE1hdGguc3FydChhKSwgTWF0aC5zcXJ0KDEgLSBhKSk7XG4gICAgdmFyIGQgPSBSICogYztcbiAgICByZXR1cm4gZDtcbn07XG5cblxuLyoqXG4gKiBBZGQgYSBtYXJrZXIgdG8gYSBjbHVzdGVyLCBvciBjcmVhdGVzIGEgbmV3IGNsdXN0ZXIuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGFkZC5cbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuYWRkVG9DbG9zZXN0Q2x1c3Rlcl8gPSBmdW5jdGlvbihtYXJrZXIpIHtcbiAgICB2YXIgZGlzdGFuY2UgPSA0MDAwMDsgLy8gU29tZSBsYXJnZSBudW1iZXJcbiAgICB2YXIgY2x1c3RlclRvQWRkVG8gPSBudWxsO1xuICAgIHZhciBwb3MgPSBtYXJrZXIuZ2V0UG9zaXRpb24oKTtcbiAgICBmb3IgKHZhciBpID0gMCwgY2x1c3RlcjsgY2x1c3RlciA9IHRoaXMuY2x1c3RlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgdmFyIGNlbnRlciA9IGNsdXN0ZXIuZ2V0Q2VudGVyKCk7XG4gICAgICAgIGlmIChjZW50ZXIpIHtcbiAgICAgICAgICAgIHZhciBkID0gdGhpcy5kaXN0YW5jZUJldHdlZW5Qb2ludHNfKGNlbnRlciwgbWFya2VyLmdldFBvc2l0aW9uKCkpO1xuICAgICAgICAgICAgaWYgKGQgPCBkaXN0YW5jZSkge1xuICAgICAgICAgICAgICAgIGRpc3RhbmNlID0gZDtcbiAgICAgICAgICAgICAgICBjbHVzdGVyVG9BZGRUbyA9IGNsdXN0ZXI7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAoY2x1c3RlclRvQWRkVG8gJiYgY2x1c3RlclRvQWRkVG8uaXNNYXJrZXJJbkNsdXN0ZXJCb3VuZHMobWFya2VyKSkge1xuICAgICAgICBjbHVzdGVyVG9BZGRUby5hZGRNYXJrZXIobWFya2VyKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB2YXIgY2x1c3RlciA9IG5ldyBDbHVzdGVyKHRoaXMpO1xuICAgICAgICBjbHVzdGVyLmFkZE1hcmtlcihtYXJrZXIpO1xuICAgICAgICB0aGlzLmNsdXN0ZXJzXy5wdXNoKGNsdXN0ZXIpO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBDcmVhdGVzIHRoZSBjbHVzdGVycy5cbiAqXG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmNyZWF0ZUNsdXN0ZXJzXyA9IGZ1bmN0aW9uKCkge1xuICAgIGlmICghdGhpcy5yZWFkeV8pIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIC8vIEdldCBvdXIgY3VycmVudCBtYXAgdmlldyBib3VuZHMuXG4gICAgLy8gQ3JlYXRlIGEgbmV3IGJvdW5kcyBvYmplY3Qgc28gd2UgZG9uJ3QgYWZmZWN0IHRoZSBtYXAuXG4gICAgdmFyIG1hcEJvdW5kcyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHModGhpcy5tYXBfLmdldEJvdW5kcygpLmdldFNvdXRoV2VzdCgpLFxuICAgICAgICB0aGlzLm1hcF8uZ2V0Qm91bmRzKCkuZ2V0Tm9ydGhFYXN0KCkpO1xuICAgIHZhciBib3VuZHMgPSB0aGlzLmdldEV4dGVuZGVkQm91bmRzKG1hcEJvdW5kcyk7XG5cbiAgICBmb3IgKHZhciBpID0gMCwgbWFya2VyOyBtYXJrZXIgPSB0aGlzLm1hcmtlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgaWYgKCFtYXJrZXIuaXNBZGRlZCAmJiB0aGlzLmlzTWFya2VySW5Cb3VuZHNfKG1hcmtlciwgYm91bmRzKSkge1xuICAgICAgICAgICAgdGhpcy5hZGRUb0Nsb3Nlc3RDbHVzdGVyXyhtYXJrZXIpO1xuICAgICAgICB9XG4gICAgfVxufTtcblxuXG4vKipcbiAqIEEgY2x1c3RlciB0aGF0IGNvbnRhaW5zIG1hcmtlcnMuXG4gKlxuICogQHBhcmFtIHtNYXJrZXJDbHVzdGVyZXJ9IG1hcmtlckNsdXN0ZXJlciBUaGUgbWFya2VyY2x1c3RlcmVyIHRoYXQgdGhpc1xuICogICAgIGNsdXN0ZXIgaXMgYXNzb2NpYXRlZCB3aXRoLlxuICogQGNvbnN0cnVjdG9yXG4gKiBAaWdub3JlXG4gKi9cbmZ1bmN0aW9uIENsdXN0ZXIobWFya2VyQ2x1c3RlcmVyKSB7XG4gICAgdGhpcy5tYXJrZXJDbHVzdGVyZXJfID0gbWFya2VyQ2x1c3RlcmVyO1xuICAgIHRoaXMubWFwXyA9IG1hcmtlckNsdXN0ZXJlci5nZXRNYXAoKTtcbiAgICB0aGlzLmdyaWRTaXplXyA9IG1hcmtlckNsdXN0ZXJlci5nZXRHcmlkU2l6ZSgpO1xuICAgIHRoaXMubWluQ2x1c3RlclNpemVfID0gbWFya2VyQ2x1c3RlcmVyLmdldE1pbkNsdXN0ZXJTaXplKCk7XG4gICAgdGhpcy5hdmVyYWdlQ2VudGVyXyA9IG1hcmtlckNsdXN0ZXJlci5pc0F2ZXJhZ2VDZW50ZXIoKTtcbiAgICB0aGlzLmNlbnRlcl8gPSBudWxsO1xuICAgIHRoaXMubWFya2Vyc18gPSBbXTtcbiAgICB0aGlzLmJvdW5kc18gPSBudWxsO1xuICAgIHRoaXMuY2x1c3Rlckljb25fID0gbmV3IENsdXN0ZXJJY29uKHRoaXMsIG1hcmtlckNsdXN0ZXJlci5nZXRTdHlsZXMoKSxcbiAgICAgICAgbWFya2VyQ2x1c3RlcmVyLmdldEdyaWRTaXplKCkpO1xufVxuXG4vKipcbiAqIERldGVybWlucyBpZiBhIG1hcmtlciBpcyBhbHJlYWR5IGFkZGVkIHRvIHRoZSBjbHVzdGVyLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byBjaGVjay5cbiAqIEByZXR1cm4ge2Jvb2xlYW59IFRydWUgaWYgdGhlIG1hcmtlciBpcyBhbHJlYWR5IGFkZGVkLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5pc01hcmtlckFscmVhZHlBZGRlZCA9IGZ1bmN0aW9uKG1hcmtlcikge1xuICAgIGlmICh0aGlzLm1hcmtlcnNfLmluZGV4T2YpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMubWFya2Vyc18uaW5kZXhPZihtYXJrZXIpICE9IC0xO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBtOyBtID0gdGhpcy5tYXJrZXJzX1tpXTsgaSsrKSB7XG4gICAgICAgICAgICBpZiAobSA9PSBtYXJrZXIpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gZmFsc2U7XG59O1xuXG5cbi8qKlxuICogQWRkIGEgbWFya2VyIHRoZSBjbHVzdGVyLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byBhZGQuXG4gKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIHRoZSBtYXJrZXIgd2FzIGFkZGVkLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5hZGRNYXJrZXIgPSBmdW5jdGlvbihtYXJrZXIpIHtcbiAgICBpZiAodGhpcy5pc01hcmtlckFscmVhZHlBZGRlZChtYXJrZXIpKSB7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBpZiAoIXRoaXMuY2VudGVyXykge1xuICAgICAgICB0aGlzLmNlbnRlcl8gPSBtYXJrZXIuZ2V0UG9zaXRpb24oKTtcbiAgICAgICAgdGhpcy5jYWxjdWxhdGVCb3VuZHNfKCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKHRoaXMuYXZlcmFnZUNlbnRlcl8pIHtcbiAgICAgICAgICAgIHZhciBsID0gdGhpcy5tYXJrZXJzXy5sZW5ndGggKyAxO1xuICAgICAgICAgICAgdmFyIGxhdCA9ICh0aGlzLmNlbnRlcl8ubGF0KCkgKiAobC0xKSArIG1hcmtlci5nZXRQb3NpdGlvbigpLmxhdCgpKSAvIGw7XG4gICAgICAgICAgICB2YXIgbG5nID0gKHRoaXMuY2VudGVyXy5sbmcoKSAqIChsLTEpICsgbWFya2VyLmdldFBvc2l0aW9uKCkubG5nKCkpIC8gbDtcbiAgICAgICAgICAgIHRoaXMuY2VudGVyXyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcobGF0LCBsbmcpO1xuICAgICAgICAgICAgdGhpcy5jYWxjdWxhdGVCb3VuZHNfKCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBtYXJrZXIuaXNBZGRlZCA9IHRydWU7XG4gICAgdGhpcy5tYXJrZXJzXy5wdXNoKG1hcmtlcik7XG5cbiAgICB2YXIgbGVuID0gdGhpcy5tYXJrZXJzXy5sZW5ndGg7XG4gICAgaWYgKGxlbiA8IHRoaXMubWluQ2x1c3RlclNpemVfICYmIG1hcmtlci5nZXRNYXAoKSAhPSB0aGlzLm1hcF8pIHtcbiAgICAgICAgLy8gTWluIGNsdXN0ZXIgc2l6ZSBub3QgcmVhY2hlZCBzbyBzaG93IHRoZSBtYXJrZXIuXG4gICAgICAgIG1hcmtlci5zZXRNYXAodGhpcy5tYXBfKTtcbiAgICB9XG5cbiAgICBpZiAobGVuID09IHRoaXMubWluQ2x1c3RlclNpemVfKSB7XG4gICAgICAgIC8vIEhpZGUgdGhlIG1hcmtlcnMgdGhhdCB3ZXJlIHNob3dpbmcuXG4gICAgICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbGVuOyBpKyspIHtcbiAgICAgICAgICAgIHRoaXMubWFya2Vyc19baV0uc2V0TWFwKG51bGwpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgaWYgKGxlbiA+PSB0aGlzLm1pbkNsdXN0ZXJTaXplXykge1xuICAgICAgICBtYXJrZXIuc2V0TWFwKG51bGwpO1xuICAgIH1cblxuICAgIHRoaXMudXBkYXRlSWNvbigpO1xuICAgIHJldHVybiB0cnVlO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIG1hcmtlciBjbHVzdGVyZXIgdGhhdCB0aGUgY2x1c3RlciBpcyBhc3NvY2lhdGVkIHdpdGguXG4gKlxuICogQHJldHVybiB7TWFya2VyQ2x1c3RlcmVyfSBUaGUgYXNzb2NpYXRlZCBtYXJrZXIgY2x1c3RlcmVyLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRNYXJrZXJDbHVzdGVyZXIgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXJrZXJDbHVzdGVyZXJfO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIGJvdW5kcyBvZiB0aGUgY2x1c3Rlci5cbiAqXG4gKiBAcmV0dXJuIHtnb29nbGUubWFwcy5MYXRMbmdCb3VuZHN9IHRoZSBjbHVzdGVyIGJvdW5kcy5cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUuZ2V0Qm91bmRzID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGJvdW5kcyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHModGhpcy5jZW50ZXJfLCB0aGlzLmNlbnRlcl8pO1xuICAgIHZhciBtYXJrZXJzID0gdGhpcy5nZXRNYXJrZXJzKCk7XG4gICAgZm9yICh2YXIgaSA9IDAsIG1hcmtlcjsgbWFya2VyID0gbWFya2Vyc1tpXTsgaSsrKSB7XG4gICAgICAgIGJvdW5kcy5leHRlbmQobWFya2VyLmdldFBvc2l0aW9uKCkpO1xuICAgIH1cbiAgICByZXR1cm4gYm91bmRzO1xufTtcblxuXG4vKipcbiAqIFJlbW92ZXMgdGhlIGNsdXN0ZXJcbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUucmVtb3ZlID0gZnVuY3Rpb24oKSB7XG4gICAgdGhpcy5jbHVzdGVySWNvbl8ucmVtb3ZlKCk7XG4gICAgdGhpcy5tYXJrZXJzXy5sZW5ndGggPSAwO1xuICAgIGRlbGV0ZSB0aGlzLm1hcmtlcnNfO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIG51bWJlciBvZiBtYXJrZXJzIGluIHRoZSBjbHVzdGVyLlxuICpcbiAqIEByZXR1cm4ge251bWJlcn0gVGhlIG51bWJlciBvZiBtYXJrZXJzIGluIHRoZSBjbHVzdGVyLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRTaXplID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWFya2Vyc18ubGVuZ3RoO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgYSBsaXN0IG9mIHRoZSBtYXJrZXJzIGluIHRoZSBjbHVzdGVyLlxuICpcbiAqIEByZXR1cm4ge0FycmF5Ljxnb29nbGUubWFwcy5NYXJrZXI+fSBUaGUgbWFya2VycyBpbiB0aGUgY2x1c3Rlci5cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUuZ2V0TWFya2VycyA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLm1hcmtlcnNfO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIGNlbnRlciBvZiB0aGUgY2x1c3Rlci5cbiAqXG4gKiBAcmV0dXJuIHtnb29nbGUubWFwcy5MYXRMbmd9IFRoZSBjbHVzdGVyIGNlbnRlci5cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUuZ2V0Q2VudGVyID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMuY2VudGVyXztcbn07XG5cblxuLyoqXG4gKiBDYWxjdWxhdGVkIHRoZSBleHRlbmRlZCBib3VuZHMgb2YgdGhlIGNsdXN0ZXIgd2l0aCB0aGUgZ3JpZC5cbiAqXG4gKiBAcHJpdmF0ZVxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5jYWxjdWxhdGVCb3VuZHNfID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGJvdW5kcyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHModGhpcy5jZW50ZXJfLCB0aGlzLmNlbnRlcl8pO1xuICAgIHRoaXMuYm91bmRzXyA9IHRoaXMubWFya2VyQ2x1c3RlcmVyXy5nZXRFeHRlbmRlZEJvdW5kcyhib3VuZHMpO1xufTtcblxuXG4vKipcbiAqIERldGVybWluZXMgaWYgYSBtYXJrZXIgbGllcyBpbiB0aGUgY2x1c3RlcnMgYm91bmRzLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFya2VyfSBtYXJrZXIgVGhlIG1hcmtlciB0byBjaGVjay5cbiAqIEByZXR1cm4ge2Jvb2xlYW59IFRydWUgaWYgdGhlIG1hcmtlciBsaWVzIGluIHRoZSBib3VuZHMuXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmlzTWFya2VySW5DbHVzdGVyQm91bmRzID0gZnVuY3Rpb24obWFya2VyKSB7XG4gICAgcmV0dXJuIHRoaXMuYm91bmRzXy5jb250YWlucyhtYXJrZXIuZ2V0UG9zaXRpb24oKSk7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgbWFwIHRoYXQgdGhlIGNsdXN0ZXIgaXMgYXNzb2NpYXRlZCB3aXRoLlxuICpcbiAqIEByZXR1cm4ge2dvb2dsZS5tYXBzLk1hcH0gVGhlIG1hcC5cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUuZ2V0TWFwID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWFwXztcbn07XG5cblxuLyoqXG4gKiBVcGRhdGVzIHRoZSBjbHVzdGVyIGljb25cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUudXBkYXRlSWNvbiA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciB6b29tID0gdGhpcy5tYXBfLmdldFpvb20oKTtcbiAgICB2YXIgbXogPSB0aGlzLm1hcmtlckNsdXN0ZXJlcl8uZ2V0TWF4Wm9vbSgpO1xuXG4gICAgaWYgKG16ICYmIHpvb20gPiBteikge1xuICAgICAgICAvLyBUaGUgem9vbSBpcyBncmVhdGVyIHRoYW4gb3VyIG1heCB6b29tIHNvIHNob3cgYWxsIHRoZSBtYXJrZXJzIGluIGNsdXN0ZXIuXG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBtYXJrZXI7IG1hcmtlciA9IHRoaXMubWFya2Vyc19baV07IGkrKykge1xuICAgICAgICAgICAgbWFya2VyLnNldE1hcCh0aGlzLm1hcF8pO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBpZiAodGhpcy5tYXJrZXJzXy5sZW5ndGggPCB0aGlzLm1pbkNsdXN0ZXJTaXplXykge1xuICAgICAgICAvLyBNaW4gY2x1c3RlciBzaXplIG5vdCB5ZXQgcmVhY2hlZC5cbiAgICAgICAgdGhpcy5jbHVzdGVySWNvbl8uaGlkZSgpO1xuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgdmFyIG51bVN0eWxlcyA9IHRoaXMubWFya2VyQ2x1c3RlcmVyXy5nZXRTdHlsZXMoKS5sZW5ndGg7XG4gICAgdmFyIHN1bXMgPSB0aGlzLm1hcmtlckNsdXN0ZXJlcl8uZ2V0Q2FsY3VsYXRvcigpKHRoaXMubWFya2Vyc18sIG51bVN0eWxlcyk7XG4gICAgdGhpcy5jbHVzdGVySWNvbl8uc2V0Q2VudGVyKHRoaXMuY2VudGVyXyk7XG4gICAgdGhpcy5jbHVzdGVySWNvbl8uc2V0U3VtcyhzdW1zKTtcbiAgICB0aGlzLmNsdXN0ZXJJY29uXy5zaG93KCk7XG59O1xuXG5cbi8qKlxuICogQSBjbHVzdGVyIGljb25cbiAqXG4gKiBAcGFyYW0ge0NsdXN0ZXJ9IGNsdXN0ZXIgVGhlIGNsdXN0ZXIgdG8gYmUgYXNzb2NpYXRlZCB3aXRoLlxuICogQHBhcmFtIHtPYmplY3R9IHN0eWxlcyBBbiBvYmplY3QgdGhhdCBoYXMgc3R5bGUgcHJvcGVydGllczpcbiAqICAgICAndXJsJzogKHN0cmluZykgVGhlIGltYWdlIHVybC5cbiAqICAgICAnaGVpZ2h0JzogKG51bWJlcikgVGhlIGltYWdlIGhlaWdodC5cbiAqICAgICAnd2lkdGgnOiAobnVtYmVyKSBUaGUgaW1hZ2Ugd2lkdGguXG4gKiAgICAgJ2FuY2hvcic6IChBcnJheSkgVGhlIGFuY2hvciBwb3NpdGlvbiBvZiB0aGUgbGFiZWwgdGV4dC5cbiAqICAgICAndGV4dENvbG9yJzogKHN0cmluZykgVGhlIHRleHQgY29sb3IuXG4gKiAgICAgJ3RleHRTaXplJzogKG51bWJlcikgVGhlIHRleHQgc2l6ZS5cbiAqICAgICAnYmFja2dyb3VuZFBvc2l0aW9uOiAoc3RyaW5nKSBUaGUgYmFja2dyb3VuZCBwb3N0aXRpb24geCwgeS5cbiAqIEBwYXJhbSB7bnVtYmVyPX0gb3B0X3BhZGRpbmcgT3B0aW9uYWwgcGFkZGluZyB0byBhcHBseSB0byB0aGUgY2x1c3RlciBpY29uLlxuICogQGNvbnN0cnVjdG9yXG4gKiBAZXh0ZW5kcyBnb29nbGUubWFwcy5PdmVybGF5Vmlld1xuICogQGlnbm9yZVxuICovXG5mdW5jdGlvbiBDbHVzdGVySWNvbihjbHVzdGVyLCBzdHlsZXMsIG9wdF9wYWRkaW5nKSB7XG4gICAgY2x1c3Rlci5nZXRNYXJrZXJDbHVzdGVyZXIoKS5leHRlbmQoQ2x1c3Rlckljb24sIGdvb2dsZS5tYXBzLk92ZXJsYXlWaWV3KTtcblxuICAgIHRoaXMuc3R5bGVzXyA9IHN0eWxlcztcbiAgICB0aGlzLnBhZGRpbmdfID0gb3B0X3BhZGRpbmcgfHwgMDtcbiAgICB0aGlzLmNsdXN0ZXJfID0gY2x1c3RlcjtcbiAgICB0aGlzLmNlbnRlcl8gPSBudWxsO1xuICAgIHRoaXMubWFwXyA9IGNsdXN0ZXIuZ2V0TWFwKCk7XG4gICAgdGhpcy5kaXZfID0gbnVsbDtcbiAgICB0aGlzLnN1bXNfID0gbnVsbDtcbiAgICB0aGlzLnZpc2libGVfID0gZmFsc2U7XG5cbiAgICB0aGlzLnNldE1hcCh0aGlzLm1hcF8pO1xufVxuXG5cbi8qKlxuICogVHJpZ2dlcnMgdGhlIGNsdXN0ZXJjbGljayBldmVudCBhbmQgem9vbSdzIGlmIHRoZSBvcHRpb24gaXMgc2V0LlxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUudHJpZ2dlckNsdXN0ZXJDbGljayA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBtYXJrZXJDbHVzdGVyZXIgPSB0aGlzLmNsdXN0ZXJfLmdldE1hcmtlckNsdXN0ZXJlcigpO1xuXG4gICAgLy8gVHJpZ2dlciB0aGUgY2x1c3RlcmNsaWNrIGV2ZW50LlxuICAgIGdvb2dsZS5tYXBzLmV2ZW50LnRyaWdnZXIobWFya2VyQ2x1c3RlcmVyLm1hcF8sICdjbHVzdGVyY2xpY2snLCB0aGlzLmNsdXN0ZXJfKTtcblxuICAgIGlmIChtYXJrZXJDbHVzdGVyZXIuaXNab29tT25DbGljaygpKSB7XG4gICAgICAgIC8vIFpvb20gaW50byB0aGUgY2x1c3Rlci5cbiAgICAgICAgdGhpcy5tYXBfLmZpdEJvdW5kcyh0aGlzLmNsdXN0ZXJfLmdldEJvdW5kcygpKTtcbiAgICB9XG59O1xuXG5cbi8qKlxuICogQWRkaW5nIHRoZSBjbHVzdGVyIGljb24gdG8gdGhlIGRvbS5cbiAqIEBpZ25vcmVcbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLm9uQWRkID0gZnVuY3Rpb24oKSB7XG4gICAgdGhpcy5kaXZfID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnRElWJyk7XG4gICAgaWYgKHRoaXMudmlzaWJsZV8pIHtcbiAgICAgICAgdmFyIHBvcyA9IHRoaXMuZ2V0UG9zRnJvbUxhdExuZ18odGhpcy5jZW50ZXJfKTtcbiAgICAgICAgdGhpcy5kaXZfLnN0eWxlLmNzc1RleHQgPSB0aGlzLmNyZWF0ZUNzcyhwb3MpO1xuICAgICAgICB0aGlzLmRpdl8uaW5uZXJIVE1MID0gdGhpcy5zdW1zXy50ZXh0O1xuICAgIH1cblxuICAgIHZhciBwYW5lcyA9IHRoaXMuZ2V0UGFuZXMoKTtcbiAgICBwYW5lcy5vdmVybGF5TW91c2VUYXJnZXQuYXBwZW5kQ2hpbGQodGhpcy5kaXZfKTtcblxuICAgIHZhciB0aGF0ID0gdGhpcztcbiAgICBnb29nbGUubWFwcy5ldmVudC5hZGREb21MaXN0ZW5lcih0aGlzLmRpdl8sICdjbGljaycsIGZ1bmN0aW9uKCkge1xuICAgICAgICB0aGF0LnRyaWdnZXJDbHVzdGVyQ2xpY2soKTtcbiAgICB9KTtcbn07XG5cblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBwb3NpdGlvbiB0byBwbGFjZSB0aGUgZGl2IGRlbmRpbmcgb24gdGhlIGxhdGxuZy5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLkxhdExuZ30gbGF0bG5nIFRoZSBwb3NpdGlvbiBpbiBsYXRsbmcuXG4gKiBAcmV0dXJuIHtnb29nbGUubWFwcy5Qb2ludH0gVGhlIHBvc2l0aW9uIGluIHBpeGVscy5cbiAqIEBwcml2YXRlXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5nZXRQb3NGcm9tTGF0TG5nXyA9IGZ1bmN0aW9uKGxhdGxuZykge1xuICAgIHZhciBwb3MgPSB0aGlzLmdldFByb2plY3Rpb24oKS5mcm9tTGF0TG5nVG9EaXZQaXhlbChsYXRsbmcpO1xuICAgIHBvcy54IC09IHBhcnNlSW50KHRoaXMud2lkdGhfIC8gMiwgMTApO1xuICAgIHBvcy55IC09IHBhcnNlSW50KHRoaXMuaGVpZ2h0XyAvIDIsIDEwKTtcbiAgICByZXR1cm4gcG9zO1xufTtcblxuXG4vKipcbiAqIERyYXcgdGhlIGljb24uXG4gKiBAaWdub3JlXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5kcmF3ID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHRoaXMudmlzaWJsZV8pIHtcbiAgICAgICAgdmFyIHBvcyA9IHRoaXMuZ2V0UG9zRnJvbUxhdExuZ18odGhpcy5jZW50ZXJfKTtcbiAgICAgICAgdGhpcy5kaXZfLnN0eWxlLnRvcCA9IHBvcy55ICsgJ3B4JztcbiAgICAgICAgdGhpcy5kaXZfLnN0eWxlLmxlZnQgPSBwb3MueCArICdweCc7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIEhpZGUgdGhlIGljb24uXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5oaWRlID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHRoaXMuZGl2Xykge1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG4gICAgdGhpcy52aXNpYmxlXyA9IGZhbHNlO1xufTtcblxuXG4vKipcbiAqIFBvc2l0aW9uIGFuZCBzaG93IHRoZSBpY29uLlxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUuc2hvdyA9IGZ1bmN0aW9uKCkge1xuICAgIGlmICh0aGlzLmRpdl8pIHtcbiAgICAgICAgdmFyIHBvcyA9IHRoaXMuZ2V0UG9zRnJvbUxhdExuZ18odGhpcy5jZW50ZXJfKTtcbiAgICAgICAgdGhpcy5kaXZfLnN0eWxlLmNzc1RleHQgPSB0aGlzLmNyZWF0ZUNzcyhwb3MpO1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUuZGlzcGxheSA9ICcnO1xuICAgIH1cbiAgICB0aGlzLnZpc2libGVfID0gdHJ1ZTtcbn07XG5cblxuLyoqXG4gKiBSZW1vdmUgdGhlIGljb24gZnJvbSB0aGUgbWFwXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5yZW1vdmUgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnNldE1hcChudWxsKTtcbn07XG5cblxuLyoqXG4gKiBJbXBsZW1lbnRhdGlvbiBvZiB0aGUgb25SZW1vdmUgaW50ZXJmYWNlLlxuICogQGlnbm9yZVxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUub25SZW1vdmUgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAodGhpcy5kaXZfICYmIHRoaXMuZGl2Xy5wYXJlbnROb2RlKSB7XG4gICAgICAgIHRoaXMuaGlkZSgpO1xuICAgICAgICB0aGlzLmRpdl8ucGFyZW50Tm9kZS5yZW1vdmVDaGlsZCh0aGlzLmRpdl8pO1xuICAgICAgICB0aGlzLmRpdl8gPSBudWxsO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBTZXQgdGhlIHN1bXMgb2YgdGhlIGljb24uXG4gKlxuICogQHBhcmFtIHtPYmplY3R9IHN1bXMgVGhlIHN1bXMgY29udGFpbmluZzpcbiAqICAgJ3RleHQnOiAoc3RyaW5nKSBUaGUgdGV4dCB0byBkaXNwbGF5IGluIHRoZSBpY29uLlxuICogICAnaW5kZXgnOiAobnVtYmVyKSBUaGUgc3R5bGUgaW5kZXggb2YgdGhlIGljb24uXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5zZXRTdW1zID0gZnVuY3Rpb24oc3Vtcykge1xuICAgIHRoaXMuc3Vtc18gPSBzdW1zO1xuICAgIHRoaXMudGV4dF8gPSBzdW1zLnRleHQ7XG4gICAgdGhpcy5pbmRleF8gPSBzdW1zLmluZGV4O1xuICAgIGlmICh0aGlzLmRpdl8pIHtcbiAgICAgICAgdGhpcy5kaXZfLmlubmVySFRNTCA9IHN1bXMudGV4dDtcbiAgICB9XG5cbiAgICB0aGlzLnVzZVN0eWxlKCk7XG59O1xuXG5cbi8qKlxuICogU2V0cyB0aGUgaWNvbiB0byB0aGUgdGhlIHN0eWxlcy5cbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLnVzZVN0eWxlID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGluZGV4ID0gTWF0aC5tYXgoMCwgdGhpcy5zdW1zXy5pbmRleCAtIDEpO1xuICAgIGluZGV4ID0gTWF0aC5taW4odGhpcy5zdHlsZXNfLmxlbmd0aCAtIDEsIGluZGV4KTtcbiAgICB2YXIgc3R5bGUgPSB0aGlzLnN0eWxlc19baW5kZXhdO1xuICAgIHRoaXMudXJsXyA9IHN0eWxlWyd1cmwnXTtcbiAgICB0aGlzLmhlaWdodF8gPSBzdHlsZVsnaGVpZ2h0J107XG4gICAgdGhpcy53aWR0aF8gPSBzdHlsZVsnd2lkdGgnXTtcbiAgICB0aGlzLnRleHRDb2xvcl8gPSBzdHlsZVsndGV4dENvbG9yJ107XG4gICAgdGhpcy5hbmNob3JfID0gc3R5bGVbJ2FuY2hvciddO1xuICAgIHRoaXMudGV4dFNpemVfID0gc3R5bGVbJ3RleHRTaXplJ107XG4gICAgdGhpcy5iYWNrZ3JvdW5kUG9zaXRpb25fID0gc3R5bGVbJ2JhY2tncm91bmRQb3NpdGlvbiddO1xufTtcblxuXG4vKipcbiAqIFNldHMgdGhlIGNlbnRlciBvZiB0aGUgaWNvbi5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLkxhdExuZ30gY2VudGVyIFRoZSBsYXRsbmcgdG8gc2V0IGFzIHRoZSBjZW50ZXIuXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5zZXRDZW50ZXIgPSBmdW5jdGlvbihjZW50ZXIpIHtcbiAgICB0aGlzLmNlbnRlcl8gPSBjZW50ZXI7XG59O1xuXG5cbi8qKlxuICogQ3JlYXRlIHRoZSBjc3MgdGV4dCBiYXNlZCBvbiB0aGUgcG9zaXRpb24gb2YgdGhlIGljb24uXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5Qb2ludH0gcG9zIFRoZSBwb3NpdGlvbi5cbiAqIEByZXR1cm4ge3N0cmluZ30gVGhlIGNzcyBzdHlsZSB0ZXh0LlxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUuY3JlYXRlQ3NzID0gZnVuY3Rpb24ocG9zKSB7XG4gICAgdmFyIHN0eWxlID0gW107XG4gICAgc3R5bGUucHVzaCgnYmFja2dyb3VuZC1pbWFnZTp1cmwoJyArIHRoaXMudXJsXyArICcpOycpO1xuICAgIHZhciBiYWNrZ3JvdW5kUG9zaXRpb24gPSB0aGlzLmJhY2tncm91bmRQb3NpdGlvbl8gPyB0aGlzLmJhY2tncm91bmRQb3NpdGlvbl8gOiAnMCAwJztcbiAgICBzdHlsZS5wdXNoKCdiYWNrZ3JvdW5kLXBvc2l0aW9uOicgKyBiYWNrZ3JvdW5kUG9zaXRpb24gKyAnOycpO1xuXG4gICAgaWYgKHR5cGVvZiB0aGlzLmFuY2hvcl8gPT09ICdvYmplY3QnKSB7XG4gICAgICAgIGlmICh0eXBlb2YgdGhpcy5hbmNob3JfWzBdID09PSAnbnVtYmVyJyAmJiB0aGlzLmFuY2hvcl9bMF0gPiAwICYmXG4gICAgICAgICAgICB0aGlzLmFuY2hvcl9bMF0gPCB0aGlzLmhlaWdodF8pIHtcbiAgICAgICAgICAgIHN0eWxlLnB1c2goJ2hlaWdodDonICsgKHRoaXMuaGVpZ2h0XyAtIHRoaXMuYW5jaG9yX1swXSkgK1xuICAgICAgICAgICAgICAgICdweDsgcGFkZGluZy10b3A6JyArIHRoaXMuYW5jaG9yX1swXSArICdweDsnKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHN0eWxlLnB1c2goJ2hlaWdodDonICsgdGhpcy5oZWlnaHRfICsgJ3B4OyBsaW5lLWhlaWdodDonICsgdGhpcy5oZWlnaHRfICtcbiAgICAgICAgICAgICAgICAncHg7Jyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR5cGVvZiB0aGlzLmFuY2hvcl9bMV0gPT09ICdudW1iZXInICYmIHRoaXMuYW5jaG9yX1sxXSA+IDAgJiZcbiAgICAgICAgICAgIHRoaXMuYW5jaG9yX1sxXSA8IHRoaXMud2lkdGhfKSB7XG4gICAgICAgICAgICBzdHlsZS5wdXNoKCd3aWR0aDonICsgKHRoaXMud2lkdGhfIC0gdGhpcy5hbmNob3JfWzFdKSArXG4gICAgICAgICAgICAgICAgJ3B4OyBwYWRkaW5nLWxlZnQ6JyArIHRoaXMuYW5jaG9yX1sxXSArICdweDsnKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHN0eWxlLnB1c2goJ3dpZHRoOicgKyB0aGlzLndpZHRoXyArICdweDsgdGV4dC1hbGlnbjpjZW50ZXI7Jyk7XG4gICAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgICBzdHlsZS5wdXNoKCdoZWlnaHQ6JyArIHRoaXMuaGVpZ2h0XyArICdweDsgbGluZS1oZWlnaHQ6JyArXG4gICAgICAgICAgICB0aGlzLmhlaWdodF8gKyAncHg7IHdpZHRoOicgKyB0aGlzLndpZHRoXyArICdweDsgdGV4dC1hbGlnbjpjZW50ZXI7Jyk7XG4gICAgfVxuXG4gICAgdmFyIHR4dENvbG9yID0gdGhpcy50ZXh0Q29sb3JfID8gdGhpcy50ZXh0Q29sb3JfIDogJ2JsYWNrJztcbiAgICB2YXIgdHh0U2l6ZSA9IHRoaXMudGV4dFNpemVfID8gdGhpcy50ZXh0U2l6ZV8gOiAxMTtcblxuICAgIHN0eWxlLnB1c2goJ2N1cnNvcjpwb2ludGVyOyB0b3A6JyArIHBvcy55ICsgJ3B4OyBsZWZ0OicgK1xuICAgICAgICBwb3MueCArICdweDsgY29sb3I6JyArIHR4dENvbG9yICsgJzsgcG9zaXRpb246YWJzb2x1dGU7IGZvbnQtc2l6ZTonICtcbiAgICAgICAgdHh0U2l6ZSArICdweDsgZm9udC1mYW1pbHk6QXJpYWwsc2Fucy1zZXJpZjsgZm9udC13ZWlnaHQ6Ym9sZCcpO1xuICAgIHJldHVybiBzdHlsZS5qb2luKCcnKTtcbn07XG5cblxuLy8gRXhwb3J0IFN5bWJvbHMgZm9yIENsb3N1cmVcbi8vIElmIHlvdSBhcmUgbm90IGdvaW5nIHRvIGNvbXBpbGUgd2l0aCBjbG9zdXJlIHRoZW4geW91IGNhbiByZW1vdmUgdGhlXG4vLyBjb2RlIGJlbG93Llxud2luZG93WydNYXJrZXJDbHVzdGVyZXInXSA9IE1hcmtlckNsdXN0ZXJlcjtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2FkZE1hcmtlciddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5hZGRNYXJrZXI7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydhZGRNYXJrZXJzJ10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmFkZE1hcmtlcnM7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydjbGVhck1hcmtlcnMnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5jbGVhck1hcmtlcnM7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydmaXRNYXBUb01hcmtlcnMnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5maXRNYXBUb01hcmtlcnM7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydnZXRDYWxjdWxhdG9yJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0Q2FsY3VsYXRvcjtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldEdyaWRTaXplJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0R3JpZFNpemU7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydnZXRFeHRlbmRlZEJvdW5kcyddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldEV4dGVuZGVkQm91bmRzO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0TWFwJ10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldE1hcDtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldE1hcmtlcnMnXSA9IE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0TWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldE1heFpvb20nXSA9IE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0TWF4Wm9vbTtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldFN0eWxlcyddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRTdHlsZXM7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydnZXRUb3RhbENsdXN0ZXJzJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0VG90YWxDbHVzdGVycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldFRvdGFsTWFya2VycyddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldFRvdGFsTWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3JlZHJhdyddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZWRyYXc7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydyZW1vdmVNYXJrZXInXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZW1vdmVNYXJrZXI7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydyZW1vdmVNYXJrZXJzJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVtb3ZlTWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3Jlc2V0Vmlld3BvcnQnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZXNldFZpZXdwb3J0O1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsncmVwYWludCddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlcGFpbnQ7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydzZXRDYWxjdWxhdG9yJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0Q2FsY3VsYXRvcjtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3NldEdyaWRTaXplJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0R3JpZFNpemU7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydzZXRNYXhab29tJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0TWF4Wm9vbTtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ29uQWRkJ10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLm9uQWRkO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZHJhdyddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5kcmF3O1xuXG5DbHVzdGVyLnByb3RvdHlwZVsnZ2V0Q2VudGVyJ10gPSBDbHVzdGVyLnByb3RvdHlwZS5nZXRDZW50ZXI7XG5DbHVzdGVyLnByb3RvdHlwZVsnZ2V0U2l6ZSddID0gQ2x1c3Rlci5wcm90b3R5cGUuZ2V0U2l6ZTtcbkNsdXN0ZXIucHJvdG90eXBlWydnZXRNYXJrZXJzJ10gPSBDbHVzdGVyLnByb3RvdHlwZS5nZXRNYXJrZXJzO1xuXG5DbHVzdGVySWNvbi5wcm90b3R5cGVbJ29uQWRkJ10gPSBDbHVzdGVySWNvbi5wcm90b3R5cGUub25BZGQ7XG5DbHVzdGVySWNvbi5wcm90b3R5cGVbJ2RyYXcnXSA9IENsdXN0ZXJJY29uLnByb3RvdHlwZS5kcmF3O1xuQ2x1c3Rlckljb24ucHJvdG90eXBlWydvblJlbW92ZSddID0gQ2x1c3Rlckljb24ucHJvdG90eXBlLm9uUmVtb3ZlO1xuXG5PYmplY3Qua2V5cyA9IE9iamVjdC5rZXlzIHx8IGZ1bmN0aW9uKG8pIHtcbiAgICAgICAgdmFyIHJlc3VsdCA9IFtdO1xuICAgICAgICBmb3IodmFyIG5hbWUgaW4gbykge1xuICAgICAgICAgICAgaWYgKG8uaGFzT3duUHJvcGVydHkobmFtZSkpXG4gICAgICAgICAgICAgICAgcmVzdWx0LnB1c2gobmFtZSk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHJlc3VsdDtcbiAgICB9OyIsInZhciB3cHNlb19kaXJlY3Rpb25zID0gW107XG52YXIgd3BzZW9fbWFwcyA9IFtdO1xudmFyIG1hcmtlcnMgPSBuZXcgT2JqZWN0KCk7XG5cbnZhciB3cHNlb19kaXJlY3Rpb25zID0gW107XG52YXIgd3BzZW9fbWFwcyA9IFtdO1xudmFyIG1hcmtlcnMgPSBuZXcgT2JqZWN0KCk7XG5cbndpbmRvdy53cHNlb19zaG93X21hcCA9IGZ1bmN0aW9uIHdwc2VvX3Nob3dfbWFwKGxvY2F0aW9uX2RhdGEsIGNvdW50ZXIsIGNlbnRlcl9sYXQsIGNlbnRlcl9sb25nLCB6b29tLCBtYXBfc3R5bGUsIHNjcm9sbGFibGUsIGRyYWdnYWJsZSwgZGVmYXVsdF9zaG93X2luZm93aW5kb3csIGlzX2FkbWluLCBtYXJrZXJfY2x1c3RlcmluZykge1xuICAgIHZhciBib3VuZHMgPSBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nQm91bmRzKCk7XG4gICAgdmFyIGNlbnRlciA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcoY2VudGVyX2xhdCwgY2VudGVyX2xvbmcpO1xuICAgIHZhciBtb2JpbGVCcmVha3BvaW50ID0gNDgwO1xuICAgIG1hcmtlcnNbY291bnRlcl0gPSBbXTtcblxuICAgIHZhciB3cHNlb19tYXBfb3B0aW9ucyA9IHtcbiAgICAgICAgem9vbTogem9vbSxcbiAgICAgICAgbWluWm9vbTogMSxcbiAgICAgICAgbWFwVHlwZUNvbnRyb2w6IHRydWUsXG4gICAgICAgIHpvb21Db250cm9sOiBzY3JvbGxhYmxlLFxuICAgICAgICBzdHJlZXRWaWV3Q29udHJvbDogdHJ1ZSxcbiAgICAgICAgbWFwVHlwZUlkOiBnb29nbGUubWFwcy5NYXBUeXBlSWRbbWFwX3N0eWxlLnRvVXBwZXJDYXNlKCldLFxuICAgICAgICBzY3JvbGx3aGVlbDogc2Nyb2xsYWJsZSAmJiB3aW5kb3cuaW5uZXJXaWR0aCA+IG1vYmlsZUJyZWFrcG9pbnRcbiAgICB9O1xuXG4gICAgLy8gZ2VzdHVyZUhhbmRsaW5nIHNob3VsZCBvbmx5IGJlIHNldCBvbiBkZXZpY2VzIHRoYXQgc3VwcG9ydCB0b3VjaC5cbiAgICBpZiAoY2hlY2tGb3JUb3VjaCgpKSB7XG4gICAgICAgIHdwc2VvX21hcF9vcHRpb25zLmdlc3R1cmVIYW5kbGluZyA9IGRyYWdnYWJsZSA/ICdhdXRvJyA6ICdub25lJztcbiAgICB9IGVsc2Uge1xuICAgICAgICB3cHNlb19tYXBfb3B0aW9ucy5kcmFnZ2FibGUgPSBkcmFnZ2FibGU7XG4gICAgfVxuXG4gICAgLy8gU2V0IGNlbnRlclxuICAgIGlmICh6b29tID09IC0xKSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbG9jYXRpb25fZGF0YS5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgdmFyIGxhdExvbmcgPSBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKGxvY2F0aW9uX2RhdGFbaV1bXCJsYXRcIl0sIGxvY2F0aW9uX2RhdGFbaV1bXCJsb25nXCJdKTtcbiAgICAgICAgICAgIGJvdW5kcy5leHRlbmQobGF0TG9uZyk7XG4gICAgICAgIH1cblxuICAgICAgICBjZW50ZXIgPSBib3VuZHMuZ2V0Q2VudGVyKCk7XG4gICAgfVxuICAgIHdwc2VvX21hcF9vcHRpb25zLmNlbnRlciA9IGNlbnRlcjtcblxuICAgIHRyeSB7XG5cdCAgICB2YXIgbWFwID0gbmV3IGdvb2dsZS5tYXBzLk1hcCggZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoIFwibWFwX2NhbnZhc1wiICsgKCBjb3VudGVyICE9IDAgPyAnXycgKyBjb3VudGVyIDogJycgKSApLCB3cHNlb19tYXBfb3B0aW9ucyApO1xuICAgIH1cbiAgICBjYXRjaCggZXJyb3IgKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBpZiAoem9vbSA9PT0gLTEpIHtcbiAgICAgICAgbWFwLmZpdEJvdW5kcyhib3VuZHMpO1xuICAgIH1cblxuICAgIC8vIFNldCBtYXJrZXJzICsgaW5mb1xuICAgIHZhciBpbmZvV2luZG93ID0gbmV3IGdvb2dsZS5tYXBzLkluZm9XaW5kb3coe1xuICAgICAgICBjb250ZW50OiBpbmZvV2luZG93SFRNTFxuICAgIH0pO1xuXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCBsb2NhdGlvbl9kYXRhLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIC8vIENyZWF0ZSBpbmZvIHdpbmRvdyBIVE1MXG4gICAgICAgIHZhciBpbmZvV2luZG93SFRNTCA9IGdldEluZm9CdWJibGVUZXh0KGxvY2F0aW9uX2RhdGFbaV1bXCJuYW1lXCJdLCBsb2NhdGlvbl9kYXRhW2ldW1wiYWRkcmVzc1wiXSwgbG9jYXRpb25fZGF0YVtpXVtcInVybFwiXSwgbG9jYXRpb25fZGF0YVtpXVtcInNlbGZfdXJsXCJdKTtcblxuICAgICAgICB2YXIgbGF0TG9uZyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcobG9jYXRpb25fZGF0YVtpXVtcImxhdFwiXSwgbG9jYXRpb25fZGF0YVtpXVtcImxvbmdcIl0pO1xuICAgICAgICB2YXIgaWNvbiA9IGxvY2F0aW9uX2RhdGFbaV1bXCJjdXN0b21fbWFya2VyXCJdO1xuICAgICAgICB2YXIgY2F0ZWdvcmllcyA9IGxvY2F0aW9uX2RhdGFbaV1bXCJjYXRlZ29yaWVzXCJdO1xuXG4gICAgICAgIG1hcmtlcnNbY291bnRlcl1baV0gPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcbiAgICAgICAgICAgIHBvc2l0aW9uOiBsYXRMb25nLFxuICAgICAgICAgICAgY2VudGVyOiBjZW50ZXIsXG4gICAgICAgICAgICBtYXA6IG1hcCxcbiAgICAgICAgICAgIG1hcF9pZDogY291bnRlcixcbiAgICAgICAgICAgIGh0bWw6IGluZm9XaW5kb3dIVE1MLFxuICAgICAgICAgICAgZHJhZ2dhYmxlOiBCb29sZWFuKGlzX2FkbWluKSxcbiAgICAgICAgICAgIGljb246IHR5cGVvZiBpY29uICE9PSAndW5kZWZpbmVkJyAmJiBpY29uIHx8ICcnLFxuICAgICAgICAgICAgY2F0ZWdvcmllczogdHlwZW9mIGNhdGVnb3JpZXMgIT09ICd1bmRlZmluZWQnICYmIGNhdGVnb3JpZXMgfHwgJydcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbWFya2Vyc1tjb3VudGVyXS5sZW5ndGg7IGkrKykge1xuICAgICAgICB2YXIgbWFya2VyID0gbWFya2Vyc1tjb3VudGVyXVtpXTtcblxuICAgICAgICBnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsIFwiY2xpY2tcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgaW5mb1dpbmRvdy5zZXRDb250ZW50KHRoaXMuaHRtbCk7XG4gICAgICAgICAgICBpbmZvV2luZG93Lm9wZW4obWFwLCB0aGlzKTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgZ29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIoaW5mb1dpbmRvdywgJ2Nsb3NlY2xpY2snLCBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBtYXAuc2V0Q2VudGVyKHRoaXMuZ2V0UG9zaXRpb24oKSk7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGdvb2dsZS5tYXBzLmV2ZW50LmFkZExpc3RlbmVyKG1hcmtlciwgJ2RyYWdlbmQnLCBmdW5jdGlvbiAoZXZlbnQpIHtcbiAgICAgICAgICAgIC8vIElmIG9uIGEgc2luZ2xlIGxvY2F0aW9uIHBhZ2UgaW4gYSBtdWx0aXBsZSBsb2NhdGlvbiBzZXR1cC5cbiAgICAgICAgICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW9fY29vcmRpbmF0ZXNfbGF0JykgJiYgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvX2Nvb3JkaW5hdGVzX2xvbmcnKSkge1xuICAgICAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHNlb19jb29yZGluYXRlc19sYXQnKS52YWx1ZSA9IGV2ZW50LmxhdExuZy5sYXQoKTtcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW9fY29vcmRpbmF0ZXNfbG9uZycpLnZhbHVlID0gZXZlbnQubGF0TG5nLmxuZygpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBJZiBvbiB0aGUgWW9hc3QgTG9jYWwgU0VPIHNldHRpbmdzIHBhZ2UsIHVzaW5nIGEgc2luZ2xlIGxvY2F0aW9uLlxuICAgICAgICAgICAgaWYgKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdsb2NhdGlvbl9jb29yZHNfbGF0JykgJiYgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2xvY2F0aW9uX2Nvb3Jkc19sb25nJykpIHtcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbG9jYXRpb25fY29vcmRzX2xhdCcpLnZhbHVlID0gZXZlbnQubGF0TG5nLmxhdCgpO1xuICAgICAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdsb2NhdGlvbl9jb29yZHNfbG9uZycpLnZhbHVlID0gZXZlbnQubGF0TG5nLmxuZygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICAvLyBJZiBtYXJrZXIgY2x1c3RlcmluZyBpcyBzZXQsIHVzZSBpdC5cbiAgICBpZiAobWFya2VyX2NsdXN0ZXJpbmcpIHtcbiAgICAgICAgbmV3IE1hcmtlckNsdXN0ZXJlcihtYXAsIG1hcmtlcnNbY291bnRlcl0sIHsgaW1hZ2VQYXRoOiB3cHNlb19sb2NhbF9kYXRhLm1hcmtlcl9jbHVzdGVyX2ltYWdlX3BhdGggfSk7XG4gICAgfVxuXG4gICAgLy8gSWYgdGhlcmUgaXMgb25seSBvbmUgbWFya2VyIGFuZCB0aGUgaW5mb3dpbmRvdyBzaG91bGQgYmUgc2hvd24sIG1ha2UgaXQgc28uXG4gICAgaWYgKG1hcmtlcnNbY291bnRlcl0ubGVuZ3RoID09IDEgJiYgZGVmYXVsdF9zaG93X2luZm93aW5kb3cpIHtcbiAgICAgICAgaW5mb1dpbmRvdy5zZXRDb250ZW50KG1hcmtlcnNbY291bnRlcl1bMF0uaHRtbCk7XG4gICAgICAgIGluZm9XaW5kb3cub3BlbihtYXAsIG1hcmtlcik7XG4gICAgfVxuXG4gICAgcmV0dXJuIG1hcDtcbn07XG5cbndpbmRvdy5jaGVja0ZvclRvdWNoID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuICEhKG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL0FuZHJvaWQvaSkgfHwgbmF2aWdhdG9yLnVzZXJBZ2VudC5tYXRjaCgvd2ViT1MvaSkgfHwgbmF2aWdhdG9yLnVzZXJBZ2VudC5tYXRjaCgvaVBob25lL2kpIHx8IG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL2lQYWQvaSkgfHwgbmF2aWdhdG9yLnVzZXJBZ2VudC5tYXRjaCgvaVBvZC9pKSB8fCBuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC9CbGFja0JlcnJ5L2kpIHx8IG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL1dpbmRvd3MgUGhvbmUvaSkpO1xufVxuXG53aW5kb3cud3BzZW9fZ2V0X2RpcmVjdGlvbnMgPSBmdW5jdGlvbiAobWFwLCBsb2NhdGlvbl9kYXRhLCBjb3VudGVyLCBzaG93X3JvdXRlKSB7XG4gICAgdmFyIGRpcmVjdGlvbnNEaXNwbGF5ID0gJyc7XG5cbiAgICBpZiAoc2hvd19yb3V0ZSAmJiBsb2NhdGlvbl9kYXRhLmxlbmd0aCA+PSAxKSB7XG4gICAgICAgIGRpcmVjdGlvbnNEaXNwbGF5ID0gbmV3IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNSZW5kZXJlcigpO1xuICAgICAgICBkaXJlY3Rpb25zRGlzcGxheS5zZXRNYXAobWFwKTtcbiAgICAgICAgZGlyZWN0aW9uc0Rpc3BsYXkuc2V0UGFuZWwoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJkaXJlY3Rpb25zXCIgKyAoY291bnRlciAhPSAwID8gJ18nICsgY291bnRlciA6ICcnKSkpO1xuICAgIH1cblxuICAgIHJldHVybiBkaXJlY3Rpb25zRGlzcGxheTtcbn1cblxud2luZG93LmdldEluZm9CdWJibGVUZXh0ID0gZnVuY3Rpb24oYnVzaW5lc3NfbmFtZSwgYnVzaW5lc3NfY2l0eV9hZGRyZXNzLCBidXNpbmVzc191cmwsIHNlbGZfdXJsKSB7XG4gICAgdmFyIGluZm9XaW5kb3dIVE1MID0gJzxkaXYgY2xhc3M9XCJ3cHNlby1pbmZvLXdpbmRvdy13cmFwcGVyXCI+JztcblxuICAgIHZhciBzaG93U2VsZkxpbmsgPSBmYWxzZTtcbiAgICBpZiAoc2VsZl91cmwgIT0gdW5kZWZpbmVkICYmIHdwc2VvX2xvY2FsX2RhdGEuaGFzX211bHRpcGxlX2xvY2F0aW9ucyAhPSAnJyAmJiBzZWxmX3VybCAhPSB3aW5kb3cubG9jYXRpb24uaHJlZikgc2hvd1NlbGZMaW5rID0gdHJ1ZTtcblxuICAgIGlmIChzaG93U2VsZkxpbmspIGluZm9XaW5kb3dIVE1MICs9ICc8YSBocmVmPVwiJyArIHNlbGZfdXJsICsgJ1wiPic7XG4gICAgaW5mb1dpbmRvd0hUTUwgKz0gJzxzdHJvbmc+JyArIGJ1c2luZXNzX25hbWUgKyAnPC9zdHJvbmc+JztcbiAgICBpZiAoc2hvd1NlbGZMaW5rKSBpbmZvV2luZG93SFRNTCArPSAnPC9hPic7XG4gICAgaW5mb1dpbmRvd0hUTUwgKz0gJzxicj4nO1xuICAgIGluZm9XaW5kb3dIVE1MICs9IGJ1c2luZXNzX2NpdHlfYWRkcmVzcztcblxuICAgIGluZm9XaW5kb3dIVE1MICs9ICc8L2Rpdj4nO1xuXG4gICAgcmV0dXJuIGluZm9XaW5kb3dIVE1MO1xufVxuXG53aW5kb3cud3BzZW9fY2FsY3VsYXRlX3JvdXRlID0gZnVuY3Rpb24obWFwLCBkaXJEaXNwbGF5LCBjb29yZHNfbGF0LCBjb29yZHNfbG9uZywgY291bnRlcikge1xuICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW8tc2wtY29vcmRzLWxhdCcpICE9IG51bGwpIGNvb3Jkc19sYXQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW8tc2wtY29vcmRzLWxhdCcpLnZhbHVlO1xuICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW8tc2wtY29vcmRzLWxvbmcnKSAhPSBudWxsKSBjb29yZHNfbG9uZyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHNlby1zbC1jb29yZHMtbG9uZycpLnZhbHVlO1xuXG4gICAgdmFyIHN0YXJ0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJvcmlnaW5cIiArIChjb3VudGVyICE9IDAgPyBcIl9cIiArIGNvdW50ZXIgOiBcIlwiKSkudmFsdWUgKyAnICcgKyB3cHNlb19sb2NhbF9kYXRhLmRlZmF1bHRfY291bnRyeTtcbiAgICB2YXIgdW5pdF9zeXN0ZW0gPSBnb29nbGUubWFwcy5Vbml0U3lzdGVtLk1FVFJJQztcbiAgICBpZiAod3BzZW9fbG9jYWxfZGF0YS51bml0X3N5c3RlbSA9PSAnSU1QRVJJQUwnKSB1bml0X3N5c3RlbSA9IGdvb2dsZS5tYXBzLlVuaXRTeXN0ZW0uSU1QRVJJQUw7XG5cbiAgICAvLyBDbGVhciBhbGwgbWFya2VycyBmcm9tIHRoZSBtYXAsIG9ubHkgc2hvdyBBIGFuZCBCXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCBtYXJrZXJzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIG1hcmtlcnNbaV0uc2V0TWFwKG51bGwpO1xuICAgIH1cblxuICAgIC8vIENoYW5nZSBidXR0b24gdG8gbGluayB0byBHb29nbGUgTWFwcy4gaVBob25lcyBhbmQgQW5kcm9pZCBwaG9uZXMgd2lsbCBhdXRvbWF0aWNhbGx5IG9wZW4gdGhlbSBpbiBNYXBzIGFwcCwgd2hlbiBhdmFpbGFibGUuXG4gICAgaWYgKC9BbmRyb2lkfHdlYk9TfGlQaG9uZXxpUGFkfGlQb2R8QmxhY2tCZXJyeXxJRU1vYmlsZXxPcGVyYSBNaW5pL2kudGVzdChuYXZpZ2F0b3IudXNlckFnZW50KSkge1xuICAgICAgICB2YXIgdXJsID0gJ2h0dHBzOi8vbWFwcy5nb29nbGUuY29tL21hcHM/c2FkZHI9JyArIGVzY2FwZShzdGFydCkgKyAnJmRhZGRyPScgKyBjb29yZHNfbGF0ICsgJywnICsgY29vcmRzX2xvbmc7XG4gICAgICAgIHdpbmRvdy5vcGVuKHVybCwgJ19ibGFuaycpO1xuXG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB2YXIgbGF0bG5nID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhjb29yZHNfbGF0LCBjb29yZHNfbG9uZyk7XG5cbiAgICAgICAgdmFyIHJlcXVlc3QgPSB7XG4gICAgICAgICAgICBvcmlnaW46IHN0YXJ0LFxuICAgICAgICAgICAgZGVzdGluYXRpb246IGxhdGxuZyxcbiAgICAgICAgICAgIHByb3ZpZGVSb3V0ZUFsdGVybmF0aXZlczogdHJ1ZSxcbiAgICAgICAgICAgIG9wdGltaXplV2F5cG9pbnRzOiB0cnVlLFxuICAgICAgICAgICAgdHJhdmVsTW9kZTogZ29vZ2xlLm1hcHMuRGlyZWN0aW9uc1RyYXZlbE1vZGUuRFJJVklORyxcbiAgICAgICAgICAgIHVuaXRTeXN0ZW06IHVuaXRfc3lzdGVtXG4gICAgICAgIH07XG5cbiAgICAgICAgdmFyIGRpcmVjdGlvbnNTZXJ2aWNlID0gbmV3IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNTZXJ2aWNlKCk7XG5cbiAgICAgICAgZGlyZWN0aW9uc1NlcnZpY2Uucm91dGUocmVxdWVzdCwgZnVuY3Rpb24gKHJlc3BvbnNlLCBzdGF0dXMyKSB7XG4gICAgICAgICAgICBpZiAoc3RhdHVzMiA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLk9LKSB7XG4gICAgICAgICAgICAgICAgZGlyRGlzcGxheS5zZXREaXJlY3Rpb25zKHJlc3BvbnNlKTtcbiAgICAgICAgICAgIH0gZWxzZSBpZiAoc3RhdHVzMiA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLlpFUk9fUkVTVUxUUykge1xuICAgICAgICAgICAgICAgIHZhciBub3JvdXRlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvLW5vcm91dGUnKTtcbiAgICAgICAgICAgICAgICBub3JvdXRlLnNldEF0dHJpYnV0ZSgnc3R5bGUnLCAnY2xlYXI6IGJvdGg7IGRpc3BsYXk6IGJsb2NrOycpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG59XG5cbndpbmRvdy53cHNlb19zbF9zaG93X3JvdXRlID0gZnVuY3Rpb24ob2JqLCBjb29yZHNfbGF0LCBjb29yZHNfbG9uZykge1xuICAgICQgPSBqUXVlcnk7XG5cbiAgICAvLyBDcmVhdGUgaGlkZGVuIGlucHV0cyB0byBwYXNzIHRocm91Z2ggdGhlIGxhdC9sb25nIGNvb3JkaW5hdGVzIGZvciB3aGljaCBpcyBuZWVkZWQgZm9yIGNhbGN1bGF0aW5nIHRoZSByb3V0ZS5cbiAgICAkKCcud3BzZW8tc2wtY29vcmRzJykucmVtb3ZlKCk7XG4gICAgdmFyIGlucHV0cyA9ICc8aW5wdXQgdHlwZT1cImhpZGRlblwiIGNsYXNzPVwid3BzZW8tc2wtY29vcmRzXCIgaWQ9XCJ3cHNlby1zbC1jb29yZHMtbGF0XCIgdmFsdWU9XCInICsgY29vcmRzX2xhdCArICdcIj4nO1xuICAgIGlucHV0cyArPSAnPGlucHV0IHR5cGU9XCJoaWRkZW5cIiBjbGFzcz1cIndwc2VvLXNsLWNvb3Jkc1wiIGlkPVwid3BzZW8tc2wtY29vcmRzLWxvbmdcIiB2YWx1ZT1cIicgKyBjb29yZHNfbG9uZyArICdcIj4nO1xuXG4gICAgJCgnI3dwc2VvLWRpcmVjdGlvbnMtZm9ybScpLmFwcGVuZChpbnB1dHMpLnN1Ym1pdCgpO1xuICAgICQoJyN3cHNlby1kaXJlY3Rpb25zLXdyYXBwZXInKS5zbGlkZVVwKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgJCh0aGlzKS5pbnNlcnRBZnRlcigkKG9iaikucGFyZW50cygnLndwc2VvLXJlc3VsdCcpKS5zbGlkZURvd24oKTtcbiAgICB9KTtcbn1cblxud2luZG93LmZpbHRlck1hcmtlcnMgPSBmdW5jdGlvbihjYXRlZ29yeSwgbWFwX2lkKSB7XG4gICAgZm9yIChpID0gMDsgaSA8IG1hcmtlcnNbbWFwX2lkXS5sZW5ndGg7IGkrKykge1xuICAgICAgICBtYXJrZXIgPSBtYXJrZXJzW21hcF9pZF1baV07XG5cbiAgICAgICAgLy8gSWYgaXMgc2FtZSBjYXRlZ29yeSBvciBjYXRlZ29yeSBub3QgcGlja2VkXG4gICAgICAgIGlmIChtYXJrZXIuY2F0ZWdvcmllcy5oYXNPd25Qcm9wZXJ0eShjYXRlZ29yeSkgfHwgY2F0ZWdvcnkubGVuZ3RoID09PSAwKSB7XG4gICAgICAgICAgICBtYXJrZXIuc2V0VmlzaWJsZSh0cnVlKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBDYXRlZ29yaWVzIGRvbid0IG1hdGNoXG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgbWFya2VyLnNldFZpc2libGUoZmFsc2UpO1xuICAgICAgICB9XG4gICAgfVxufVxuIl19
