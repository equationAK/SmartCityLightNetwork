package com.DB;

import java.sql.*;
import java.text.ParseException;
import java.text.SimpleDateFormat;

public class DBQuery {
    private ConnectDB connectDB;

    public DBQuery(ConnectDB connectDB) {
        this.connectDB = connectDB;
    }

    // TODO the query design for the scenarios

    public void insertNewData(String tableName, int poleId, float mq7Value, float mq135Value, float uvValue, float temperatureValue, float humidityValue, float gasValue, String timestampValue) {

        try {
            Connection connection = connectDB.connect();
            String insertSQL = "Insert into " + tableName + " (poleID, mq7Value, mq135Value, uvValue, temperatureValue, humidityValue, gasValue, timestampValue) values(?,?,?,?,?,?,?,?)";
            PreparedStatement preparedStatement = connection.prepareStatement(insertSQL);
            preparedStatement.setInt(1, poleId);
            preparedStatement.setFloat(2, mq7Value);
            preparedStatement.setFloat(3, mq135Value);
            preparedStatement.setFloat(4, uvValue);
            preparedStatement.setFloat(5, temperatureValue);
            preparedStatement.setFloat(6, humidityValue);
            preparedStatement.setFloat(7, gasValue);
            preparedStatement.setString(8, timestampValue);

            ExecuteQuery(tableName, connection, preparedStatement);
            // System.out.println("Done!"); // for BackEnd
        } catch (SQLException sqlException) {
            System.out.println(sqlException.getLocalizedMessage());
        }
    }

    public void insertData(String insertSQL) {

        try {
            Connection connection = connectDB.connect();
            Statement stmt = connection.createStatement();
            stmt.executeUpdate(insertSQL);
            // System.out.println("Done!"); // for BackEnd
        } catch (SQLException sqlException) {
            System.out.println(sqlException.getLocalizedMessage());
        }
    }


    // method to select poleID on Pole table by pole_name

    public int selectSpecificPole(String poleName) {
        Integer poleId = null;
        try {
            Connection connection = connectDB.connect();
            String selectSQL = "SELECT poleID from Pole where pole_name = ?;";
            PreparedStatement preparedStatement = connection.prepareStatement(selectSQL);
            preparedStatement.setString(1, poleName);
            ResultSet rs = preparedStatement.executeQuery();
            rs.next();
            poleId = rs.getInt("poleID");
            preparedStatement.close();
            connection.close();
            return (int) poleId;
        } catch (SQLException throwables) {
            System.out.println(throwables.getLocalizedMessage());
        }
        return (int) poleId;
    }


    public void insertSensorData(String tableName, int poleId, float value, String timestampValue) {

        try {
            Connection connection = connectDB.connect();
            String insertSQL = "Insert into " + tableName + " (poleID, value, timestampValue) values(?,?,?)";
            PreparedStatement preparedStatement = connection.prepareStatement(insertSQL);
            preparedStatement.setInt(1, poleId);
            preparedStatement.setFloat(2, value);
            preparedStatement.setTimestamp(3, Timestamp.valueOf(timestampValue));
            ExecuteQuery(tableName, connection, preparedStatement);
        } catch (SQLException sqlException) {
            System.out.println(sqlException.getLocalizedMessage());
        }
    }

    public void insertAlarmData(String tableName, int poleId, String columnName, String activationTime, int sensorValue) {
        try {
            Connection connection = connectDB.connect();
            String insertSQL = "Insert into " + tableName + " (poleID, timestamp, " + columnName + ") values(?,?,?)";
            PreparedStatement preparedStatement = connection.prepareStatement(insertSQL);
            preparedStatement.setInt(1, poleId);
            preparedStatement.setTimestamp(2, Timestamp.valueOf(convertTimestampFormat(activationTime)));
            preparedStatement.setInt(3, sensorValue);
            ExecuteQuery(tableName, connection, preparedStatement);
        } catch (SQLException sqlException) {
            System.out.println(sqlException.getLocalizedMessage());
        }
    }

    public void insertLightData(String tableName, int poleId, String startTime, String endTime, String timeStamp, int value) {

        try {
            Connection connection = connectDB.connect();
            String insertSQL = "Insert into " + tableName + " (poleID, StartTime, EndTime, timestamp, value) values(?,?,?)";
            PreparedStatement preparedStatement = connection.prepareStatement(insertSQL);
            preparedStatement.setInt(1, poleId);
            preparedStatement.setTimestamp(2, Timestamp.valueOf(startTime));
            preparedStatement.setTimestamp(3, Timestamp.valueOf(endTime));
            preparedStatement.setTimestamp(4, Timestamp.valueOf(timeStamp));
            preparedStatement.setInt(5, value);
            ExecuteQuery(tableName, connection, preparedStatement);
        } catch (SQLException sqlException) {
            System.out.println(sqlException.getLocalizedMessage());
        }
    }

    private void ExecuteQuery(String tableName, Connection connection, PreparedStatement preparedStatement) throws SQLException {
        int count = preparedStatement.executeUpdate();
        if (count > 0) {
            System.out.printf("The %s table was updated\n", tableName);
            System.out.printf("%d row(s) inserted", count);
        } else {
            System.out.println("Something went wrong. Check the exception"); // for BackEnd
        }
        preparedStatement.close();
        connection.close();
    }


    private String convertTimestampFormat(String timestamp) {
        SimpleDateFormat inputFormat = new SimpleDateFormat("dd/MM/yyyy HH:mm:ss");
        SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

        try {
            return outputFormat.format(inputFormat.parse(timestamp));
        } catch (ParseException e) {
            return timestamp;
        }
    }
}



        /*
                =================================================================
                *       The following methods were made during development      *
                *       from the BackEnd team for test and debugging and are    *
                *       kept in case of new features of the MealsApp            *
                =================================================================

     */