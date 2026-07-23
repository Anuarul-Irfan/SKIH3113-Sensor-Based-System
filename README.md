# SKIH3113 Sensor-Based System

This repository contains a collection of sensor-based system projects developed for the SKIH3113 course. It includes both coursework assignments and a final integrated IoT weather-monitoring project based on an ESP8266 microcontroller, PHP backend, and MySQL database.

## Project Overview

The main objective of this project is to collect environmental data such as temperature, humidity, and carbon monoxide concentration using sensors and transmit the readings to a web-based dashboard. The system demonstrates the flow of:

1. Sensor data acquisition from hardware
2. Data transmission from ESP8266 to a PHP endpoint
3. Data storage in MySQL
4. Visualization and analysis through a web dashboard

## Main Components

### Hardware
- ESP8266 / NodeMCU
- DHT22 temperature and humidity sensor
- MQ7 gas sensor for carbon monoxide monitoring
- Wi-Fi connection for IoT data upload

### Software / Stack
- Arduino IDE for ESP8266 firmware
- PHP for web API and server-side processing
- MySQL for sensor data storage
- HTML/CSS/JavaScript for the dashboard UI

## Repository Structure

- `Assignment 1: Temperature and Humidity Sensor`  
  Basic temperature and humidity sensor task.

- `Assignment 2: Esp8266 with EEPROM`  
  ESP8266 task involving EEPROM-based data handling.

- `Mid-Term: DHT22_Temperature&Humidity`  
  Mid-term implementation related to DHT22 sensing and data logging.

- `Final_Project/esp-weatherstation with CO reading.ino`  
  Main Arduino sketch for the ESP8266 weather station with MQ7 CO reading.

- `Final_Project/esp-post-data.php`  
  Receives POST requests from the ESP8266 and inserts sensor readings into the database.

- `Final_Project/esp-database.php`  
  Contains the database connection settings and SQL helper functions.

- `Final_Project/esp-weather-station.php`  
  Web dashboard that retrieves and visualizes recent sensor readings.

- `Final_Project/get-latest-reading.php`  
  Returns the most recent sensor reading in JSON format.

- `Final_Project/esp-style.css`  
  Styling for the dashboard interface.

- `Final_Project/MySQL Query`  
  SQL statements used for database setup and data retrieval.

- `*.fzz` files  
  Circuit design files for the sensor and ESP8266 wiring diagrams.

## Features

- Real-time sensor monitoring through ESP8266
- Temperature, humidity, and CO measurement support
- PHP-based API endpoint for sensor data insertion
- MySQL database integration
- Web dashboard with trend/summary analysis
- Support for reading multiple historical sensor samples

## Hardware Setup

Connect the following components:

- DHT22 to the ESP8266 digital input pin
- MQ7 to the analog input pin
- ESP8266 to a Wi-Fi network with internet access

Make sure the sensor pins and server endpoint in the firmware match your actual physical wiring and local network configuration.

## Software Setup

1. Install the Arduino IDE.
2. Configure the ESP8266 board package.
3. Install required libraries for:
   - ESP8266 Wi-Fi
   - HTTP client
   - DHT22 sensor
   - MQ7 sensor
4. Set up a PHP + MySQL server.
5. Import the database schema and SQL queries from the project files.
6. Update the Wi-Fi credentials, server URL, and database connection details in the source files.

## Usage

1. Upload the Arduino sketch to the ESP8266.
2. Start the PHP server environment.
3. Ensure MySQL is running and the database is configured.
4. Open the web dashboard page to view live and historical readings.

## Notes

- The project uses hardcoded credentials and sample network/server details in the source code; these should be replaced with your own secure configuration before deployment.
- The repository reflects coursework and prototype implementation, so some files may be adapted for demonstration purposes.

## License

This project is intended for academic and educational use.
