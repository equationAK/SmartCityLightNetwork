package com.httpserver;


import com.DB.ConnectDB;
import com.DB.DBQuery;
import com.DB.configurationDB.Config;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.httpserver.config.Configuration;
import com.httpserver.core.ServerListenerThread;
import com.httpserver.config.ConfigurationManager;
import org.slf4j.*;

import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class HttpServer {

    private final static Logger LOGGER = LoggerFactory.getLogger(HttpServer.class);


    public static void main(String[] args) {
        LOGGER.info("Server starting....");

        ConfigurationManager.getInstance().loadConfigurationFile("src/main/resources/http.json");
        Configuration configuration = ConfigurationManager.getInstance().getCurrentConfiguration();

        LOGGER.info(STR."Using Port: \{configuration.getPort()}");
        LOGGER.info(STR."Using WebRoot: \{configuration.getWebRoot()}");


        try {
            ServerListenerThread serverListenerThread = new ServerListenerThread(configuration.getPort(), configuration.getWebRoot());
            serverListenerThread.start();
        } catch (IOException e) {
            e.printStackTrace();
            // TODO handle exception
        }


        //bodyToJsonConvert();
        //startConnectionDB();
    }
}
    /*
    // TODO modify the DB connection better
    public static void startConnectionDB() {
        String connectionString = "jdbc:mysql://".concat(Config.ADDRESS).concat(":").concat(String.valueOf(Config.PORT)).concat("/testGetPost");  // this should change to the final DB name
        try {
            Connection connection = DriverManager.getConnection(connectionString, Config.USER, Config.PASSWORD);
            ConnectDB connectDB = ConnectDB.getConnectionInstance();
            connectDB.connect();
            if (connection != null)
                System.out.println("connected to DB....");

        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
    }

    public static void bodyToJsonConvert() {

        // Test string
        // TODO implementation for the HTTP post requests
        String test = "{\"data\":[{\"mq7\": 43, \"mq135\": 7.573481, \"uv\": 0.02, \"temperature\": 20.6, \"humidity\": 60, \"ldr\": 624, \"flame\": 0, \"timestamp\": 'January 08, 2024 12:12', \"gas\": 0, \"poleId\": \"arduinoXbee\"}, {\"mq7\": 43, \"mq135\": 7.573481, \"uv\": 0.02, \"temperature\": 20.6, \"humidity\": 60, \"ldr\": 624, \"flame\": 0, \"timestamp\": 'January 08, 2024 13:13', \"gas\": 0, \"poleId\": \"picoXbee\"}]}";

        // converting the incoming string to json element
        JsonElement jsonElement = JsonParser.parseString(test);
        // converting the jsonElement to jsonObject to start the parsing procedure
        JsonObject jsonObject = jsonElement.getAsJsonObject();

        JsonArray jsonArray = jsonObject.getAsJsonArray("data");
        // Map to store the data of each pole by key, json data structure
        Map<String, JsonElement> jsonElementMap = new HashMap<>();
        // loop data to separate them
        jsonArray.forEach(jsonElement1-> {
            JsonObject jsonObject1 =  jsonElement1.getAsJsonObject();
            String poleId = jsonObject1.remove("poleId").getAsString();
            jsonElementMap.put(poleId, jsonObject1);
        });

        jsonElementMap.forEach((poleId, json) -> {
            String tableName = "sensor_values";

            float mq7Value = json.getAsJsonObject().remove("mq7").getAsFloat();
            float mq135Value = json.getAsJsonObject().remove("mq135").getAsFloat();
            float uvValue = json.getAsJsonObject().remove("uv").getAsFloat();
            float temperatureValue = json.getAsJsonObject().remove("temperature").getAsFloat();
            float humidityValue = json.getAsJsonObject().remove("humidity").getAsFloat();
            float gasValue = json.getAsJsonObject().remove("gas").getAsFloat();
            String timestampValue = json.getAsJsonObject().remove("timestamp").getAsString();
            String pole = poleId.toString();

            ConnectDB connectDB = ConnectDB.getConnectionInstance();
            DBQuery dbQuery = new DBQuery(connectDB);
            int poleIdentity = dbQuery.selectSpecificPole(pole);
            dbQuery.insertNewData(tableName, poleIdentity, mq7Value,mq135Value,uvValue,temperatureValue,humidityValue,gasValue,timestampValue);
        });
    }
}

     */
