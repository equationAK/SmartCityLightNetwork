/***************************************************************************
Author: Kyriakidis Aris
email: a.kyriakidis@hotmail.com
***************************************************************************/


#include <SPI.h>
#include <LoRa.h>
#include <WiFi.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>



#define nss 8
#define rst 9
#define dio0 7

const char* ssid = "";
const char* password = "";
String poleName = "picoLoRa";
String alarmName = "floodAlarm";
int lastAlarmState = 0;

// the temperature value from tmp36 sensor
float temperature = 0.0;

unsigned long lastSensorDataToServer = millis();


// the REST Server variables

const char* server_ip = "";
const int server_port = 3360;

const byte MasterNode = 0xFF;   // Master Node address # raspberry pi 4
const byte Node1 = 0xBB;        // Master Node1 address # arduino uno
const byte Node2 = 0xCC;        // Master Node2 address # pico w
/*
      ======  This is Node02 -- Pico W -- and has static address 0xCC   ========
      ======  in case that the address has to change then the code to   ========
      ======  all devices has to be modified accordingly                ========
*/
const byte delimiter = 0x7F;    // the frame start delimiter

String receivedMessage = "";

// Pin configuration for LDR and LED
const int ldrPin = 28;  // Analog pin for LDR
const int ledPin = 2;   // Digital pin for LED

// Pin configuration for water sensor
const int waterSensorPin = 27; // Digital pin for water sensor

// Pin configuration for PIR motion sensor
const int pirPin = 22;            // Digital pin for PIR motion sensor

// Adjust this threshold according to your ambient light conditions
const int threshold = 400; 


// Define NTP Client to get time
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP);

// Define the energy saving variables

bool isLedOn = false;
unsigned long ledActivationTime = 0;
bool isEnergySavingMode = false;


// Create json object
DynamicJsonDocument jsonDocument(254); // JSON document for storing data

// variables to store flags for manageEnergySaving()
unsigned long previousMotionTime = millis(); // Variable to store the previous motion detection time
bool isMotionDetected = false; // Flag to indicate if motion is currently detected

// Analog pin connected to the TMP36 sensor on Raspberry Pi Pico
const int tmp36Pin = 26; 



void setup() {
  Serial.begin(9600);

  pinMode(ledPin, OUTPUT);
  pinMode(waterSensorPin, INPUT_PULLUP); // Assuming the water sensor provides LOW signal when water is detected
  pinMode(pirPin, INPUT);                // PIR motion sensor pin

  while (!Serial);

  LoRa.setPins(nss, rst, dio0);
  Serial.println("LoRa Receiver");
  while (!LoRa.begin(868E6)) {    // Set your desired frequency (868 MHz in this example)
    Serial.println("Starting LoRa failed!");
    while (1);
  }

  Serial.println("LoRa init succeeded.");

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("WiFi connected");
  
  // Initialize NTP client
  timeClient.begin();
  timeClient.setTimeOffset(7200); // Adjust this offset according to your timezone (in seconds)
}

String receiveMessage(byte recipient, byte sender) {
  byte frameData[256]; // Adjust the buffer size as needed
  int frameDataLength = 0;

  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    while (LoRa.available()) {
      if (delimiter == LoRa.read()) {
        // Add the start delimiter to the frame
        frameData[frameDataLength++] = delimiter;
        byte receivedRecipient = LoRa.read();
        // Add the recipient to the frame
        frameData[frameDataLength++] = receivedRecipient;
        byte receivedSender = LoRa.read();
        // Add the sender to the frame
        frameData[frameDataLength++] = receivedSender;


        if (receivedRecipient == recipient && receivedSender == sender) {

          while (LoRa.available()) {
            frameData[frameDataLength++] = LoRa.read();
          }

          // the checksum from sender
          byte checksum = frameData[frameDataLength - 1];
          //Serial.println(checksum,HEX);  // for debug

          // Verify the checksum
          byte calculatedChecksum = 0;
          for (int i = 0; i < frameDataLength - 1; i++) {
            calculatedChecksum += frameData[i];
            //Serial.println(calculatedChecksum,HEX);  // for debug
          }

          if (calculatedChecksum == checksum) {
            receivedMessage = "";
            for (int i = 3; i < frameDataLength - 1; i++) {
              char receivedChar = (char)frameData[i];
              receivedMessage += receivedChar;
            }
            return receivedMessage;
            //Serial.print("[");
            //printTime();
            //Serial.print("] Received message: '");
            //Serial.print(receivedMessage);
            //Serial.print("' from Node ");
            //Serial.println(receivedSender);
          } else {
            Serial.println("Checksum error. Message ignored.");
            return ""; // Return empty string if checksum error
          }
        } else {
          Serial.println("Unknown recipient. Message ignored.");
          return ""; // Return empty string if checksum error
        }
      }
    }
  }
  return ""; // Return empty string if checksum error
}


// Function to print current time with timezone adjustment for Athens, Greece (Summer Time)
String printTime() {
  timeClient.update(); // Update time from NTP server

  // Get current time from NTP client
  time_t rawTime = timeClient.getEpochTime();
  struct tm *timeinfo;
  timeinfo = gmtime(&rawTime); // Get time in UTC

  // Adjust for Athens, Greece (Summer Time)
  timeinfo->tm_hour += 1; // Add 1 hour for Athens timezone
  timeinfo->tm_isdst = 1;  // Set Daylight Saving Time (DST) to true

  // Convert adjusted time to epoch
  time_t adjustedTime = mktime(timeinfo);

  // Create a string to hold the formatted time
  char buffer[20]; // Allocate space for the formatted time
  strftime(buffer, sizeof(buffer), "%d/%m/%Y %H:%M:%S", timeinfo); // Format the time
  return String(buffer); // Convert the formatted time to a String and return it
}



// Function to check water sensor and sent alarm message to server if water is detected
int checkWaterSensor() {
  if (digitalRead(waterSensorPin)) {
    return 1;
  } else {
    //Serial.print("[ ");
    //printTime();
    //Serial.print(" ]");
    //Serial.print(" - Everything cool!");
    //Serial.println();
    return 0;
  }
}

String createAlarmJSON(String poleName, String timestamp, String alarmName,int value) {

  StaticJsonDocument<200> jsonDocument;
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("AlarmData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;
  sensorDataObj["sensorName"] = alarmName;
  sensorDataObj["value"] = value;
  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);
  jsonDocument.clear();
  return jsonString;
}


String createSensorDataJSON(String poleName, String timestamp, float temperature) {

  StaticJsonDocument<200> jsonDocument;
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("SensorData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;

  JsonObject valuesObj = sensorDataObj.createNestedObject("values");
  valuesObj["temperature"] = temperature;

  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);
  jsonDocument.clear();
  return jsonString;
}

void sendSensorDataToServer() {
  unsigned long currentMillis = millis();
  unsigned long interval = 600000; // 10 minutes in milliseconds
  String timestamp = printTime();

  // Check if 10 minutes have elapsed since the last sensor data was sent
  if (currentMillis - lastSensorDataToServer >= interval) {
    // Update the last sent time to the current time
    lastSensorDataToServer = currentMillis;

    // Call the createSensorDataJSON method to create the JSON string
    String json_data = createSensorDataJSON(poleName, timestamp, temperature); // Assuming you have this method to create sensor data JSON
    // print the json to the server
    Serial.println("Sensor data to send to the server:");
    Serial.println(json_data);
    sendJsonToServer(json_data);
  }
}

void measureTemperature() {
  // Read the raw analog voltage from the TMP36 sensor
  int sensorValue = analogRead(tmp36Pin);

  // Convert the analog value to voltage (in millivolts)
  float voltage = sensorValue * (3300.0 / 1023.0); // 3300 mV (3.3V) is the Raspberry Pi Pico reference voltage

  // Convert the voltage to temperature in Celsius
  temperature = (voltage - 500.0) / 10.0;
}

// Function to manage energy saving mode

void manageEnergySaving() {
  int ldrValue = analogRead(ldrPin); // Read LDR value
  
  if (ldrValue < threshold) {
    // If LDR value is less than threshold, set LED brightness to 25%
    analogWrite(ledPin, 64); // 25% duty cycle for brightness
  } else {
    // If LDR value is greater than or equal to threshold, turn off the LED
    analogWrite(ledPin, 0); // Turn off LED
    isMotionDetected = false; // Reset motion detected flag
    return; // Exit the function early as there's no need to continue if LDR value is high
  }

  if (digitalRead(pirPin)) {
    // If motion is detected
    if (!isMotionDetected) {
      // If motion was not previously detected, record the current time
      previousMotionTime = millis();
      isMotionDetected = true; // Set motion detected flag
    }
    // Check if 1 minute has elapsed since motion was detected
    if (millis() - previousMotionTime >= 60000) {
      // If 2 minutes have passed, set LED brightness back to 25%
      analogWrite(ledPin, 64); // Set LED brightness back to 25%
      isMotionDetected = false; // Reset motion detected flag
    } else {
      // If less than 2 minutes have passed, set LED brightness to 100%
      analogWrite(ledPin, 255); // Set LED brightness to 100% (full brightness)
    }
  }
}

void sendJsonToServer(String json_data) {

    // Specify the server URL with a concrete endpoint
    String server_url = "http://" + String(server_ip) + ":" + String(server_port) + "/";

    // Set up HTTP headers
    HTTPClient http;
    http.begin(server_url);
    http.addHeader("Content-Type", "application/json");

    // Make the HTTP POST request
    int httpResponseCode = http.POST(json_data);

    // Print the HTTP response code
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);

    // Print the server's response (optional)
    String response = http.getString();
    Serial.println("Server Response:");
    Serial.println(response);

    // End the request
    http.end();

}

void loop() {

  // Receive messages from Node1
  String receivedMsg = receiveMessage(Node2, Node1); 
  Serial.print(receivedMsg);



  // Check if the received message is not empty
  if (receivedMsg.length() > 0) {
    sendJsonToServer(receivedMsg); // Send the received message to the server
  }

  // Check water sensor and send message to server if water is detected
  int currentState = checkWaterSensor();

  if (lastAlarmState != currentState) {
    String timestamp = printTime();
    String json = createAlarmJSON(poleName, timestamp, alarmName,currentState);
    sendJsonToServer(json);
    lastAlarmState = currentState;
  }

  // measure Temperature and store it to global variable temrerature
  measureTemperature();
  
  // Manage energy saving mode
  manageEnergySaving();

  // Check sensor and send message every 10 minutes to server
  sendSensorDataToServer();

}