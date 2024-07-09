<?php
// Include the 'esp-database.php' file which likely contains database configurations and functions.
include_once('esp-database.php');

// Check if 'readingsCount' parameter is set in the GET request.
if (isset($_GET["readingsCount"])) {
    // Sanitize and store the 'readingsCount' parameter.
    $data = $_GET["readingsCount"];
    $data = trim($data);  // Trim whitespace
    $data = stripslashes($data);  // Remove backslashes
    $data = htmlspecialchars($data);  // Convert special characters to HTML entities
    $readings_count = $data;  // Assign sanitized value to $readings_count
} else {
    $readings_count = 20; // Default readings count set to 20 if 'readingsCount' is not provided
}

// Fetch the last sensor readings from the database.
$last_reading = getLastReadings();
$last_reading_temp = $last_reading["value1"];
$last_reading_humi = $last_reading["value2"];
$last_reading_pres = $last_reading["value3"];
$last_reading_time = $last_reading["reading_time"];

// Calculate minimum, maximum, and average readings for temperature, humidity, and pressure.
$min_temp = minReading($readings_count, 'value1');
$max_temp = maxReading($readings_count, 'value1');
$avg_temp = avgReading($readings_count, 'value1');

$min_humi = minReading($readings_count, 'value2');
$max_humi = maxReading($readings_count, 'value2');
$avg_humi = avgReading($readings_count, 'value2');

$min_pres = minReading($readings_count, 'value3');
$max_pres = maxReading($readings_count, 'value3');
$avg_pres = avgReading($readings_count, 'value3');

// Fetch historical sensor data for the specified number of readings.
$historical_data = getAllReadings($readings_count);
$temp_data = [];
$humi_data = [];
$pres_data = [];
$timestamps = [];

// Process historical data if available.
if ($historical_data) {
    while ($row = $historical_data->fetch_assoc()) {
        // Convert reading timestamp to milliseconds for JavaScript compatibility.
        $timestamp = strtotime($row["reading_time"]) * 1000;
        $timestamps[] = $timestamp;
        $temp_data[] = [(float)$timestamp, (float)$row["value1"]];
        $humi_data[] = [(float)$timestamp, (float)$row["value2"]];
        $pres_data[] = [(float)$timestamp, (float)$row["value3"]];
    }
}

// Free the result set.
$historical_data->free();

// Function to generate insights based on measurement type, minimum, maximum, and average values.
function getInsight($measurement, $min, $max, $avg) {
    switch ($measurement) {
        case 'Temperature':
            if ($avg > 25 && $max > 30) {
                return "High temperatures and potential heatwave conditions detected.";
            } elseif ($avg < 20 && $min < 0) {
                return "Low temperatures indicate cold weather.";
            } else {
                return "Normal temperature conditions observed.";
            }
            break;
            
        case 'Humidity':
            if ($avg > 70) {
                return "High humidity levels, possibly humid weather.";
            } elseif ($min < 50) {
                return "Low humidity levels, drier weather.";
            } else {
                return "Moderate humidity conditions observed.";
            }
            break;
            
        case 'CO':
            if ($avg > 50) {
                return "Dangerously high CO levels detected. Immediate action required.";
            } elseif ($avg > 10) {
                return "Moderate CO levels detected. Monitor for prolonged exposure.";
            } else {
                return "Low CO levels detected. Air quality is safe.";
            }
            break;
            
        default:
            return "Insight not available.";
    }
}
?>
<!DOCTYPE html>
<html>
<style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #444;
        }

        /* Table Styles */
        .summary {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 20px auto;
        }

        .styled-table {
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 1em;
            min-width: 600px;
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .styled-table thead tr {
            background-color: #3498db;
            color: #ffffff;
            text-align: left;
            font-weight: bold;
        }

        .styled-table th,
        .styled-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #3498db;
        }

        .styled-table tbody tr.active-row {
            font-weight: bold;
            color: #3498db;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .styled-table {
                min-width: 100%;
                width: 100%;
            }

            .styled-table th,
            .styled-table td {
                padding: 10px 15px;
            }
        }
    </style>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="esp-style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>
<body>
<header class="header">
    <h1>📊 ESP Weather Station</h1>
    <form method="get">
        <input type="number" name="readingsCount" min="1" placeholder="Number of readings (<?php echo $readings_count; ?>)">
        <input type="submit" value="UPDATE">
    </form>
</header>
<div id="chart-temperature" class="container"></div>
<div id="chart-humidity" class="container"></div>
<div id="chart-pressure" class="container"></div>
<p>Last reading: <?php echo $last_reading_time; ?></p>
 <section class="summary">
        <h2>Summary of Readings</h2>
        <table cellspacing="5" cellpadding="5" id="summaryTable" class="styled-table">
            <thead>
                <tr>
                    <th>Measurement</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Average</th>
                    <th>Insight</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Temperature</td>
                    <td><?php echo $min_temp['min_amount']; ?> &deg;C</td>
                    <td><?php echo $max_temp['max_amount']; ?> &deg;C</td>
                    <td><?php echo round($avg_temp['avg_amount'], 2); ?> &deg;C</td>
                    <td><?php echo getInsight('Temperature', $min_temp['min_amount'], $max_temp['max_amount'], $avg_temp['avg_amount']); ?></td>
                </tr>
                <tr>
                    <td>Humidity</td>
                    <td><?php echo $min_humi['min_amount']; ?> %</td>
                    <td><?php echo $max_humi['max_amount']; ?> %</td>
                    <td><?php echo round($avg_humi['avg_amount'], 2); ?> %</td>
                    <td><?php echo getInsight('Humidity', $min_humi['min_amount'], $max_humi['max_amount'], $avg_humi['avg_amount']); ?></td>
                </tr>
                <tr>
                    <td>CO</td>
                    <td><?php echo $min_pres['min_amount']; ?> ppm</td>
                    <td><?php echo $max_pres['max_amount']; ?> ppm</td>
                    <td><?php echo round($avg_pres['avg_amount'], 2); ?> ppm</td>
                    <td><?php echo getInsight('CO', $min_pres['min_amount'], $max_pres['max_amount'], $avg_pres['avg_amount']); ?></td>
                </tr>
            </tbody>
        </table>
    </section>




<section class="content">
    <div class="box gauge--1">
        <h3>TEMPERATURE</h3>
        <div class="mask">
            <div class="semi-circle"></div>
            <div class="semi-circle--mask"></div>
        </div>
        <p style="font-size: 30px;" id="temp">--</p>
        <table cellspacing="5" cellpadding="5">
            <tr>
                <th colspan="3">Temperature <?php echo $readings_count; ?> readings</th>
            </tr>
            <tr>
                <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo isset($min_temp['min_amount']) ? $min_temp['min_amount'] . ' &deg;C' : 'N/A'; ?></td>
                <td><?php echo isset($max_temp['max_amount']) ? $max_temp['max_amount'] . ' &deg;C' : 'N/A'; ?></td>
                <td><?php echo isset($avg_temp['avg_amount']) ? round($avg_temp['avg_amount'], 2) . ' &deg;C' : 'N/A'; ?></td>
            </tr>
        </table>
    </div>
    <div class="box gauge--2">
        <h3>HUMIDITY</h3>
        <div class="mask">
            <div class="semi-circle"></div>
            <div class="semi-circle--mask"></div>
        </div>
        <p style="font-size: 30px;" id="humi">--</p>
        <table cellspacing="5" cellpadding="5">
            <tr>
                <th colspan="3">Humidity <?php echo $readings_count; ?> readings</th>
            </tr>
            <tr>
                <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo isset($min_humi['min_amount']) ? $min_humi['min_amount'] . ' %' : 'N/A'; ?></td>
                <td><?php echo isset($max_humi['max_amount']) ? $max_humi['max_amount'] . ' %' : 'N/A'; ?></td>
                <td><?php echo isset($avg_humi['avg_amount']) ? round($avg_humi['avg_amount'], 2) . ' %' : 'N/A'; ?></td>
            </tr>
        </table>
    </div>
	<div class="box gauge--3">
        <h3>CO</h3>
        <div class="mask">
            <div class="semi-circle"></div>
            <div class="semi-circle--mask"></div>
        </div>
        <p style="font-size: 30px;" id="co">--</p>
        <table cellspacing="5" cellpadding="5">
            <tr>
                <th colspan="3">CO <?php echo $readings_count; ?> readings</th>
            </tr>
            <tr>
                <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo isset($min_pres['min_amount']) ? $min_pres['min_amount'] . ' ppm' : 'N/A'; ?></td>
                <td><?php echo isset($max_pres['max_amount']) ? $max_pres['max_amount'] . ' ppm' : 'N/A'; ?></td>
                <td><?php echo isset($avg_pres['avg_amount']) ? round($avg_pres['avg_amount'], 2) . ' ppm' : 'N/A'; ?></td>
            </tr>
        </table>
    </div>
</section>
<?php
echo '<h2>View Latest ' . $readings_count . ' Readings</h2>
        <table cellspacing="5" cellpadding="5" id="tableReadings">
            <tr>
                <th>ID</th>
                <th>Sensor</th>
                <th>Location</th>
                <th>TEMPERATURE (Celsius)</th>
                <th>HUMIDITY (%)</th>
                <th>CO reading(PPM)</th>
                <th>Timestamp</th>
            </tr>';
$result = getAllReadings($readings_count);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $row["id"] . '</td>
                <td>' . $row["sensor"] . '</td>
                <td>' . $row["location"] . '</td>
                <td>' . $row["value1"] . '</td>
                <td>' . $row["value2"] . '</td>
                <td>' . $row["value3"] . '</td>
                <td>' . $row["reading_time"] . '</td>
              </tr>';
    }
    echo '</table>';
    $result->free();
}
?>
<script>
    var tempData = <?php echo json_encode($temp_data); ?>;
    var humiData = <?php echo json_encode($humi_data); ?>;
    var presData = <?php echo json_encode($pres_data); ?>;
    var timestamps = <?php echo json_encode($timestamps); ?>;

    var chartT, chartH, chartP; // Declare variables for charts

	function initTemperatureChart() {
		chartT = new Highcharts.Chart({
			chart: { renderTo: 'chart-temperature' },
			title: { text: 'Temperature DHT22' },
			series: [{ name: 'Temperature', data: tempData, color: '#ff0e0e' }],
			xAxis: { 
				type: 'datetime',
				labels: {
					format: '{value:%H:%M:%S}'
				}
			},
			yAxis: { title: { text: 'Temperature (°C)' } }
		});
	}

	function initHumidityChart() {
		chartH = new Highcharts.Chart({
			chart: { renderTo: 'chart-humidity' },
			title: { text: 'Humidity DHT22' },
			series: [{ name: 'Humidity', data: humiData }],
			xAxis: { 
				type: 'datetime',
				labels: {
					format: '{value:%H:%M:%S}'
				}
			},
			yAxis: { title: { text: 'Humidity (%)' } }
		});
	}

	function initPressureChart() {
		chartP = new Highcharts.Chart({
			chart: { renderTo: 'chart-pressure' },
			title: { text: 'CO ppm MQ7' },
			series: [{ name: 'CO ppm', data: presData, color: '#f3ff0e' }],
			xAxis: { 
				type: 'datetime',
				labels: {
					format: '{value:%H:%M:%S}'
				}
			},
			yAxis: { title: { text: 'Parts Per Million (ppm)' } }
		});
	}

    // Initialize charts when the page loads
    document.addEventListener('DOMContentLoaded', function () {
        initTemperatureChart();
        initHumidityChart();
        initPressureChart();
        setTemperature(<?php echo $last_reading_temp; ?>);
        setHumidity(<?php echo $last_reading_humi; ?>);
        setPressure(<?php echo $last_reading_pres; ?>);
    });

    function setTemperature(curVal){
        var minTemp = -5.0;
        var maxTemp = 38.0;
        var newVal = scaleValue(curVal, [minTemp, maxTemp], [0, 180]);
        document.querySelector('.gauge--1 .semi-circle--mask').style.transform = 'rotate(' + newVal + 'deg)';
        document.getElementById("temp").textContent = curVal + ' ºC';
    }

    function setHumidity(curVal){
        var minHumi = 0;
        var maxHumi = 100;
        var newVal = scaleValue(curVal, [minHumi, maxHumi], [0, 180]);
        document.querySelector('.gauge--2 .semi-circle--mask').style.transform = 'rotate(' + newVal + 'deg)';
        document.getElementById("humi").textContent = curVal + ' %';
    }

    function setPressure(curVal){
        var minPres = 0.0;
        var maxPres = 50.0;
        var newVal = scaleValue(curVal, [minPres, maxPres], [0, 180]);
        document.querySelector('.gauge--3 .semi-circle--mask').style.transform = 'rotate(' + newVal + 'deg)';
        document.getElementById("co").textContent = curVal + ' PPM';
    }

    function scaleValue(value, from, to) {
        var scale = (to[1] - to[0]) / (from[1] - from[0]);
        var capped = Math.min(from[1], Math.max(from[0], value)) - from[0];
        return Math.floor(capped * scale + to[0]);
    }
	
	 // Function to fetch the latest readings
        function fetchLatestReadings() {
            $.ajax({
                url: 'get-latest-reading.php',
                method: 'GET',
                success: function(data) {
                    var reading = JSON.parse(data);
                    updateReadingsTable(reading);
                }
            });
        }

        // Function to update the latest readings table
        function updateReadingsTable(reading) {
            var table = document.getElementById("tableReadings");
            var newRow = table.insertRow(1); // Insert new row at index 1 (just below headers)

            // Create cells for the new row
            var idCell = newRow.insertCell(0);
            var sensorCell = newRow.insertCell(1);
            var locationCell = newRow.insertCell(2);
            var tempCell = newRow.insertCell(3);
            var humiCell = newRow.insertCell(4);
            var coCell = newRow.insertCell(5);
            var timestampCell = newRow.insertCell(6);

            // Populate cells with data
            idCell.textContent = reading.id;
            sensorCell.textContent = reading.sensor;
            locationCell.textContent = reading.location;
            tempCell.textContent = reading.value1 + ' °C';
            humiCell.textContent = reading.value2 + ' %';
            coCell.textContent = reading.value3 + ' PPM';
            timestampCell.textContent = reading.reading_time;
        }

        // Fetch the latest readings every 5 seconds
        setInterval(fetchLatestReadings, 5000);

        // Initialize the page with the latest readings
        fetchLatestReadings();
</script>
</body>
</html>
