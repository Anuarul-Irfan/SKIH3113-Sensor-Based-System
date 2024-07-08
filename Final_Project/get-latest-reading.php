<?php
include_once('esp-database.php');

$latest_reading = getLastReadings();

if ($latest_reading) {
    echo json_encode($latest_reading);
} else {
    echo json_encode([]);
}
?>