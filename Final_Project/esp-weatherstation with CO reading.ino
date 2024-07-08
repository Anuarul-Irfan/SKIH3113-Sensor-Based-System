#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"
#include "MQ7.h"
#include <WiFiClient.h>
#include <UrlEncode.h>

// Replace with your network credentials
const char* ssid = "Trailblazer";
const char* password = "01162199220";

// WhatsApp API details
String phoneNumber = "+601162199220";
String apiKey = "3681735";

// Replace with your Domain name and URL path or IP address with path
const char* serverName = "http://192.168.112.24/dhtProject/esp-post-data.php";

// Keep this API Key value to be compatible with the PHP code provided in the project page.
String apiKeyValue = "tPmAT5Ab3j7F9";

String sensorName = "DHT22";
String sensorLocation = "My Room";

#define DHTTYPE DHT22
#define DHTPIN D5
#define MQ7PIN A0  // Assuming the MQ7 sensor is connected to the analog pin A0

DHT dht(DHTPIN, DHTTYPE);
MQ7 mq7(MQ7PIN, 5.0); // Create an instance of the MQ7 class

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Check WiFi connection status
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    // Your Domain name with URL path or IP address with path
    http.begin(client, serverName);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Reading temperature and humidity from DHT22
    float temperature = dht.readTemperature();
    float humidity = dht.readHumidity();
    float coConcentration = mq7.getPPM(); // Get CO concentration from MQ7

    // Check if any reads failed and exit early (to try again).
    if (isnan(temperature) || isnan(humidity)) {
      Serial.println("Failed to read from DHT sensor!");
      return;
    }

    // Prepare HTTP POST request data
    String httpRequestData = "api_key=" + apiKeyValue +
                             "&sensor=" + sensorName +
                             "&location=" + sensorLocation +
                             "&value1=" + String(temperature) +
                             "&value2=" + String(humidity) +
                             "&value3=" + String(coConcentration); // Add CO concentration as value3

    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    // Send HTTP POST request
    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    // Free resources
    http.end();

    // Check conditions and send WhatsApp alerts if needed
    checkConditionsAndSendAlert(temperature, humidity, coConcentration);

  } else {
    Serial.println("WiFi Disconnected");
  }

  // Delay before sending next HTTP POST request
  delay(10000); // 10 seconds delay
}

void checkConditionsAndSendAlert(float temperature, float humidity, float coConcentration) {
  String message = "";

    if (temperature > 30) {
      message += "High temperatures and potential heatwave conditions detected!!!\n";
    }

    if (humidity > 70) {
      message += "High humidity levels, possibly humid weather!!!\n";
    }

    if (coConcentration > 50) {
      message += "Dangerously high CO levels detected. Immediate action required!!!\n";
    }

    if (temperature > 30 && humidity > 70) {
      message += " High temperature and humidity levels detected!!!\n";
    }

    if (temperature > 30 && coConcentration > 50) {
      message += "High temperature and Dangerously high CO levels detected!!!\n";
    }

    if (humidity > 70 && coConcentration > 50) {
      message += "High humidity and Dangerously high CO levels detected!!!\n";
    }

    if (temperature > 30 && humidity > 70 && coConcentration > 50) {
      message += "High temperature, High humidity, and High CO levels detected!!!\n";
    }
    if (message != "") {
    sendMessage(message);
    }
}

void sendMessage(String message){
  // Data to send with HTTP POST
  String url = "http://api.callmebot.com/whatsapp.php?phone=" + phoneNumber + "&apikey=" + apiKey + "&text=" + urlEncode(message);
  WiFiClient client;    
  HTTPClient http;
  http.begin(client, url);

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  // Send HTTP POST request
  int httpResponseCode = http.POST(url);
  if (httpResponseCode == 200){
    Serial.print("Message sent successfully");
  }
  else{
    Serial.println("Error sending the message");
    Serial.print("HTTP response code: ");
    Serial.println(httpResponseCode);
  }

  // Free resources
  http.end();
}
