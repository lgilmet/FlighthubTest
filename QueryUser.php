<html>

<body>
    <style>
        body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            display: flex;
            flex-direction: row;
            -ms-flex-align: center;
        }

        form {
            width: 50%;
            margin: auto;
            padding: 20px;
            border: 1px solid black;
        }

        label {
            width: 24%;
            display: inline-block;
        }
    </style>

    <form action="FlightCalculator.php" method="post">
        <h3>Find flights between Montreal, Cornwall and Vancouver</h3>
        <label for="departure">Departure city:</label>
        <select name="departure_airport" id="departure">
            <option disabled  value="0">Choose a departure city</option>
            <option selected value="YUL">Montreal</option>
            <option value="YCC">Cornwall</option>
            <option value="YVR">Vancouver</option>
        </select>
        <br><br>
        <label for="arrival">Destination city:</label>
        <select name="arrival_airport" id="arrival">
            <option disabled selected value="0">Choose a destination city</option>
            <option value="YUL">Montreal</option>
            <option value="YCC">Cornwall</option>
            <option selected value="YVR">Vancouver</option>
        </select>
        <br><br>
        <!-- for now the calculator only works out round trips -->
        <label for="roundTrips">Trip ways:</label>
        <select name="trips" id="trips">
            <option value=2>Two ways</option>
            <option disabled value=1>One way</option>
        </select>
        <br><br>
        <input value="Search for flights" type="submit">
    </form>


</body>

</html>