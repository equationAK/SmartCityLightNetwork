package com.DB.configurationDB;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.lang.reflect.Type;
import java.util.HashMap;

public class TableMapper {

    private static TableMapper myTableManager;
    private final String filePath = "src/main/java/com/DB/configurationDB/mapper.json";

    private TableMapper() {}

    // Singleton Pattern with thread safety
    public static synchronized TableMapper getInstance() {
        if (myTableManager == null) {
            myTableManager = new TableMapper();
        }
        return myTableManager;
    }

    public HashMap<String, String[]> getConfigMap() {
        String jsonString = readFile(filePath);

        if (jsonString != null) {
            // Using Gson to parse JSON string into HashMap
            Gson gson = new Gson();
            Type type = new TypeToken<HashMap<String, String[]>>(){}.getType();
            return gson.fromJson(jsonString, type);
        } else {
            System.err.println("Failed to read the JSON from the file.");
            return null;
        }
    }

    // Function to read file content and return as string
    private String readFile(String filePath) {
        StringBuilder content = new StringBuilder();
        try (BufferedReader reader = new BufferedReader(new FileReader(filePath))) {
            String line;
            while ((line = reader.readLine()) != null) {
                content.append(line);
            }
        } catch (IOException e) {
            System.err.println("Error reading file: " + e.getMessage());
            return null;
        }
        return content.toString();
    }
}