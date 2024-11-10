package com.entities;

import javax.lang.model.element.Element;
import java.util.Arrays;

public class Sensor {
    private String sensorId;

    private SensorType sensorType;

    private double sensorValue;


    public Sensor(String sensorId) {
        this.sensorId = sensorId;
        for (SensorType sensor : SensorType.values()) {
            if (sensorId.equals(sensor.getSensorId()))
                sensorType = sensor;
        }
    }

    public String getSensorId() {
        return sensorId;
    }

    public void setSensorId(String sensorId) {
        this.sensorId = sensorId;
    }

    public SensorType getSensorType() {
        return sensorType;
    }

    public void setSensorType(SensorType sensorType) {
        this.sensorType = sensorType;
    }

    public double getSensorValue() {
        return sensorValue;
    }

    public void setSensorValue(double sensorValue) {
        this.sensorValue = sensorValue;
    }
}
