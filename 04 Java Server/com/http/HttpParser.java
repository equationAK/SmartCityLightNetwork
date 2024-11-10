package com.http;

import com.DB.ConnectDB;
import com.DB.DBQuery;
import com.DB.configurationDB.TableMapper;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.google.gson.*;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.HashMap;
import java.util.Map;

public class HttpParser {

    private HttpRequest httpRequestTest;

    private final static Logger LOGGER = LoggerFactory.getLogger(HttpParser.class);


    public HttpParser(HttpRequest httpRequestTest) {
        this.httpRequestTest = httpRequestTest;
    }


    // TODO method logic decision
    // TODO RestResponse class to create the response to hardware
    //  every method should return response object;

    private void handlePOST() {

    }

    public void bodyToJsonConvert(String json) {

        // converting the incoming string to json element
        JsonObject dataObject = JsonParser.parseString(json).getAsJsonObject();

        // call handle Data to process the json
        handleData(dataObject);


    }


    private void handleData (JsonObject dataObject){
        if (dataObject == null) {
            return;
        }

        if (dataObject.has("SensorData")) {
            handleSensorData(dataObject.getAsJsonObject("SensorData"));
        } else if (dataObject.has("AlarmData")) {
            handleAlarmData(dataObject.getAsJsonObject("AlarmData"));
        } else if (dataObject.has("LightData")) {
            handleLightData(dataObject.getAsJsonObject("LightData"));
        } else {
            // here to return the correct http response
            System.out.println("Unknown data type.");
        }
    }

    private void handleSensorData(JsonObject sensorDataObject) {
        if (sensorDataObject != null) {
            // Extract poleID and timestamp
            String pole = sensorDataObject.get("poleName").getAsString();
            String timestamp = sensorDataObject.get("timestamp").getAsString();

            // Format the timestamp
            String formattedTimestamp;
            SimpleDateFormat inputFormat = new SimpleDateFormat("dd/MM/yyyy HH:mm:ss");
            SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            try {
                formattedTimestamp = outputFormat.format(inputFormat.parse(timestamp));
            } catch (ParseException e) {
                throw new RuntimeException(e);
            }

            ConnectDB connectDB = ConnectDB.getConnectionInstance();
            DBQuery dbQuery = new DBQuery(connectDB);
            int poleIdentity = dbQuery.selectSpecificPole(pole);

            // Get the configMap from TableMapper
            HashMap<String, String[]> configMap = TableMapper.getInstance().getConfigMap();

            StringBuilder insertPart = new StringBuilder("INSERT INTO sensor_values (poleID");
            StringBuilder valuesPart = new StringBuilder("VALUES (");
            valuesPart.append(poleIdentity);

            // Iterate over sensor values
            JsonObject valuesObject = sensorDataObject.getAsJsonObject("values");
            for (Map.Entry<String, JsonElement> entry : valuesObject.entrySet()) {
                String sensorName = entry.getKey();

                // Check if sensorName exists in configMap
                if (configMap.containsKey(sensorName)) {
                    insertPart.append(",");
                    String columnName = configMap.get(sensorName)[0];
                    insertPart.append(columnName);
                    String sensorValue = entry.getValue().getAsString();
                    switch (configMap.get(sensorName)[1]) {
                        case "String":
                            valuesPart.append(", '").append(sensorValue).append("'");
                            break;
                        default:
                            valuesPart.append(", ").append(sensorValue);
                            break;
                    }
                } else {
                    System.out.println("Sensor Name: " + sensorName + " not found in configMap.");
                }
            }
            insertPart.append(", timestamp)");
            valuesPart.append(", '").append(formattedTimestamp).append("')");
            String insertQuery = insertPart.append(valuesPart).toString();

            // Print the query to be inserted
            System.out.println("=".repeat(30));
            System.out.println("Sensor values from poleId = " + poleIdentity);
            System.out.println(insertQuery);
            System.out.println("=".repeat(30));
            dbQuery.insertData(insertQuery);
        }
    }

    private void handleAlarmData (JsonObject alarmDataObject){
        if (alarmDataObject != null) {

            // Extract poleID and timestamp
            String pole = alarmDataObject.get("poleName").getAsString();
            String timestamp = alarmDataObject.get("timestamp").getAsString();
            System.out.println(timestamp);
            String sensorName = alarmDataObject.get("sensorName").getAsString();
            int sensorValue = alarmDataObject.get("value").getAsInt();

            ConnectDB connectDB = ConnectDB.getConnectionInstance();
            DBQuery dbQuery = new DBQuery(connectDB);
            int poleIdentity = dbQuery.selectSpecificPole(pole);

            String tableName = "sensor_values";

            // Get the configMap from TableMapper
            HashMap<String, String[]> configMap = TableMapper.getInstance().getConfigMap();
            if (configMap.containsKey(sensorName)) {
                String columnName = configMap.get(sensorName)[0];
                dbQuery.insertAlarmData(tableName, poleIdentity, columnName, timestamp, sensorValue);
                System.out.printf("%s inserted in %s\n", sensorName, tableName);
            } else {
                System.out.println("Sensor Name: " + sensorName + " not found in configMap.");
            }
        }
    }

    private void handleLightData (JsonObject lightDataObject){
        if (lightDataObject != null) {

            // Extract poleID and timestamp
            String pole = lightDataObject.get("poleName").getAsString();
            String timestamp = lightDataObject.get("timestamp").getAsString();
            JsonObject valuesObject = lightDataObject.getAsJsonObject("values");

            // Extract values from the valuesObject
            String startTime = valuesObject.get("StartTime").getAsString();
            String endTime = valuesObject.get("EndTime").getAsString();
            int value = valuesObject.get("value").getAsInt();

            ConnectDB connectDB = ConnectDB.getConnectionInstance();
            DBQuery dbQuery = new DBQuery(connectDB);
            int poleIdentity = dbQuery.selectSpecificPole(pole);

            // Get the configMap from TableMapper
            HashMap<String, String[]> configMap = TableMapper.getInstance().getConfigMap();
            if (configMap.containsKey("LightData")) {
                String tableName = configMap.get("LightData")[0];
                dbQuery.insertLightData(tableName, poleIdentity, startTime, endTime, timestamp, value);
                System.out.printf("From %s, light data inserted in %s\n", pole, tableName);
            } else {
                System.out.println("LightData not found in configMap.");
            }
        }
    }
}

    // TODO URL logic decision in GET method

    // TODO version logic decision

    // TODO content type logic

    // TODO response logic
