function dickotomicalMap() {

    var i = {};
    var points = [];
    i.find = function (dist) {
        var low = 0;
        var high = points.length;
        var mid;
        var point;
        do {
            mid = Math.floor((low + high) / 2);
            point = points[mid];
            if (point[0] < dist) {
                low = mid
            } else {
                high = mid
            }
        } while (high - low > 1);

        return [point[1], point[2]];
    };

    i.set = function (pointsToSet) {
        points = pointsToSet;
    };

    i.move = function (dist) {
        positionMarker.features[0].geometry.coordinates = i.find(dist);
        map.getSource('point').setData(positionMarker);
    };

    i.highChartsHover = function () {
        i.move(this.x);
    };

    return i;
}

var timeMap = dickotomicalMap();
var distanceMap = dickotomicalMap();
var movingTimeMap = dickotomicalMap();
