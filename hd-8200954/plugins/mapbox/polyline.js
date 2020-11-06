var Polyline = {

    py2_round: function(value) {
        // Google's polyline algorithm uses the same rounding strategy as Python 2, which is different from JS for negative values
        return Math.floor(Math.abs(value) + 0.5) * Math.sign(value);
    },

    encodeFunc: function(current, previous, factor) {
        current = Polyline.py2_round(current * factor);
        previous = Polyline.py2_round(previous * factor);
        var coordinate = current - previous;
        coordinate <<= 1;
        if (current - previous < 0) {
            coordinate = ~coordinate;
        }
        var output = '';
        while (coordinate >= 0x20) {
            output += String.fromCharCode((0x20 | (coordinate & 0x1f)) + 63);
            coordinate >>= 5;
        }
        output += String.fromCharCode(coordinate + 63);
        return output;
    },

    /**
     * Decodes to a [latitude, longitude] coordinates array.
     *
     * This is adapted from the implementation in Project-OSRM.
     *
     * @param {String} str
     * @param {Number} precision
     * @returns {Array}
     *
     * @see https://github.com/Project-OSRM/osrm-frontend/blob/master/WebContent/routing/OSRM.RoutingGeometry.js
     */
    decode: function(str, precision) {
        var index = 0,
            lat = 0,
            lng = 0,
            coordinates = [],
            shift = 0,
            result = 0,
            byte = null,
            latitude_change,
            longitude_change,
            factor = Math.pow(10, precision || 5);

        // Coordinates have variable length when encoded, so just keep
        // track of whether we've hit the end of the string. In each
        // loop iteration, a single coordinate is decoded.
        while (index < str.length) {

            // Reset shift, result, and byte
            byte = null;
            shift = 0;
            result = 0;

            do {
                byte = str.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

            shift = result = 0;

            do {
                byte = str.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

            lat += latitude_change;
            lng += longitude_change;

            //coordinates.push([lng / factor, lat / factor]);
            coordinates.push([lat / factor, lng / factor]);
        }

        return coordinates;
    },

    /**
     * Encodes the given [latitude, longitude] coordinates array.
     *
     * @param {Array.<Array.<Number>>} coordinates
     * @param {Number} precision
     * @returns {String}
     */
    encode: function(coordinates, precision) {
        if (!coordinates.length) { return ''; }

        var factor = Math.pow(10, precision || 5),
            output = Polyline.encodeFunc(coordinates[0][0], 0, factor) + Polyline.encodeFunc(coordinates[0][1], 0, factor);

        for (var i = 1; i < coordinates.length; i++) {
            var a = coordinates[i], b = coordinates[i - 1];
            output += Polyline.encodeFunc(a[0], b[0], factor);
            output += Polyline.encodeFunc(a[1], b[1], factor);
        }

        return output;
    },

    flipped: function(coords) {
        var flipped = [];
        for (var i = 0; i < coords.length; i++) {
            flipped.push(coords[i].slice().reverse());
        }
        return flipped;
    },

    /**
     * Encodes a GeoJSON LineString feature/geometry.
     *
     * @param {Object} geojson
     * @param {Number} precision
     * @returns {String}
     */
    fromGeoJSON: function(geojson, precision) {
        if (geojson && geojson.type === 'Feature') {
            geojson = geojson.geometry;
        }
        if (!geojson || geojson.type !== 'LineString') {
            throw new Error('Input must be a GeoJSON LineString');
        }
        return Polyline.encode(Polyline.flipped(geojson.coordinates), precision);
    },

    /**
     * Decodes to a GeoJSON LineString geometry.
     *
     * @param {String} str
     * @param {Number} precision
     * @returns {Object}
     */
    toGeoJSON: function(str, precision) {
        var coords = Polyline.decode(str, precision);
        return {
            type: 'LineString',
            coordinates: Polyline.flipped(coords)
        };
    }

};