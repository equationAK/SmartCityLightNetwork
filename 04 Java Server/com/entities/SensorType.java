package com.entities;

public enum SensorType {
    TEMPERATURE(1,"Temperature", "Data_Sensor"),
    NOISE_LEVEL(2,"Noise_Level", "Data_Sensor"),
    HUMIDITY(3,"Humidity", "Data_Sensor"),
    LIQUID_LEVEL(4,"Liquid_Level", "Data_Sensor"),
    LIGHT_LEVEL_LDR(5,"External_Light_Level", "Data_Sensor"),
    DISTANCE(6,"Distance", "Data_Sensor"),
    C0_LEVEL_MQ7(7,"C0_Level", "Data_Sensor"),
    AIR_QUALITY_MQ135(8,"Air_Quality_Sensor", "Data_Sensor"),
    UV_LEVEL(9,"UV_Radiation_Level", "Data_Sensor"),

    RAIN_SENSOR(10,"Rain_Alarm", "Alarm"),
    MOTION_SENSOR(11,"Motion_Alarm", "Alarm"),
    FLAME_SENSOR(12,"Flame_Alarm", "Alarm"),
    GAS_ALARM(13,"Gas_Alarm", "Alarm"),
    LIQUID_LEVEL_ALARM(14,"liquidLevel_Alarm","Alarm");

    private final int sensorId;
    private final String sensorName;
    private final String sensorType;
    private boolean activated;

    SensorType(int sensorId, String sensorName, String sensorType) {
        this.sensorId = sensorId;
        this.sensorName = sensorName;
        this.sensorType = sensorType;
        isAlarm(sensorType);

    }

    public String getSensorName() {
        return sensorName;
    }

    public String getSensorType() {
        return sensorType;
    }

    public int getSensorId() {
        return sensorId;
    }


    public boolean isActivated() {
        return activated;
    }

    public void setActivated(boolean status) {
        activated = status;
    }


    private void isAlarm(String sensorType) {
        // check if the sensor is of alarm type and set the activation status to false
        if(sensorType.equals("Alarm"))
            activated = false;
    }
}
