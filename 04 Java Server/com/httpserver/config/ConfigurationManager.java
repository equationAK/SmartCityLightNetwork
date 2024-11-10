package com.httpserver.config;

import com.httpserver.util.Json;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.JsonNode;

import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;

public class ConfigurationManager {

    private static ConfigurationManager myConfigurationManager;
    private static Configuration myCurrentConfiguration;

    private ConfigurationManager() {}

    // Singleton Pattern to get only one instance
    public static ConfigurationManager getInstance() {
        if(myConfigurationManager == null) {
            myConfigurationManager = new ConfigurationManager();
        }
        return myConfigurationManager;
    }

    // is used to load the configuration file by the provided path

    public void loadConfigurationFile (String filePath) {
        FileReader fileReader = null;
        try {
            fileReader = new FileReader(filePath);
        } catch (FileNotFoundException e) {
            throw new HttpConfigurationException(e);
        }
        StringBuffer stringBuffer = new StringBuffer();

        int i;
        try {
            while ( (i = fileReader.read()) != -1)
                stringBuffer.append((char) i);
        }catch (IOException e) {
                throw new HttpConfigurationException(e);
            }

        JsonNode configuration = null;
        try {
            configuration = Json.parse(stringBuffer.toString());
        } catch (IOException e) {
            throw new HttpConfigurationException("Error parsing the Configuration File.");
        }
        try {
            myCurrentConfiguration = Json.fromJson(configuration, Configuration.class);
        } catch (JsonProcessingException e) {
            throw new HttpConfigurationException("Error parsing the configuration file, internal", e);
        }
    }


    // returns the current loaded configuration
    public Configuration getCurrentConfiguration() {
        if(myCurrentConfiguration == null)
            throw new HttpConfigurationException("No current Configuration set!");
        return myCurrentConfiguration;
    }
}
