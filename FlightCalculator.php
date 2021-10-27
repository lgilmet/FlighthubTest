<?php
// $requestData = json_decode(file_get_contents("data/sampleRequest.json"), true);

function indexAirports($sampleData) {
    $airmap = [];
    foreach ($sampleData["airports"] as $airport) {
        array_push($airmap, $airport["code"]);
    }
    
    return $airmap;
}

class Graph {
    function __construct($nodes){
        $this->nodes = $nodes;
        $this->adjacentList = array();
        $this->availableRoutes = array();
        for ($i = 0; $i < $nodes; $i++)
            $this->adjacentList[$i] = array();
    }

    function addPath($from, $to) {
        array_push($this->adjacentList[$from], $to);
    }

    function findAllPaths($departure, $arrival){
        $pathList = array();
        $isVisited = array();
        for ($i = 0; $i < $this->nodes; $i++)
            $isVisited[$i] = false;

        array_push($pathList, $departure);
        $this->findAllPathsUtil($departure, $arrival, $isVisited, $pathList);
        
        return $this->availableRoutes;
    }

    function findAllPathsUtil($departure, $arrival, $isVisited, $paths){

        if ($departure == $arrival)
            array_push($this->availableRoutes, $paths);
        
        // mark node as visited and store in path
        $isVisited[$departure] = true;

        foreach ($this->adjacentList[$departure] as $u) {
            if (!$isVisited[$u]) {
                array_push($paths, $u);

                $this->findAllPathsUtil($u, $arrival, $isVisited, $paths);

                array_pop($paths);
            }
        }

        $isVisited[$departure] = false;
    }
}

function buildTrips($sampleData, $airmap, $flightPaths) {
    $trips = array();

    foreach ($flightPaths as $route) {
        $flights = array();
        for ($i = 0; $i < count($route)-1; $i++) {
            foreach ($sampleData["flights"] as $flight) {
                if ($flight["departure_airport"] == $airmap[$route[$i]]
                    && $flight["arrival_airport"] == $airmap[$route[$i+1]]) {
                    array_push($flights, $flight);
                }
            }
            
            // echo "from ", $airmap[$route[$i]], " to ", $airmap[$route[$i+1]], "<br>";
        }
        array_push($trips, $flights);
    }

    return $trips;
}

function getRoutes() {
    $flightData = json_decode(file_get_contents("data/sampleData.json"), true);
    $airmap = indexAirports($flightData);
    $airportCount = count($flightData["airports"]);

    $g = new Graph($airportCount);
    foreach ($flightData["flights"] as $flight) { 
        $g->addPath(array_search($flight["departure_airport"], $airmap), 
            array_search($flight["arrival_airport"], $airmap));
    }
    $departureFlights = $g->findAllPaths(0, 1);
    
    $h = new Graph($airportCount);
    foreach ($flightData["flights"] as $flight) { 
        $h->addPath(array_search($flight["departure_airport"], $airmap), 
            array_search($flight["arrival_airport"], $airmap));
    }
    $returningFlights = $h->findAllPaths(1, 0);

    $goings = buildTrips($flightData, $airmap, $departureFlights);
    $comings = buildTrips($flightData, $airmap, $returningFlights);
    $roundTrips = array();
    echo count($comings);

    foreach ($goings as $flight1) {
        foreach ($comings as $flight2) {
            array_push($roundTrips, [$flight1, $flight2]);
        }
    }

    $response["trips"] = $roundTrips;

    $fp = fopen('repsonse.json', 'w');
    fwrite($fp, json_encode($response, JSON_PRETTY_PRINT));
    fclose($fp);
}

getRoutes();