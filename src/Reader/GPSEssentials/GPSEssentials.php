<?php

namespace GeoTool\Reader\GPSEssentials;

use Yaoi\Database;

class GPSEssentials
{
    /** @var Database */
    private $db;

    public function __construct($pathToSqliteDatabase)
    {
        $this->db = new Database('sqlite:///' . $pathToSqliteDatabase);
    }


    public function getTracks()
    {


    }

    const RADIUS = 6371000; // Meters

    public static function distance(TrackElement $from, TrackElement $to)
    {
        if ($from->latitude === $to->latitude && $from->longitude === $to->longitude) {
            return 0;
        }

        $lat1 = pi() * $from->latitude / 180;
        $lat2 = pi() * $to->latitude / 180;
        $long1 = pi() * $from->longitude / 180;
        $long2 = pi() * $to->longitude / 180;

        return self::RADIUS * acos(
            sin($lat1) * sin($lat2)
            + cos($lat1) * cos($lat2) * cos($long2 - $long1)
        );
    }

    public static function rawDistance($fromLat, $fromLon, $toLat, $toLon)
    {
        $lat1 = pi() * $fromLat / 180;
        $lat2 = pi() * $toLat / 180;
        $long1 = pi() * $fromLon / 180;
        $long2 = pi() * $toLon / 180;

        return self::RADIUS * acos(
            sin($lat1) * sin($lat2)
            + cos($lat1) * cos($lat2) * cos($long2 - $long1)
        );
    }

    public function calc()
    {
        /*
         * LUA
local EARTH_RAD = 6378137.0
  -- earth's radius in meters (official geoid datum, not 20,000km / pi)

local radmiles = EARTH_RAD*100.0/2.54/12.0/5280.0;
  -- earth's radius in miles

local multipliers = {
  radians = 1, miles = radmiles, mi = radmiles, feet = radmiles * 5280,
  meters = EARTH_RAD, m = EARTH_RAD, km = EARTH_RAD / 1000,
  degrees = 360 / (2 * math.pi), min = 60 * 360 / (2 * math.pi)
}

function gcdist(pt1, pt2, units) -- return distance in radians or given units
  --- this formula works best for points close together or antipodal
  --- rounding error strikes when distance is one-quarter Earth's circumference
  --- (ref: wikipedia Great-circle distance)
  if not pt1.radians then pt1 = rad(pt1) end
  if not pt2.radians then pt2 = rad(pt2) end
  local sdlat = sin((pt1.lat - pt2.lat) / 2.0);
  local sdlon = sin((pt1.lon - pt2.lon) / 2.0);
  local res = sqrt(sdlat * sdlat + cos(pt1.lat) * cos(pt2.lat) * sdlon * sdlon);
  res = res > 1 and 1 or res < -1 and -1 or res
  res = 2 * asin(res);
  if units then return res * assert(multipliers[units])
  else return res
  end
end
         */

        /*
        var R = 6371; // km
        var dLat = (lat2-lat1).toRad();
        var dLon = (lon2-lon1).toRad();
        var lat1 = lat1.toRad();
        var lat2 = lat2.toRad();

        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c;
        */

        /*
        Here it is in C# (lat and long in radians):

        double CalculateGreatCircleDistance(double lat1, double long1, double lat2, double long2, double radius)
        {
            return radius * Math.Acos(
                Math.Sin(lat1) * Math.Sin(lat2)
                + Math.Cos(lat1) * Math.Cos(lat2) * Math.Cos(long2 - long1));
        }

        If your lat and long are in degrees then divide by 180/PI to convert to radians.
        */
    }

}