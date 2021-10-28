<?php

function getFormValues()
{
    $inputs = array();
    $inputs["departure_airport"] = $_POST["departure_airport"];
    $inputs["arrival_airport"] = $_POST["arrival_airport"];
    $inputs["trips"] = $_POST["trips"];

    return $inputs;
}

function queryFlightData()
{
    return json_decode(file_get_contents("data/workingData.json"), true);
}

function indexAirports($sampleData)
{
    $airmap = [];
    foreach ($sampleData["airports"] as $airport) {
        array_push($airmap, $airport["code"]);
    }

    return $airmap;
}

class Graph
{
    function __construct($nodes)
    {
        $this->nodes = $nodes;
        $this->adjacentList = array();
        $this->availableRoutes = array();
        for ($i = 0; $i < $nodes; $i++)
            $this->adjacentList[$i] = array();
    }

    function addPath($from, $to)
    {
        array_push($this->adjacentList[$from], $to);
    }

    function findAllPaths($departure, $arrival)
    {
        $pathList = array();
        $isVisited = array();
        for ($i = 0; $i < $this->nodes; $i++)
            $isVisited[$i] = false;

        array_push($pathList, $departure);
        $this->findAllPathsUtil($departure, $arrival, $isVisited, $pathList);

        return $this->availableRoutes;
    }

    function findAllPathsUtil($departure, $arrival, $isVisited, $paths)
    {

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

function buildTrips($sampleData, $airmap, $flightPaths)
{
    $trips = array();

    foreach ($flightPaths as $route) {
        $flights = array();
        for ($i = 0; $i < count($route) - 1; $i++) {
            foreach ($sampleData["flights"] as $flight) {
                if (
                    $flight["departure_airport"] == $airmap[$route[$i]]
                    && $flight["arrival_airport"] == $airmap[$route[$i + 1]]
                ) {
                    array_push($flights, $flight);
                }
            }
        }
        array_push($trips, $flights);
    }

    return $trips;
}

function planFlights($goings, $comings, $departureDate)
{
    // plan round trips by combining a trip to go to the destination 
    // with a trip to come back to original localtion

    $roundTrips = array();

    foreach ($goings as $flight1) {
        foreach ($comings as $flight2) {
            $tripPrice = 0;
            foreach ($flight1 as $trips1) {
                $tripPrice = $tripPrice + $trips1["price"];
                $departureTime1 = $trips1['departure_time'];
                // here I am trying to change the time ot datetime but it does not work
                $trips1["departure_datetime"] = "$departureDate $departureTime1";
            }
            foreach ($flight2 as $trips2) {
                $tripPrice = $tripPrice + $trips2["price"];
                $departureTime2 = $trips2['departure_time'];
                $trips2["departure_datetime"] = "$departureDate $departureTime2";
            }

            $trip["price"] = $tripPrice;
            $trip["flights"] = [$flight1, $flight2];

            array_push($roundTrips, $trip);
        }
    }

    return $roundTrips;
}

function getRoutes()
{
    $flightData = queryFlightData();
    $airmap = indexAirports($flightData);
    $airportCount = count($flightData["airports"]);
    $inputs = getFormValues();
    $departureDate = "2021-10-28";

    // initiate object to calculate the first trip
    $g = new Graph($airportCount);
    foreach ($flightData["flights"] as $flight) {
        $g->addPath(
            array_search($flight["departure_airport"], $airmap),
            array_search($flight["arrival_airport"], $airmap)
        );
    }
    $departureFlights = $g->findAllPaths(
        array_search($inputs["departure_airport"], $airmap),
        array_search($inputs["arrival_airport"], $airmap)
    );

    // initiate object to calculate second trip
    $h = new Graph($airportCount);
    foreach ($flightData["flights"] as $flight) {
        $h->addPath(
            array_search($flight["departure_airport"], $airmap),
            array_search($flight["arrival_airport"], $airmap)
        );
    }
    $returningFlights = $h->findAllPaths(
        array_search($inputs["arrival_airport"], $airmap),
        array_search($inputs["departure_airport"], $airmap)
    );

    $goings = buildTrips($flightData, $airmap, $departureFlights);
    $comings = buildTrips($flightData, $airmap, $returningFlights);

    // calculate prices
    $flightPlan = planFlights($goings, $comings, $departureDate);
    $response["response"]["trips"] = $flightPlan;

    $fp = fopen('response.json', 'w');
    fwrite($fp, json_encode($response, JSON_PRETTY_PRINT));
    fclose($fp);

    echo "Response data found at FlighthubTest/data/response.json";
}

getRoutes();
