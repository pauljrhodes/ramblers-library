<?php

/**
 * Description of gpx
 *
 * @author Chris Vaughan
 */
class RGpxFile {

    public $longitude = 0;
    public $latitude = 0;
    public $distance = 0;
    public $cumulativeElevationGain = 0;
    public $minAltitude = 0;
    public $maxAltitude = 0;
    public $tracks = 0;
    public $routes = 0;
    public $segments = 0;
    public $name = "";
    public $description = "";
    public $date = '';
    public $author = '';
    public $duration = 0;
    //   public $copyright = '';
    private $file = null;
    private $registered = false;

    public function __construct($file) {
        $this->file = $file;
        $this->parse();
    }

    private function parse() {
        if (!$this->registered) {
            JLoader::registerNamespace('phpGPX', "libraries/ramblers/vendors/phpGPX-master2081220/src");
            $this->registered = true;
        }
        $gpxfile = new phpGPX\phpGPX();

        $gpx = $gpxfile->load($this->file);
        $meta = $gpx->metadata;
        if ($meta !== null) {
            if ($meta->name !== null) {
                $this->name = $meta->name;
            }
            if ($meta->description !== null) {
                $this->description = $meta->description;
            }
            if ($meta->author !== null) {
                if ($meta->author->name !== null) {
                    $this->author = $meta->author->name;
                }
            }
            //   if ($meta->copyright !== null) {
            //       $this->copyright = $meta->copyright->year . $meta->copyright->license;
            //   }
            $this->date = "...";
            if (isset($meta->time)) {
                $this->date = $meta->time->format("Y-m-d");
            } 
        }
        // tracks
        $firstpointset = false;
        foreach ($gpx->tracks as $track) {
            //     $s = $track->stats;
            $this->tracks += 1;
//            $this->distance += $s->distance;
//            $this->cumulativeElevationGain += $s->cumulativeElevationGain;
//            $this->minAltitude = $s->minAltitude;
//            $this->maxAltitude = $s->maxAltitude;
            foreach ($track->segments as $segment) {
                $s = $segment->stats;
                $this->distance += $s->distance;
                $this->cumulativeElevationGain += $s->cumulativeElevationGain;
                $this->minAltitude = $s->minAltitude;
                $this->maxAltitude = $s->maxAltitude;
                $this->duration = $s->duration;
                $this->segments += 1;
                foreach ($segment->points as $pt) {
                    if ($firstpointset === false) {
                        $this->longitude = $pt->longitude;
                        $this->latitude = $pt->latitude;
                        $firstpointset = true;
                    }
                }
            }
        }
        // routes

        foreach ($gpx->routes as $route) {
            $this->routes += 1;
            $s = $route->stats;
            $this->distance += $s->distance;
            $this->cumulativeElevationGain += $s->cumulativeElevationGain;
            if (isset($s->minAltitude)) {
                $this->minAltitude = $s->minAltitude;
            }
            if (isset($s->maxAltitude)) {
                $this->maxAltitude = $s->maxAltitude;
            }
        }

        if (count($gpx->routes) > 0) {
            $route = $gpx->routes[0];
            if (count($route->points) > 0) {
                $pt = $route->points[0];
                $this->longitude = $pt->longitude;
                $this->latitude = $pt->latitude;
            }
        }
    }

}
