/***************************************************************************
Author: Kyriakidis Aris
email: a.kyriakidis@hotmail.com
***************************************************************************/

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>
#include <DHT.h>
#include <SoftwareSerial.h>
#include <ArduinoJson.h>
#include <TimeLib.h>
#include <MQ135.h>
//#include "Adafruit_SI1145.h"
#include "Si1145.h"
#include <virtuabotixRTC.h>


// ****** Pin connections  ******
// LDR and LED

const int lightSensorPin = A0;        // Analog pin for light sensor
const int ledPin = 7;                 // Digital pin for LED

// MQ-7 Carbon Monoxide Sensor
const int AOUT_MQ7pin = A1;           //the AOUT pin of the CO sensor goes into analog pin A1
const int DOUT_MQ7pin = 6;            //the DOUT pin of the CO sensor goes into digital pin D8

// MQ-135 Air quality Sensor

#define PIN_MQ135 A2


// Variables
float temperature = 0.0;              // Variable to store the temperature value
float humidity = 0.0;                 // Variable to store the humidity value
unsigned long switchOnTime = 0;        // Variable to store LED switch on time
unsigned long switchOffTime = 0;       // Variable to store LED switch off time
const String poleId = "arduinoXbee";  // the pole indentification parameter  
String currentTime = "";

int limitMQ7Sensor;
int valueMQ7Sensor;
int alarmMQ7Sensor = 0;


// Light threshold values
const int lightThreshold = 500;       // Adjust this value to set the sensitivity

// DHT11 sensor
#define DHTPIN 3            // Digital pin for DHT11 sensor
#define DHTTYPE DHT11       // DHT sensor type (DHT11)

// Create DHT object
DHT dht(DHTPIN, DHTTYPE);

// Create json object
StaticJsonDocument<128> jsonDocument; // JSON document for storing Alarm data
//DynamicJsonDocument jsonDocument(128); // JSON document for storing Alarm data
//StaticJsonDocument<128> jsonDocThing; // JSON document for storing Alarm data

// Create Software serial object to establish xbee communication
SoftwareSerial xbeeSerial(4, 5); // RX, TX pins for XBee module

// create the UV object
Si1145 uv = Si1145();
//Adafruit_SI1145 uv = Adafruit_SI1145();

// Create MQ135 sensor object
MQ135 mq135_sensor(PIN_MQ135);


// Real clock Module Pin assignement
virtuabotixRTC myRTC(8, 9, 10); //If you change the wiring change the pins here also


// Timing variables for periodic actions
unsigned long previousMillis = 0; // Stores the last time the action was performed
const unsigned long interval = 60000; // Interval for action (1 minute in milliseconds)


String poleName = "arduinoXbee";
String alarmName = "gasAlarm";


void startUvSensor(){
  Serial.println("Adafruit SI1145 test");
    if (! uv.begin()) {
    Serial.println("Didn't find Si1145 !\r\n");
    while (1);
  }

  Serial.println("Si1145 Init success !\r\n");
}

float checkUvSensor(){
  Serial.println("===================");
  Serial.print("Vis: "); Serial.println(uv.readVisible());
  Serial.print("IR: "); Serial.println(uv.readIR());
  
  // Uncomment if you have an IR LED attached to LED pin!
  //Serial.print("Prox: "); Serial.println(uv.readProx());

  float UVindex = uv.readUV();
  // the index is multiplied by 100 so to get the
  // integer index, divide by 100!
  UVindex /= 100.0;

  Serial.print("UV: ");  Serial.println(UVindex);
  return UVindex;
}


void setInitialDayTime(){
  // ONE TIME ONLY EXECUTION 
  // Set the current date, and time in the following format:
  // seconds, minutes, hours, day of the week, day of the month, month, year
  myRTC.setDS1302Time(00, 18, 14, 1, 14, 7, 2024); //Here you write your actual time/date as shown above 
  //but remember to "comment/remove" this function once you're done
  //The setup is done only one time and the module will continue counting it automatically
}


void checkClockModule(){
   // This allows for the update of variables for time or accessing the individual elements.
    myRTC.updateTime();
    
    /* Start printing elements as individuals
    Serial.print("Current Date / Time: ");
    Serial.print(myRTC.dayofmonth); //You can switch between day and month if you're using American system
    Serial.print("/");
    Serial.print(myRTC.month);
    Serial.print("/");
    Serial.print(myRTC.year);
    Serial.print(" ");
    Serial.print(myRTC.hours);
    Serial.print(":");
    Serial.print(myRTC.minutes);
    Serial.print(":");
    Serial.println(myRTC.seconds);
    */
    currentTime = String(myRTC.dayofmonth) + "/" + String(myRTC.month) + "/" + String(myRTC.year) + " " + String(myRTC.hours) + ":" + String(myRTC.minutes) + ":" + String(myRTC.seconds);
}



void printMQ135Values(float rzero, float correctedRZero, float resistance, float ppm, float correctedPPM, float temperature, float humidity){

  Serial.print("MQ135 RZero: ");
  Serial.print(rzero);
  Serial.print("\t Corrected RZero: ");
  Serial.print(correctedRZero);
  Serial.print("\t Resistance: ");
  Serial.print(resistance);
  Serial.print("\t PPM: ");
  Serial.print(ppm);
  Serial.print("\t Corrected PPM: ");
  Serial.print(correctedPPM);
  Serial.println("ppm");
}

void checkLightStatus(){
  // Read the light sensor value
  int lightValue = analogRead(lightSensorPin);

    // Check if it's dark based on the light sensor reading
  if (lightValue < lightThreshold) {
    // It's dark, turn on the LED
    digitalWrite(ledPin, HIGH);
    switchOnTime = millis();            // Record the LED switch on time
  } else {
    // It's light, turn off the LED
    digitalWrite(ledPin, LOW);
    switchOffTime = millis();           // Record the LED switch off time
  }
}

void checkDHT11Status(){
    // Read temperature and humidity from DHT11 sensor
  temperature = dht.readTemperature();
  humidity = dht.readHumidity();
}

void checkMQ7Status(){
  valueMQ7Sensor = analogRead(AOUT_MQ7pin);  //reads the analaog value from the CO sensor's AOUT pin
  limitMQ7Sensor = digitalRead(DOUT_MQ7pin); //reads the digital value from the CO sensor's DOUT pin
  if (valueMQ7Sensor > 200){
    alarmMQ7Sensor = 1;                       //if limit of 200ppm has been reached, the alarm value will become 1
  } else {
    alarmMQ7Sensor = 0;                       //if limit has not been reached, the alarm value will remain 0;
  }
}

String createThingSpeakJson(int ldr, float temperature, float humidity, float valueMQ7Sensor, int alarmMQ7Sensor, float correctedPPM) {
  
  jsonDocument.clear();
  JsonObject sensorDataObj = jsonDocument.createNestedObject("ThingSpeak");
  sensorDataObj["ldr"] = ldr;
  sensorDataObj["temperature"] = temperature;
  sensorDataObj["humidity"] = humidity;
  sensorDataObj["mq7"] = valueMQ7Sensor;
  sensorDataObj["gasAlarm"] = alarmMQ7Sensor;
  sensorDataObj["mq135"] = correctedPPM;
    // Serialize the JSON object to a string
  String jsonString1 = "";
  serializeJson(jsonDocument, jsonString1);

  return jsonString1;
}


String createSensorDataJSON(String poleName, String timestamp, float temperature, float humidity, float valueMQ7Sensor, float correctedPPM,float UVindex,int ldr) {
  //StaticJsonDocument<128> jsonDoc;
  jsonDocument.clear();
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("SensorData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;
  JsonObject valuesObj = sensorDataObj.createNestedObject("values");
  valuesObj["temperature"] = temperature;
  valuesObj["humidity"] = humidity;
  valuesObj["mq7"] = valueMQ7Sensor;
  valuesObj["mq135"] = correctedPPM;
  valuesObj["ldr"] = ldr;
  valuesObj["uv"] = UVindex;
  delay(500);
  // Serialize JSON to string
  String jsonString = "";
  serializeJson(jsonDocument, jsonString);
  return jsonString;
}


String createAlarmJSON(String poleName, String timestamp, String alarmName, int alarmMQ7Sensor) {
  jsonDocument.clear();
  // Create nested objects and set values
  JsonObject sensorDataObj = jsonDocument.createNestedObject("AlarmData");
  sensorDataObj["poleName"] = poleName;
  sensorDataObj["timestamp"] = timestamp;
  sensorDataObj["sensorName"] = alarmName;
  sensorDataObj["value"] = alarmMQ7Sensor;
  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);
  return jsonString;
}

// Function to create and send an alarm JSON to node 02 whenever the gas alarm is activated
void checkAndSendGasAlarm(String poleName, String timestamp, String alarmName) {
  if (alarmMQ7Sensor == 1) {
    // Create JSON object
    String alarmJson = createAlarmJSON(poleName, timestamp, alarmName,alarmMQ7Sensor);
    Serial.println("Gas Alarm JSON Data: ");
    Serial.println(alarmJson);
    // Send the JSON message
    xbeeSerial.println(alarmJson);
    delay(1000);
  } else {
    // Create JSON object
    String alarmJson = createAlarmJSON(poleName, timestamp, alarmName, alarmMQ7Sensor);
    Serial.println("Gas Alarm JSON Data: ");
    Serial.println(alarmJson);
    // Send the JSON message
    xbeeSerial.println(alarmJson);
    delay(1000);
  }
}


  // Function to perform an action every 10 minutes
void performActionEvery10Minutes(String poleName, String currentTime) {
  unsigned long currentMillis = millis();
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    // Action to perform every 10 minutes
    int ldr = analogRead(lightSensorPin);
    float UVindex = checkUvSensor();
    // Get the MQ135 sensor values
    float rzero = mq135_sensor.getRZero();
    float correctedRZero = mq135_sensor.getCorrectedRZero(temperature, humidity);
    float resistance = mq135_sensor.getResistance();
    float ppm = mq135_sensor.getPPM();
    float correctedPPM = mq135_sensor.getCorrectedPPM(temperature, humidity);
    // void printMQ135Values(rzero, correctedRZero, resistance, ppm, correctedPPM, temperature, humidity)

    /*
    // Create JSON object
    String jsonThingSpeak = createThingSpeakJson(ldr, temperature, humidity, valueMQ7Sensor, alarmMQ7Sensor, correctedPPM);
    Serial.println("ThingSpeak Json:");
    Serial.println(jsonThingSpeak);
    // Send from XBee module
    xbeeSerial.println(jsonThingSpeak);
    delay(3000);
    */

    // Create JSON object
    String jsonDataToServer = createSensorDataJSON(poleName, currentTime, temperature, humidity, valueMQ7Sensor, correctedPPM,UVindex,ldr);
    Serial.println("Json Data to Server:");
    Serial.println(jsonDataToServer);
    // Send from XBee module
    xbeeSerial.println(jsonDataToServer);
    delay(3000);
  
  }
}



void setup() {
  pinMode(ledPin, OUTPUT);            // Set LED pin as output
  pinMode(DOUT_MQ7pin, INPUT);        //sets the pin of MQ7 sensor as an input to the arduino
  Serial.begin(9600);                 // Initialize serial communication
  xbeeSerial.begin(9600);
  dht.begin();                        // Initialize DHT11 sensor
  startUvSensor();                    // Initialize UV sensor
}


void loop() {
  
  //setInitialDayTime();


  checkLightStatus();                 // check the environmental light 
  checkDHT11Status();                 // Read temperature and humidity from DHT11 sensor

  //time_t currentTime = now();         // Get current timestamp
  checkClockModule();

  checkMQ7Status();                   // Get MQ7 status

  // check enviromental conditions and sen them to central node
  performActionEvery10Minutes(poleName, currentTime);

  // if alarm is activated send the message
  checkAndSendGasAlarm(poleName,currentTime,alarmName);
  /*
  if (Serial.available()) {
    char data = Serial.read();
    xbeeSerial.write(data);
  }
  if (xbeeSerial.available()) {
    char data = xbeeSerial.read();
    Serial.write(data);
  }
*/
    delay(3000);  // Delay for 3 seconds
}