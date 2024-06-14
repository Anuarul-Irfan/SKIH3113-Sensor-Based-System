<?php
$servername = "localhost";
$dbname = "anuardht";
$username = "root";
$password = "Anuarul12345";

// Function to insert sensor data into the database
function insertReading($sensor, $location, $value1, $value2, $value3) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "INSERT INTO SensorData (sensor, location, value1, value2, value3)
            VALUES ('" . $sensor . "', '" . $location . "', '" . $value1 . "', '" . $value2 . "', '" . $value3 . "')";

    if ($conn->query($sql) === TRUE) {
        return "New record created successfully";
    } else {
        return "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

// Function to retrieve all sensor readings
function getAllReadings($limit) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "SELECT id, sensor, location, value1, value2, value3, reading_time FROM SensorData ORDER BY reading_time DESC LIMIT " . $limit;
    $result = $conn->query($sql);

    if ($result) {
        return $result;
    } else {
        return false;
    }

    $conn->close();
}

// Function to retrieve the last sensor reading
function getLastReadings() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "SELECT id, sensor, location, value1, value2, value3, reading_time FROM SensorData ORDER BY reading_time DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return false;
    }

    $conn->close();
}

// Function to retrieve minimum sensor reading
function minReading($limit, $value) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "SELECT MIN(" . $value . ") AS min_amount FROM (SELECT " . $value . " FROM SensorData ORDER BY reading_time DESC LIMIT " . $limit . ") AS min";
    $result = $conn->query($sql);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return false;
    }

    $conn->close();
}

// Function to retrieve maximum sensor reading
function maxReading($limit, $value) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "SELECT MAX(" . $value . ") AS max_amount FROM (SELECT " . $value . " FROM SensorData ORDER BY reading_time DESC LIMIT " . $limit . ") AS max";
    $result = $conn->query($sql);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return false;
    }

    $conn->close();
}

// Function to retrieve average sensor reading
function avgReading($limit, $value) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to database!<br>";
    }

    $sql = "SELECT AVG(" . $value . ") AS avg_amount FROM (SELECT " . $value . " FROM SensorData ORDER BY reading_time DESC LIMIT " . $limit . ") AS avg";
    $result = $conn->query($sql);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return false;
    }

    $conn->close();
}
?>
