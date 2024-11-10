/***************************************************************************
Author: Kyriakidis Aris
email: a.kyriakidis@hotmail.com
***************************************************************************/

#include <SPI.h>
#include <LoRa.h>
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BMP280.h>
#include <ArduinoJson.h>
#include <TimeLib.h>
#include <i2cdetect.h>
#include <virtuabotixRTC.h>
/*
#define BMP_SCK  (13)
#define BMP_MISO (12)
#define BMP_MOSI (11)
#define BMP_CS   (10)
*/
#define BMP280_ADDRESS 0x76  // if address is not 0x76 the sensor won't work

Adafruit_BMP280 bmp; // I2C


// Real clock Module Pin assignement
virtuabotixRTC myRTC(4, 5, 6); //If you change the wiring change the pins here also

// Pin connections
const int lightSensorPin = A0;        // Analog pin for light sensor
const int ledPin = 3;                 // Digital pin for LED
const int soundSensorPin = A2;        // pin connected to pin 2 module sound sensor  

// Variables

String poleName = "arduinoLoRa";
String sensorName = "rain";
//StaticJsonDocument<100> jsonDocumentAlarm;
//StaticJsonDocument<200> jsonDocumentSensor;



//const byte MasterNode = 0xFF;   // Master Node address # raspberry pi 4
const byte Node1 = 0xBB;        // Master Node1 address # arduino uno
const byte Node2 = 0xCC;        // Master Node2 address # pico w
const byte delimiter = 0x7F;    // the frame start delimiter
/*
      ======  This is Node01 -- Arduino Uno -- and has static address 0xBB   ========
      ======  in case that the address has to change then the code to   ========
      ======  all devices has to be modified accordingly                ========
*/

String currentTime = "";

// lowest and highest for rain & fire sensors readings:

const int sensorMin = 0;     // sensor minimum
const int sensorMax = 1024;  // sensor maximum
int rainStatus = 0;          // rain status

// msg counter should be removed in the final version -- for tests only
//int counter = 0;

// timing parameter for transmition sensorData

unsigned long previousMillis = 0; // Variable to store the previous time
const unsigned long interval = 60000;//600000; // Interval in milliseconds (10 minutes)

// Light values
const int lightThreshold = 500;       // Adjust this value to set the sensitivity
int lightValue = 0;                   // Variable to store the light sensor value


// BMP280 sensor return values
float temperature = 0.0;
float pressure = 0.0;

// Sound sensor decibel level
float noiseDB = 0.0;
const int sampleWindow = 50;          // Sample window width in mS (50 mS = 20Hz)
unsigned int sample;


void sendMessage(const String& message, byte recipient, byte sender) {
  byte frameData[256]; // Adjust the buffer size as needed
  int frameDataLength = 0;

  // Construct the frame

  // Add the start delimiter to the frame
  frameData[frameDataLength++] = delimiter; 

  // Add the destination address to the frame
  frameData[frameDataLength++] = recipient; // using one of the addresses

  // Add the sender address to the frame
  frameData[frameDataLength++] = sender; // using one of the addresses

  // Convert the message to bytes
  int messageLength = message.length();
  for (int i = 0; i < messageLength; i++) {
    frameData[frameDataLength++] = message.charAt(i);
  }

  // Calculate and add the checksum
  byte checksum = 0;
  for (int i = 0; i < frameDataLength; i++) {
    checksum += frameData[i];
    //Serial.println(checksum,HEX);  // for debug
  }
  frameData[frameDataLength++] = checksum;

  // Send the LoRa frame
  LoRa.beginPacket();
  LoRa.write(frameData, frameDataLength);
  LoRa.endPacket();
}

void checkBMP280(){
    temperature = bmp.readTemperature();
    pressure = bmp.readPressure();
}

int checkRainStatus(){

	int sensorReading = analogRead(A3);
	int range = map(sensorReading, sensorMin, sensorMax, 0, 3);
  switch (range) {
 case 0:    // Sensor getting wet
    Serial.println("Flood");
    return 2;
    break;
 case 1:    // Sensor getting wet
    Serial.println("Rain Warning");
    return 1;
    break;
 case 2:    // Sensor dry - To shut this up delete the " Serial.println("Not Raining"); " below.
    //Serial.println("Not Raining");
    return 0;
    break;
  }
}


void setInitialDayTime(){
  // ONE TIME ONLY EXECUTION 
  // Set the current date, and time in the following format:
  // seconds, minutes, hours, day of the week, day of the month, month, year
  myRTC.setDS1302Time(00, 02, 12, 5, 12, 7, 2024); //Here you write your actual time/date as shown above 
  //but remember to "comment/remove" this function once you're done
  //The setup is done only one time and the module will continue counting it automatically
}


void checkClockModule(){
   // This allows for the update of variables for time or accessing the individual elements.
    myRTC.updateTime();
    currentTime = String(myRTC.dayofmonth) + "/" + String(myRTC.month) + "/" + String(myRTC.year) + " " + String(myRTC.hours) + ":" + String(myRTC.minutes) + ":" + String(myRTC.seconds);
}


void sendJsonToNode02(String jsonString){
            // Print the JSON string to the serial monitor
            Serial.println("JSON Data: ");
            Serial.println(jsonString);
            // from here start the transmition of the json
            sendMessage(jsonString, Node2, Node1);
}

void checkLightStatus(){
  lightValue = analogRead(lightSensorPin);      // Read the light sensor value
  if (lightValue < lightThreshold) {            // Check if it's dark based on the light sensor reading
    digitalWrite(ledPin, HIGH);                 // It's dark, turn on the LED
    //switchOnTime = millis();                    // Record the LED switch on time
  } else {                                      // It's light, turn off the LED
    digitalWrite(ledPin, LOW);
    //switchOffTime = millis();                   // Record the LED switch off time
  }
}

void checkSoundStatus(){

  unsigned long startMillis= millis();                   // Start of sample window
  float peakToPeak = 0;                                  // peak-to-peak level
  unsigned int signalMax = 0;                            //minimum value
  unsigned int signalMin = 1024;                         //maximum value
  // collect data for 50 mS
  while (millis() - startMillis < sampleWindow) {
    sample = analogRead(soundSensorPin);                //get reading from microphone
    if (sample < 1024) {                                // toss out spurious readings
        if (sample > signalMax) {
          signalMax = sample;                           // save just the max levels
        } else if (sample < signalMin) {
          signalMin = sample;                           // save just the min levels
        }
    }
  }
  peakToPeak = signalMax - signalMin;                    // max - min = peak-peak amplitude
  noiseDB = map(peakToPeak,20,900,49.5,90);               //calibrate for deciBels  
  //Serial.print("Sound Level: ");
  //Serial.println(noiseDB);
}


void createSensorDataJSON(String poleName, String timestamp, float temperature, float pressure, float noiseDB) {
  StaticJsonDocument<128> jsonDocument;
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("SensorData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;

  JsonObject valuesObj = sensorDataObj.createNestedObject("values");
  valuesObj["temperature"] = temperature;
  valuesObj["pressure"] = pressure;
  valuesObj["noiseDB"] = noiseDB;
  delay(500);

  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);
  sendJsonToNode02(jsonString);
  
}

void createAlarmJSON(String poleName, String timestamp, String sensorName) {
  StaticJsonDocument<100>  jsonDocument;
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("AlarmData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;
  sensorDataObj["sensorName"] = sensorName;
  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);
  delay(200);
  sendJsonToNode02(jsonString);
  delay(200);
}

void setup() {
  Serial.begin(9600);                           // initialize serial communication @ 9600 baud:
  while (!Serial);
  pinMode(ledPin, OUTPUT);                      // Set LED pin as output
  //setInitialDayTime();                        // only for the first time
  pinMode (soundSensorPin, INPUT); // Set the signal pin as input 
  Serial.println(F("BMP280 test"));
  unsigned status;
  status = bmp.begin(BMP280_ADDRESS);
  if (!status) {
      Serial.println(F("Could not find a valid BMP280 sensor, check wiring or "
                        "try a different address!"));
      Serial.print("SensorID was: 0x"); Serial.println(bmp.sensorID(),16);
      Serial.print("        ID of 0xFF probably means a bad address, a BMP 180 or BMP 085\n");
      Serial.print("   ID of 0x56-0x58 represents a BMP 280,\n");
      Serial.print("        ID of 0x60 represents a BME 280.\n");
      Serial.print("        ID of 0x61 represents a BME 680.\n");
      while (1) delay(10);
  }

      /* Default settings from datasheet. */
  bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,     /* Operating Mode. */
                  Adafruit_BMP280::SAMPLING_X2,     /* Temp. oversampling */
                  Adafruit_BMP280::SAMPLING_X16,    /* Pressure oversampling */
                  Adafruit_BMP280::FILTER_X16,      /* Filtering. */
                  Adafruit_BMP280::STANDBY_MS_500); /* Standby time. */
  
  Serial.println("LoRa Sender");
  if (!LoRa.begin(868E6)) { // Set your desired frequency (868 MHz in this example)
    Serial.println("Starting LoRa failed!");
    while (1);
  }
}


void loop() {

  //setInitialDayTime();
  checkClockModule();                 // get the current time
  checkLightStatus();                 // check the environmental light
  checkBMP280();                      // check temperature and pressure
  checkSoundStatus();                 // get the decibel level

  // transmit the sensorData every 10 minutes
  unsigned long currentMillis = millis(); // Get current time

  // Check if 10 minutes have elapsed since the last execution
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis; // Update the previous time
    // Call createSensorDataJSON method with desired parameters
    createSensorDataJSON(poleName,currentTime,temperature,pressure,noiseDB); // create json to send
    delay(200);
  }

  rainStatus = checkRainStatus();     // check rain status implent here the alarm send method for rain
  if (rainStatus != 0) {
    // Call createAlarmJSON method with desired parameters
    createAlarmJSON(poleName, currentTime, sensorName); // create json to send
    delay(200);
  }            
}