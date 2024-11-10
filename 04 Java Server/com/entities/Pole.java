package com.entities;

import java.util.ArrayList;
import java.util.List;

public class Pole {
    private String poleId;
    private List<Sensor> sensorList;
    public Pole(String poleId) {
        this.poleId = poleId;
        sensorList = new ArrayList<>();
    }

    public String getPoleId() {
        return poleId;
    }

    public void setPoleId(String poleId) {
        this.poleId = poleId;
    }

    public List<Sensor> getSensorList() {
        return sensorList;
    }

    public void setSensorList(List<Sensor> sensorList) {
        this.sensorList = sensorList;
    }

    public void addSensorToPole(Sensor sensor) {
        sensorList.add(sensor);
    }
}
